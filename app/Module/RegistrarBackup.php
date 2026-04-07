<?php
namespace App\Module;

use Faker\Extension\Helper;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Carbon;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;
use App\Helpers\Hooks;
use App\Models\Hosting;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;

class RegistrarBackup
{
    protected $loadedmodule = "";
    protected $metaData = array();
    protected $type = 'registrar';
    protected $domainID = 0;
    protected $function = NULL;
    protected $moduleParams = array();
    private $builtParams = array();
    private $settings = array();
    const FUNCTIONDOESNTEXIST = "!Function not found in module!";

	public function RegGetRegistrarLock(Array $params){
		$values = $this->regcallfunction($params, "GetRegistrarLock", 1);
		if (is_array($values)) {
			return "";
		}
		return $values;
	}

    public function RegGetRegistrantContactEmailAddress(Array $params){
		$values = $this->regcallfunction($params, "GetRegistrantContactEmailAddress", 1);
		if (is_array($values)) {
			return "";
		}
		return $values;
	}
    public function RegGetRegistrantSync(Array $params){
		$values = $this->regcallfunction($params, "RegGetRegistrantSync", 1);
		if (is_array($values)) {
			return "";
		}
		return $values;
	}
    public function RegGetTransfersSync(Array $params){
		$values = $this->regcallfunction($params, "RegGetTransfersSync", 1);
		if (is_array($values)) {
			return "";
		}
		return $values;
	}

	public function RegGetNameservers($params){
		return $this->regcallfunction($params, "GetNameservers");
	}

	public function RegSaveRegistrarLock($params){
		$values = $this->regcallfunction($params, "SaveRegistrarLock");
		
		if (!$values) {
			return false;
		}
		
		$userid=\App\Models\Domain::find($params["domainid"]);
		if (@$values["error"]) {
			LogActivity::Save("Domain Registrar Command: Toggle Registrar Lock - Failed: " . $values["error"] . " - Domain ID: " . $params["domainid"], $userid->userid);
		} else {
			LogActivity::Save("Domain Registrar Command: Toggle Registrar Lock - Successful", $userid->userid);
		}
		
		return $values;
	}

	public function RegSaveNameservers($params){
		for ($i = 1; $i <= 5; $i++) {
			$params["ns" . $i] = trim($params["ns" . $i]);
		}
		$values = $this->regcallfunction($params, "SaveNameservers");
		if (!$values) {
			return false;
		}
		$data=\App\Models\Domain::find($params["domainid"]);
		$userid=$data->userid;
		if (@$values["error"]) {
			LogActivity::Save("Domain Registrar Command: Save Nameservers - Failed: " . $values["error"] . " - Domain ID: " . $params["domainid"], $userid);
		} else {
			LogActivity::Save("Domain Registrar Command: Save Nameservers - Successful", $userid);
		}
		return $values;
	}

	public function RegSaveContactDetails($params){
		$domainObj = new \App\Helpers\Domain\Domain($params["sld"] . "." . $params["tld"]);
		$domain = \App\Models\Domain::where('domain', $domainObj->getDomain())->first();
		$domainid=$domain->id;
		$userid=$domain->userid;
		$additflds = new \App\Helpers\Domain\AdditionalFields();
		$params["additionalfields"] = $additflds->getFieldValuesFromDatabase($domainid);
		$originaldetails = $params;
		if (!array_key_exists("original", $params)) {
			$params = $this->foreignChrReplace($params);
			$params["original"] = $originaldetails;
		}
		$params["domainObj"] = $domainObj;
		$values = $this->regcallfunction($params, "SaveContactDetails");
		if (@$values["error"]) {
			LogActivity::save("Domain Registrar Command: Update Contact Details - Failed: " . $values["error"] . " - Domain ID: " . $params["domainid"], $userid);
		} else {
			LogActivity::save("Domain Registrar Command: Update Contact Details - Successful", $userid);
		}
		return $values;

	}

	public function RegGetContactDetails($params)
	{
		return $this->regcallfunction($params, "GetContactDetails");
	}
	
	//->first()
	public function RegRegisterDomain($paramvars){
		$domainid = $paramvars["domainid"];
		$getDomain=\App\Models\Domain::find($domainid);
		$userid 			= $getDomain->userid;
		$domain 			= $getDomain->domain;
		$orderid 			= $getDomain->orderid;
		$registrar 			= $getDomain->registrar;
		$registrationperiod = $getDomain->registrationperiod;
		$dnsmanagement 		= $getDomain->dnsmanagement ? true : false;
		$emailforwarding 	= $getDomain->emailforwarding ? true : false;
		$idprotection 		= $getDomain->idprotection ? true : false;

		$getOrder=\App\Models\Order::find($orderid);
		$contactid = $getOrder->contactid;
		$clients = new \App\Helpers\Client();
		$clientsdetails = $clients->DataClientsDetails($userid, $contactid);
		$clientsdetails["state"] = $clientsdetails["statecode"];
		$clientsdetails["fullphonenumber"] = $clientsdetails["phonenumberformatted"];
		$clientsdetails["phone-cc"] = $clientsdetails["phonecc"];
		$params = array_merge($paramvars, $clientsdetails);
		$domainObj = new \App\Helpers\Domain\Domain($domain);
		$params["sld"] = $domainObj->getSLD();
   		$params["tld"] = $domainObj->getTLD();
   		$params["registrar"] = $getDomain->registrar;
		$params["regperiod"] = $registrationperiod;
		$params["dnsmanagement"] = $dnsmanagement;
		$params["emailforwarding"] = $emailforwarding;
    	$params["idprotection"] = $idprotection;
		$params["premiumEnabled"] = (bool) (int) \App\Helpers\Cfg::get('PremiumDomains');
		if ($params["premiumEnabled"]) {
			$registrarCostPrice = json_decode( \App\Models\DomainsExtra::where('domain_id',$domainid)->where("name","registrarCostPrice")->value("value"), true);
			if ($registrarCostPrice && is_numeric($registrarCostPrice)) {
				$params["premiumCost"] = $registrarCostPrice;
			} else {
				if ($registrarCostPrice && is_array($registrarCostPrice) && array_key_exists("price", $registrarCostPrice)) {
					$params["premiumCost"] = $registrarCostPrice["price"];
				}
			}
		}
		
		if(\App\Helpers\Cfg::get('RegistrarAdminUseClientDetails') == 'on' ){
			$params["adminfirstname"] = $clientsdetails["firstname"];
			$params["adminlastname"] = $clientsdetails["lastname"];
			$params["admincompanyname"] = $clientsdetails["companyname"];
			$params["adminemail"] = $clientsdetails["email"];
			$params["adminaddress1"] = $clientsdetails["address1"];
			$params["adminaddress2"] = $clientsdetails["address2"];
			$params["admincity"] = $clientsdetails["city"];
			$params["adminfullstate"] = $clientsdetails["fullstate"];
			$params["adminstate"] = $clientsdetails["state"];
			$params["adminpostcode"] = $clientsdetails["postcode"];
			$params["admincountry"] = $clientsdetails["country"];
			$params["adminphonenumber"] = $clientsdetails["phonenumber"];
			$params["adminphonecc"] = $clientsdetails["phonecc"];
			$params["adminfullphonenumber"] = $clientsdetails["phonenumberformatted"];
		}else{ 
			$ClientHelper= new \App\Helpers\ClientHelper();
			$params["adminfirstname"] 		= \App\Helpers\Cfg::get('RegistrarAdminFirstName');
			$params["adminlastname"] 		= \App\Helpers\Cfg::get('RegistrarAdminLastName');
			$params["admincompanyname"] 	= \App\Helpers\Cfg::get('RegistrarAdminCompanyName');
			$params["adminemail"] 			= \App\Helpers\Cfg::get('RegistrarAdminEmailAddress');
			$params["adminaddress1"] 		= \App\Helpers\Cfg::get('RegistrarAdminAddress1');
			$params["adminaddress2"] 		= \App\Helpers\Cfg::get('RegistrarAdminAddress2');
			$params["admincity"] 			= \App\Helpers\Cfg::get('RegistrarAdminCity');
			$params["adminfullstate"] 		= \App\Helpers\Cfg::get('RegistrarAdminStateProvince');
			$params["adminstate"] 			= $ClientHelper->convertStateToCode( \App\Helpers\Cfg::get('RegistrarAdminStateProvince'), \App\Helpers\Cfg::get('RegistrarAdminCountry'));
			$params["adminpostcode"] 		= \App\Helpers\Cfg::get('RegistrarAdminPostalCode');
			$params["admincountry"] 		= \App\Helpers\Cfg::get('RegistrarAdminCountry');
			$phoneDetails = $ClientHelper->formatPhoneNumber(array("phonenumber" => \App\Helpers\Cfg::get('RegistrarAdminPhone'), "countrycode" => \App\Helpers\Cfg::get('RegistrarAdminCountry')));
			$params["adminphonenumber"] = $phoneDetails["phonenumber"];
			$params["adminfullphonenumber"] = $phoneDetails["phonenumberformatted"];
			$params["adminphonecc"] = $phoneDetails["phonecc"];
		}
		if ($params["tld"] == "ca" || substr($params["tld"], -3) == ".ca") {
			$params["adminstate"] = $this->convertToCiraCode($params["adminstate"]);
		}
		if (!@$params["ns1"] && !@$params["ns2"]) {
			$getNS=\App\Models\Order::find($orderid);
			$nameservers = $getNS->nameservers;
			$getHosting=\App\Models\Hosting::where('domain',$domain);
			$server = $getHosting->value('server');
			if ($server) {
				$getServer=\App\Models\Server::find($server);
				for ($i = 1; $i <= 5; $i++) {
					$params["ns" . $i] = trim($getServer->nameserver.$i);
				}
			}else{
				if ($nameservers && $nameservers != ",") {
					$nameservers = explode(",", $nameservers);
					for ($i = 1; $i <= 5; $i++) {
						$params["ns" . $i] = trim($nameservers[$i - 1]);
					}
				} else {
					for ($i = 1; $i <= 5; $i++) {
						$params["ns" . $i] = trim( \App\Helpers\Cfg::get('DefaultNameserver'.$i));
					}
				}
			}

		}else {
			for ($i = 1; $i <= 5; $i++) {
				$params["ns" . $i] = trim($params["ns" . $i]);
			}
		}
		$additflds =new \App\Helpers\Domain\AdditionalFields();
		$params["additionalfields"] = $additflds->getFieldValuesFromDatabase($domainid);
		$originaldetails = $params;
		if (!array_key_exists("original", $params)) {
			$params = $this->foreignChrReplace($params);
			$params["original"] = $originaldetails;
		}
		$params["domainObj"] = $domainObj;
		Hooks::run_hook("PreDomainRegister", array("domain" => $domain));
		
		$values = $this->regcallfunction($params, "RegisterDomain");
		if (!is_array($values)) {
			return false;
		}
		if (@$values["na"]) {
			LogActivity::Save("Domain Registration Not Supported by Module - Domain ID: " . $domainid . " - Domain: " . $domain);
			return array("error" => "Registrar Function Not Supported");
		}
		if (@$values["error"]) {
			LogActivity::Save("Domain Registration Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values["error"], $userid);
			Hooks::run_hook("AfterRegistrarRegistrationFailed", array("params" => $params, "error" => $values["error"]));
		} else {
			if (@$values["pending"]) {
				$updateDomain=\App\Models\Domain::find($domainid);
				$updateDomain->status = 'Pending';
				LogActivity::Save("Domain Pending Registration Successful - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
			} else {
				$expirydate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") + $registrationperiod));
				$updateDomain=\App\Models\Domain::find($domainid);
				$updateDomain->registrationdate = date("Ymd");
				$updateDomain->expirydate = $expirydate;
				$updateDomain->status ='Active';
				$updateDomain->save();

				LogActivity::Save("Domain Registered Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
			}
			Hooks::run_hook("AfterRegistrarRegistration", array("params" => $params));
		}
		return $values;
	}

	function RegReleaseDomain($params){
		$values = $this->regcallfunction($params, "ReleaseDomain");
		if (isset($values["na"]) && $values["na"] === true) {
			return $values;
		}
		if (!isset($values["error"]) || !$values["error"]) {
			DB::table("tbldomains")->where("id", $params["domainid"])->update(array("status" => "Transferred Away"));
		}
		return $values;
	}

    /**
     * RegCallFunction
     */
    public function RegCallFunction($params, $function, $noarr = false)
    {
        $registrar = $params["registrar"];
        $hookResults = Hooks::run_hook("PreRegistrar" . $function, array("params" => $params));
        try {
            if ($this->processHookResults($registrar, $function, $hookResults)) {
                return array();
            }
        } catch (\Exception $e) {
            return array("error" => $e->getMessage());
        }
        $functionExists = $functionSuccessful = false;
        $module = new self();
        $module->setDomainID($params["domainid"]);
        $module->load($registrar);
        $queueFunctions = array("IDProtectToggle", "RegisterDomain", "RenewDomain", "TransferDomain");
        if (!$module->isActivated()) {
            return array("error" => "Module not active");
        }
        if ($module->functionExists($function)) {
            $functionExists = true;
            $values = $module->call($function, $params);
            if (!is_array($values) && !$noarr) {
                $values = array();
            }
            if (empty($values["error"])) {
                if (in_array($function, $queueFunctions)) {
                    $this->resolve("domain", $params["domainid"], $registrar, $function);
                }
                $functionSuccessful = true;
            } else {
                if (in_array($function, $queueFunctions)) {
                    $this->add("domain", $params["domainid"], $registrar, $function, $values["error"]);
                }
            }
        } else {
            $values = array("na" => true);
        }
        $vars = array("params" => $params, "results" => $values, "functionExists" => $functionExists, "functionSuccessful" => $functionSuccessful);
        $hookResults = Hooks::run_hook("AfterRegistrar" . $function, $vars);
        try {
            if ($this->processHookResults($registrar, $function, $hookResults)) {
                return array();
            }
        } catch (\Exception $e) {
            return array("error" => $e->getMessage());
        }
        return $values;
    }

	public function RegCallFunctionOLD(Array $params, $function, $noarr = false){
		$registrar = $params["registrar"];
		
		eval('if (class_exists(\App\Events\PreRegistrar'.$function.'::Class)) {
					$runHooks=true;
				}else{
					$runHooks=false;
				}');
		if ($runHooks) {
			$hookResults = event( new $classHooks(["params" => $params]));
			try{
				if ($this->processHookResults($registrar, $function, $hookResults)) {
					return array();
				}
			}catch (Exception $e) {
				return array("error" => $e->getMessage());
			}
		}
		$functionExists = $functionSuccessful = false;
		$queueFunctions = array("IDProtectToggle", "RegisterDomain", "RenewDomain", "TransferDomain");
		$values=$this->loadModule($registrar,$function,$params);
		//dd($values);
		if (!is_array($values) && !$noarr) {
            $values = array();
        }
        if (empty($values["error"])) {
            if (in_array($function, $queueFunctions)) {
				$this->resolve("domain", $params["domainid"], $registrar, $function);
            }
            $functionSuccessful = true;
        } else {
            if (in_array($function, $queueFunctions)) {
                $this->add("domain", $params["domainid"], $registrar, $function, $values["error"]);
            }
        }
		$vars = array("params" => $params, "results" => $values, "functionExists" => $functionExists, "functionSuccessful" => $functionSuccessful);
		eval('if (class_exists(\App\Events\AfterRegistrar'.$function.'::Class)) {
					$runHooks=true;
				}else{
					$runHooks=false;
				}');
		if ($runHooks) {
			try {
				if ($this->processHookResults($registrar, $function, $hookResults)) {
					return array();
				}
			} catch (Exception $e) {
				return array("error" => $e->getMessage());
			}
		}


		return $values;

	}

	public function resolve($serviceType, $serviceId, $module, $moduleAction){
		
		$queue = \App\Models\Modulequeue::where('service_type',$serviceType)
										->where('service_id',$serviceId)
										->where('module_name',$module)
										->where('module_action', $moduleAction)
										->where('completed',0)->first();
		if($queue){
			$queue->completed = 1;
			$queue->updated_at = Carbon::now();
			return $queue->save();
		}
		return true;
	}

	public function add($serviceType, $serviceId, $module, $moduleAction, $lastAttemptError){
		if (defined("NO_QUEUE") && NO_QUEUE == true) {
            return true;
        }
		if (is_null($lastAttemptError)) {
            $lastAttemptError = "";
        }
		
		$queue=\App\Models\Modulequeue::firstOrCreate(["service_type" => $serviceType, "service_id" => $serviceId, "module_name" => $module, "module_action" => $moduleAction, "completed" => 0]);
		$queue->created_at = Carbon::now();
        $queue->last_attempt_error = $lastAttemptError;
		if ($queue->exists) {
            $queue->num_retries++;
        } else {
            $queue->num_retries = 0;
        }
        return $queue->save();

	}

	public function loadModule($registrar,$function,$param=[]){
		$values=array();
		$moduleCek=\App\Models\Registrar::where('registrar',$registrar);
		if($moduleCek->count() <= 0){
			return ['error' => "Registrar function not found"];
		}
		$registrar=ucwords($registrar);
		try{
			$modulParam=array();
			foreach($moduleCek->get() as $md){
				$modulParam['config'][$md->setting] = $md->value;
			}
			$param=array_merge($modulParam,$param);
			if(!class_exists('\Modules\Registrar\\'.$registrar.'\Http\Controllers\\'.$registrar.'Controller')){   
				return ["error" => "Module Na"];  
			}
			eval(' if(!method_exists(\Modules\Registrar\\'.$registrar.'\Http\Controllers\\'.$registrar.'Controller::class,$function)){ return ["error" => "Module function  {{$function}}  Na"];  }');
			$modul = '\\Modules\\Registrar\\'.ucwords($registrar).'\\Http\\Controllers\\'.ucwords($registrar).'Controller';
			$registrar= new $modul();
			
			$data= $registrar->$function($param);
			if(isset($data['config'])){
				unset($data['config']);
			}

			return $data;
		}catch (\Exception $e) {
            return  $e->getMessage();
        }
	}

	public function processHookResults($moduleName, $function, array $hookResults = array()){
		if (!empty($hookResults)) {
			$hookErrors = array();
			$abortWithSuccess = false;
			foreach ($hookResults as $hookResult) {
				if (!empty($hookResult["abortWithError"])) {
					$hookErrors[] = $hookResult["abortWithError"];
				}
				if (array_key_exists("abortWithSuccess", $hookResult) && $hookResult["abortWithSuccess"] === true) {
					$abortWithSuccess = true;
				}
			}
			if (count($hookErrors)) {
				throw new \Exception(implode(" ", $hookErrors));
			}
			if ($abortWithSuccess) {
				//LogActivity("Function " . $moduleName . "->" . $function . "() Aborted by Action Hook Code");
				\App\Helpers\LogActivity::Save("Function " . $moduleName . "->" . $function . "() Aborted by Action Hook Code");
				return true;
			}
		}
		return false;
	}

	public function convertToCiraCode($code)
	{
		if ($code == "YT") {
			$code = "YK";
		}
		return $code;
	}
	public function foreignChrReplace($arr)
    {
        $cleandata = array();
        if (is_array($arr)) {
            foreach ($arr as $key => $val) {
                if (is_array($val)) {
                    $cleandata[$key] = $this->foreignChrReplace($val);
                } else {
                    if (!is_object($val)) {
                        if (function_exists("hook_transliterate")) {
                            $cleandata[$key] = hook_transliterate($val);
                        } else {
                            $cleandata[$key] = $this->foreignChrReplace2($val);
                        }
                    }
                }
            }
        } else {
            if (!is_object($arr)) {
                if (function_exists("hook_transliterate")) {
                    $cleandata = hook_transliterate($arr);
                } else {
                    $cleandata = $this->foreignChrReplace2($arr);
                }
            }
        }
        return $cleandata;
    }

	public function foreignChrReplace2($string){
		if (is_null($string) || !(is_numeric($string) || is_string($string))) {
            return $string;
        }
        $accents = "/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig|tilde|ring|slash|zlig|elig|quest|caron);/";
        $string = htmlentities($string, ENT_NOQUOTES, \App\Helpers\Cfg::get('Charset'));
        $string = preg_replace($accents, "\$1", $string);
        $string = html_entity_decode($string, ENT_NOQUOTES, \App\Helpers\Cfg::get('Charset'));
        if (function_exists("mb_internal_encoding") && function_exists("mb_regex_encoding") && function_exists("mb_ereg_replace")) {
            mb_internal_encoding("UTF-8");
            mb_regex_encoding("UTF-8");
            $changeKey = array("g" => "g", "ü" => "u", "s" => "s", "ö" => "o", "i" => "i", "ç" => "c", "G" => "G", "Ü" => "U", "S" => "S", "Ö" => "O", "I" => "I", "Ç" => "C");
            foreach ($changeKey as $i => $u) {
                $string = mb_ereg_replace($i, $u, $string);
            }
        }
        return $string;

	}

	/**
	 * RegTransferDomain
	 */
	public function RegTransferDomain($paramvars)
	{
		global $CONFIG;
		//dd($CONFIG);
		$domainid = $paramvars["domainid"];
		$passedepp = $paramvars["transfersecret"];
		$data=\App\Models\Domain::find($domainid);
		
		$userid 			= $data->userid;
		$domain 			= $data->domain;
		$orderid 			= $data->orderid;
		$registrar 			= $data->registrar;
		$registrationperiod = $data->registrationperiod;
		$dnsmanagement 		= $data->dnsmanagement ? true : false;
		$emailforwarding 	= $data->emailforwarding ? true : false;
		$idprotection 		= $data->idprotection ? true : false;

		$order=\App\Models\Order::find($orderid );
		$contactid 		= $order->contactid;
   		$nameservers 	= $order->nameservers;
    	$transfersecret = $order->transfersecret;

		$clientHelpers=new \App\Helpers\Client();

		$clientsdetails = $clientHelpers->DataClientsDetails($userid, $contactid);
		$clientsdetails["state"] = $clientsdetails["statecode"];
    	$clientsdetails["fullphonenumber"] = $clientsdetails["phonenumberformatted"];

		$params=array();
		$params = array_merge($paramvars, $clientsdetails);
		$domainObj = new \App\Helpers\Domain\Domain($domain);
		$params["registrar"] = $registrar;
		$params["sld"] = $domainObj->getSLD();
		$params["tld"] = $domainObj->getTLD();
		$params["regperiod"] = $registrationperiod;
		$params["dnsmanagement"] = $dnsmanagement;
		$params["emailforwarding"] = $emailforwarding;
		$params["idprotection"] = $idprotection;
		$params["premiumEnabled"] = (bool) (int) \App\Helpers\Cfg::get("PremiumDomains");
		if($params["premiumEnabled"]) {
			$registrarCostPrice = \App\Models\DomainsExtra::whereDomainId($domainid)->whereName("registrarCostPrice")->value("value");
			if ($registrarCostPrice) {
				$params["premiumCost"] = (double) $registrarCostPrice;
			}
		}
		if ($CONFIG["RegistrarAdminUseClientDetails"] == "on") {
			$params["adminfirstname"] = $clientsdetails["firstname"];
			$params["adminlastname"] = $clientsdetails["lastname"];
			$params["admincompanyname"] = $clientsdetails["companyname"];
			$params["adminemail"] = $clientsdetails["email"];
			$params["adminaddress1"] = $clientsdetails["address1"];
			$params["adminaddress2"] = $clientsdetails["address2"];
			$params["admincity"] = $clientsdetails["city"];
			$params["adminfullstate"] = $clientsdetails["fullstate"];
			$params["adminstate"] = $clientsdetails["state"];
			$params["adminpostcode"] = $clientsdetails["postcode"];
			$params["admincountry"] = $clientsdetails["country"];
			$params["adminphonenumber"] = $clientsdetails["phonenumber"];
			$params["adminfullphonenumber"] = $clientsdetails["phonenumberformatted"];
		} else {
			$params["adminfirstname"] = $CONFIG["RegistrarAdminFirstName"];
			$params["adminlastname"] = $CONFIG["RegistrarAdminLastName"];
			$params["admincompanyname"] = $CONFIG["RegistrarAdminCompanyName"];
			$params["adminemail"] = $CONFIG["RegistrarAdminEmailAddress"];
			$params["adminaddress1"] = $CONFIG["RegistrarAdminAddress1"];
			$params["adminaddress2"] = $CONFIG["RegistrarAdminAddress2"];
			$params["admincity"] = $CONFIG["RegistrarAdminCity"];
			$params["adminfullstate"] = $CONFIG["RegistrarAdminStateProvince"];

			$clientHelp=new \App\Helpers\ClientHelper();
			$params["adminstate"] = $clientHelp->convertStateToCode($CONFIG["RegistrarAdminStateProvince"], $CONFIG["RegistrarAdminCountry"]);
			$params["adminpostcode"] = $CONFIG["RegistrarAdminPostalCode"];
       		$params["admincountry"] = $CONFIG["RegistrarAdminCountry"];
			$phoneDetails = $clientHelp->formatPhoneNumber(array("phonenumber" => $CONFIG["RegistrarAdminPhone"], "countrycode" => $CONFIG["RegistrarAdminCountry"]));
			$params["adminphonenumber"] = $phoneDetails["phonenumber"];
       		$params["adminfullphonenumber"] = $phoneDetails["phonenumberformatted"];
        	$params["adminphonecc"] = $phoneDetails["phonecc"];
		}
		if ($params["tld"] == "ca" || substr($params["tld"], -3) == ".ca") {
			$params["adminstate"] = $this->convertToCiraCode($params["adminstate"]);
		}
		if (!@$params["ns1"] && !@$params["ns2"]) {
			$getNS=\App\Models\Order::find($orderid);
			$nameservers = $getNS->nameservers;
			$getHosting=\App\Models\Hosting::where('domain',$domain);
			$server = $getHosting->value('server');
			if ($server) {
				$getServer=\App\Models\Server::find($server);
				for ($i = 1; $i <= 5; $i++) {
					$params["ns" . $i] = trim($data->nameserver.$i);
				}
			}else{
				if ($nameservers && $nameservers != ",") {
					$nameservers = explode(",", $nameservers);
					for ($i = 1; $i <= 5; $i++) {
						$params["ns" . $i] = trim($nameservers[$i - 1]);
					}
				} else {
					for ($i = 1; $i <= 5; $i++) {
						$params["ns" . $i] = trim( \App\Helpers\Cfg::get('DefaultNameserver'.$i));
					}
				}
			}

		}else {
			for ($i = 1; $i <= 5; $i++) {
				$params["ns" . $i] = trim($params["ns" . $i]);
			}
		}


		$additflds =new \App\Helpers\Domain\AdditionalFields();
		$params["additionalfields"] = $additflds->getFieldValuesFromDatabase($domainid);
		$originaldetails = $params;
		if (!array_key_exists("original", $params)) {
			$params = $this->foreignChrReplace($params);
			$params["original"] = $originaldetails;
		}
		if (!$params["transfersecret"]) {
			$transfersecret = $transfersecret ? $clientHelpers->safe_unserialize($transfersecret) : array();
			$params["eppcode"] = $transfersecret[$domain] ?? "";
			$params["transfersecret"] = $params["eppcode"];
		} else {
			$params["eppcode"] = $passedepp;
			$params["transfersecret"] = $params["eppcode"];
		}
		$params["domainObj"] = $domainObj;
		
		Hooks::run_hook("PreDomainTransfer", array("domain" => $domain));
		$values = $this->regcallfunction($params, "TransferDomain");
		if (!is_array($values)) {
			return false;
		}
		if (@$values["na"]) {
			LogActivity::Save("Domain Transfer Not Supported by Module - Domain ID: " . $domainid . " - Domain: " . $domain);
			return array("error" => "Registrar Function Not Supported");
		}
		if (@$values["error"]) {
			LogActivity::Save("Domain Transfer Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values["error"], $userid);
			Hooks::run_hook("AfterRegistrarTransferFailed",  array("params" => $params, "error" => $values["error"]));
		} else {
			$upDomain=\App\Models\Domain::find( $domainid);
			$upDomain->status = '"Pending Transfer';
			$upDomain->save();

			$todo=new \App\Models\Todolist();
			$todo->date 	= Carbon::now()->format('Y-m-d');
			$todo->title 	= "Domain Pending Transfer";
			$todo->description 	=  "Check the transfer status of the domain " . $params["sld"] . "." . $params["tld"] . "";
			$todo->admin 	=  "";
			$todo->status 	=  "In Progress";
			$todo->duedate 	= Carbon::now()->addDays(5)->format('Y-m-d');
			$todo->save(); 
			LogActivity::Save("Domain Transfer Initiated Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
			Hooks::run_hook("AfterRegistrarTransferFailed",  array("params" => $params));
		}
		return $values;
	}

	/**
	 * RegRenewDomain
	 */
	public function RegRenewDomain($params = [])
	{
		$domainid = $params["domainid"];
		$data=\App\Models\Domain::find($domainid);
		//dd($data);


		$userid 			= $data->userid;
		$domain 			= $data->domain;
		$orderid 			= $data->orderid;
		$registrar 			= $data->registrar;
		$registrationperiod = $data->registrationperiod;
		$dnsmanagement 		= $data->dnsmanagement ? true : false;
		$emailforwarding 	= $data->emailforwarding ? true : false;
		$idprotection 		= $data->idprotection ? true : false;
		$domainObj 			= new \App\Helpers\Domain\Domain($domain);
		$params["registrar"] = $registrar;
		$params["sld"] = $domainObj->getSLD();
		$params["tld"] = $domainObj->getTLD();
		$params["regperiod"] = $registrationperiod;
		$params["dnsmanagement"] = $dnsmanagement;
		$params["emailforwarding"] = $emailforwarding;
		$params["idprotection"] = $idprotection;
		$params["isInGracePeriod"] = $data["status"] == 'Grace';
		$params["isInRedemptionGracePeriod"] = $data["status"] == 'Redemption';
		$params["premiumEnabled"] = (bool) (int) \App\Helpers\Cfg::get('PremiumDomains');
		if ($params["premiumEnabled"] && $data["is_premium"]) {
			$params["premiumCost"] = \App\Models\DomainsExtra::whereDomainId($domainid)->whereName("registrarRenewalCostPrice")->value("value");
		}
		$params["domainObj"] = $domainObj;
		$values = $this->regcallfunction($params, "RenewDomain");
		if (!is_array($values)) {
			return false;
		}
		if (isset($values["na"])) {
			return ["error" => "Registrar Function Not Supported"];
		}
		if (isset($values["error"])) {
			LogActivity::Save("Domain Renewal Failed - Domain ID: " . $domainid . " - Domain: " . $domain . " - Error: " . $values["error"], $userid);
			Hooks::run_hook("AfterRegistrarRenewalFailed",["params" => $params, "error" => $values["error"]]);
		} else {
			$expiryInfo = DB::table("tbldomains")->where("id", "=", $domainid)->first(array("expirydate", "registrationperiod"));
			$expirydate = $expiryInfo->expirydate;
			$registrationperiod = $expiryInfo->registrationperiod;
			$year = substr($expirydate, 0, 4);
			$month = substr($expirydate, 5, 2);
			$day = substr($expirydate, 8, 2);
			if (strpos($expirydate, "0000-00-00") === false) {
				$newExpiryDate = Carbon::createFromDate($year, $month, $day);
			} else {
				$newExpiryDate = Carbon::create();
			}
			$newExpiryDate = $newExpiryDate->addYears($registrationperiod)->format("Y-m-d");
			$update = array("expirydate" => $newExpiryDate, "status" => "Active", "reminders" => "");
			DB::table("tbldomains")->where("id", "=", $domainid)->update($update);
			LogActivity::Save("Domain Renewed Successfully - Domain ID: " . $domainid . " - Domain: " . $domain, $userid);
			Hooks::run_hook("AfterRegistrarRenewal", array("params" => $params));
		
		}
		return $values;

	}

	public function RegGetEPPCode($params)
	{
		$values = $this->regcallfunction($params, "GetEPPCode");
		if (!$values) {
			return false;
		}
		if (@$values["eppcode"]) {
			@$values["eppcode"] = htmlentities($values["eppcode"]);
		}
		return $values;
	}

	/**
	 * RegRequestDelete
	 */
	public function RegRequestDelete($params = [])
	{
		$values = $this->RegCallFunction2($params, "RequestDelete");
		if (!$values) {
			return false;
		}
		
		if (isset($values["error"]) && !$values["error"]) {
			$domain = \App\Models\Domain::find($params["domainid"]);
			$domain->status = "Cancelled";
			$domain->save();
		}

		return $values;
	}

	public function RegIDProtectToggle($params){
		if (!array_key_exists("protectenable", $params)) {
			$domainid = $params["domainid"];
			$data=\App\Models\Domain::find($domainid);
			$idprotection = $data->idprotection ? true : false;
			$params["protectenable"] = $idprotection;
		}
		return $this->regcallfunction($params, "IDProtectToggle");
	}

    public function getList()
    {
        $data = [];
        $modules = \Module::toCollection();
        foreach ($modules as $key => $module) {
            if (strpos($module->getPath(), '/Registrar') !== false) {
                $data[] = $module;
            }
        }
        return $data;
    }
	public function getActiveModules()
    {
        return DB::table("tblregistrars")->distinct("registrar")->orderBy("registrar")->pluck("registrar");
    }
	function getRegistrarsDropdownMenu2($registrar, $name = "registrar")
	{
		$code = "<select name=\"" . $name . "\" class=\"form-control select-inline\" id=\"registrarsDropDown\">" . "<option value=\"\">" . \Lang::get("admin.none") . "</option>";
		foreach ($this->getActiveModules() as $module) {
			$code .= "<option value=\"" . $module . "\"";
			if ($registrar == $module) {
				$code .= " selected";
			}
			$code .= ">" . ucfirst($module) . "</option>";
		}
		$code .= "</select>";
		return $code;
	}
    public function getRegistrarsDropdownMenu($registrar, $name = "registrar", $additionalClasses = "select-inline")
	{
		static $registrarList = NULL;
		if (is_null($registrarList)) {
			$registrarList = $this->getActiveModules();
		}

		$none = __("admin.none");
		$name = "name=\"" . $name . "\"";
		$class = "class=\"form-control " . $additionalClasses . "\"";
		$id = "id=\"registrarsDropDown\"";
		$code = "<select " . $id . " " . $name . " " . $class . ">" . "<option value = \"\">" . $none . "</option>";
		foreach ($registrarList as $reg) {
			$selected = "";
            $module = $reg;
			if ($registrar == $module) {
				$selected = "selected=\"selected\"";
			}
			$moduleName = ucfirst($module);
			$code .= "<option value=\"" . $module . "\" " . $selected . ">" . $moduleName . "</option>";
		}
		$code .= "</select>";

		return $code;
	}
    public function prepareParams($params)
    {
        return $params;
    }
    public function getParams()
    {
        $moduleParams = $this->moduleParams;
        return $this->prepareParams($moduleParams);
    }
    public function getLoadedModule()
    {
        return $this->loadedmodule;
    }
    public function getSettings()
    {
        $settings = $this->settings;
        if (!array_key_exists($this->getLoadedModule(), $settings)) {
            $settings[$this->getLoadedModule()] = array();
            $dbSettings = DB::table("tblregistrars")->select("setting", "value")->where("registrar", $this->getLoadedModule())->get();
            foreach ($dbSettings as $dbSetting) {
                $settings[$this->getLoadedModule()][$dbSetting->setting] = (new \App\Helpers\Pwd())->decrypt($dbSetting->value ?? "");
            }
        }
        $this->settings = $settings;
        return $settings[$this->getLoadedModule()];
    }
    public function load($module, $globalVariable = NULL)
    {
        $this->builtParams = array();
        return $this->loadModuleV2($module);
    }
    public function loadModuleV2($module, $globalVariable = NULL)
    {
        $modpath = $this->getModulePath($module);
        \Log::debug("Attempting to load module", array("type" => $this->getType(), "module" => $module, "path" => $modpath));
        // $moduleInstance = \Module::find($module);
        if (\Module::find($module)) {
            // if ($moduleInstance->isEnabled()) {
                if (!is_null($globalVariable)) {
                    global ${$globalVariable};
                }
                $this->setLoadedModule($module);
                $this->setMetaData($this->getMetaData());
                return true;
            // }
        }
        return false;
    }
    protected function setMetaData($metaData)
    {
        if (is_array($metaData)) {
            $this->metaData = $metaData;
            return true;
        }
        $this->metaData = array();
        return false;
    }
    protected function getMetaData()
    {
        $moduleName = $this->getLoadedModule();

        if ($this->functionExists("MetaData")) {
            return $this->call("MetaData");
        }
    }
    public function callModule($function, array $params = array())
    {
        if ($this->functionExists($function)) {
            $params = array_merge($this->getParams(), $params);
            $moduleName = $this->getLoadedModule();
			$module = \Module::find($moduleName);
			if ($module) {
				$modName = $module->getName();
				$className = "\\Modules\\Registrar\\{$modName}\\Http\\Controllers\\{$modName}Controller";
				$object = new $className();
				return $object->{$function}($params);
			} else {
				return "Module not found";
			}
        }
        return self::FUNCTIONDOESNTEXIST;
    }
	/**
	 * RegCallFunction
	 */
	public function RegCallFunction2($params = [], $method, $noarr = false)
	{
		$registrar = $params["registrar"];

		$module = \Module::find($registrar);
		if ($module) {
			$className = "\\Modules\\Registrar\\{$registrar}\\Http\\Controllers\\{$registrar}Controller";
			$object = new $className();

			try {
				// if (method_exists($object, $method)) {
				// 	$values = $object->$method($params);
				// } else {
				// 	return [
				// 		"error" => "Method not found",
				// 	];
				// }
				$values = $object->$method($params);
				if (!is_array($values) && !$noarr) {
					$values = array();
				}
			} catch (\Exception $e) {
				return [
					"error" => $e->getMessage(),
				];
			}
		} else {
			$values = [
				"na" => true,
			];
		}

		return $values;
	}
    public function call($function, array $additionalParams = array())
    {
        $noDomainIdRequirement = array("getConfigArray", "CheckAvailability", "GetDomainSuggestions", "DomainSuggestionOptions", "AdditionalDomainFields");
        if (!in_array($function, $noDomainIdRequirement) && !$this->getDomainID()) {
            return array("error" => "Domain ID is required");
        }
        try {
            $this->function = $function;
            $params = $this->buildParams();
            $hookResults = Hooks::run_hook("PreRegistrar" . $function, array("params" => $params));
            if ($this->processHookResults($this->getLoadedModule(), $function, $hookResults)) {
                return true;
            }
        } catch (\Exception $e) {
            return array("error" => $e->getMessage());
        }
        if (is_array($additionalParams)) {
            $params = array_merge($params, $additionalParams);
        }
        $originalDetails = $params;
        if (!array_key_exists("original", $params)) {
            $params = $this->foreignChrReplace($params);
            $params["original"] = $originalDetails;
        }
        if (!isset($params["domainObj"]) || !is_object($params["domainObj"])) {
            if (isset($params["sld"], $params["sld"])) {
                $params["domainObj"] = new \App\Helpers\Domain\Domain(sprintf("%s.%s", $params["sld"], $params["tld"]));
            }
        }
        return $this->callModule($function, $params);
    }
    public function buildParams()
    {
        if (!$this->builtParams) {
            $params = $this->getSettings();
            if ($this->domainID) {
                try {
                    $domain = \App\Models\Domain::with(array("order", "extra"))->findOrFail($this->domainID);
                    $domainObj = new \App\Helpers\Domain\Domain($domain->domain);
                    $params["domainObj"] = $domainObj;
                    $params["domainid"] = $domain->id;
                    $params["domainname"] = $domain->domain;
                    $params["sld"] = $domainObj->getSecondLevel();
                    $params["tld"] = $domainObj->getTopLevel();
                    $params["regtype"] = $domain->type;
                    $params["regperiod"] = $domain->registrationPeriod;
                    $params["registrar"] = $domain->registrarModuleName;
                    $params["dnsmanagement"] = $domain->hasDnsManagement;
                    $params["emailforwarding"] = $domain->hasEmailForwarding;
                    $params["idprotection"] = $domain->hasIdProtection;
                    $params["premiumEnabled"] = (bool) (int) Cfg::getValue("PremiumDomains");
                    $params["userid"] = $domain->clientId;
                    $this->buildFunctionSpecificParams($domain, $params);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            $this->builtParams = $params;
        }
        return $this->builtParams;
    }
    protected function buildFunctionSpecificParams(\App\Models\Domain $domain, array &$params)
    {
        $premiumEnabled = (bool) (int) Cfg::getValue("PremiumDomains");
        if (in_array($this->function, array("RegisterDomain", "TransferDomain", "SaveContactDetails"))) {
            $userId = $domain->clientId;
            $contactId = 0;
            if ($domain->order) {
                $contactId = $domain->order->contactId;
            }
            $clientsDetails = \App\Helpers\ClientHelper::getClientsDetails($userId, $contactId);
            $clientsDetails["state"] = $clientsDetails["statecode"];
            $clientsDetails["fullphonenumber"] = $clientsDetails["phonenumberformatted"];
            $clientsDetails["phone-cc"] = $clientsDetails["phonecc"];
            if ($premiumEnabled) {
                $registrarCostPrice = json_decode($domain->extra()->whereName("registrarCostPrice")->value("value"), true);
                if ($registrarCostPrice && is_numeric($registrarCostPrice)) {
                    $params["premiumCost"] = $registrarCostPrice;
                } else {
                    if ($registrarCostPrice && is_array($registrarCostPrice) && array_key_exists("price", $registrarCostPrice)) {
                        $params["premiumCost"] = $registrarCostPrice["price"];
                    }
                }
            }
            if (Cfg::getValue("RegistrarAdminUseClientDetails") == "on") {
                $params["adminfirstname"] = $clientsDetails["firstname"];
                $params["adminlastname"] = $clientsDetails["lastname"];
                $params["admincompanyname"] = $clientsDetails["companyname"];
                $params["adminemail"] = $clientsDetails["email"];
                $params["adminaddress1"] = $clientsDetails["address1"];
                $params["adminaddress2"] = $clientsDetails["address2"];
                $params["admincity"] = $clientsDetails["city"];
                $params["adminfullstate"] = $clientsDetails["fullstate"];
                $params["adminstate"] = $clientsDetails["state"];
                $params["adminpostcode"] = $clientsDetails["postcode"];
                $params["admincountry"] = $clientsDetails["country"];
                $params["adminphonenumber"] = $clientsDetails["phonenumber"];
                $params["adminphonecc"] = $clientsDetails["phonecc"];
                $params["adminfullphonenumber"] = $clientsDetails["phonenumberformatted"];
            } else {
                $params["adminfirstname"] = Cfg::getValue("RegistrarAdminFirstName");
                $params["adminlastname"] = Cfg::getValue("RegistrarAdminLastName");
                $params["admincompanyname"] = Cfg::getValue("RegistrarAdminCompanyName");
                $params["adminemail"] = Cfg::getValue("RegistrarAdminEmailAddress");
                $params["adminaddress1"] = Cfg::getValue("RegistrarAdminAddress1");
                $params["adminaddress2"] = Cfg::getValue("RegistrarAdminAddress2");
                $params["admincity"] = Cfg::getValue("RegistrarAdminCity");
                $params["adminfullstate"] = Cfg::getValue("RegistrarAdminStateProvince");
                $params["adminstate"] = convertStateToCode(Cfg::getValue("RegistrarAdminStateProvince"), Cfg::getValue("RegistrarAdminCountry"));
                if ($params["tld"] == "ca" || substr($params["tld"], -3) == ".ca") {
                    $params["adminstate"] = convertToCiraCode($params["adminstate"]);
                }
                $params["adminpostcode"] = Cfg::getValue("RegistrarAdminPostalCode");
                $params["admincountry"] = Cfg::getValue("RegistrarAdminCountry");
                $phoneDetails = \App\Helpers\ClientClass::formatPhoneNumber(array("phonenumber" => Cfg::getValue("RegistrarAdminPhone"), "countrycode" => Cfg::getValue("RegistrarAdminCountry")));
                $params["adminphonenumber"] = $phoneDetails["phonenumber"];
                $params["adminfullphonenumber"] = $phoneDetails["phonenumberformatted"];
                $params["adminphonecc"] = $phoneDetails["phonecc"];
            }
            $nameservers = "";
            if ($domain->order) {
                $nameservers = $domain->order->nameservers;
            }
            $hostingAccount = \App\Models\Hosting::where("domain", $domain->domain)->first();
            if ($hostingAccount && $hostingAccount->serverId) {
                $serverData = DB::table("tblservers")->find($hostingAccount->serverId);
                if ($serverData) {
                    for ($i = 1; $i <= 5; $i++) {
                        $nameserver = "nameserver" . $i;
                        $params["ns" . $i] = trim($serverData->{$nameserver});
                    }
                }
            } else {
                if ($nameservers && $nameservers != ",") {
                    $nameservers = explode(",", $nameservers);
                    for ($i = 1; $i <= 5; $i++) {
                        $params["ns" . $i] = trim($nameservers[$i - 1]);
                    }
                } else {
                    for ($i = 1; $i <= 5; $i++) {
                        $params["ns" . $i] = trim(Cfg::getValue("DefaultNameserver" . $i));
                    }
                }
            }
            $params["additionalfields"] = (new \App\Helpers\AdditionalFields())->setDomainType($domain->type)->getFieldValuesFromDatabase($domain->id);
            $params = array_merge($params, $clientsDetails);
            $originalDetails = $params;
            $params = $this->foreignChrReplace($params);
            $params["original"] = $originalDetails;
            if ($this->function == "TransferDomain") {
                $transferSecret = array($domain->domain => "");
                if ($domain->order && $domain->order->transferSecret) {
                    $transferSecret = (new \App\Helpers\Client())->safe_unserialize($domain->order->transferSecret);
                }
                $params["eppcode"] = $transferSecret[$domain->domain];
                $params["transfersecret"] = $params["eppcode"];
            }
        } else {
            if ($this->function == "RenewDomain" && $premiumEnabled && $domain->isPremium) {
                $params["premiumCost"] = $domain->extra()->whereName("registrarRenewalCostPrice")->value("value");
            }
        }
    }
    public function functionExists($name)
    {
        $moduleName = $this->getLoadedModule();
        $module = \Module::find($moduleName);
        if (!$moduleName || !$module) {
            return false;
        }
		$modName = $module->getName();
        $className = "\\Modules\\Registrar\\{$modName}\\Http\\Controllers\\{$modName}Controller";
        $object = new $className();
        return method_exists($object, $name);
    }
    public function getModulePath($module)
    {
		if ($module) {
			return \Module::getModulePath($module);
		}
		return "";
    }
    public function getType()
    {
        return $this->type;
    }
    public function setLoadedModule($module)
    {
        $this->loadedmodule = $module;
    }
    public function getDisplayName()
    {
        $DisplayName = $this->getMetaDataValue("DisplayName");
        if (!$DisplayName) {
            $configData = $this->call("getConfigArray");
            if (isset($configData["FriendlyName"]["Value"])) {
                $DisplayName = $configData["FriendlyName"]["Value"];
            } else {
                $DisplayName = ucfirst($this->getLoadedModule());
            }
        }
        return \App\Helpers\Sanitize::makeSafeForOutput($DisplayName);
    }
    public function getMetaDataValue($keyName)
    {
        return array_key_exists($keyName, $this->metaData) ? $this->metaData[$keyName] : "";
    }
    public function setDomainID($domainID)
    {
        $this->domainID = $domainID;
    }
    protected function getDomainID()
    {
        return (int) $this->domainID;
    }
    public function deactivate(array $parameters = array())
    {
        \App\Models\RegistrarSetting::registrar($this->getLoadedModule())->delete();
        $module = \Module::find($this->getLoadedModule());
        if ($module) {
            $module->disable();
        }
        $this->clearSettings();
        return $this;
    }
    public function clearSettings()
    {
        if (array_key_exists($this->getLoadedModule(), $this->settings)) {
            unset($this->settings[$this->getLoadedModule()]);
        }
        return $this;
    }
    public function isActivated()
    {
        // return (bool) \App\Models\RegistrarSetting::registrar($this->getLoadedModule())->first();
        $moduleName = $this->getLoadedModule();
        $module = \Module::find($moduleName);
        if ($module) {
            return $module->isEnabled();
        }
        return false;
    }
    public function activate(array $parameters = array())
    {
        $this->deactivate();
        $registrarSetting = new \App\Models\RegistrarSetting();
        $registrarSetting->registrar = $this->getLoadedModule();
        $registrarSetting->setting = "Username";
        $registrarSetting->value = "";
        $registrarSetting->save();
        $moduleSettings = $this->call("getConfigArray");
        $settingsToSave = array("Username" => "");
		if (is_array($moduleSettings)) {
			foreach ($moduleSettings as $key => $values) {
				if ($values["Type"] == "yesno" && !empty($values["Default"]) && $values["Default"] !== "off" && $values["Default"] !== "disabled") {
					$settingsToSave[$key] = $values["Default"];
				}
			}
			$logChanges = false;
			if (0 < count($parameters)) {
				foreach ($parameters as $key => $value) {
					if (array_key_exists($key, $moduleSettings)) {
						$settingsToSave[$key] = $value;
						$logChanges = true;
					}
				}
			}
			\App\Helpers\AdminFunctions::logAdminActivity("Registrar Activated: '" . $this->getDisplayName() . "'");
			$this->saveSettings($settingsToSave, $logChanges);
			$module = \Module::find($this->getLoadedModule());
			if ($module) {
				$module->enable();
			}
			return $this;
		}
    }
    public function saveSettings(array $newSettings = array(), $logChanges = true)
    {
        $moduleName = $this->getLoadedModule();
        $moduleSettings = $this->call("getConfigArray");
        $previousSettings = $this->getSettings();
        $settingsToSave = array();
        $changes = array();
        foreach ($moduleSettings as $key => $values) {
            if ($values["Type"] == "System") {
                continue;
            }
            if (isset($newSettings[$key])) {
                $settingsToSave[$key] = $newSettings[$key];
            } else {
                if ($values["Type"] == "yesno") {
                    $settingsToSave[$key] = "";
                } else {
                    if (isset($values["Default"])) {
                        $settingsToSave[$key] = $values["Default"];
                    }
                }
            }
            if ($values["Type"] == "password" && isset($newSettings[$key]) && isset($previousSettings[$key])) {
                $updatedPassword = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($newSettings[$key], $previousSettings[$key]);
                if ($updatedPassword === false) {
                    $settingsToSave[$key] = $previousSettings[$key];
                } else {
                    $changes[] = "'" . $key . "' value modified";
                }
            }
            if ($values["Type"] == "yesno") {
                if (!empty($settingsToSave[$key]) && $settingsToSave[$key] !== "off" && $settingsToSave[$key] !== "disabled") {
                    $settingsToSave[$key] = "on";
                } else {
                    $settingsToSave[$key] = "";
                }
                if (empty($previousSettings[$key])) {
                    $previousSettings[$key] = "";
                }
                if ($previousSettings[$key] != $settingsToSave[$key]) {
                    $newSetting = $settingsToSave[$key] ?: "off";
                    $oldSetting = $previousSettings[$key] ?: "off";
                    $changes[] = "'" . $key . "' changed from '" . $oldSetting . "' to '" . $newSetting . "'";
                }
            } else {
                if (empty($settingsToSave[$key])) {
                    $settingsToSave[$key] = "";
                }
                if (empty($previousSettings[$key])) {
                    $previousSettings[$key] = "";
                }
                if ($values["Type"] != "password") {
                    if (!$previousSettings[$key] && $settingsToSave[$key]) {
                        $changes[] = "'" . $key . "' set to '" . $settingsToSave[$key] . "'";
                    } else {
                        if ($previousSettings[$key] != $settingsToSave[$key]) {
                            $changes[] = "'" . $key . "' changed from '" . $previousSettings[$key] . "' to '" . $settingsToSave[$key] . "'";
                        }
                    }
                }
            }
        }
        foreach ($settingsToSave as $setting => $value) {
            $model = \App\Models\RegistrarSetting::registrar($moduleName)->setting($setting)->first();
            if ($model) {
                $model->value = $value;
            } else {
                $model = new \App\Models\RegistrarSetting();
                $model->registrar = $moduleName;
                $model->setting = $setting;
                $model->value = \App\Helpers\Sanitize::decode(trim($value));
            }
            $model->save();
        }
        if ($changes && $logChanges) {
            \App\Helpers\AdminFunctions::logAdminActivity("Domain Registrar Modified: '" . $this->getDisplayName() . "' - " . implode(". ", $changes) . ".");
        }
        unset($this->settings[$this->getLoadedModule()]);
        return $this;
    }
    public function getConfiguration()
    {
        return $this->call("getConfigArray");
    }
    public function updateConfiguration(array $parameters = array())
    {
        if (!$this->isActivated()) {
            throw new \App\Exceptions\Module\NotActivated("Module not active");
        }
        $moduleSettings = $this->call("getConfigArray");
        $settingsToSave = array();
        $logChanges = false;
        if (0 < count($parameters)) {
            foreach ($parameters as $key => $value) {
                if (array_key_exists($key, $moduleSettings)) {
                    $settingsToSave[$key] = $value;
                    $logChanges = true;
                }
            }
        }
        if (0 < count($settingsToSave)) {
            $this->saveSettings($settingsToSave, $logChanges);
        }
    }

    function injectDomainObjectIfNecessary($params)
    {
        if (!isset($params["domainObj"]) || !is_object($params["domainObj"])) {
            $params["domainObj"] = new \App\Helpers\Domain\Domain(sprintf("%s.%s", $params["sld"], $params["tld"]));
        }
        return $params;
    }

    function getRegistrarConfigOptions($registrar)
    {
        $module = new \App\Module\Registrar();
        $module->load($registrar);
        return $module->getSettings();
    }
    public function RegGetDefaultNameservers($params = [], $domain)
    {
        $getserverids = \App\Models\Hosting::select('server')->where('userid', $params['clientid'])->where('domain', $domain)->get(); #("tblhosting", "server", array("domain" => $domain));
        foreach ($getserverids as $serverData) {
            $serverid = $serverData->server;
        }
        if ($serverid) {
            $result = \App\Models\Server::where('id', $serverid)->first();
            // $nameserver = $result->nameserver1;
            // dd($nameserver);
            for ($i = 1; $i <= 5; $i++) {
                $params["ns" . $i] = "$result->nameserver" . "$i";
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $params["ns" . $i] = trim(\App\Helpers\Cfg::get('DefaultNameserver'.$i));
            }
        }
        return $params;
    }
	public function getLogoFilename()
    {
		$moduleName = $this->getLoadedModule();
        $module = Module::find($moduleName);
		// return \Module::asset($module->getLowerName().':logo.png');
		return Module::asset($module->getLowerName().':logo.gif');
    }
	public function findTemplate($templateName)
	{
		// $currentTheme = \Route::currentRouteName();
		// $html = view($templateName)->render();
		$module = \Module::find($this->getLoadedModule());
		if ($module) {
			if (strpos($templateName, ":") !== false) {
				return $templateName;
			} else {
				return $module->getLowerName()."::".$templateName;
			}
		}
		return "";
	}
}


