<?php

namespace App\Http\Controllers\API\Domains;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ResponseAPI;
use App\Helpers\Domains;
use DB;

class DomainsController extends Controller
{
    public function __construct()
	{
		$this->domain = new Domains();
	}

    public function DomainWhois(Request $request){
        $validator = Validator::make($request->all(), [
            'domain'        => 'required|string'
        ]);

        if ($validator->fails()) {
             return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        try {
            
            return ResponseAPI::Success($this->domain->DomainWhois($request->domain));
            
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function GetTLDPricingOLD(Request $request){
        try {
            $params=[
                        'currencyid'    => (int) $request->currencyid,
                        'clientid'      => (int) $request->clientid,
                    ];
            return ResponseAPI::Success($this->domain->GetTLDPricing($params));
            
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }
    public function GetTLDPricing(Request $request)
    {
        $rules = [
            // The client ID to fetch pricing for. Pass this or clientid, but not both. clientid overrides currencyid.
            'clientid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            // The currency ID to fetch pricing for. Pass this or clientid, but not both. clientid overrides currencyid.
            'currencyid' => ['nullable', 'integer', 'exists:App\Models\Currency,id'],
        ];

        $messages = [
            'clientid.exists' => "Invalid Client ID",
            'currencyid.exists' => "Invalid Currency ID",
        ];
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $currencyId = (int) $request->input("currencyid");
        $userId = (int) $request->input("clientid");
        $clientGroupId = 0;
        if ($userId) {
            $client = \App\User\Client::find($userId);
            $userId = $client->id;
            $currencyId = $client->currencyId;
            $clientGroupId = $client->groupId;
        }
        $currency = \App\Helpers\Format::getCurrency("", $currencyId);
        $pricing = array();
        $result = DB::table("tblpricing")->whereIn("type", array("domainregister", "domaintransfer", "domainrenew"))->where("currency", $currency["id"])->where("tsetupfee", 0)->get();
        foreach ($result as $data) {
            $pricing[$data->relid][$data->type] = get_object_vars($data);
        }
        if ($clientGroupId) {
            $result2 = DB::table("tblpricing")->whereIn("type", array("domainregister", "domaintransfer", "domainrenew"))->where("currency", $currency["id"])->where("tsetupfee", $clientGroupId)->get();
            foreach ($result2 as $data) {
                $pricing[$data->relid][$data->type] = get_object_vars($data);
            }
        }
        $tldIds = array();
        $tldGroups = array();
        $tldAddons = array();
        $result = DB::table("tbldomainpricing")->get(array("id", "extension", "dnsmanagement", "emailforwarding", "idprotection", "group"));
        foreach ($result as $data) {
            $ext = ltrim($data->extension, ".");
            $tldIds[$ext] = $data->id;
            $tldGroups[$ext] = $data->group != "" && $data->group != "none" ? $data->group : "";
            $tldAddons[$ext] = array("dns" => (bool) $data->dnsmanagement, "email" => (bool) $data->emailforwarding, "idprotect" => (bool) $data->idprotection);
        }
        $extensions = \App\Models\Extension::all();
        $extensionsByTld = array();
        foreach ($extensions as $extension) {
            $tld = ltrim($extension->extension, ".");
            $extensionsByTld[$tld] = $extension;
        }
        $tldList = array_keys($extensionsByTld);
        $periods = array("msetupfee" => 1, "qsetupfee" => 2, "ssetupfee" => 3, "asetupfee" => 4, "bsetupfee" => 5, "monthly" => 6, "quarterly" => 7, "semiannually" => 8, "annually" => 9, "biennially" => 10);
        $categories = array();
        $result = DB::table("tbltlds")->join("tbltld_category_pivot", "tbltld_category_pivot.tld_id", "=", "tbltlds.id")->join("tbltld_categories", "tbltld_categories.id", "=", "tbltld_category_pivot.category_id")->whereIn("tld", $tldList)->get();
        foreach ($result as $data) {
            $categories[$data->tld][] = $data->category;
        }
        $usedTlds = array_keys($categories);
        $missedTlds = array_values(array_filter($tldList, function ($key) use($usedTlds) {
            return !in_array($key, $usedTlds);
        }));
        if ($missedTlds) {
            foreach ($missedTlds as $missedTld) {
                $categories[$missedTld][] = "Other";
            }
        }
        $apiresults = array("result" => "success", "currency" => $currency);
        foreach ($tldList as $tld) {
            $tldId = $tldIds[$tld];
            $apiresults["pricing"][$tld]["categories"] = $categories[$tld];
            $apiresults["pricing"][$tld]["addons"] = $tldAddons[$tld];
            $apiresults["pricing"][$tld]["group"] = $tldGroups[$tld];
            foreach (array("domainregister", "domaintransfer", "domainrenew") as $type) {
                foreach ($pricing[$tldId][$type] as $key => $price) {
                    if (array_key_exists($key, $periods) && ($type == "domainregister" && 0 <= $price || $type == "domaintransfer" && 0 < $price || $type == "domainrenew" && 0 < $price)) {
                        $apiresults["pricing"][$tld][str_replace("domain", "", $type)][$periods[$key]] = $price;
                    }
                }
            }
            if (isset($extensionsByTld[$tld])) {
                $extension = $extensionsByTld[$tld];
                $apiresults["pricing"][$tld]["grace_period"] = NULL;
                if (0 <= $extension->grace_period_fee) {
                    $gracePeriodFee = \App\Helpers\Format::convertCurrency($extension->grace_period_fee, 1, $currency["id"]);
                    $apiresults["pricing"][$tld]["grace_period"] = array("days" => 0 <= $extension->grace_period ? $extension->grace_period : $extension->defaultGracePeriod, "price" => new \App\Helpers\FormatterPrice($gracePeriodFee, $currency));
                }
                $apiresults["pricing"][$tld]["redemption_period"] = NULL;
                if (0 <= $extension->redemption_grace_period_fee) {
                    $redemptionGracePeriodFee = \App\Helpers\Format::convertCurrency($extension->redemption_grace_period_fee, 1, $currency["id"]);
                    $apiresults["pricing"][$tld]["redemption_period"] = array("days" => 0 <= $extension->redemption_grace_period ? $extension->redemption_grace_period : $extension->defaultRedemptionGracePeriod, "price" => new \App\Helpers\FormatterPrice($redemptionGracePeriodFee, $currency));
                }
            } else {
                continue;
            }
        }
        
        return ResponseAPI::Success($apiresults);
    }

    public function UpdateClientDomain(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);

        $params=[
                    'domainid'          => (int) $request->domainid,
                    'dnsmanagement'     => $request->dnsmanagement,
                    'emailforwarding'   => $request->emailforwarding,
                    'idprotection'      => $request->idprotection,
                    'donotrenew'        => $request->donotrenew,
                    'type'              => $request->type,
                    'regdate'           => $request->regdate,
                    'nextduedate'       => $request->nextduedate,
                    'expirydate'        => $request->expirydate,
                    'domain'            => $request->domain,
                    'firstpaymentamount'            => $request->firstpaymentamount,
                    'recurringamount'   => $request->recurringamount,
                    'registrar'         => $request->registrar,
                    'regperiod'         => $request->regperiod,
                    'paymentmethod'     => $request->paymentmethod,
                    'subscriptionid'    => $request->subscriptionid,
                    'status'            => $request->status,
                    'notes'             => $request->notes,
                    'promoid'           => $request->promoid,
                    'autorecalc'        => (int) (bool) $request->autorecalc,
                    'updatens'          => (int) (bool) $request->updatens,
                    'ns1'               => $request->ns1,
                    'ns2'               => $request->ns2,
                    'ns3'               => $request->ns3,
                    'ns4'               => $request->ns4,
                    'ns5'               => $request->ns5,

                ];
       
        if ($validator->fails()) {
             return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        try {
            return ResponseAPI::Success($this->domain->UpdateClientDomain($params));
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }


    public function GetHealthStatus(Request $request){
        try {

            $params=[ 'fetchStatus' => $request->fetchStatus ];

            /* if(isset($request->fetchStatus)){
                echo 'digunakan';
            }

            dd($params); */

            return ResponseAPI::Success($this->domain->GetHealthStatus($params));
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }


    public function GetServers(Request $request){
        try {
            $params=[
                    'serviceId'     => $request->serviceId, 
                    'addonId'       => $request->addonId, 
                    'fetchStatus'   => $request->fetchStatus 
                ];
            return ResponseAPI::Success($this->domain->GetServers($params));
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
        
    }


    public function DomainGetLockingStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        return $this->domain->DomainGetLockingStatus($request->all());
        
    }


    public function DomainGetNameservers(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }

        $domainid = $request->input('domainid');

        // return $this->domain->DomainGetNameservers($request->all());
        $result = \App\Models\Domain::where(array("id" => $domainid))->first();
        if (!$result) {
            return ResponseApi::Error(['message' => "Domain ID Not Found"]);
        }
        $data = $result->toArray();
        $domainid = $data['id'];
        $domain = $data["domain"];
        $registrar = $data["registrar"];
        $regperiod = $data["registrationperiod"];
        $domainparts = explode(".", $domain, 2);
        $params = array();
        $params["domainid"] = $domainid;
        list($params["sld"], $params["tld"]) = $domainparts;
        $params["regperiod"] = $regperiod;
        $params["registrar"] = $registrar;
        $values = (new \App\Module\Registrar())->RegGetNameservers($params);
        if (isset($values["na"])) {
            return ResponseApi::Error(['message' => "Registrar Function Not Supported"]);
        }
        if (isset($values["error"]) && $values["error"]) {
            return ResponseApi::Error(['message' => "Registrar Error Message", 'error' => $values["error"]]);
        }
        return ResponseApi::Success($values);
    }


    public function DomainGetWhoisInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        return $this->domain->DomainGetWhoisInfo($request->all());

    }


    public function DomainRegister(Request $request){
        try {
            $params=[
                    'domainid'     => $request->domainid ?? '', 
                    'domain'       => $request->domain ??'', 
                    'idnlanguage'  => $request->idnlanguage ?? ''
                ];
            return ResponseAPI::Success($this->domain->DomainRegister($params));
        } catch (\Exception $e) {
            dd($e);
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function DomainRelease(Request $request){
        try {
            $params=[
                    'domainid'     => $request->domainid ?? '', 
                    'domain'       => $request->domain ??'', 
                    'newtag'       => $request->newtag ?? ''
                ];
            return ResponseAPI::Success($this->domain->DomainRelease($params));
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }


    public function DomainRenew(Request $request){
        $params=[
            'domainid'     => $request->domainid ?? '', 
            'domain'       => $request->domain ??'', 
            'regperiod'    => $request->regperiod ?? ''
        ];
        try {
            return ResponseAPI::Success($this->domain->DomainRenew($params));
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }

    }

    public function DomainRequestEPP(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        try {
            return $this->domain->DomainRequestEPP($request->all());
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function DomainToggleIdProtect(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }

        $params=[
                    'domainid'      => (int) $request->domainid,
                    'idprotect'     => $request->idprotect ?? ''
                ];

        try {
            return $this->domain->DomainToggleIdProtect($params);
        } catch (\Exception $e) {
            return ResponseApi::Error(['message' => $e->getMessage()]);
        }
    }

    public function DomainTransfer(Request $request){
        $params=[
                    'domainid'      =>  $request->domainid,
                    'domain'        =>  $request->domain,
                    'eppcode'       =>  $request->eppcode,
                ];
        return $this->domain->DomainTransfer($params);

    }


    public function DomainUpdateLockingStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'        => 'required|int'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        $params=[
            'domainid'      =>  $request->domainid,
            'lockstatus'    =>  $request->lockstatus ?? '',
        ];
       // dd($params);
        return $this->domain->DomainUpdateLockingStatus($params);
    }

    public function DomainUpdateNameservers(Request $request){
        $validator = Validator::make($request->all(), [
            'ns1'           => 'required',
            'ns2'           => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }

        $params=[
            'domainid'      =>  $request->domainid,
            'domain'        =>  $request->domain ?? '',
            'ns1'           =>  $request->ns1,
            'ns2'           =>  $request->ns2,
            'ns3'           =>  $request->ns3,
            'ns4'           =>  $request->ns4,
            'ns5'           =>  $request->ns5,
        ];


        return $this->domain->DomainUpdateNameservers($params);
    }


    public function DomainUpdateWhoisInfo(Request $request){
        $validator = Validator::make($request->all(), [
            'domainid'      => 'required|int',
            'xml'           => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseApi::Error(['message' => $validator->errors()->first()]);
        }
        $params=[
            'domainid'      =>  $request->domainid,
            'xml'           =>  $request->xml ?? ''
        ];
        return $this->domain->DomainUpdateWhoisInfo($params);

    }



}
