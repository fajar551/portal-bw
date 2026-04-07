<?php

namespace App\Module;

// Import Model Class here

use App\Helpers\LogActivity;
use App\Helpers\Cfg;
use App\Helpers\Database;
use App\Models\Domain;
use App\Models\Hosting;
// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RegistrarX
{
    public $registrar;

    public function __construct($registrar = '')
    {
        $this->registrar = $registrar;
    }

    public function getRegistrarsDropdownMenu($registrar, $name = "registrar", $additionalClasses = "select-inline")
    {
        // Deprecated use \App\Helpers\Registrar\getRegistrarsDropdownMenu
        static $registrarList = NULL;
        if (is_null($registrarList)) {
            $registrarList = $this->getActiveModules();
        }

        $none = __("admin.none");
        $name = "name=\"" . $name . "\"";
        $class = "class=\"form-control " . $additionalClasses . "\"";
        $id = "id=\"registrarsDropDown\"";
        $code = "<select " . $id . " " . $name . " " . $class . ">" . "<option value = \"\">" . $none . "</option>";
        foreach ($registrarList as $module) {
            $selected = "";
            if ($registrar == $module) {
                $selected = "selected=\"selected\"";
            }
            $moduleName = ucfirst($module);
            $code .= "<option value=\"" . $module . "\" " . $selected . ">" . $moduleName . "</option>";
        }
        $code .= "</select>";

        return $code;
    }

    public function getActiveModules()
    {
        $pfx = \Database::prefix();
        return \DB::table("{$pfx}registrars")->distinct("registrar")->orderBy("registrar")->pluck("registrar");
    }

    /**
     * RegCallFunction
     */
    public function RegCallFunction($params = [], $method, $noarr = false)
    {
        $registrar = $this->registrar;

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

    /**
     * RegGetNameservers
     */
    public function RegGetNameservers($params = [])
    {
        return $this->RegCallFunction($params, "GetNameservers");
    }

    /**
     * RegSaveNameservers
     */
    public function RegSaveNameservers($params = [])
    {
        dd($params);
        for ($i = 1; $i <= 5; $i++) {
            $params["ns" . $i] = trim($params["ns" . $i]);
        }
        $values = $this->RegCallFunction($params, "SaveNameservers");
        if (!$values) {
            return false;
        }
        $domain = Domain::find($params["domainid"]);
        $userid = $domain->userid;
        if (isset($values["error"])) {
            LogActivity::Save("Domain Registrar Command: Save Nameservers - Failed: " . $values["error"] . " - Domain ID: " . $params["domainid"], $userid);
        } else {
            LogActivity::Save("Domain Registrar Command: Save Nameservers - Successful", $userid);
        }
        return $values;
    }

    /**
     * RegGetRegistrarLock
     */
    public function RegGetRegistrarLock($params = [])
    {
        $values = $this->RegCallFunction($params, "GetRegistrarLock", 1);
        if (is_array($values)) {
            return "";
        }
        return $values;
    }

    /**
     * RegSaveRegistrarLock
     */
    public function RegSaveRegistrarLock($params = [])
    {
        $values = $this->RegCallFunction($params, "SaveRegistrarLock");
        if (!$values) {
            return false;
        }
        $domain = Domain::find($params["domainid"]);
        $userid = $domain->userid;
        if (isset($values["error"])) {
            LogActivity::Save("Domain Registrar Command: Toggle Registrar Lock - Failed: " . $values["error"] . " - Domain ID: " . $params["domainid"], $userid);
        } else {
            LogActivity::Save("Domain Registrar Command: Toggle Registrar Lock - Successful", $userid);
        }
        return $values;
    }

    /**
     * RegGetURLForwarding
     */
    public function RegGetURLForwarding($params = [])
    {
        return $this->RegCallFunction($params, "GetURLForwarding");
    }

    /**
     * RegSaveURLForwarding
     */
    public function RegSaveURLForwarding($params = [])
    {
        return $this->RegCallFunction($params, "SaveURLForwarding");
    }

    /**
     * RegGetEmailForwarding
     */
    public function RegGetEmailForwarding($params = [])
    {
        return $this->RegCallFunction($params, "GetEmailForwarding");
    }

    /**
     * RegSaveEmailForwarding
     */
    public function RegSaveEmailForwarding($params = [])
    {
        return $this->RegCallFunction($params, "SaveEmailForwarding");
    }

    /**
     * RegGetDNS
     */
    public function RegGetDNS($params = [])
    {
        return $this->RegCallFunction($params, "GetDNS");
    }

    /**
     * RegSaveDNS
     */
    public function RegSaveDNS($params = [])
    {
        return $this->RegCallFunction($params, "SaveDNS");
    }

    /**
     * RegRenewDomain
     */
    public function RegRenewDomain($params = [])
    {
        # code...
    }

    /**
     * RegRegisterDomain
     */
    public function RegRegisterDomain($params = [])
    {
        # code...
    }

    /**
     * RegTransferDomain
     */
    public function RegTransferDomain($params = [])
    {
        # code...
    }

    /**
     * RegGetContactDetails
     */
    public function RegGetContactDetails($params = [])
    {
        return $this->RegCallFunction($params, "GetContactDetails");
    }

    /**
     * RegSaveContactDetails
     */
    public function RegSaveContactDetails($params = [])
    {
        # code...
    }

    /**
     * RegGetEPPCode
     */
    public function RegGetEPPCode($params = [])
    {
        $values = $this->RegCallFunction($params, "GetEPPCode");
        if (!$values) {
            return false;
        }
        if ($values["eppcode"]) {
            $values["eppcode"] = htmlentities($values["eppcode"]);
        }
        return $values;
    }

    /**
     * RegRequestDelete
     */
    public function RegRequestDelete($params = [])
    {
        $values = $this->RegCallFunction($params, "RequestDelete");
        if (!$values) {
            return false;
        }
        if (isset($values["error"]) && !$values["error"]) {
            $domain = Domain::find($params["domainid"]);
            $domain->status = "Cancelled";
            $domain->save();
        }
        return $values;
    }

    /**
     * RegReleaseDomain
     */
    public function RegReleaseDomain($params = [])
    {
        $values = $this->RegCallFunction($params, "ReleaseDomain");
        if (isset($values["na"]) && $values["na"] === true) {
            return $values;
        }
        if (!isset($values["error"]) || !$values["error"]) {
            $domain = Domain::find($params["domainid"]);
            $domain->status = "Transferred Away";
            $domain->save();
        }
        return $values;
    }

    /**
     * RegRegisterNameserver
     */
    public function RegRegisterNameserver($params = [])
    {
        return $this->RegCallFunction($params, "RegisterNameserver");
    }

    /**
     * RegModifyNameserver
     */
    public function RegModifyNameserver($params = [])
    {
        return $this->RegCallFunction($params, "ModifyNameserver");
    }

    /**
     * RegDeleteNameserver
     */
    public function RegDeleteNameserver($params = [])
    {
        return $this->RegCallFunction($params, "DeleteNameserver");
    }

    /**
     * RegIDProtectToggle
     */
    public function RegIDProtectToggle($params = [])
    {
        if (!array_key_exists("protectenable", $params)) {
            $domainid = $params["domainid"];
            $domain = Domain::find($domainid);
            $idprotection = $domain->idprotection ? true : false;
            $params["protectenable"] = $idprotection;
        }
        return $this->RegCallFunction($params, "IDProtectToggle");
    }

    /**
     * RegGetDefaultNameservers
     */
    public function RegGetDefaultNameservers($params = [], $domain)
    {
        global $CONFIG;
        $getserverids = Hosting::select('server')->where('userid', $params['clientid'])->where('domain', $domain)->get(); #("tblhosting", "server", array("domain" => $domain));
        foreach ($getserverids as $serverData) {
            $serverid = $serverData->server;
        }
        if ($serverid) {
            $result = select_query("tblservers", "", array("id" => $serverid));
            // $data = mysql_fetch_array($result);
            for ($i = 1; $i <= 5; $i++) {
                $params["ns" . $i] = trim($data["nameserver" . $i]);
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $params["ns" . $i] = trim($CONFIG["DefaultNameserver" . $i]);
            }
        }
        return $params;
    }
}
