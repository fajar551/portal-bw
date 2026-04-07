<?php

namespace App\Http\Controllers\Api\Client;

use DB, Auth;
use Validator;
use App\Helpers\Cfg;
use App\Helpers\Hooks;
use App\Helpers\ResponseAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Client
 *
 * APIs for managing client
 */
class ClientV2Controller extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * AddClient
     *
     * Adds a client.
     */
    public function AddClient()
    {
        $validator = Validator::make($this->request->all(), [
            // The ID of the user that should own the client. Optional. When not provided, a new user will be created.
            'owner_user_id' => ['nullable', 'integer'],
            //
            'firstname' => ['required', 'string'],
            //
            'lastname' => ['required', 'string'],
            //
            'companyname' => ['nullable', 'string'],
            //
            'email' => ['required', 'string', 'unique:App\Models\Client'],
            //
            'address1' => ['required', 'string'],
            //
            'address2' => ['nullable', 'string'],
            //
            'city' => ['required', 'string'],
            //
            'state' => ['required', 'string'],
            //
            'postcode' => ['required', 'string'],
            // 2 character ISO country code
            'country' => ['required', 'string'],
            //
            'phonenumber' => ['required', 'string'],
            // The client Tax ID
            'tax_id' => ['nullable', 'string'],
            // The password for the newly created user account. Required when $owner_user_id is not provided
            'password2' => ['required_without:owner_user_id', 'string'],
            // The ID of the Security Question from tbladminsecurityquestions for a newly created user
            'securityqid' => ['nullable', 'integer'],
            // The Security Question Answer for a newly created user
            'securityqans' => ['nullable', 'string'],
            // Currency ID from tblcurrencies
            'currency' => ['nullable', 'integer'],
            // Client Group ID from tblclientgroups
            'groupid' => ['nullable', 'integer'],
            // Base64 encoded serialized array of custom field values
            'customfields' => ['nullable', 'string'],
            // Default language setting. Provide full name: ‘english’, ‘french’, etc…
            'language' => ['nullable', 'string'],
            // The originating IP address for the request
            'clientip' => ['nullable', 'string'],
            // Admin only notes
            'notes' => ['nullable', 'string'],
            // Set true to opt client in to marketing emails
            'marketingoptin' => ['nullable', 'boolean'],
            // Pass as true to skip sending welcome email
            'noemail' => ['nullable', 'boolean'],
            // Pass as true to ignore required fields validation. Does not apply to $email and $password2 when $owner_user_id is not provided
            'skipvalidation' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $clientIp = $this->request->input("clientip");
        $customFields = $this->request->input("customfields");
        $skipValidation = $this->request->input("skipvalidation");
        $noEmail = $this->request->input("noemail");
        if ($clientIp) {
            $remote_ip = $clientIp;
        }
        $currency = (int) $this->request->input("currency");
        $language = $this->request->input("language") ?? "";
        $firstName = $this->request->input("firstname");
        $lastName = $this->request->input("lastname");
        $companyName = $this->request->input("companyname") ?? "";
        $email = $this->request->input("email");
        $address1 = $this->request->input("address1") ?? "";
        $address2 = $this->request->input("address2") ?? "";
        $city = $this->request->input("city");
        $state = $this->request->input("state");
        $postcode = $this->request->input("postcode");
        $country = $this->request->input("country");
        $phoneNumber = $this->request->input("phonenumber");
        $taxId = $this->request->input("tax_id");
        $password2 = $this->request->input("password2");
        $securityQuestionId = (int) $this->request->input("securityqid");
        $securityQuestionAnswer = $this->request->input("securityqans");
        $clientGroupId = $this->request->input("groupid");
        $notes = $this->request->input("notes");
        $marketingOptIn = $this->request->has("marketingoptin") ? (bool) $this->request->input("marketingoptin") : (bool) (!Cfg::getValue("EmailMarketingRequireOptIn"));
        $customFieldsErrors = array();
        if (!empty($customFields)) {
            $customFields = (new \App\Helpers\Client())->safe_unserialize(base64_decode($customFields));
            $validate = new \App\Helpers\Validate();
            $validate->validateCustomFields("client", "", false, $customFields);
            $customFieldsErrors = $validate->getErrors();
        } else {
            $fetchedCustomClientFields = \App\Helpers\Customfield::getCustomFields("client", NULL, NULL);
            if (is_array($fetchedCustomClientFields)) {
                foreach ($fetchedCustomClientFields as $fetchedCustomClientField) {
                    if ($fetchedCustomClientField["required"] == "*") {
                        $customFieldsErrors[] = "You did not provide required custom field value for " . $fetchedCustomClientField["name"];
                    }
                }
            }
        }
        session()->put('currency', $currency);
        $sendEmail = $noEmail ? false : true;
        $langAtStart = session()->get('Language');
        if ($language) {
            session()->put('Language', $language);
        }

        DB::beginTransaction();
        try {
            $clientId = \App\Helpers\ClientHelper::addClient($firstName, $lastName, $companyName, $email, $address1, $address2, $city, $state, $postcode, $country, $phoneNumber, $password2, $securityQuestionId, $securityQuestionAnswer, $sendEmail, array("notes" => $notes, "groupid" => $clientGroupId, "customfields" => $customFields, "tax_id" => $taxId), "", true, $marketingOptIn);
            $apiresults = array("clientid" => $clientId);
            $cardType = $this->request->input("cardtype");
            if (!$cardType) {
                $cardType = $this->request->input("cctype");
            }
            if ($cardType) {
                // $apiresults["warning"] = "Credit card related parameters are now deprecated" . " and may be removed in a future version. Use AddPayMethod instead.";
                // if (!function_exists("updateCCDetails")) {
                //     require ROOTDIR . "/includes/ccfunctions.php";
                // }
                // $cardNumber = $whmcs->get_req_var("cardnum");
                // $cardCVV = $whmcs->get_req_var("cvv");
                // $cardExpiry = $whmcs->get_req_var("expdate");
                // $cardStartDate = $whmcs->get_req_var("startdate");
                // $cardIssueNumber = $whmcs->get_req_var("issuenumber");
                // updateCCDetails($clientId, $cardType, $cardNumber, $cardCVV, $cardExpiry, $cardStartDate, $cardIssueNumber);
                // unset($cardNumber);
                // unset($cardCVV);
                // unset($cardExpiry);
                // unset($cardStartDate);
                // unset($cardIssueNumber);
            }
            if (Cfg::getValue("TaxEUTaxValidation")) {
                $client = \App\User\Client::find($clientId);
                $taxExempt = \App\Helpers\Vat::setTaxExempt($client);
                $client->save();
                if ($taxExempt != $additionalData["taxexempt"]) {
                    $additionalData["taxexempt"] = $taxExempt;
                }
            }
            Hooks::run_hook("ClientAdd", array_merge(array("userid" => $clientId, "firstname" => $firstName, "lastname" => $lastName, "companyname" => $companyName, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phoneNumber, "tax_id" => $taxId, "password" => $password2), array("notes" => $notes, "groupid" => $clientGroupId), array("customfields" => $customFields)));
            session()->put('Language', $langAtStart);

            DB::commit();

            return ResponseAPI::Success($apiresults);
        } catch (\Exception $e) {
            DB::rollBack();

            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function GetClientsDomains()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'clientid' => ['nullable', 'integer'],
            'domainid' => ['nullable', 'integer'],
            'domain' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $limitstart = $this->request->input("limitstart") ?? 0;
        $limitnum = $this->request->input("limitnum") ?? 25;
        $clientid = $this->request->input("clientid");
        $domainid = $this->request->input("domainid");
        $domain = $this->request->input("domain");

        $where = array();
        if ($clientid) {
            $where["tbldomains.userid"] = $clientid;
        }
        if ($domainid) {
            $where["tbldomains.id"] = $domainid;
        }
        if ($domain) {
            $where["tbldomains.domain"] = $domain;
        }
        $result = \App\Models\Domain::where($where)->count();
        $data = $result;
        $totalresults = $data;
        $limitstart = (int) $limitstart;
        $limitnum = (int) $limitnum;
        if (!$limitnum) {
            $limitnum = 25;
        }
        $result = \App\Models\Domain::selectRaw("tbldomains.*,(SELECT tblpaymentgateways.value FROM tblpaymentgateways WHERE tblpaymentgateways.gateway=tbldomains.paymentmethod AND tblpaymentgateways.setting='name' LIMIT 1) AS paymentmethodname")
        ->where($where)
        ->orderBy("tbldomains.id", "ASC")
        ->limit($limitstart, $limitnum)
        ->get();
        $apiresults = array("result" => "success", "clientid" => $clientid, "domainid" => $domainid, "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $result->count());
        if (!$totalresults) {
            $apiresults["domains"] = "";
        }
        foreach ($result->toArray() as $data) {
            $id = $data["id"];
            $userid = $data["userid"];
            $orderid = $data["orderid"];
            $type = $data["type"];
            $registrationdate = $data["registrationdate"];
            $domain = $data["domain"];
            $firstpaymentamount = $data["firstpaymentamount"];
            $recurringamount = $data["recurringamount"];
            $registrar = $data["registrar"];
            $registrationperiod = $data["registrationperiod"];
            $expirydate = $data["expirydate"];
            $nextduedate = $data["nextduedate"];
            $status = $data["status"];
            $subscriptionid = $data["subscriptionid"];
            $promoid = $data["promoid"];
            $additionalnotes = $data["additionalnotes"];
            $paymentmethod = $data["paymentmethod"];
            $paymentmethodname = $data["paymentmethodname"];
            $dnsmanagement = $data["dnsmanagement"];
            $emailforwarding = $data["emailforwarding"];
            $idprotection = $data["idprotection"];
            $donotrenew = $data["donotrenew"];
            $nameservers = array();
            if ($getnameservers) {
                $domainparts = explode(".", $domain, 2);
                $params = array();
                $params["domainid"] = $id;
                list($params["sld"], $params["tld"]) = $domainparts;
                $params["regperiod"] = $registrationperiod;
                $params["registrar"] = $registrar;
                $nameservers = (new \App\Module\Registrar)->RegGetNameservers($params);
                $nameservers["nameservers"] = true;
            }
            $apiresults["domains"]["domain"][] = array_merge(array("id" => $id, "userid" => $userid, "orderid" => $orderid, "regtype" => $type, "domainname" => $domain, "registrar" => $registrar, "regperiod" => $registrationperiod, "firstpaymentamount" => $firstpaymentamount, "recurringamount" => $recurringamount, "paymentmethod" => $paymentmethod, "paymentmethodname" => $paymentmethodname, "regdate" => $registrationdate, "expirydate" => $expirydate, "nextduedate" => $nextduedate, "status" => $status, "subscriptionid" => $subscriptionid, "promoid" => $promoid, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "donotrenew" => $donotrenew, "notes" => $additionalnotes), $nameservers);
        }

        return ResponseAPI::Success($apiresults);
    }

    public function GetClientsDetails()
    {
        $validator = Validator::make($this->request->all(), [
            'clientid' => ['nullable', 'integer'],
            'email' => ['nullable', 'string'],
            'stats' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $clientid = $this->request->input("clientid");
        $email = $this->request->input("email");
        $stats = $this->request->input("stats");

        $where = array();
        if ($clientid) {
            $where["id"] = $clientid;
        } else {
            if ($email) {
                $where["email"] = $email;
            } else {
                $apiresults = array("result" => "error", "message" => "Either clientid Or email Is Required");
                return ResponseAPI::Error($apiresults);
            }
        }
        $client = DB::table("tblclients");
        if ($clientid) {
            $client->where("id", $clientid);
        } else {
            if ($email) {
                $client->where("email", $email);
            } else {
                $apiresults = array("result" => "error", "message" => "Either clientid Or email Is Required");
                return ResponseAPI::Error($apiresults);
            }
        }
        if ($client->count() === 0) {
            $apiresults = array("result" => "error", "message" => "Client Not Found");
            return ResponseAPI::Error($apiresults);
        } else {
            $clientid = $client->value("id");
            $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($clientid);
            unset($clientsdetails["model"]);
            $currency_result = \App\Models\Currency::where('id', $clientsdetails["currency"]);
            $currency = $currency_result;
            $clientsdetails["currency_code"] = $currency->value("code");
            $apiresults = array_merge(array("result" => "success"), $clientsdetails);
            if ($clientsdetails["cctype"]) {
                $apiresults["warning"] = "Credit Card related parameters are now deprecated " . "and have been removed. Use GetPayMethods instead.";
            }
            unset($clientsdetails["cctype"]);
            unset($clientsdetails["cclastfour"]);
            unset($clientsdetails["gatewayid"]);
            $apiresults["client"] = $clientsdetails;
            if ($stats) {
                $apiresults["stats"] = (new \App\Helpers\ClientHelper)->getClientsStats($clientid);
            }
            return ResponseAPI::Success($apiresults);
        }
    }

    public function GetClientsProducts()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'clientid' => ['nullable', 'integer'],
            'serviceid' => ['nullable', 'integer'],
            'pid' => ['nullable', 'integer'],
            'domain' => ['nullable', 'string'],
            'username2' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $limitstart = $this->request->input('limitstart');
        $limitnum = $this->request->input('limitnum');
        $clientid = $this->request->input('clientid');
        $serviceid = $this->request->input('serviceid');
        $pid = $this->request->input('pid');
        $domain = $this->request->input('domain');
        $username2 = $this->request->input('username2');

        $where = array();
        if ($clientid) {
            $where["tblhosting.userid"] = $clientid;
        }
        if ($serviceid) {
            $where["tblhosting.id"] = $serviceid;
        }
        if ($pid) {
            $where["tblhosting.packageid"] = $pid;
        }
        if ($domain) {
            $where["tblhosting.domain"] = $domain;
        }
        if ($username2) {
            $where["tblhosting.username"] = $username2;
        }
        $result = \App\Models\Hosting::where($where)->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")->join("tblproductgroups", "tblproductgroups.id", "=", "tblproducts.gid")->count();
        $data = $result;
        $totalresults = $data;
        $limitstart = (int) $limitstart;
        $limitnum = (int) $limitnum;
        if (!$limitnum) {
            $limitnum = 999999;
        }
        $result = \App\Models\Hosting::selectRaw("tblhosting.*,tblproductgroups.name as group_name,tblproductgroups.id AS group_id,tblproducts.name," . "(SELECT CONCAT(name,'|',ipaddress,'|',hostname) FROM tblservers WHERE tblservers.id=tblhosting.server) AS serverdetails," . "(SELECT tblpaymentgateways.value FROM tblpaymentgateways WHERE tblpaymentgateways.gateway=tblhosting.paymentmethod AND tblpaymentgateways.setting='name' LIMIT 1) AS paymentmethodname")
            ->where($where)
            ->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
            ->join("tblproductgroups", "tblproductgroups.id", "=", "tblproducts.gid")
            ->offset($limitstart)
            ->limit($limitnum)
            ->orderby("tblhosting.id", "ASC")
            ->get();
        $apiresults = array("result" => "success", "clientid" => $clientid, "serviceid" => $serviceid, "pid" => $pid, "domain" => $domain, "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $result->count());
        if (!$totalresults) {
            $apiresults["products"] = "";
        }
        foreach ($result->toArray() as $data) {
            $id = $data["id"];
            $userid = $data["userid"];
            $orderid = $data["orderid"];
            $pid = $data["packageid"];
            $name = $data["name"];
            $suspensionReason = $data["suspendreason"];
            if (empty($name)) {
                $name = \App\Models\Product::find($pid, array("name"))->name;
            }
            $language = \App\Helpers\Cfg::getValue("Language");
            if ($userid) {
                $language = \App\User\Client::find($userid, array("language"))->language ?: $language;
            }
            $translatedName = \App\Models\Product::getProductName($data["packageid"], $name);
            $groupname = $data["group_name"];
            $translatedGroupName =\App\Models\Productgroup::getGroupName($data["group_id"], $groupname);
            $server = $data["server"];
            $regdate = $data["regdate"];
            $domain = $data["domain"];
            $paymentmethod = $data["paymentmethod"];
            $paymentmethodname = $data["paymentmethodname"];
            $firstpaymentamount = $data["firstpaymentamount"];
            $recurringamount = $data["amount"];
            $billingcycle = $data["billingcycle"];
            $nextduedate = $data["nextduedate"];
            $domainstatus = $data["domainstatus"];
            $username = $data["username"];
            $password = (new \App\Helpers\Pwd)->decrypt($data["password"]);
            $notes = $data["notes"];
            $subscriptionid = $data["subscriptionid"];
            $promoid = $data["promoid"];
            // $ipaddress = $data["ipaddress"];
            $overideautosuspend = $data["overideautosuspend"];
            $overidesuspenduntil = $data["overidesuspenduntil"];
            $ns1 = $data["ns1"];
            $ns2 = $data["ns2"];
            $dedicatedip = $data["dedicatedip"];
            $assignedips = $data["assignedips"];
            $diskusage = $data["diskusage"];
            $disklimit = $data["disklimit"];
            $bwusage = $data["bwusage"];
            $bwlimit = $data["bwlimit"];
            $lastupdate = $data["lastupdate"];
            $serverdetails = $data["serverdetails"];
            $serverdetails = explode("|", $serverdetails);
            $customfieldsdata = array();
            $customfields = \App\Helpers\Customfield::getCustomFields("product", $pid, $id, "on", "");
            foreach ($customfields as $customfield) {
                $customfieldsdata[] = array("id" => $customfield["id"], "name" => $customfield["name"], "translated_name" => \App\Models\Customfield::getFieldName($customfield["id"], $customfield["name"], $language), "value" => $customfield["value"]);
            }
            $configoptionsdata = array();
            $currency = \App\Helpers\Format::getCurrency($userid);
            $configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, "", $billingcycle, $id, "", true);
            foreach ($configoptions as $configoption) {
                switch ($configoption["optiontype"]) {
                    case 1:
                        $type = "dropdown";
                        break;
                    case 2:
                        $type = "radio";
                        break;
                    case 3:
                        $type = "yesno";
                        break;
                    case 4:
                        $type = "quantity";
                        break;
                }
                if ($configoption["optiontype"] == "3" || $configoption["optiontype"] == "4") {
                    $configoptionsdata[] = array("id" => $configoption["id"], "option" => $configoption["optionname"], "type" => $type, "value" => $configoption["selectedqty"]);
                } else {
                    $configoptionsdata[] = array("id" => $configoption["id"], "option" => $configoption["optionname"], "type" => $type, "value" => $configoption["selectedoption"]);
                }
            }
            $apiresults["products"]["product"][] = array(
                "id" => $id,
                "clientid" => $userid,
                "orderid" => $orderid,
                "pid" => $pid,
                "regdate" => $regdate,
                "name" => $name,
                "translated_name" => $translatedName,
                "groupname" => $groupname,
                "translated_groupname" => $translatedGroupName,
                "domain" => $domain,
                "dedicatedip" => $dedicatedip,
                "serverid" => $server,
                "servername" => array_key_exists(0, $serverdetails) ? $serverdetails[0] : "",
                "serverip" => array_key_exists(1, $serverdetails) ? $serverdetails[1] : "",
                "serverhostname" => array_key_exists(2, $serverdetails) ? $serverdetails[2] : "",
                "suspensionreason" => $suspensionReason,
                "firstpaymentamount" => $firstpaymentamount,
                "recurringamount" => $recurringamount,
                "paymentmethod" => $paymentmethod,
                "paymentmethodname" => $paymentmethodname,
                "billingcycle" => $billingcycle,
                "nextduedate" => $nextduedate,
                "status" => $domainstatus,
                "username" => $username,
                "password" => $this->convert_encoding_password($password),
                "subscriptionid" => $subscriptionid,
                "promoid" => $promoid,
                "overideautosuspend" => $overideautosuspend,
                "overidesuspenduntil" => $overidesuspenduntil,
                "ns1" => $ns1,
                "ns2" => $ns2,
                "dedicatedip" => $dedicatedip,
                "assignedips" => $assignedips,
                "notes" => $notes,
                "diskusage" => $diskusage,
                "disklimit" => $disklimit,
                "bwusage" => $bwusage,
                "bwlimit" => $bwlimit,
                "lastupdate" => $lastupdate,
                "customfields" => array("customfield" => $customfieldsdata),
                "configoptions" => array("configoption" => $configoptionsdata),
            );
        }

        return ResponseAPI::Success($apiresults);
    }

    private function convert_encoding_password($value)
    {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        return "";
    }

    public function GetClientsAddons()
    {
        $validator = Validator::make($this->request->all(), [
            'serviceid' => ['nullable', 'integer'],
            'clientid' => ['nullable', 'integer'],
            'addonid' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceid = $this->request->input('serviceid');
        $clientid = $this->request->input('clientid');
        $addonid = $this->request->input('addonid');

        $query = DB::table("tblhostingaddons")->distinct()->join("tblhosting", "tblhosting.id", "=", "tblhostingaddons.hostingid")->join("tbladdons", "tbladdons.id", "=", "tblhostingaddons.addonid", "LEFT");
        if ($serviceid) {
            if (is_numeric($serviceid)) {
                $query = $query->where("tblhostingaddons.hostingid", "=", $serviceid);
            } else {
                $serviceids = array_map("trim", explode(",", $serviceid));
                $query = $query->whereIn("tblhostingaddons.hostingid", $serviceids);
            }
        }
        if ($clientid) {
            $query = $query->where("tblhosting.userid", "=", $clientid);
        }
        if ($addonid) {
            $query = $query->where("tblhostingaddons.addonid", "=", $addonid);
        }
        $query = $query->orderBy("tblhostingaddons.id", "ASC");
        $result = $query->get(array("tblhostingaddons.*", "tblhosting.userid", "tbladdons.name AS addon_name"));
        $apiresults = array("result" => "success", "serviceid" => $serviceid, "clientid" => $clientid, "totalresults" => count($result));
        foreach ($result as $data) {
            $addonarray = array("id" => $data->id, "userid" => $data->userid, "orderid" => $data->orderid, "serviceid" => $data->hostingid, "addonid" => $data->addonid, "name" => $data->name ?: $data->addon_name, "setupfee" => $data->setupfee, "recurring" => $data->recurring, "billingcycle" => $data->billingcycle, "tax" => $data->tax, "status" => $data->status, "regdate" => $data->regdate, "nextduedate" => $data->nextduedate, "nextinvoicedate" => $data->nextinvoicedate, "paymentmethod" => $data->paymentmethod, "notes" => $data->notes);
            $apiresults["addons"]["addon"][] = $addonarray;
        }

        return ResponseAPI::Success($apiresults);
    }

    public function GetClientGroups()
    {
        $result = \App\Models\Clientgroup::all();
        $data = $result;
        $totalresults = $data->count();
        $apiresults = array("result" => "success", "totalresults" => $totalresults);
        $result = \App\Models\Clientgroup::orderBy('id', 'ASC')->get();
        foreach ($result->toArray() as $data) {
            $apiresults["groups"]["group"][] = $data;
        }
        return ResponseAPI::Success($apiresults);
    }

    public function GetContacts()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'userid' => ['nullable', 'integer'],
            'firstname' => ['nullable', 'string'],
            'lastname' => ['nullable', 'string'],
            'companyname' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'address1' => ['nullable', 'string'],
            'address2' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'phonenumber' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $limitstart = $this->request->input('limitstart');
        $limitnum = $this->request->input('limitnum');
        $userid = $this->request->input('userid');
        $firstname = $this->request->input('firstname');
        $lastname = $this->request->input('lastname');
        $companyname = $this->request->input('companyname');
        $email = $this->request->input('email');
        $address1 = $this->request->input('address1');
        $address2 = $this->request->input('address2');
        $city = $this->request->input('city');
        $state = $this->request->input('state');
        $postcode = $this->request->input('postcode');
        $country = $this->request->input('country');
        $phonenumber = $this->request->input('phonenumber');
        $subaccount = $this->request->input('subaccount');

        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $where = array();
        if ($userid) {
            $where["userid"] = $userid;
        }
        if ($firstname) {
            $where["firstname"] = $firstname;
        }
        if ($lastname) {
            $where["lastname"] = $lastname;
        }
        if ($companyname) {
            $where["companyname"] = $companyname;
        }
        if ($email) {
            $where["email"] = $email;
        }
        if ($address1) {
            $where["address1"] = $address1;
        }
        if ($address2) {
            $where["address2"] = $address2;
        }
        if ($city) {
            $where["city"] = $city;
        }
        if ($state) {
            $where["state"] = $state;
        }
        if ($postcode) {
            $where["postcode"] = $postcode;
        }
        if ($country) {
            $where["country"] = $country;
        }
        if ($phonenumber) {
            $where["phonenumber"] = $phonenumber;
        }
        if ($subaccount) {
            $where["subaccount"] = "1";
        }
        $result = \App\Models\Contact::where($where)->get();
        $data = $result;
        $totalresults = $data->count();
        $result = \App\Models\Contact::where($where)->orderBy('id', 'ASC')->offset($limitstart)->limit($limitnum)->get();
        $apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $result->count());
        foreach ($result->toArray() as $data) {
            $apiresults["contacts"]["contact"][] = $data;
        }

        return ResponseAPI::Success($apiresults);
    }

    public function GetClients()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'sorting' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'orderby' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $limitStart = (int) $this->request->input('limitstart');
        $limitNum = (int) $this->request->input('limitnum');
        $sorting = $this->request->input('sorting');
        $status = $this->request->input('status');
        $search = $this->request->input('search');
        $orderby = $this->request->input('orderby') ?? "lastname";

        if (!$limitStart) {
            $limitStart = 0;
        }
        if (!$limitNum || $limitNum == 0) {
            $limitNum = 25;
        }
        if (!in_array($sorting, array("ASC", "DESC"))) {
            $sorting = "ASC";
        }

        if (0 < strlen(trim($search))) {
            $whereStmt = "WHERE email LIKE '" . $search . "%' OR firstname LIKE '" . $search . "%' " . "OR lastname LIKE '" . $search . "%' OR companyname LIKE '" . $search . "%'" . "OR CONCAT(firstname, ' ', lastname) LIKE '" . $search . "%'";
        } else {
            $whereStmt = "";
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS id, firstname, lastname, companyname, email, groupid, datecreated, status\n        FROM tblclients\n        " . $whereStmt . "\n        ORDER BY $orderby " . $sorting . ", firstname " . $sorting . ", companyname " . $sorting . "\n        LIMIT " . (int) $limitStart . ", " . (int) $limitNum;
        $result = DB::select(DB::raw($sql));
        $result = array_map(function ($value) {
            return (array)$value;
        }, $result);
        $resultCount = count($result);
        $data = $resultCount;
        $totalResults = $data;
        $apiresults = array("result" => "success", "totalresults" => $totalResults, "startnumber" => $limitStart, "numreturned" => count($result));
        foreach ($result as $data) {
            $id = $data["id"];
            $firstName = $data["firstname"];
            $lastName = $data["lastname"];
            $companyName = $data["companyname"];
            $email = $data["email"];
            $groupID = $data["groupid"];
            $dateCreated = $data["datecreated"];
            $status = $data["status"];
            $apiresults["clients"]["client"][] = array("id" => $id, "firstname" => $firstName, "lastname" => $lastName, "companyname" => $companyName, "email" => $email, "datecreated" => $dateCreated, "groupid" => $groupID, "status" => $status);
        }

        return ResponseAPI::Success($apiresults);
    }

    public function GetEmails()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
            'clientid' => ['required', 'integer'],
            'date' => ['nullable', 'string'],
            'subject' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $limitstart = $this->request->input('limitstart');
        $limitnum = $this->request->input('limitnum');
        $clientid = $this->request->input('clientid');
        $date = $this->request->input('date');
        $subject = $this->request->input('subject');

        $result = \App\Models\Client::where(array("id" => $clientid));
        $data = $result;
        $clientid = $data->value('id');
        if (!$clientid) {
            $apiresults = array("status" => "error", "message" => "Client ID Not Found");
            return ResponseAPI::Error($apiresults);
        }

        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $where = array();
        $where[] = ['userid', '=', $clientid];
        if ($date) {
            $where[] = ['date', 'like', "%$date%"];
        }
        if ($subject) {
            $where[] = ['subject', 'like', "%$subject%"];
        }
        $result = \App\Models\Email::where($where);
        $data = $result;
        $totalresults = $data->count();
        $result = \App\Models\Email::where($where)->orderBy('id', 'DESC')->offset($limitstart)->limit($limitnum)->get();
        $apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $result->count());
        foreach ($result->toArray() as $data) {
            $apiresults["emails"]["email"][] = $data;
        }
        return ResponseAPI::Success($apiresults);
    }

    public function GetClientPassword()
    {
        $validator = Validator::make($this->request->all(), [
            'userid' => ['nullable', 'integer'],
            'email' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $userid = (int) $this->request->input('userid');
        $email = (int) $this->request->input('email');

        if ($userid) {
            $result = \App\Models\Client::where(array("id" => $userid));
        } else {
            $result = \App\Models\Client::where(array("email" => $email));
        }
        $data = $result;
        if ($data->value("id")) {
            $password = $data->value("password");
            $apiresults = array("result" => "success", "password" => $password);
            return ResponseAPI::Success($apiresults);
        } else {
            $apiresults = array("result" => "error", "message" => "Client ID Not Found");
            return ResponseAPI::Error($apiresults);
        }
    }

    public function GetCancelledPackages()
    {
        $validator = Validator::make($this->request->all(), [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $limitstart = $this->request->input('limitstart');
        $limitnum = $this->request->input('limitnum');

        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $result = \App\Models\Cancelrequest::all();
        $data = $result;
        $totalresults = $data->count();
        $result2 = \App\Models\Cancelrequest::offset($limitstart)->limit($limitnum)->get();
        $apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $result2->count(), "packages" => array());
        foreach ($result2->toArray() as $data) {
            $apiresults["packages"]["package"][] = $data;
        }
        return ResponseAPI::Success($apiresults);
    }
}
