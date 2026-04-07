<?php

namespace App\Helpers;

use App\Module\Registrar;
use App\Helpers\Database;

use App\Exceptions\Module\NotServicable;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Server;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\Array_;

class Domains
{
	protected $request;
	protected $statusValues = NULL;

    const PENDING = "Pending";
    const PENDING_REGISTRATION = "Pending Registration";
    const PENDING_TRANSFER = "Pending Transfer";
    const ACTIVE = "Active";
    const GRACE = "Grace";
    const REDEMPTION = "Redemption";
    const EXPIRED = "Expired";
    const TRANSFERRED_AWAY = "Transferred Away";
    const CANCELLED = "Cancelled";
    const FRAUD = "Fraud";

	private $prefix;
	private $registrar;
	private $id = 0;
    private $data = [];
    private $domainModel = NULL;
    private $moduleresults = [];
    private $domainInformation = NULL;
    private $registrarModule = NULL;

	public function __construct()
	{
		$this->prefix = Database::prefix();
		$this->registrar = new Registrar();

		$this->statusValues = [
			$this::PENDING,
			$this::PENDING_REGISTRATION,
			$this::PENDING_TRANSFER,
			$this::ACTIVE,
			$this::GRACE,
			$this::REDEMPTION,
			$this::EXPIRED,
			$this::TRANSFERRED_AWAY,
			$this::CANCELLED,
			$this::FRAUD,
		];
	}

	public function DomainWhois($domain)
	{
		$domains = new \App\Helpers\CoreDomains();
		$domainparts = $domains->splitAndCleanDomainInput($domain);
		$isValid = $domains->checkDomainisValid($domainparts);
	
		if (!$isValid) {
			return [
				"result" => "error",
				"message" => "Domain not valid"
			];
		}
	
		$whois = new \App\Helpers\WHOIS();
		if (!$whois->canLookup($domainparts["tld"])) {
			return [
				"result" => "error",
				"message" => "The given TLD is not supported for WHOIS lookups"
			];
		}
	
		$result = $whois->lookup($domainparts);
		return [
			"result" => "success",
			"status" => $result["result"],
			"whois" => $result["whois"]
		];
	}
	
	public function GetTLDPricing($params)
	{
		$currencyId = (int) $params['currencyid'];
		$userId = (int) $params['clientid'];
		$clientGroupId = 0;
	
		if ($userId) {
			$client = \App\Models\Client::find($userId);
			$currencyId = $client->currencyId;
			$clientGroupId = $client->groupId;
		}
	
		$user = new \App\Helpers\AdminFunctions();
		$currency = $user->getCurrency($userId);
		$pricing = [];
	
		$pricingTypes = ["domainregister", "domaintransfer", "domainrenew"];
		$result = \App\Models\Pricing::whereIn("type", $pricingTypes)
			->where("currency", $currency["id"])
			->where("tsetupfee", 0)
			->get();
	
		foreach ($result as $data) {
			$pricing[$data->relid][$data->type] = get_object_vars($data);
		}
	
		if ($clientGroupId) {
			$result2 = \App\Models\Pricing::whereIn("type", $pricingTypes)
				->where("currency", $currency["id"])
				->where("tsetupfee", $clientGroupId)
				->get();
	
			foreach ($result2 as $data) {
				$pricing[$data->relid][$data->type] = get_object_vars($data);
			}
		}
	
		$tldIds = [];
		$tldGroups = [];
		$tldAddons = [];
		$result = \App\Models\Domainpricing::get(["id", "extension", "dnsmanagement", "emailforwarding", "idprotection", "group"]);
	
		foreach ($result as $data) {
			$ext = ltrim($data->extension, ".");
			$tldIds[$ext] = $data->id;
			$tldGroups[$ext] = $data->group ?: "";
			$tldAddons[$ext] = [
				"dns" => (bool) $data->dnsmanagement,
				"email" => (bool) $data->emailforwarding,
				"idprotect" => (bool) $data->idprotection
			];
		}
	
		$extensions = \App\Models\Domainpricing::all();
		$extensionsByTld = [];
		foreach ($extensions as $extension) {
			$tld = ltrim($extension->extension, ".");
			$extensionsByTld[$tld] = $extension;
		}
	
		$tldList = array_keys($extensionsByTld);
		$periods = [
			"msetupfee" => 1, "qsetupfee" => 2, "ssetupfee" => 3,
			"asetupfee" => 4, "bsetupfee" => 5, "monthly" => 6,
			"quarterly" => 7, "semiannually" => 8, "annually" => 9,
			"biennially" => 10
		];
	
		$categories = [];
		$result = DB::table("{$this->prefix}tlds")
			->join("{$this->prefix}tld_category_pivot", "{$this->prefix}tld_category_pivot.tld_id", "=", "{$this->prefix}tlds.id")
			->join("{$this->prefix}tld_categories", "{$this->prefix}tld_categories.id", "=", "{$this->prefix}tld_category_pivot.category_id")
			->whereIn("tld", $tldList)
			->get();
	
		foreach ($result as $data) {
			$categories[$data->tld][] = $data->category;
		}
	
		$usedTlds = array_keys($categories);
		$missedTlds = array_diff($tldList, $usedTlds);
	
		foreach ($missedTlds as $missedTld) {
			$categories[$missedTld][] = "Other";
		}
	
		$apiresults = ["result" => "success", "currency" => $currency];
	
		foreach ($tldList as $tld) {
			$tldId = $tldIds[$tld];
			$apiresults["pricing"][$tld] = [
				"categories" => $categories[$tld],
				"addons" => $tldAddons[$tld],
				"group" => $tldGroups[$tld]
			];
	
			foreach ($pricingTypes as $type) {
				foreach ($pricing[$tldId][$type] as $key => $price) {
					if (isset($periods[$key]) && $price >= 0) {
						$apiresults["pricing"][$tld][str_replace("domain", "", $type)][$periods[$key]] = $price;
					}
				}
			}
	
			if (isset($extensionsByTld[$tld])) {
				$extension = $extensionsByTld[$tld];
				$apiresults["pricing"][$tld]["grace_period"] = null;
				if ($extension->grace_period_fee >= 0) {
					$gracePeriodFee = \App\Helpers\Format::Currency($extension->grace_period_fee, 1, $currency["id"]);
					$apiresults["pricing"][$tld]["grace_period"] = [
						"days" => max($extension->grace_period, $extension->defaultGracePeriod),
						"price" => \App\Helpers\Format::Currency($gracePeriodFee, $currency)
					];
				}
	
				$apiresults["pricing"][$tld]["redemption_period"] = null;
				if ($extension->redemption_grace_period_fee >= 0) {
					$redemptionGracePeriodFee = \App\Helpers\Format::Currency($extension->redemption_grace_period_fee, 1, $currency["id"]);
					$apiresults["pricing"][$tld]["redemption_period"] = [
						"days" => max($extension->redemption_grace_period, $extension->defaultRedemptionGracePeriod),
						"price" => \App\Helpers\Format::Currency($redemptionGracePeriodFee, $currency)
					];
				}
			}
		}
	
		return $apiresults;
	}

	public function UpdateClientDomain(array $params)
	{
		extract($params);
	
		$getDomain = $domainid 
			? \App\Models\Domain::find($domainid)
			: \App\Models\Domain::where('domain', $domain)->first();
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain ID Not Found"];
		}
	
		$updateFields = [
			'type' => $type ?? null,
			'registrationdate' => $regdate ?? null, 
			'domain' => $domain ?? null,
			'firstpaymentamount' => $firstpaymentamount ?? null,
			'recurringamount' => $recurringamount ?? null,
			'registrar' => $registrar ?? null,
			'registrationperiod' => $regperiod ?? null,
			'expirydate' => $expirydate ?? null,
			'paymentmethod' => $paymentmethod ?? null,
			'subscriptionid' => $subscriptionid ?? null,
			'status' => $status ?? null,
			'additionalnotes' => $notes ?? null
		];
	
		if ($nextduedate) {
			$getDomain->nextduedate = $nextduedate;
			$getDomain->nextinvoicedate = $nextduedate;
		}
	
		$booleanFields = [
			'dnsmanagement' => $dnsmanagement ?? null,
			'emailforwarding' => $emailforwarding ?? null, 
			'idprotection' => $idprotection ?? null,
			'donotrenew' => $donotrenew ?? null
		];
	
		foreach ($booleanFields as $field => $value) {
			if (isset($value)) {
				$getDomain->{$field} = empty($value) ? "" : "1";
			}
		}
	
		if ($promoid) {
			$getDomain->promoid = $idprotection;
		}
	
		foreach ($updateFields as $field => $value) {
			if (!is_null($value)) {
				$getDomain->{$field} = $value;
			}
		}
	
		$getDomain->save();
	
		if ($autorecalc) {
			$getDomain = $domainid 
				? \App\Models\Domain::find($domainid)
				: \App\Models\Domain::where('domain', $domain)->first();
	
			$domainParts = explode(".", $getDomain->domain, 2);
			$user = new \App\Helpers\AdminFunctions();
			$currency = $user->getCurrency($getDomain->userid);
	
			$tempPriceList = $this->getTLDPriceList("." . $domainParts[1], "", true, $getDomain->userid);
			$recurringAmount = $tempPriceList[$getDomain->registrationperiod]["renew"];
	
			$addonPricing = \App\Models\Pricing::where('type', 'domainaddons')
				->where('currency', $currency["id"])
				->where('relid', 0)
				->first();
	
			$addonCosts = [
				'dns' => $addonPricing->msetupfee * $getDomain->registrationperiod,
				'email' => $addonPricing->qsetupfee * $getDomain->registrationperiod,
				'idprotect' => $addonPricing->ssetupfee * $getDomain->registrationperiod
			];
	
			if ($getDomain->dnsmanagement) $recurringAmount += $addonCosts['dns'];
			if ($getDomain->emailforwarding) $recurringAmount += $addonCosts['email'];
			if ($getDomain->idprotection) $recurringAmount += $addonCosts['idprotect'];
	
			if ($promoid) {
				$promoDiscount = $this->recalcPromoAmount(
					"D." . $domainParts[1],
					$getDomain->userid,
					$getDomain->id,
					$getDomain->registrationperiod . "Years",
					$recurringAmount,
					$promoid
				);
				$recurringAmount -= $promoDiscount;
			}
	
			$getDomain->recurringamount = $recurringAmount;
			$getDomain->save();
		}
	
		if ($updatens) {
			$getDomain = $domainid 
				? \App\Models\Domain::find($domainid)
				: \App\Models\Domain::where('domain', $domain)->first();
	
			if (!($ns1 && $ns2)) {
				return ["result" => "error", "message" => "ns1 and ns2 required"];
			}
	
			$domainParts = explode(".", $getDomain->domain, 2);
			
			$nsParams = [
				"domainid" => $getDomain->id,
				"sld" => $domainParts[0],
				"tld" => $domainParts[1],
				"regperiod" => $getDomain->registrationperiod,
				"registrar" => $getDomain->registrar,
				"ns1" => $ns1,
				"ns2" => $ns2,
				"ns3" => $ns3 ?? null,
				"ns4" => $ns4 ?? null,
				"ns5" => $ns5 ?? null
			];
	
			foreach ($nsParams as $key => $value) {
				if (!is_null($value)) {
					$getDomain->{$key} = $value;
				}
			}
	
			$values = $this->RegSaveNameservers($nsParams);
			
			if (!empty($values["error"])) {
				return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
			}
		}
	
		return ["result" => "success", "domainid" => $getDomain->id];
	}

	public function recalcPromoAmount($pid, $userid, $serviceid, $billingcycle, $recurringamount, $promoid)
	{
		$user = new \App\Helpers\AdminFunctions();
		$currency = $user->getCurrency();
		$recurringdiscount = "";
		
		$data = \App\Models\Promotion::find($promoid);
		$type = $data->type;
		$recurring = $data->recurring;
		$value = $data->value;
	
		if (!$recurring) {
			return $recurringdiscount;
		}
	
		switch ($type) {
			case "Percentage":
				$recurringdiscount = $recurringamount * $value / 100;
				break;
	
			case "Fixed Amount":
				if ($currency["id"] != 1) {
					$value = \App\Helpers\Format::Currency($value, 1, NULL, $currency["id"]);
				}
				$recurringdiscount = min($recurringamount, $value);
				break;
	
			case "Price Override":
				if ($currency["id"] != 1) {
					$value = \App\Helpers\Format::Currency($value, null, $currency["id"]);
				}
				$recurringdiscount = $recurringamount - $value;
				break;
		}
	
		return $recurringdiscount;
	}

	public function RegSaveNameservers($params)
	{
		for ($i = 1; $i <= 5; $i++) {
			$params["ns" . $i] = trim($params["ns" . $i]);
		}
	
		$values = $this->registrar->regcallfunction($params, "SaveNameservers");
		if (!$values) {
			return false;
		}
	
		$userid = \App\Models\Domain::find($params["domainid"])->userid;
	
		if (!empty($values["error"])) {
			LogActivity::Save(
				"Domain Registrar Command: Save Nameservers - Failed: " . $values["error"] . 
				" - Domain ID: " . $params["domainid"], 
				$userid
			);
		} else {
			LogActivity::Save("Domain Registrar Command: Save Nameservers - Successful", $userid);
		}
	
		return $values;
	}
	
	public function getDefaultNameservers()
	{
		$vars = [];
		$serverid = Hosting::where("domain", $this->getData("domain"))->first();
		
		if ($serverid) {
			$serverid = $serverid->server;
			$result = Server::selectRaw("nameserver1,nameserver2,nameserver3,nameserver4,nameserver5")
				->find($serverid);
	
			if ($result) {
				$data = $result->toArray();
				for ($i = 1; $i <= 5; $i++) {
					$vars["ns" . $i] = trim($data["nameserver" . $i]);
				}
			}
		} else {
			for ($i = 1; $i <= 5; $i++) {
				$vars["ns" . $i] = trim((string) Cfg::get("DefaultNameserver$i"));
			}
		}
	
		return $vars;
	}
	
	public function getTLDPriceList($tld, $display = false, $renewpricing = "", $userid = 0, $useCache = true)
	{
		$user = new \App\Helpers\AdminFunctions();
		$currency = $user->getCurrency();
		
		if (!$userid && Auth::user()->id) {
			$userid = Auth::user()->id;
		}
	
		if (ltrim($tld, ".") == $tld) {
			$tld = "." . $tld;
		}
	
		static $pricingCache = NULL;
		$cacheKey = NULL;
	
		if (!$pricingCache) {
			$pricingCache = [];
		} else {
			foreach ($pricingCache as $key => $pricing) {
				if ($pricing["tld"] == $tld && 
					$pricing["display"] == $display && 
					$pricing["renewpricing"] == $renewpricing && 
					$pricing["userid"] == $userid) {
					if ($useCache) {
						return $pricing["pricing"];
					}
					$cacheKey = $key;
					break;
				}
			}
		}
	
		if (is_null($cacheKey)) {
			$pricing = [
				"tld" => $tld,
				"display" => $display,
				"renewpricing" => $renewpricing,
				"userid" => $userid
			];
			$cacheKey = count($pricingCache);
			$pricingCache[$cacheKey] = $pricing;
		}
	
		if ($renewpricing == "renew") {
			$renewpricing = true;
		}
	
		$currency_id = $currency["id"];
	
		try {
			$extensionData = \App\Models\Domainpricing::where("extension", $tld)
				->firstOrFail(["id"]);
			$id = $extensionData->id;
		} catch (\Exception $e) {
			return [];
		}
	
		$clientgroupid = $userid ? \App\Models\Client::find($userid)->groupid : "0";
		
		$checkfields = [
			"msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee",
			"monthly", "quarterly", "semiannually", "annually", "biennially"
		];
	
		$pricingData = DB::table("{$this->prefix}pricing")
			->whereIn("type", ["domainregister", "domaintransfer", "domainrenew"])
			->where("currency", "=", $currency_id)
			->where("relid", "=", $id)
			->orderBy("tsetupfee", "desc")
			->get();
	
		$sortedData = [
			"domainregister" => [],
			"domaintransfer" => [],
			"domainrenew" => []
		];
	
		foreach ($pricingData as $entry) {
			$entryPricingGroupId = (int) $entry->tsetupfee;
			if ($entryPricingGroupId == 0 || $entryPricingGroupId == $clientgroupid) {
				$type = $entry->type;
				if (empty($sortedData[$type])) {
					$sortedData[$type] = (array) $entry;
				}
			}
		}
	
		if (!$renewpricing || $renewpricing === "transfer") {
			$data = $sortedData["domainregister"];
			foreach ($checkfields as $k => $v) {
				$register[$k + 1] = $data[$v] ?: -1;
			}
			
			$data = $sortedData["domaintransfer"];
			foreach ($checkfields as $k => $v) {
				$transfer[$k + 1] = $data[$v] ?: -1;
			}
		}
	
		if (!$renewpricing || $renewpricing !== "transfer") {
			$data = $sortedData["domainrenew"];
			foreach ($checkfields as $k => $v) {
				$renew[$k + 1] = $data[$v] ?: -1;
			}
		}
	
		$tldpricing = [];
		
		for ($years = 1; $years <= 10; $years++) {
			if ($renewpricing === "transfer") {
				if (0 <= $register[$years] && 0 <= $transfer[$years]) {
					if ($display) {
						$transfer[$years] = \App\Helpers\Format::formatCurrency($transfer[$years]);
					}
					$tldpricing[$years]["transfer"] = $transfer[$years];
				}
			} else if ($renewpricing) {
				if (0 < $renew[$years]) {
					if ($display) {
						$renew[$years] = \App\Helpers\Format::formatCurrency($renew[$years]);
					}
					$tldpricing[$years]["renew"] = $renew[$years];
				}
			} else {
				if (0 <= $register[$years]) {
					if ($display) {
						$register[$years] = \App\Helpers\Format::formatCurrency($register[$years]);
					}
					$tldpricing[$years]["register"] = $register[$years];
					
					if (0 <= $transfer[$years]) {
						if ($display) {
							$transfer[$years] = \App\Helpers\Format::formatCurrency($transfer[$years]);
						}
						$tldpricing[$years]["transfer"] = $transfer[$years];
					}
					
					if (0 < $renew[$years]) {
						if ($display) {
							$renew[$years] = \App\Helpers\Format::formatCurrency($renew[$years]);
						}
						$tldpricing[$years]["renew"] = $renew[$years];
					}
				}
			}
		}
	
		$pricingCache[$cacheKey]["pricing"] = $tldpricing;
		return $tldpricing;
	}

	public function DomainGetLockingStatus(Array $params)
	{
		$params = [
			'domainid' => (int) $params['domainid'],
			'lockenabled' => $params['lockenabled'] ?? ''
		];
		
		extract($params);
		
		$getDomain = \App\Models\Domain::find($domainid);
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain ID Not Found"];
		}
	
		$domainparts = explode(".", $getDomain->domain, 2);
		
		$params = [
			"domainid" => $getDomain->id,
			"sld" => $domainparts[0],
			"tld" => $domainparts[1], 
			"regperiod" => $getDomain->registrationperiod,
			"registrar" => $getDomain->registrar,
			"lockenabled" => $lockenabled
		];
	
		$lockstatus = $this->registrar->RegGetRegistrarLock($params);
		if (!$lockstatus) {
			$lockstatus = "Unknown";
		}
	
		return ["result" => "success", "lockstatus" => $lockstatus];
	}

    public function DomainGetNameservers(array $params)
    {
        $params = [
            'domainid' => (int) $params['domainid'],
            'lockenabled' => $params['lockenabled'] ?? ''
        ];

        $getDomain = Domain::find($params['domainid']);
        if (is_null($getDomain)) {
            return ["result" => "error", "message" => "Domain ID Not Found"];
        }

        $domainParts = explode(".", $getDomain->domain, 2);
        $params = [
            "domainid" => $getDomain->id,
            "sld" => $domainParts[0],
            "tld" => $domainParts[1],
            "regperiod" => $getDomain->registrationperiod,
            "registrar" => $getDomain->registrar,
            "lockenabled" => $params['lockenabled']
        ];

        $values = $this->registrar->RegGetNameservers($params);
        if (isset($values["na"])) {
            return ["result" => "error", "message" => "Registrar Function Not Supported"];
        }
        if (isset($values["error"])) {
            return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
        }
        return ["result" => "success", "lockstatus" => $params['lockenabled']];
    }

    public function DomainGetWhoisInfo(array $params)
    {
        $params = [
            'domainid' => (int) $params['domainid'],
            'lockenabled' => $params['lockenabled'] ?? ''
        ];

        $getDomain = Domain::find($params['domainid']);
        if (is_null($getDomain)) {
            return ["result" => "error", "message" => "Domain ID Not Found"];
        }

        $domainParts = explode(".", $getDomain->domain, 2);
        $params = [
            "domainid" => $getDomain->id,
            "sld" => $domainParts[0],
            "tld" => $domainParts[1],
            "regperiod" => $getDomain->registrationperiod,
            "registrar" => $getDomain->registrar
        ];

        $values = $this->registrar->RegGetContactDetails($params);
        if (isset($values["error"])) {
            return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
        }

        $passback = [];
        foreach ($values as $type => $value) {
            if (is_array($value)) {
                foreach ($value as $type2 => $value2) {
                    if (is_array($value2)) {
                        foreach ($value2 as $type3 => $value3) {
                            $passback[str_replace(" ", "_", $type)][str_replace(" ", "_", $type2)][str_replace(" ", "_", $type3)] = $value3;
                        }
                    } else {
                        $passback[str_replace(" ", "_", $type)][str_replace(" ", "_", $type2)] = $value2;
                    }
                }
            } else {
                $passback[str_replace(" ", "_", $type)] = $value;
            }
        }
        return array_merge(["result" => "success"], $passback);
    }

    public function DomainRegister(array $param)
    {
        $params = [
            'domainid' => (int) $param['domainid'],
            'domain' => $param['domain'] ?? '',
            'idnlanguage' => $param['idnlanguage'] ?? ''
        ];

        $getDomain = $params['domainid'] ? Domain::find($params['domainid']) : Domain::where('domain', $params['domain'])->first();
        if (is_null($getDomain)) {
            return ["result" => "error", "message" => "Domain Not Found"];
        }

        $params = ["domainid" => $getDomain->id];
        $values = $this->registrar->RegRegisterDomain($params);
        if (isset($values["error"])) {
            return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
        }

        return array_merge(["result" => "success"], $values);
    }

    public function DomainRelease(array $param)
    {
        extract($param);

        $getDomain = $domainid ? Domain::find($domainid) : Domain::where('domain', $domain)->first();
        if (is_null($getDomain)) {
            return ["result" => "error", "message" => "Domain Not Found"];
        }

        $domainParts = explode(".", $getDomain->domain, 2);
        $params = [
            "domainid" => $getDomain->id,
            "sld" => $domainParts[0],
            "tld" => $domainParts[1],
            "regperiod" => $getDomain->registrationperiod,
            "registrar" => $getDomain->registrar,
            "transfertag" => $newtag
        ];

        $values = $this->registrar->RegReleaseDomain($params);
        if (isset($values["error"])) {
            return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
        }
        return array_merge(["result" => "success"], $values);
    }

    public function DomainRenew(array $param)
    {
        extract($param);

        $getDomain = $domainid ? Domain::find($domainid) : Domain::where('domain', $domain)->first();
        if (is_null($getDomain)) {
            return ["result" => "error", "message" => "Domain Not Found"];
        }

        if ((int) $regperiod) {
            $getDomain->registrationperiod = $regperiod;
            $getDomain->save();
        }

        $params = ["domainid" => $getDomain->id];
        sleep(5);
        $values = $this->registrar->RegRenewDomain($params);
        if (isset($values["error"])) {
            return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
        }

        return array_merge(["result" => "success"], $values);
    }
	
	public function DomainRequestEPP(array $params)
	{
		$domainId = $params['domainid'] ?? null;
		$getDomain = \App\Models\Domain::find($domainId);
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain ID Not Found"];
		}
	
		$domainParts = explode(".", $getDomain->domain, 2);
		$params = [
			"domainid" => $domainId,
			"sld" => $domainParts[0],
			"tld" => $domainParts[1],
			"regperiod" => $getDomain->registrationperiod,
			"registrar" => $getDomain->registrar
		];
	
		$values = $this->registrar->RegGetEPPCode($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return array_merge(["result" => "success"], $values);
	}
	
	public function DomainToggleIdProtect(array $params)
	{
		$domainId = $params['domainid'] ?? null;
		$idProtect = isset($params['idprotect']) ? (int)(bool)$params['idprotect'] : null;
		$getDomain = \App\Models\Domain::find($domainId);
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain ID Not Found"];
		}
	
		$getDomain->idprotection = $idProtect;
		$getDomain->save();
	
		$domainParts = explode(".", $getDomain->domain, 2);
		$params = [
			"domainid" => $domainId,
			"sld" => $domainParts[0],
			"tld" => $domainParts[1],
			"regperiod" => $getDomain->registrationperiod,
			"registrar" => $getDomain->registrar
		];
	
		$values = $this->registrar->RegIDProtectToggle($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return array_merge(["result" => "success"], $values);
	}
	
	public function DomainTransfer(array $params)
	{
		$domainId = $params['domainid'] ?? null;
		$domain = $params['domain'] ?? null;
		$eppCode = $params['eppcode'] ?? null;
	
		$getDomain = $domainId ? \App\Models\Domain::find($domainId) : \App\Models\Domain::where('domain', $domain)->first();
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain Not Found"];
		}
	
		$params = [
			"domainid" => $getDomain->id,
			"transfersecret" => $eppCode
		];
	
		$values = $this->registrar->RegTransferDomain($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return array_merge(["result" => "success"], $values);
	}
	
	public function DomainUpdateLockingStatus(array $params)
	{
		$domainId = $params['domainid'] ?? null;
		$lockStatus = $params['lockstatus'] ?? null;
	
		$getDomain = \App\Models\Domain::find($domainId);
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain Not Found"];
		}
	
		$domainParts = explode(".", $getDomain->domain, 2);
		$lockEnabled = $lockStatus ? "locked" : "";
	
		$params = [
			"domainid" => $domainId,
			"sld" => $domainParts[0],
			"tld" => $domainParts[1],
			"regperiod" => $getDomain->registrationperiod,
			"registrar" => $getDomain->registrar,
			"lockenabled" => $lockEnabled
		];
	
		$values = $this->registrar->RegSaveRegistrarLock($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return ["result" => "success"];
	}
	
	public function DomainUpdateNameservers(array $params)
	{
		extract($params);
	
		$getDomain = $domainid 
			? \App\Models\Domain::find($domainid) 
			: \App\Models\Domain::where('domain', $domain)->first();
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain Not Found"];
		}
	
		$domain = $getDomain->domain;
		$registrar = $getDomain->registrar;
		$regperiod = $getDomain->registrationperiod;
		$domainparts = explode(".", $domain, 2);
	
		$params = [
			"domainid" => $domainid,
			"sld" => $domainparts[0],
			"tld" => $domainparts[1],
			"regperiod" => $regperiod,
			"registrar" => $registrar,
			"ns1" => $ns1,
			"ns2" => $ns2,
			"ns3" => $ns3,
			"ns4" => $ns4,
			"ns5" => $ns5
		];
	
		$values = $this->registrar->RegSaveNameservers($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return array_merge(["result" => "success"], $values ?: []);
	}
	
	public function DomainUpdateWhoisInfo(array $params)
	{
		extract($params);
	
		$getDomain = $domainid 
			? \App\Models\Domain::find($domainid) 
			: \App\Models\Domain::where('domain', $domain)->first();
	
		if (is_null($getDomain)) {
			return ["result" => "error", "message" => "Domain Not Found"];
		}
	
		$xml = \App\Helpers\Sanitize::decode($xml);
		$xmlarray = $this->uwd_xml2array($xml);
		$contact = [];
	
		foreach ($xmlarray as $type => $value) {
			if (is_array($value)) {
				foreach ($value as $type2 => $value2) {
					if (is_array($value2)) {
						foreach ($value2 as $type3 => $value3) {
							if (is_array($value3)) {
								foreach ($value3 as $type4 => $value4) {
									$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)][str_replace("_", " ", $type3)][str_replace("_", " ", $type4)] = $value4;
								}
							} else {
								$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)][str_replace("_", " ", $type3)] = $value3;
							}
						}
					} else {
						$contact[str_replace("_", " ", $type)][str_replace("_", " ", $type2)] = $value2;
					}
				}
			} else {
				$contact[str_replace("_", " ", $type)] = $value;
			}
		}
	
		$id = $getDomain->id;
		$domain = $getDomain->domain;
		$registrar = $getDomain->registrar;
		$regperiod = $getDomain->registrationperiod;
		$domainparts = explode(".", $domain, 2);
	
		$params = [
			"domainid" => $id,
			"sld" => $domainparts[0],
			"tld" => $domainparts[1],
			"regperiod" => $regperiod,
			"registrar" => $registrar
		];
	
		$params = array_merge($params, $contact);
		$values = $this->registrar->RegSaveContactDetails($params);
	
		if (isset($values["error"])) {
			return ["result" => "error", "message" => "Registrar Error Message", "error" => $values["error"]];
		}
	
		return ["result" => "success"];
	}

	public function uwd_xml2array($contents, $get_attributes = 1, $priority = "tag")
	{
		$parser = xml_parser_create("");
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
	
		if (!$xml_values) {
			return null;
		}
	
		$xml_array = [];
		$parents = [];
		$opened_tags = [];
		$arr = [];
		$current =& $xml_array;
		$repeated_tag_index = [];
	
		foreach ($xml_values as $data) {
			unset($attributes, $value);
			extract($data);
			$result = [];
			$attributes_data = [];
	
			if (isset($value)) {
				$result = ($priority == "tag") ? $value : ["value" => $value];
			}
	
			if (isset($attributes) && $get_attributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == "tag") {
						$attributes_data[$attr] = $val;
					} else {
						$result["attr"][$attr] = $val;
					}
				}
			}
	
			if ($type == "open") {
				$parent[$level - 1] =& $current;
				if (!is_array($current) || !in_array($tag, array_keys($current))) {
					$current[$tag] = $result;
					if ($attributes_data) {
						$current[$tag . "_attr"] = $attributes_data;
					}
					$repeated_tag_index[$tag . "_" . $level] = 1;
					$current =& $current[$tag];
				} else {
					if (isset($current[$tag][0])) {
						$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;
						$repeated_tag_index[$tag . "_" . $level]++;
					} else {
						$current[$tag] = [$current[$tag], $result];
						$repeated_tag_index[$tag . "_" . $level] = 2;
						if (isset($current[$tag . "_attr"])) {
							$current[$tag]["0_attr"] = $current[$tag . "_attr"];
							unset($current[$tag . "_attr"]);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . "_" . $level] - 1;
					$current =& $current[$tag][$last_item_index];
				}
			} elseif ($type == "complete") {
				if (!isset($current[$tag])) {
					$current[$tag] = $result;
					$repeated_tag_index[$tag . "_" . $level] = 1;
					if ($priority == "tag" && $attributes_data) {
						$current[$tag . "_attr"] = $attributes_data;
					}
				} else {
					if (isset($current[$tag][0]) && is_array($current[$tag])) {
						$current[$tag][$repeated_tag_index[$tag . "_" . $level]] = $result;
						if ($priority == "tag" && $get_attributes && $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
						}
						$repeated_tag_index[$tag . "_" . $level]++;
					} else {
						$current[$tag] = [$current[$tag], $result];
						$repeated_tag_index[$tag . "_" . $level] = 1;
						if ($priority == "tag" && $get_attributes) {
							if (isset($current[$tag . "_attr"])) {
								$current[$tag]["0_attr"] = $current[$tag . "_attr"];
								unset($current[$tag . "_attr"]);
							}
							if ($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag . "_" . $level] . "_attr"] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . "_" . $level]++;
					}
				}
			} elseif ($type == "close") {
				$current =& $parent[$level - 1];
			}
		}
	
		return $xml_array;
	}
	
	public function all()
    {
        return $this->statusValues;
    }

	public function allWithTranslations()
    {
        $statuses = [];
        foreach ($this->statusValues as $status) {
            $statuses[$status] = $this->translate($status);
        }
        return $statuses;
    }

	protected function translate($status)
    {
        $status = strtolower(str_replace(" ", "", $status));

        return __("admin.status" . $status);
    }

	public function translatedDropdownOptions(array $selectedStatus = NULL)
    {
        $options = "";
        foreach ($this->allWithTranslations() as $dbValue => $translation) {
            $selected = is_array($selectedStatus) && in_array($dbValue, $selectedStatus) ? " selected=\"selected\"" : "";
            $options .= "<option value=\"" . $dbValue . "\"" . $selected . ">" . $translation . "</option>";
        }

        return $options;
    }

	public function getDomainsDatabyID($domainid)
    {
        $where = ["id" => (int) $domainid];

		// If auth = Client
        if (!auth()->guard("admin")->check()) {
            if (!auth()->user()->id) {
                return false;
            }
			
            $where["userid"] = auth()->user()->id;
        }
		
        return $this->getDomainsData($where);
    }

	private function getDomainsData(array $where)
    {
        try {
            $domain = Domain::findOrFail($where["id"]);
            if (array_key_exists("userid", $where) && $domain->userid != $where["userid"]) {
                throw new NotServicable("Invalid Access Attempt");
            }

            $this->id = $domain->id;
            $this->data = $domain->toArray();
            $this->domainModel = $domain;

            return $this->data;
        } catch (\Exception $e) {
            return false;
        }
    }

	public function getData($var)
    {
        return isset($this->data[$var]) ? $this->data[$var] : "";
    }

	public function getModule()
    {
        return (new \App\Helpers\Sanitize())->sanitize("0-9a-z_-", $this->getData("registrar"));
    }

	public function hasFunction($function)
	{
		static $mod = null;
		if (!$mod) {
			$mod = new \App\Module\Registrar();
			$mod->setLoadedModule($this->getModule());
		}
	
		return $mod->functionExists($function);
	}
	
	public function moduleCall2($function, $additionalVars = [])
	{
		$module = $this->getModule();
		if (!$module) {
			$this->moduleresults = ["error" => "Domain not assigned to a registrar module"];
			return false;
		}
	
		if (is_null($this->registrarModule)) {
			$this->registrarModule = new \App\Module\Registrar();
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
			"functionExists" => $results !== \App\Module\Registrar::FUNCTIONDOESNTEXIST,
			"functionSuccessful" => (is_array($results) && empty($results["error"])) || is_object($results)
		];
	
		$successOrFail = !$vars["functionSuccessful"] && $vars["functionExists"] ? "Failed" : "";
		$hookResults = Hooks::run_hook("AfterRegistrar" . $function . $successOrFail, $vars);
	
		try {
			if ($mod->processHookResults($module, $function, $hookResults)) {
				return true;
			}
		} catch (\Exception $e) {
			return ["error" => $e->getMessage()];
		}
	
		if ($results === \App\Module\Registrar::FUNCTIONDOESNTEXIST) {
			$this->moduleresults = ["error" => "Function not found"];
			return false;
		}
	
		$this->moduleresults = $results;
		return !(is_array($results) && array_key_exists("error", $results) && $results["error"]);
	}
	
	public function getModuleReturn($var = "")
	{
		return $var ? ($this->moduleresults[$var] ?? "") : $this->moduleresults;
	}
	
	public function getLastError()
	{
		return $this->getModuleReturn("error");
	}
	
	public function moduleCall($function, $additionalVars = [])
	{
		return $this->getRegistrarModule()->loadModule($this->getModule(), $function, $additionalVars);
	}
	
	public function getRegistrarModule()
	{
		if (!$this->getModule()) {
			return ["error", "Modul N/A"];
		}
	
		$mod = new \App\Module\Registrar();
		$mod->setLoadedModule($this->getModule());
	
		return $mod;
	}
	
	public function getNameservers()
	{
		static $mod = null;
		if (!$mod) {
			$mod = new \App\Module\Registrar();
			$mod->setLoadedModule($this->getModule());
		}
	
		return $mod->loadModule($this->getModule(), "GetNameservers", []);
	}
	
	public function obtainEmailReminders()
	{
		$reminders = \App\Models\Domainreminder::where("domain_id", $this->id)
			->orderBy("id", "DESC")
			->get()
			->toArray();
	
		return array_map(fn($data) => $data, $reminders);
	}
	
	public function saveContactDetails(ClientClass $client, array $contactdetails, array $wc, array $sel = null)
	{
		$userContactDetails = $client->getDetails();
		$language = $userContactDetails["language"] ?? Cfg::get("Language");
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
				$this->normaliseInternationalPhoneNumberFormat($contactdetails[$wc_key]);
			}
		}
	
		$success = $this->moduleCall2("SaveContactDetails", [
			"irtpOptOut" => request()->get("irtpOptOut"),
			"irtpOptOutReason" => request()->get("irtpOptOutReason"),
			"contactdetails" => (new \App\Module\Registrar())->foreignChrReplace($contactdetails),
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
	
		throw new \App\Exceptions\Module\NotServicable($this->getLastError());
	}
	
	public function buildWHOISSaveArray($data)
	{
		$arr = [
			"First Name" => "firstname", "Last Name" => "lastname", "Full Name" => "fullname",
			"Contact Name" => "fullname", "Email" => "email", "Email Address" => "email",
			"Job Title" => "", "Company Name" => "companyname", "Organisation Name" => "companyname",
			"Address" => "address1", "Address 1" => "address1", "Street" => "address1",
			"Address 2" => "address2", "City" => "city", "State" => "state", "County" => "state",
			"Region" => "state", "Postcode" => "postcode", "ZIP Code" => "postcode", "ZIP" => "postcode",
			"Country" => "country", "Phone" => "phonenumberformatted", "Phone Number" => "phonenumberformatted",
			"Phone Country Code" => "phonecc"
		];
	
		$retarr = [];
		foreach ($arr as $k => $v) {
			if (isset($data[$v])) {
				$retarr[$k] = $data[$v];
			}
		}
		return $retarr;
	}
	
	public function normaliseInternationalPhoneNumberFormat(array &$details)
	{
		if (isset($details["Phone Country Code"])) {
			if (isset($details["Phone"])) {
				$details["Phone"] = "+" . $details["Phone Country Code"] . "." . preg_replace("/[^0-9]/", "", $details["Phone"]);
				$details["phone-normalised"] = true;
			}
			if (isset($details["Phone Number"])) {
				$details["Phone Number"] = "+" . $details["Phone Country Code"] . "." . preg_replace("/[^0-9]/", "", $details["Phone Number"]);
				$details["phone-normalised"] = true;
			}
		}
	}
	
	public function getDomainInformation()
	{
		if (is_null($this->domainInformation)) {
			$domainInformation = null;
			if ($this->hasFunction("GetDomainInformation")) {
				$success = $this->moduleCall2("GetDomainInformation");
				if (!$success) {
					throw new \App\Exceptions\Module\NotServicable($this->getLastError());
				}
				$domainInformation = $this->getModuleReturn();
				if (!$domainInformation instanceof \App\Helpers\Domain\Registrar\Domain) {
					throw new \App\Exceptions\Module\NotServicable("Invalid Response");
				}
			}
			if (!$domainInformation) {
				$domainInformation = new \App\Helpers\Domain\Registrar\Domain();
			}
			if (!$domainInformation->hasNameservers() && $this->hasFunction("GetNameservers")) {
				$success = $this->moduleCall2("GetNameservers");
				if ($success) {
					$domainInformation->setNameservers($this->getModuleReturn());
				} else {
					throw new \App\Exceptions\Module\NotServicable($this->getLastError());
				}
			}
			if (!$domainInformation->hasTransferLock() && $this->hasFunction("GetRegistrarLock")) {
				$success = $this->moduleCall2("GetRegistrarLock");
				if ($success) {
					$domainInformation->setTransferLock($this->getModuleReturn() === "locked");
				}
			}
			$this->domainInformation = $domainInformation;
		}
	
		return $this->domainInformation;
	}
}