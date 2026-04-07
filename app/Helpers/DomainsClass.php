<?php
namespace App\Helpers;

use App\Module\Registrar;

use App\Helpers\Cfg;
use App\Helpers\LogActivity;
use App\Helpers\Hooks;
use App\Helpers\Application;

use App\Models\Domain;
use App\Models\DomainpricingPremium;
use App\Models\DomainsExtra;
use App\Models\Hosting;
use App\Models\Server;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Module\NotServicable;
use Carbon\Carbon;

class DomainsClass
{
    private $id = 0;
    private $data = [];
    private $domainModel = null;
    private $moduleresults = [];
    private $domainInformation = null;
    private $registrarModule = null;

    public function splitAndCleanDomainInput($domain)
    {
        $domain = trim($domain);
        $domain = rtrim($domain, '/');
        $domain = preg_replace('/^https?:\/\//', '', $domain);

        if (strpos($domain, ".") !== false) {
            $domain = $this->stripOutSubdomains($domain);
            $domainparts = explode(".", $domain, 2);
            $sld = $domainparts[0];
            $tld = isset($domainparts[1]) ? "." . $domainparts[1] : "";
        } else {
            $sld = $domain;
            $tld = "";
        }

        $sld = $this->clean($sld);
        $tld = $this->clean($tld);

        return ["sld" => $sld, "tld" => $tld];
    }

    protected function stripOutSubdomains($domain)
    {
        return preg_replace("/^www\\./", "", $domain);
    }

    public function clean($val)
    {
        $val = trim($val);
        if (!Cfg::get("AllowIDNDomains")) {
            $val = strtolower($val);
        } elseif (function_exists("mb_strtolower")) {
            $val = mb_strtolower($val);
        }
        return $val;
    }

    public function checkDomainisValid($parts)
    {
        global $CONFIG;
        $sld = $parts["sld"];
        $tld = $parts["tld"];

        if ($sld[0] == "-" || $sld[strlen($sld) - 1] == "-") {
            return 0;
        }

        $isIdn = $isIdnTld = $skipAllowIDNDomains = false;
        if ($CONFIG["AllowIDNDomains"]) {
            $idnConvert = new \App\Helpers\Domain\Idna();
            $idnConvert->encode($sld);
            if ($idnConvert->get_last_error() && $idnConvert->get_last_error() != "The given string does not contain encodable chars") {
                return 0;
            }
            if ($idnConvert->get_last_error() == "The given string does not contain encodable chars") {
                $skipAllowIDNDomains = true;
            } else {
                $isIdn = true;
            }
        }

        if (!$isIdn) {
            if (preg_replace("/[^.%\$^'#~@&*(),_£?!+=:{}[]()|\\/ \\\\ ]/", "", $sld)) {
                return 0;
            }
            if ((!$CONFIG["AllowIDNDomains"] || $skipAllowIDNDomains) && preg_replace("/[^a-z0-9-.]/i", "", $sld . $tld) != $sld . $tld) {
                return 0;
            }
            if (preg_replace("/[^a-z0-9-.]/", "", $tld) != $tld) {
                return 0;
            }
            $validMask = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-";
            if (strspn($sld, $validMask) != strlen($sld)) {
                return 0;
            }
        }

        Hooks::run_hook("DomainValidation", ["sld" => $sld, "tld" => $tld]);

        if ($sld === false && $sld !== 0 || !$tld) {
            return 0;
        }

        $coreTLDs = [".com", ".net", ".org", ".info", "biz", ".mobi", ".name", ".asia", ".tel", ".in", ".mn", ".bz", ".cc", ".tv", ".us", ".me", ".co.uk", ".me.uk", ".org.uk", ".net.uk", ".ch", ".li", ".de", ".jp"];
        $DomainMinLengthRestrictions = $DomainMaxLengthRestrictions = [];

        foreach ($coreTLDs as $cTLD) {
            $DomainMinLengthRestrictions[$cTLD] = $DomainMinLengthRestrictions[$cTLD] ?? 3;
            $DomainMaxLengthRestrictions[$cTLD] = $DomainMaxLengthRestrictions[$cTLD] ?? 63;
        }

        if (array_key_exists($tld, $DomainMinLengthRestrictions) && strlen($sld) < $DomainMinLengthRestrictions[$tld]) {
            return 0;
        }
        if (array_key_exists($tld, $DomainMaxLengthRestrictions) && $DomainMaxLengthRestrictions[$tld] < strlen($sld)) {
            return 0;
        }

        return 1;
    }

    public function getDomainsDatabyID($domainid)
    {
        $auth = Auth::guard('web')->user();
        $where = ["id" => (int)$domainid];

        if (defined("CLIENTAREA") || Application::isClientAreaRequest()) {
            if (!$auth) {
                return false;
            }
            $where["userid"] = $auth->id;
        }

        return $this->getDomainsData($where);
    }

    private function getDomainsData(array $where)
    {
        try {
            $domain = Domain::findOrFail($where["id"]);
            if (array_key_exists("userid", $where) && $domain->clientId != $where["userid"]) {
                throw new NotServicable("Invalid Access Attempt");
            }
            $this->id = $domain->id;
            $domainData = $domain->toArray();
            $this->data = $domainData;
            $this->domainModel = $domain;
            return $this->data;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isActive()
    {
        return is_array($this->data) && $this->data["status"] == DomainStatus::ACTIVE;
    }

    public function isPending()
    {
        return is_array($this->data) && $this->data["status"] == DomainStatus::PENDING;
    }

    public function getData($var)
    {
        return $this->data[$var] ?? "";
    }

    public function getModule()
    {
        return $this->getData("registrar");
    }

    public function hasFunction($function)
    {
        static $mod = null;
        if (!$mod) {
            $mod = new Registrar();
            $mod->load($this->getModule());
        }
        return $mod->functionExists($function);
    }

    public function moduleCall($function, $additionalVars = [])
    {
        $module = $this->getModule();
        if (!$module) {
            $this->moduleresults = ["error" => "Domain not assigned to a registrar module"];
            return false;
        }
        if (is_null($this->registrarModule)) {
            $this->registrarModule = new Registrar();
            $loaded = $this->registrarModule->load($module);
            if (!$loaded) {
                $this->moduleresults = ["error" => "Registrar module not found"];
                return false;
            }
            $this->registrarModule->setDomainID($this->getData("id"));
        }
        $mod = $this->registrarModule;
        $results = $mod->call($function, $additionalVars);
        $params = $mod->getParams();
        $vars = [
            "params" => $params,
            "results" => $results,
            "functionExists" => $results !== Registrar::FUNCTIONDOESNTEXIST,
            "functionSuccessful" => is_array($results) && empty($results["error"]) || is_object($results)
        ];
        $successOrFail = "";
        if (!$vars["functionSuccessful"] && $vars["functionExists"]) {
            $successOrFail = "Failed";
        }
        $hookResults = Hooks::run_hook("AfterRegistrar" . $function . $successOrFail, $vars);
        try {
            if (Hooks::processHookResults($module, $function, $hookResults)) {
                return true;
            }
        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
        if ($results === Registrar::FUNCTIONDOESNTEXIST) {
            $this->moduleresults = ["error" => "Function not found"];
            return false;
        }
        $this->moduleresults = $results;
        return is_array($results) && array_key_exists("error", $results) && $results["error"] ? false : true;
    }

    public function getModuleReturn($var = "")
    {
        if (!$var) {
            return $this->moduleresults;
        }
        return isset($this->moduleresults[$var]) ? $this->moduleresults[$var] : "";
    }

    public function getLastError()
    {
        return $this->getModuleReturn("error");
    }

    public function getDefaultNameservers()
    {
        $vars = [];
        $serverid = Hosting::where(['domain' => $this->getData("domain")])->value("server");

        if ($serverid) {
            $data = Server::selectRaw("nameserver1, nameserver2, nameserver3, nameserver4, nameserver5")
                ->where(['id' => $serverid])
                ->first();

            for ($i = 1; $i <= 5; $i++) {
                $vars["ns" . $i] = trim($data->{"nameserver" . $i});
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $vars["ns" . $i] = trim((string)Cfg::get("DefaultNameserver" . $i));
            }
        }

        return $vars;
    }

    public function getSLD()
    {
        $domainparts = explode(".", $this->getData("domain"), 2);
        return $domainparts[0];
    }

    public function getTLD()
    {
        $domainparts = explode(".", $this->getData("domain"), 2);
        return $domainparts[1] ?? '';
    }

    public function buildWHOISSaveArray($data)
    {
        $arr = [
            "First Name" => "firstname",
            "Last Name" => "lastname",
            "Full Name" => "fullname",
            "Contact Name" => "fullname",
            "Email" => "email",
            "Email Address" => "email",
            "Job Title" => "",
            "Company Name" => "companyname",
            "Organisation Name" => "companyname",
            "Address" => "address1",
            "Address 1" => "address1",
            "Street" => "address1",
            "Address 2" => "address2",
            "City" => "city",
            "State" => "state",
            "County" => "state",
            "Region" => "state",
            "Postcode" => "postcode",
            "ZIP Code" => "postcode",
            "ZIP" => "postcode",
            "Country" => "country",
            "Phone" => "phonenumberformatted",
            "Phone Number" => "phonenumberformatted",
            "Phone Country Code" => "phonecc"
        ];

        $retarr = [];
        foreach ($arr as $k => $v) {
            $retarr[$k] = $data[$v] ?? '';
        }

        return $retarr;
    }

    public function getManagementOptions()
    {
        $domainName = new \App\Helpers\Domain\Domain($this->getData("domain"));
        $managementOptions = [
            "nameservers" => false,
            "contacts" => false,
            "privatens" => false,
            "locking" => false,
            "dnsmanagement" => false,
            "emailforwarding" => false,
            "idprotection" => false,
            "eppcode" => false,
            "release" => false,
            "addons" => false
        ];

        if ($this->isActive()) {
            $managementOptions["nameservers"] = $this->hasFunction("GetNameservers");
            $managementOptions["contacts"] = $this->hasFunction("GetContactDetails");
        } elseif ($this->isPending()) {
            $managementOptions["nameservers"] = true;
            $managementOptions["contacts"] = true;
        }

        $managementOptions["privatens"] = $this->hasFunction("RegisterNameserver");
        $managementOptions["locking"] = $domainName->getLastTLDSegment() != "uk" && $this->hasFunction("GetRegistrarLock");
        $managementOptions["release"] = $domainName->getLastTLDSegment() == "uk" && $this->hasFunction("ReleaseDomain");

        $tldPricing = DB::table("tbldomainpricing")
            ->where("extension", "=", "." . $domainName->getTopLevel())
            ->first();

        if ($tldPricing) {
            $managementOptions["eppcode"] = $tldPricing->eppcode && $this->hasFunction("GetEPPCode");
            $managementOptions["dnsmanagement"] = $this->getData("dnsmanagement") && $this->hasFunction("GetDNS");
            $managementOptions["emailforwarding"] = $this->getData("emailforwarding") && $this->hasFunction("GetEmailForwarding");
            $managementOptions["idprotection"] = $this->getData("idprotection") ? true : false;
            $managementOptions["addons"] = $tldPricing->dnsmanagement || $tldPricing->emailforwarding || $tldPricing->idprotection;
        }

        return $managementOptions;
    }

    public static function getRenewableDomains($userID = 0, array $specificDomains = null)
    {
        $auth = Auth::guard('web')->user();
        $userID = $userID ?: ($auth ? $auth->id : 0);

        $renewals = [];
        $renewalsByStatus = [
            "domainrenewalsbeforerenewlimit" => [],
            "domainrenewalspastgraceperiod" => [],
            "domainrenewalsingraceperiod" => [],
            "domainsExpiringSoon" => [],
            "domainsActive" => []
        ];

        $hasExpiredDomains = $hasDomainsTooEarlyToRenew = $hasDomainsInGracePeriod = false;

        if ($userID) {
            $clientCurrency = Format::getCurrency($userID);
            $domainRenewalPriceOptions = [];
            $domainRenewalMinimums = [];
            $domainRenewalMinimums = array_merge(
                [".co.uk" => "180", ".org.uk" => "180", ".me.uk" => "180", ".com.au" => "90", ".net.au" => "90", ".org.au" => "90"],
                is_array($domainRenewalMinimums) ? $domainRenewalMinimums : []
            );

            $domains = Domain::ofClient($userID)
                ->orderBy("status", "desc")
                ->orderBy("expirydate", "asc");

            if (is_array($specificDomains)) {
                $domains->whereIn("id", $specificDomains);
            } else {
                $domains->whereIn("status", [Domain::ACTIVE, Domain::GRACE, Domain::REDEMPTION, Domain::EXPIRED]);
            }

            $domains = $domains->get();

            foreach ($domains as $singleDomain) {
                $id = $singleDomain->id;
                $domain = $singleDomain->domain;
                $expiryDate = $singleDomain->expiryDate;
                $normalisedExpiryDate = $singleDomain->getRawAttribute("expirydate");
                $status = $singleDomain->status;
                $renewalGracePeriod = $singleDomain->gracePeriod;
                $gracePeriodFee = $singleDomain->gracePeriodFee;
                $redemptionGracePeriod = $singleDomain->redemptionGracePeriod;
                $redemptionGracePeriodFee = $singleDomain->redemptionGracePeriodFee;
                $isPremium = $singleDomain->isPremium;

                $gracePeriodFee = max(0, Invoice::convertCurrency($gracePeriodFee, 1, $clientCurrency["id"]));
                $redemptionGracePeriodFee = max(0, Invoice::convertCurrency($redemptionGracePeriodFee, 1, $clientCurrency["id"]));

                $expiryDate = $normalisedExpiryDate == "0000-00-00" ? $singleDomain->nextDueDate : $expiryDate;
                $today = Carbon::today();
                $expiry = $expiryDate->copy();
                $daysUntilExpiry = $today->diffInDays($expiry, false);

                $tld = "." . $singleDomain->tld;
                $beforeRenewLimit = $inGracePeriod = $pastGracePeriod = $inRedemptionGracePeriod = $pastRedemptionGracePeriod = false;
                $earlyRenewalRestriction = $domainRenewalMinimums[$tld] ?? 0;

                if ($earlyRenewalRestriction < $daysUntilExpiry) {
                    $beforeRenewLimit = true;
                    $hasDomainsTooEarlyToRenew = true;
                }

                if (!$beforeRenewLimit && $daysUntilExpiry < 0) {
                    if ($renewalGracePeriod && -$renewalGracePeriod <= $daysUntilExpiry && $gracePeriodFee) {
                        $inGracePeriod = true;
                    } elseif (-($renewalGracePeriod + $redemptionGracePeriod) <= $daysUntilExpiry && $redemptionGracePeriodFee) {
                        $pastGracePeriod = true;
                        $inRedemptionGracePeriod = true;
                    } elseif (!$gracePeriodFee && !$redemptionGracePeriodFee || $daysUntilExpiry < -($renewalGracePeriod + $redemptionGracePeriod)) {
                        $pastGracePeriod = true;
                        $pastRedemptionGracePeriod = true;
                        $hasExpiredDomains = true;
                    }
                }

                if (!isset($domainRenewalPriceOptions[$tld])) {
                    $tempPriceList = Domain::getTLDPriceList($tld, true, true);
                    $renewalOptions = array_filter($tempPriceList, fn($options) => $options["renew"]);
                    $domainRenewalPriceOptions[$tld] = array_map(fn($options) => [
                        "period" => $options["period"],
                        "price" => $options["renew"],
                        "rawRenewalPrice" => $options["renew"]
                    ], $renewalOptions);
                } else {
                    $renewalOptions = $domainRenewalPriceOptions[$tld];
                }

                if ($isPremium) {
                    $renewalCostPrice = DomainsExtra::whereDomainId($singleDomain->id)->whereName("registrarRenewalCostPrice")->first();
                    if ($renewalCostPrice) {
                        $markupPremiumPrice = $renewalCostPrice->value * (1 + DomainpricingPremium::markupForCost($renewalCostPrice->value) / 100);
                        $premiumRenewalPricing = [
                            "period" => 1,
                            "price" => new FormatterPrice($markupPremiumPrice, $clientCurrency),
                            "rawRenewalPrice" => new FormatterPrice($markupPremiumPrice, $clientCurrency)
                        ];
                        $renewalOptions = [$premiumRenewalPricing];
                    }
                }

                $daysLeftInPeriod = 0;
                if ($renewalOptions && ($inGracePeriod || $inRedemptionGracePeriod)) {
                    $renewalOptions = reset($renewalOptions);
                    $renewalPeriod = $renewalOptions["period"];
                    $renewalPrice = $renewalOptions["price"]->toNumeric() ?? 0;
                    $renewalOptions = [];
                    $daysLeftInPeriod = $daysUntilExpiry;

                    if ($inGracePeriod) {
                        $graceOptions = [
                            "period" => $renewalPeriod,
                            "rawRenewalPrice" => new FormatterPrice($renewalPrice, $clientCurrency),
                            "gracePeriodFee" => new FormatterPrice($gracePeriodFee, $clientCurrency),
                            "price" => new FormatterPrice($renewalPrice + $gracePeriodFee, $clientCurrency)
                        ];
                        $renewalOptions[] = $graceOptions;
                        $daysLeftInPeriod += $renewalGracePeriod;
                    }

                    if ($inRedemptionGracePeriod) {
                        $redemptionOptions = [
                            "period" => $renewalPeriod,
                            "rawRenewalPrice" => new FormatterPrice($renewalPrice, $clientCurrency),
                            "gracePeriodFee" => new FormatterPrice($gracePeriodFee, $clientCurrency),
                            "redemptionGracePeriodFee" => new FormatterPrice($redemptionGracePeriodFee, $clientCurrency),
                            "price" => new FormatterPrice($renewalPrice + $gracePeriodFee + $redemptionGracePeriodFee, $clientCurrency)
                        ];
                        $renewalOptions[] = $redemptionOptions;
                        $daysLeftInPeriod += $renewalGracePeriod + $redemptionGracePeriod;
                    }

                    $hasDomainsInGracePeriod = true;
                }

                $eligibleForRenewal = true;
                if ($specificDomains && !in_array($status, [Domain::ACTIVE, Domain::GRACE, Domain::REDEMPTION, Domain::EXPIRED])) {
                    $eligibleForRenewal = false;
                    $beforeRenewLimit = true;
                }

                $status = strtolower(str_replace([" ", "-"], "", $status));
                $rawStatus = $status;

                if ($renewalOptions || (is_array($specificDomains) && in_array($id, $specificDomains))) {
                    $renewal = [
                        "id" => $id,
                        "domain" => $domain,
                        "tld" => $tld,
                        "status" => Lang::get("client.clientarea" . $rawStatus),
                        "expiryDate" => $expiryDate,
                        "normalisedExpiryDate" => $normalisedExpiryDate,
                        "daysUntilExpiry" => $daysUntilExpiry,
                        "beforeRenewLimit" => $beforeRenewLimit,
                        "beforeRenewLimitDays" => $earlyRenewalRestriction,
                        "inGracePeriod" => $inGracePeriod,
                        "pastGracePeriod" => $pastGracePeriod,
                        "gracePeriodDays" => $renewalGracePeriod,
                        "inRedemptionGracePeriod" => $inRedemptionGracePeriod,
                        "pastRedemptionGracePeriod" => $pastRedemptionGracePeriod,
                        "redemptionGracePeriodDays" => $redemptionGracePeriod,
                        "daysLeftInPeriod" => $daysLeftInPeriod,
                        "renewalOptions" => $renewalOptions,
                        "statusClass" => ViewHelper::generateCssFriendlyClassName($status),
                        "expiringSoon" => $daysUntilExpiry <= 45 && $status != Domain::EXPIRED,
                        "eligibleForRenewal" => $eligibleForRenewal,
                        "isPremium" => $isPremium
                    ];

                    if (defined("SHOPPING_CART")) {
                        $renewal = array_merge($renewal, [
                            "expirydate" => (new \App\Helpers\Functions)->fromMySQLDate($renewal["expiryDate"]),
                            "daysuntilexpiry" => $renewal["daysUntilExpiry"],
                            "beforerenewlimit" => $renewal["beforeRenewLimit"],
                            "beforerenewlimitdays" => $renewal["beforeRenewLimitDays"],
                            "ingraceperiod" => $renewal["inGracePeriod"],
                            "pastgraceperiod" => $renewal["pastGracePeriod"],
                            "graceperioddays" => $renewal["gracePeriodDays"],
                            "renewaloptions" => $renewal["renewalOptions"]
                        ]);
                    }

                    $renewals[] = $renewal;

                    $statusToUse = "domainsActive";
                    if ($beforeRenewLimit) {
                        $statusToUse = "domainrenewalsbeforerenewlimit";
                    }
                    if ($inGracePeriod) {
                        $statusToUse = "domainsExpiringSoon";
                    }
                    if ($inRedemptionGracePeriod) {
                        $statusToUse = "domainrenewalsingraceperiod";
                    }
                    if ($pastRedemptionGracePeriod) {
                        $statusToUse = "domainrenewalspastgraceperiod";
                    }

                    $renewalsByStatus[$statusToUse][] = $renewal;
                }
            }

            usort($renewals, fn($firstDomain, $secondDomain) => $secondDomain["daysUntilExpiry"] <=> $firstDomain["daysUntilExpiry"]);

            foreach ($renewalsByStatus as $status => &$statusRenewals) {
                usort($statusRenewals, fn($firstDomain, $secondDomain) => $secondDomain["daysUntilExpiry"] <=> $firstDomain["daysUntilExpiry"]);
            }
        }

        return [
            "renewals" => $renewals,
            "renewalsByStatus" => $renewalsByStatus,
            "hasExpiredDomains" => $hasExpiredDomains,
            "hasDomainsTooEarlyToRenew" => $hasDomainsTooEarlyToRenew,
            "hasDomainsInGracePeriod" => $hasDomainsInGracePeriod
        ];
    }

    public function obtainEmailReminders()
    {
        $reminders = Domain::where(['domain_id' => $this->id])
            ->orderBy('id', 'DESC')
            ->get();

        return $reminders->toArray();
    }

    public function getDomainInformation()
    {
        if (is_null($this->domainInformation)) {
            $domainInformation = null;

            if ($this->hasFunction("GetDomainInformation")) {
                $success = $this->moduleCall("GetDomainInformation");
                if (!$success) {
                    throw new NotServicable($this->getLastError());
                }
                $domainInformation = $this->getModuleReturn();
                if (!$domainInformation instanceof \App\Helpers\Domain\Registrar\Domain) {
                    throw new NotServicable("Invalid Response");
                }
            }

            if (!$domainInformation) {
                $domainInformation = new \App\Helpers\Domain\Registrar\Domain();
            }

            if (!$domainInformation->hasNameservers() && $this->hasFunction("GetNameservers")) {
                $success = $this->moduleCall("GetNameservers");
                if ($success) {
                    $domainInformation->setNameservers($this->getModuleReturn());
                } else {
                    throw new NotServicable($this->getLastError());
                }
            }

            if (!$domainInformation->hasTransferLock() && $this->hasFunction("GetRegistrarLock")) {
                $success = $this->moduleCall("GetRegistrarLock");
                if ($success) {
                    $domainInformation->setTransferLock($this->getModuleReturn() === "locked");
                }
            }

            $this->domainInformation = $domainInformation;
        }

        return $this->domainInformation;
    }

    public function saveContactDetails(ClientClass $client, array $contactdetails, array $wc, array $sel = null)
    {
        $userContactDetails = $client->getDetails();
        $language = $userContactDetails["language"] ?? Cfg::getValue("Language");
        $contactDetails = [];

        foreach ($wc as $wc_key => $wc_val) {
            if ($wc_val == "contact") {
                $selectedContact = $sel[$wc_key];
                $selectedContactType = substr($selectedContact, 0, 1);
                $selectedContactID = substr($selectedContact, 1);
                $tmpcontactdetails = [];

                if ($selectedContactType == "u") {
                    $tmpcontactdetails = $userContactDetails;
                } elseif ($selectedContactType == "c") {
                    if (!isset($contactDetails[$selectedContactID])) {
                        $contactDetails[$selectedContactID] = $client->getDetails($selectedContactID);
                    }
                    $tmpcontactdetails = $contactDetails[$selectedContactID];
                }

                $contactdetails[$wc_key] = $this->buildWHOISSaveArray($tmpcontactdetails);
            } elseif (isset($contactdetails[$wc_key]) && is_array($contactdetails[$wc_key])) {
                (new Domains)->normaliseInternationalPhoneNumberFormat($contactdetails[$wc_key]);
            }
        }

        $success = $this->moduleCall("SaveContactDetails", [
            "irtpOptOut" => Request::input("irtpOptOut"),
            "irtpOptOutReason" => Request::input("irtpOptOutReason"),
            "contactdetails" => Functions::foreignChrReplace($contactdetails),
            "language" => $language
        ]);

        if ($success) {
            $return = ["status" => "success", "contactDetails" => $contactdetails];
            if ($this->getModuleReturn("pending")) {
                $return["status"] = "pending";
                $return["pendingData"] = $this->getModuleReturn("pendingData");
            }
            return $return;
        }

        throw new NotServicable($this->getLastError());
    }

    public function callModuleAddon($moduleName, $functionName, $params = [])
    {
        $allowedModules = ['PrivateNsRegistrar','ForwardDomain'];
        $allowedFunctions = ['test', 'requirement', 'domainDetail', 'uploadImage', 'clientHome', 'deleteImage', 'lookupTld', 'setDocument','handleAction'];

        if (in_array($moduleName, $allowedModules) && in_array($functionName, $allowedFunctions)) {
            $controllerName = $moduleName . 'Controller';
            $className = "Modules\\Addons\\$moduleName\\Http\\Controllers\\$controllerName";

            if (class_exists($className)) {
                $controllerInstance = new $className();

                if (method_exists($controllerInstance, $functionName)) {
                    return call_user_func_array([$controllerInstance, $functionName], [$params]);
                } else {
                    return response()->json(['error' => 'Method not found'], 404);
                }
            } else {
                return response()->json(['error' => 'Controller not found'], 404);
            }
        } else {
            return response()->json(['error' => 'Invalid module or function'], 400);
        }
    }

}