<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Hosting;
use App\Models\Product;
use App\Models\Productgroup;
use App\Models\Server;
use App\Models\Hostingaddon;

// Import Package Class here
use App\Helpers\Pwd;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use DB;

class Service
{
	private $id = "";
	private $userid = "";
	private $data = array();
	private $moduleparams = array();
	private $moduleresults = array();
	private $addons_names = NULL;
	private $addons_to_pids = NULL;
	private $addons_downloads = array();
	private $associated_download_ids = array();
	public function __construct($serviceId = NULL, $userId = NULL)
	{
        if ($serviceId) {
            $this->setServiceID($serviceId, $userId);
        }
	}

    public function setServiceID($serviceid, $userid = "")
    {
        $this->id = $serviceid;
        $this->userid = $userid;
        $this->data = array();
        $this->moduleparams = array();
        $this->moduleresults = array();
        return $this->getServicesData();
    }

    public function getServicesData_OLD()
    {
        $hostingTable = (new Hosting)->getTableName();
        $productTable = (new Product)->getTableName();
        $productgroupTable = (new Productgroup)->getTableName();

        $result = Hosting::query();
        $result->select("{$hostingTable}.*", "{$productgroupTable}.id AS group_id", "{$productTable}.id AS productid", "{$productTable}.name", "{$productTable}.type", "{$productTable}.tax", "{$productTable}.configoptionsupgrade", "{$productTable}.billingcycleupgrade", "{$productTable}.servertype", "{$productgroupTable}.name as group_name");
        $result->where("{$hostingTable}.id", $this->id);
        if ($this->userid) {
            $result->where("{$hostingTable}.userid", $this->userid);
        }
        $result->join($productTable, "{$productTable}.id", "=", "{$hostingTable}.packageid");
        $result->join($productgroupTable, "{$productgroupTable}.id", "=", "{$productTable}.id");
        $data = $result->first();
        $data = $data->toArray();

        if ($data["id"]) {
            $data["pid"] = $data["packageid"];
            $data["status"] = $data["domainstatus"];
            $data["password"] = (new Pwd)->decrypt($data["password"]);
            $data["groupname"] = $data["group_name"];
            $data["productname"] = $data["name"];
            // TODO: $this->associated_download_ids = Product\Product::find($data["productid"])->getDownloadIds();
            $this->data = $data;
            // TODO: $this->data["upgradepackages"] = Product\Product::find($data["productid"])->getUpgradeProductIds();
            return true;
        }
        return false;
    }

    public function isNotValid()
    {
        return !count($this->data) ? true : false;
    }

    public function getData($var)
    {
        return isset($this->data[$var]) ? $this->data[$var] : "";
    }

    public function getID()
    {
        return (int) $this->getData("id");
    }

    public function getServerInfo()
    {
        if (!$this->getData("server")) {
            return array();
        }
        $serverarray = Server::where('id', $this->getData("server"))->first();
        return $serverarray->toArray();
    }

    public function getSuspensionReason()
    {
        if ($this->getData("status") != "Suspended") {
            return "";
        }
        $suspendreason = $this->getData("suspendreason");
        if (!$suspendreason) {
            $suspendreason = __("client.suspendreasonoverdue");
        }
        return $suspendreason;
    }

    public function getServicesData()
    {
        $pfx = \Database::prefix();
        $result = Hosting::selectRaw("{$pfx}hosting.*,{$pfx}productgroups.id AS group_id,{$pfx}products.id AS productid,{$pfx}products.name," . "{$pfx}products.type,{$pfx}products.tax,{$pfx}products.configoptionsupgrade,{$pfx}products.billingcycleupgrade,{$pfx}products.servertype,{$pfx}productgroups.name as group_name")
                            ->where("{$pfx}hosting.id", $this->id);

        if ($this->userid) {
            $result->where("{$pfx}hosting.userid", $this->userid);
        }

        $result->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                ->join("{$pfx}productgroups", "{$pfx}productgroups.id", "{$pfx}products.gid");
       
        $data = $result->first();
        if ($data) {
            $data = $data->toArray();
            $data["pid"] = $data["packageid"];
            $data["status"] = $data["domainstatus"];
            $data["password"] = (new Pwd)->decrypt($data["password"]);
            $data["groupname"] = Productgroup::getGroupName($data["group_id"], $data["group_name"]);
            $data["productname"] = Product::getProductName($data["packageid"], $data["name"]);
            
            $this->associated_download_ids = Product::find($data["productid"])->getDownloadIds();
            $this->data = $data;
            $this->data["upgradepackages"] = Product::find($data["productid"])->getUpgradeProductIds();
            
            return true;
        }

        return false;
    }

    public function getAddons()
    {
        $addonCollection = Hostingaddon::with("productAddon")->where("hostingid", "=", $this->getID())->orderBy("id", "DESC")->get();
        $addons = array();
        foreach ($addonCollection as $addon) {
            $addonName = $addon->name;
            $addonPaymentMethod = $addon->paymentGateway;
            $rawStatus = strtolower($addon->status);
            $addonRegistrationDate = (new Functions())->fromMySQLDate($addon->registrationDate, 0, 1);
            $addonNextDueDate = (new Functions())->fromMySQLDate($addon->nextDueDate, 0, 1);
            $addonPricing = "";
            if (!$addonPaymentMethod) {
                $addonPaymentMethod = Functions::ensurePaymentMethodIsSet($addon->clientId, $addon->id, "tblhostingaddons");
            }
            if ($addon->id) {
                if (!$addonName) {
                    $addonName = $addon->productAddon->name;
                }
                if (0 < count($addon->productAddon->downloads)) {
                    $this->addAssociatedDownloadID($addon->productAddon->downloads);
                }
            }
            if (substr($addon->billingCycle, 0, 4) == "Free") {
                $addonPricing = \Lang::get("client.orderfree");
                $addonNextDueDate = "-";
            } else {
                if ($addon->billingCycle == "One Time") {
                    $addonNextDueDate = "-";
                }
                if (0 < $addon->setupFee) {
                    $addonPricing .= Format::formatCurrency($addon->setupFee) . \Lang::get("client.ordersetupfee");
                }
                if (0 < $addon->recurringFee) {
                    $modifiedCycle = str_replace(array("-", " "), "", strtolower($addon->billingCycle));
                    $addonPricing .= Format::formatCurrency($addon->recurringFee) . " " . \Lang::get("client.orderpaymentterm" . $modifiedCycle);
                }
                if (!$addonPricing) {
                    $addonPricing = \Lang::get("client.orderfree");
                }
            }
            $xColour = "clientareatable" . $rawStatus;
            $addonStatus = \Lang::get("client.clientarea" . $rawStatus);
            if (!in_array($rawStatus, array("Active", "Suspended", "Pending"))) {
                $xColour = "clientareatableterminated";
            }
            $managementActions = "";
            if ((defined("CLIENTAREA") || Application::isClientAreaRequest()) && $addon->productAddon->module) {
                $server = new \App\Module\Server();
                if ($server->loadByAddonId($addon->id) && $server->functionExists("ClientArea")) {
                    $managementActions = $server->call("ClientArea");
                    if (is_array($managementActions)) {
                        $managementActions = "";
                    }
                }
            }
            $addons[] = array("id" => $addon->id, "regdate" => $addonRegistrationDate, "name" => $addonName, "pricing" => $addonPricing, "paymentmethod" => $addonPaymentMethod, "nextduedate" => $addonNextDueDate, "status" => $addonStatus, "rawstatus" => $rawStatus, "class" => $xColour, "managementActions" => $managementActions);
        }
        return $addons;
    }
    public function getAddonsOLD()
    {
        $addonCollection = Hostingaddon::with("productAddon")->where("hostingid", $this->getID())->orderBy("id", "DESC")->get();
        $addons = array();
        $prefix = \Database::prefix();

        foreach ($addonCollection as $addon) {
            $addonName = $addon->name;
            $addonPaymentMethod = $addon->paymentGateway;
            $rawStatus = strtolower($addon->status);
            $addonRegistrationDate = (new Functions())->fromMySQLDate($addon->registrationDate, 0, 1);
            $addonNextDueDate = (new Functions())->fromMySQLDate($addon->nextDueDate, 0, 1);
            $addonPricing = "";
            if (!$addonPaymentMethod) {
                $addonPaymentMethod = Functions::ensurePaymentMethodIsSet($addon->clientId, $addon->id, "{$prefix}hostingaddons");
            }

            if ($addon->id) {
                if ($addon->productAddon) {
                    if (!$addonName) {
                        $addonName = $addon->productAddon->name;
                    }
                    
                    if (0 < count($addon->productAddon->downloads)) {
                        $this->addAssociatedDownloadID($addon->productAddon->downloads);
                    }
                }
            }

            if (substr($addon->billingCycle, 0, 4) == "Free") {
                $addonPricing = \Lang::get("admin.orderfree");
                $addonNextDueDate = "-";
            } else {
                if ($addon->billingCycle == "One Time") {
                    $addonNextDueDate = "-";
                }
                
                if (0 < $addon->setupFee) {
                    $addonPricing .= Format::formatCurrency($addon->setupFee) . " " . \Lang::get("client.ordersetupfee");
                }
                
                if (0 < $addon->recurringFee) {
                    $modifiedCycle = str_replace(array("-", " "), "", strtolower($addon->billingCycle));
                    if (0 < $addon->setupFee) {
                        $addonPricing .= " + ";
                    }
                    $addonPricing .= Format::formatCurrency($addon->recurringFee) . " " . \Lang::get("client.orderpaymentterm" . $modifiedCycle);
                }

                if (!$addonPricing) {
                    $addonPricing = \Lang::get("admin.orderfree");
                }
            }
            
            $xColour = "clientareatable" . $rawStatus;
            $addonStatus = \Lang::get("client.clientarea" . $rawStatus);
            if (!in_array($rawStatus, array("Active", "Suspended", "Pending"))) {
                $xColour = "clientareatableterminated";
            }

            $managementActions = "";
            if (Application::isClientAreaRequest() && $addon->productAddon->module) {
                $server = new \App\Module\Server();
                if ($server->loadByAddonId($addon->id) && $server->functionExists("ClientArea")) {
                    $managementActions = $server->call("ClientArea");
                    if (is_array($managementActions)) {
                        $managementActions = "";
                    }
                }
            }

            $addons[] = [
                "id" => $addon->id, 
                "regdate" => $addonRegistrationDate, 
                "name" => $addonName, 
                "pricing" => $addonPricing, 
                "paymentmethod" => $addonPaymentMethod, 
                "nextduedate" => $addonNextDueDate, 
                "status" => $addonStatus, 
                "rawstatus" => $rawStatus, 
                "class" => $xColour, 
                "managementActions" => $managementActions
            ];
        }

        return $addons;
    }

    private function addAssociatedDownloadID($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $id) {
                if (is_numeric($id)) {
                    $this->associated_download_ids[] = $id;
                }
            }
        } else {
            if (is_numeric($mixed)) {
                $this->associated_download_ids[] = $mixed;
            } else {
                return false;
            }
        }
        return true;
    }

    public function getAllowChangePassword()
    {
        // HOTFIX: if ($this->getData("status") == "Active" && checkContactPermission("manageproducts", true)) {
        if ($this->getData("status") == "Active") {
            return true;
        }
        return false;
    }
    public function getAssociatedDownloads()
    {
        $download_ids = \App\Helpers\Database::db_build_in_array(\App\Helpers\Database::db_escape_numarray($this->associated_download_ids));
        if (!$download_ids) {
            return array();
        }
        $downloadsarray = array();
        $result = \App\Models\Download::whereRaw("id IN (" . $download_ids . ")")->orderBy('id', 'DESC')->get();
        foreach ($result->toArray() as $data) {
            $dlid = $data["id"];
            $category = $data["category"];
            $type = $data["type"];
            $title = $data["title"];
            $description = $data["description"];
            $downloads = $data["downloads"];
            $location = $data["location"];
            $fileext = explode(".", $location);
            $fileext = end($fileext);
            $type = "zip";
            if ($fileext == "doc") {
                $type = "doc";
            }
            if ($fileext == "gif" || $fileext == "jpg" || $fileext == "jpeg" || $fileext == "png") {
                $type = "picture";
            }
            if ($fileext == "txt") {
                $type = "txt";
            }
            $type = "<img src=\"images/" . $type . ".png\" align=\"absmiddle\" alt=\"\" />";
            $downloadsarray[] = array("id" => $dlid, "catid" => $category, "type" => $type, "title" => $title, "description" => $description, "downloads" => $downloads, "link" => "dl.php?type=d&id=" . $dlid . "&serviceid=" . $this->getID());
        }
        return $downloadsarray;
    }
    public function hasProductGotAddons()
    {
        if (is_null($this->addons_to_pids)) {
            $this->getPredefinedAddons();
        }
        $addons = array();
        foreach ($this->addons_to_pids as $addonid => $pids) {
            if (in_array($this->getData("pid"), $pids)) {
                $addons[] = $addonid;
            }
        }
        return $addons;
    }
    public function getPredefinedAddons()
    {
        $this->addons_names = $this->addons_to_pids = array();
        $result = \App\Models\Addon::all();
        foreach ($result->toArray() as $data) {
            $addon_id = $data["id"];
            $addon_packages = $data["packages"];
            $addon_packages = explode(",", $addon_packages);
            $this->addons_names[$addon_id] = $data["name"];
            $this->addons_to_pids[$addon_id] = $addon_packages;
            $this->addons_downloads[$addon_id] = explode(",", $data["downloads"]);
        }
        return $this->addons_names;
    }
    public function getPredefinedAddonsOnce()
    {
        if (is_array($this->addons_names)) {
            return $this->addons_names;
        }
        return $this->getPredefinedAddons();
    }
    public function getAllowConfigOptionsUpgrade()
    {
        if ($this->getData("status") == "Active" && $this->getData("configoptionsupgrade")) {
            return true;
        }
        return false;
    }
    public function getAllowProductUpgrades()
    {
        if ($this->getData("status") == "Active" && $this->getData("upgradepackages")) {
            $upgradepackages = count($this->getData("upgradepackages"));
            return $upgradepackages ? true : false;
        }
        return false;
    }
    public function getStatusDisplay()
    {
        $lang = strtolower($this->getData("status"));
        $lang = str_replace(" ", "", $lang);
        $lang = str_replace("-", "", $lang);
        return \Lang::get("client.clientarea" . $lang);
    }
    public function getBillingCycleDisplay()
    {
        $lang = strtolower($this->getData("billingcycle"));
        $lang = str_replace(" ", "", $lang);
        $lang = str_replace("-", "", $lang);
        return \Lang::get("admin.orderpaymentterm" . $lang);
    }
    public function getPaymentMethod()
    {
        $paymentmethod = $this->getData("paymentmethod");
        $displayname = \App\Models\Paymentgateway::where(array("gateway" => $paymentmethod, "setting" => "name"))->value("value");
        return $displayname ? $displayname : $paymentmethod;
    }
    public function getModule()
    {
        return $this->getData("servertype");
    }
    public function getCustomFields()
    {
        return \App\Helpers\Customfield::getCustomFields("product", $this->getData("pid"), $this->getData("id"), "", "", "", true);
    }
    public function getConfigurableOptions()
    {
        return \App\Helpers\ConfigOptions::getCartConfigOptions($this->getData("pid"), "", $this->getData("billingcycle"), $this->getData("id"));
    }
    public function getAllowCancellation()
    {
        // HOTFIX: if (($this->getData("status") == "Active" || $this->getData("status") == "Suspended") && checkContactPermission("orders", true)) {
        if (($this->getData("status") == "Active" || $this->getData("status") == "Suspended")) {
            $billingCycle = $this->getData("billingcycle");
            if (!in_array(strtolower($billingCycle), array("free", "free account", "one time", "onetime"))) {
                return \App\Helpers\Cfg::get("ShowCancellationButton") ? true : false;
            }
        }
        return false;
    }
    public function hasCancellationRequest()
    {
        if ($this->getData("status") != "Cancelled") {
            $cancellation = DB::table("tblcancelrequests")->select("type")->where("relid", "=", $this->getData("id"))->count();
            return 0 < $cancellation;
        }
        return false;
    }
    public function getDiskUsageStats()
    {
        $diskusage = $this->getData("diskusage");
        $disklimit = $this->getData("disklimit");
        $bwusage = $this->getData("bwusage");
        $bwlimit = $this->getData("bwlimit");
        $lastupdate = $this->getData("lastupdate");
        if ($disklimit == "0") {
            $disklimit = \Lang::get("client.clientareaunlimited");
            $diskpercent = "0%";
        } else {
            $diskpercent = round($diskusage / $disklimit * 100, 0) . "%";
        }
        if ($bwlimit == "0") {
            $bwlimit = \Lang::get("client.clientareaunlimited");
            $bwpercent = "0%";
        } else {
            $bwpercent = round($bwusage / $bwlimit * 100, 0) . "%";
        }
        $lastupdate = $lastupdate == "0000-00-00 00:00:00" ? "" : (new Functions())->fromMySQLDate($lastupdate, 1, 1);
        return array("diskusage" => $diskusage, "disklimit" => $disklimit, "diskpercent" => $diskpercent, "bwusage" => $bwusage, "bwlimit" => $bwlimit, "bwpercent" => $bwpercent, "lastupdate" => $lastupdate);
    }
    public function hasFunction($function)
    {
        $moduleInterface = new \App\Module\Server();
        $moduleName = $this->getModule();
        if (!$moduleName) {
            $this->moduleresults = array("error" => "Service not assigned to a module");
            return false;
        }
        $loaded = $moduleInterface->load($moduleName);
        if (!$loaded) {
            $this->moduleresults = array("error" => "Product module not found");
            return false;
        }
        return $moduleInterface->functionExists($function);
    }
    public function moduleCall($function, $vars = array())
    {
        $moduleInterface = new \App\Module\Server();
        $moduleName = $this->getModule();
        if (!$moduleName) {
            $this->moduleresults = array("error" => "Service not assigned to a module");
            return false;
        }
        $loaded = $moduleInterface->load($moduleName);
        if (!$loaded) {
            $this->moduleresults = array("error" => "Product module not found");
            return false;
        }
        $moduleInterface->setServiceId($this->getID());
        $builtParams = array_merge($moduleInterface->getParams(), $vars);
        switch ($function) {
            case "CreateAccount":
                $hookFunction = "Create";
                break;
            case "SuspendAccount":
                $hookFunction = "Suspend";
                break;
            case "TerminateAccount":
                $hookFunction = "Terminate";
                break;
            case "UnsuspendAccount":
                $hookFunction = "Unsuspend";
                break;
            default:
                $hookFunction = $function;
        }
        $hookResults = \App\Helpers\Hooks::run_hook("PreModule" . $hookFunction, array("params" => $builtParams));
        try {
            if (\App\Helpers\Hooks::processHookResults($moduleName, $function, $hookResults)) {
                return true;
            }
        } catch (\Exception $e) {
            $this->moduleresults = array("error" => $e->getMessage());
            return false;
        }
        $results = $moduleInterface->call($function, $builtParams);
        $hookVars = array("params" => $builtParams, "results" => $results, "functionExists" => $results !== \App\Module\Server::FUNCTIONDOESNTEXIST, "functionSuccessful" => is_array($results) && empty($results["error"]) || is_object($results));
        $successOrFail = "";
        if (!$hookVars["functionSuccessful"] && (isset($hookResults["functionExists"]) && $hookResults["functionExists"])) {
            $successOrFail = "Failed";
        }
        $hookResults = \App\Helpers\Hooks::run_hook("AfterModule" . $hookFunction . $successOrFail, $hookVars);
        try {
            if (\App\Helpers\Hooks::processHookResults($moduleName, $function, $hookResults)) {
                return true;
            }
        } catch (\Exception $e) {
            return array("error" => $e->getMessage());
        }
        if (!$hookVars["functionExists"] || $results === false) {
            $this->moduleresults = array("error" => "Function not found");
            return false;
        }
        if (is_array($results)) {
            $results = array("data" => $results);
        } else {
            $results = $results == "success" || !$results ? array() : array("error" => $results, "data" => $results);
        }
        $this->moduleresults = $results;
        return isset($results["error"]) && $results["error"] ? false : true;
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
}
