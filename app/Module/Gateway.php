<?php
namespace App\Module;

use DB, Auth;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;

class Gateway
{
    protected $type = 'gateway';
    protected $usesDirectories = false;
    protected $activeList = "";
    protected $legacyGatewayParams = array();
    const WORKFLOW_ASSISTED = "assisted";
    const WORKFLOW_REMOTE = "remote";
    const WORKFLOW_NOLOCALCARDINPUT = "nolocalcardinput";
    const WORKFLOW_TOKEN = "token";
    const WORKFLOW_MERCHANT = "merchant";
    const WORKFLOW_THIRDPARTY = "thirdparty";

    // absctract
    protected $moduleParams = array();
    protected $loadedmodule = "";
    protected $metaData = array();
    const FUNCTIONDOESNTEXIST = "!Function not found in module!";

    // lib
    private $modulename = "";
    private static $gateways = NULL;
    private $displaynames = array();
    const CC_EXPIRY_MAX_YEARS = 20;

    public function __construct()
    {
        $this->addParam("companyname", Cfg::get("CompanyName"));
        $this->addParam("systemurl", config('app.url'));
        $this->addParam("langpaynow", \Lang::get('client.invoicespaynow'));
    }
    public function getActiveModules()
    {
        return $this->getActiveGateways();
    }
    public function getList($type = "")
    {
        $data = [];
        $modules = \Module::toCollection();
        foreach ($modules as $key => $module) {
            if (strpos($module->getPath(), '/Gateways') !== false && $module->getName() != "Callback") {
                $data[strtolower($module->getName())] = $module;
            }
        }
        return $data;
    }
    public static function factory($name)
    {
        $gateway = new Gateway();
        if (!$gateway->load($name)) {
            throw new \App\Exceptions\Fatal("Module Not Found");
        }
        if (!$gateway->isLoadedModuleActive()) {
            throw new \App\Exceptions\Fatal("Module Not Activated");
        }
        return $gateway;
    }
    public function getActiveGateways()
    {
        if (is_array($this->activeList)) {
            return $this->activeList;
        }
        $this->activeList = array();
        $result = \Module::getByStatus(1);
        foreach ($result as $moduleName => $module) {
            if (strpos($module->getPath(), '/Gateways') !== false) {
                $this->activeList[] = $module;
            }
        }
        return $this->activeList;
    }
    public function getActiveGatewaysOld()
    {
        if (is_array($this->activeList)) {
            return $this->activeList;
        }
        $this->activeList = array();
        $result = \App\Models\Paymentgateway::distinct()->select('gateway')->whereNotIn('setting', ['forcesubscriptions', 'forceonetime'])->get();
        foreach ($result->toArray() as $data) {
            $gateway = $data['gateway'];
            if ($this->isNameValid($gateway)) {
                $this->activeList[] = $gateway;
            }
        }
        return $this->activeList;
    }
    public function getMerchantGateways()
    {
        return DB::table("tblpaymentgateways")->distinct("gateway")->where("setting", "type")->where("value", "CC")->orderBy("gateway")->pluck("gateway");
    }
    public function isActiveGateway($gateway)
    {
        $gateways = $this->getActiveGateways();
        $ActiveGatewaysData = [];
        foreach ($gateways as $gt) {
            $ActiveGatewaysData[] = $gt->getLowerName();
        }
        return in_array($gateway, $ActiveGatewaysData);
    }
    public function getAvailableGateways($invoiceid = "")
    {
        $validgateways = array();
        $result = DB::select(DB::raw("SELECT DISTINCT gateway, (SELECT value FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `name`, (SELECT `order` FROM tblpaymentgateways g2 WHERE g1.gateway=g2.gateway AND setting='name' LIMIT 1) AS `order` FROM `tblpaymentgateways` g1 WHERE setting='visible' AND value='on' ORDER BY `order` ASC"));
        $result = array_map(function ($value) {
            return (array)$value;
        }, $result);
        foreach ($result as $key => $data) {
            $validgateways[$data['gateway']] = $data['name'];
        }
        if ($invoiceid) {
            $invoiceid = (int) $invoiceid;
            $i = \App\Models\Invoice::find($invoiceid);
            $invoicegateway = $i ? $i->paymentmethod : '';
            $disabledgateways = array();
            $result = \App\Models\Invoiceitem::where('type', 'Hosting')->where('invoiceid', $invoiceid)->get();
            foreach ($result->toArray() as $data) {
                $relid = $data["relid"];
                if ($relid) {
                    $result2 = DB::select(DB::raw("SELECT pg.disabledgateways AS disabled FROM tblhosting h LEFT JOIN tblproducts p on h.packageid = p.id LEFT JOIN tblproductgroups pg on p.gid = pg.id where h.id = " . (int) $relid));
                    $gateways = explode(",", $result2[0]->disabled);
                    foreach ($gateways as $gateway) {
                        if (array_key_exists($gateway, $validgateways) && $gateway != $invoicegateway) {
                            unset($validgateways[$gateway]);
                        }
                    }
                }
            }
        }
        return $validgateways;
    }
    public function getFirstAvailableGateway()
    {
        $gateways = $this->getAvailableGateways();
        return key($gateways);
    }
    public function load($module, $globalVariable = NULL)
    {
        global $GATEWAYMODULE;
        $GATEWAYMODULE = array();
        // $licensing = \DI::make("license");
        // $module = \App::sanitize("0-9a-z_-", $module);
        $modulePath = $this->getModulePath($module);
        // \Log::debug("Attempting to load module", array("type" => $this->getType(), "module" => $module, "path" => $modulePath));
        $loadStatus = false;
        // $moduleInstance = \Module::find($module);
        if (\Module::find($module)) {
            // if ($moduleInstance->isEnabled()) {
                if (!is_null($globalVariable)) {
                    global ${$globalVariable};
                }
                $this->setLoadedModule($module);
                $this->setMetaData($this->getMetaData());
                $loadStatus = true;
            // }
        }
        $this->legacyGatewayParams[$module] = $GATEWAYMODULE;
        if ($loadStatus) {
            $this->loadSettings();
        }
        $this->legacyGatewayFields = $GATEWAYMODULE;
        return $loadStatus;
    }
    public function loadSettings()
    {
        $gateway = $this->getLoadedModule();
        $settings = array("paymentmethod" => $gateway);
        $result = \App\Models\Paymentgateway::where('gateway', $gateway)->get();
        foreach ($result->toArray() as $data) {
            $setting = $data["setting"];
            $value = $data["value"];
            $this->addParam($setting, $value);
            $settings[$setting] = $value;
        }
        return $settings;
    }
    public function isLoadedModuleActive()
    {
        return $this->getParam("type") ? true : false;
        // $moduleName = $this->getLoadedModule();
        // $module = \Module::find($moduleName);
        // if ($module) {
        //     return $module->isEnabled();
        // }
        // return false;
    }
    public function call($function, array $params = array())
    {
        $this->addParam("paymentmethod", $this->getLoadedModule());
        $userId = 0;
        if (array_key_exists("clientdetails", $params)) {
            $userId = $params["clientdetails"]["userid"];
        }
        if (!$userId) {
            $auth = Auth::user();
            $userId = $auth ? $auth->id : 0;
        }
        $clientBeforeCall = \App\User\Client::find($userId);
        $result = $this->callModule($function, $params);
        if ($clientBeforeCall && in_array($function, array("capture", "3dsecure", "orderformcheckout"))) {
            $this->processClientAfterCall($clientBeforeCall, $params);
        }
        return $result;
    }
    public function getWorkflowType()
    {
        if ($this->functionExists("credit_card_input")) {
            return static::WORKFLOW_ASSISTED;
        }
        if ($this->functionExists("remoteinput")) {
            return static::WORKFLOW_REMOTE;
        }
        if ($this->functionExists("nolocalcc")) {
            return static::WORKFLOW_NOLOCALCARDINPUT;
        }
        if ($this->functionExists("storeremote")) {
            return static::WORKFLOW_TOKEN;
        }
        if ($this->functionExists("capture")) {
            return static::WORKFLOW_MERCHANT;
        }
        return static::WORKFLOW_THIRDPARTY;
    }
    private function processClientAfterCall(\App\User\Client $clientBeforeCall, array $callParams)
    {
        $clientAfterCall = $clientBeforeCall->fresh();
        $invoiceModel = \App\Models\Invoice::find($callParams["invoiceid"]);
        if (!$invoiceModel) {
            return NULL;
        }
        if (!$invoiceModel->payMethod || $invoiceModel->payMethod->trashed()) {
            return NULL;
        }
        if ($clientAfterCall->paymentGatewayToken !== $clientBeforeCall->paymentGatewayToken && $invoiceModel->payMethod->payment instanceof \App\Payment\Contracts\RemoteTokenDetailsInterface) {
            if ($clientAfterCall->paymentGatewayToken) {
                $payment = $invoiceModel->payMethod->payment;
                $payment->setRemoteToken($clientAfterCall->paymentGatewayToken);
                $payment->save();
                $clientAfterCall->paymentGatewayToken = "";
                $clientAfterCall->save();
            } else {
                $invoiceModel->payMethod->delete();
            }
        }
        if ($clientAfterCall->creditCardType !== $clientBeforeCall->creditCardType) {
            if (!empty($clientAfterCall->creditCardType)) {
                $this->migrateUpdatedCardData($clientAfterCall, $invoiceModel->payMethod);
            } else {
                if (!$clientAfterCall->paymentGatewayToken) {
                    $invoiceModel->payMethod->delete();
                }
            }
        }
    }
    private function processClientAfterCallOLD(\App\Models\Client $clientBeforeCall, array $callParams)
    {
        if ($clientAfterCall) {
            $clientAfterCall = $clientBeforeCall->fresh();
            $invoiceModel = \App\Models\Invoice::find($callParams["invoiceid"]);
            if (!$invoiceModel) {
                return NULL;
            }
            if (!$invoiceModel->payMethod || $invoiceModel->payMethod->trashed()) {
                return NULL;
            }
            if ($clientAfterCall->paymentGatewayToken !== $clientBeforeCall->paymentGatewayToken && $invoiceModel->payMethod->payment instanceof \App\Payment\Contracts\RemoteTokenDetailsInterface) {
                if ($clientAfterCall->paymentGatewayToken) {
                    $payment = $invoiceModel->payMethod->payment;
                    $payment->setRemoteToken($clientAfterCall->paymentGatewayToken);
                    $payment->save();
                    $clientAfterCall->paymentGatewayToken = "";
                    $clientAfterCall->save();
                } else {
                    $invoiceModel->payMethod->delete();
                }
            }
            if ($clientAfterCall->creditCardType !== $clientBeforeCall->creditCardType) {
                if (!empty($clientAfterCall->creditCardType)) {
                    $this->migrateUpdatedCardData($clientAfterCall, $invoiceModel->payMethod);
                } else {
                    if (!$clientAfterCall->paymentGatewayToken) {
                        $invoiceModel->payMethod->delete();
                    }
                }
            }
        }
    }
    private function migrateUpdatedCardData(\App\User\Client $client, \App\Payment\PayMethod\Model $payMethod)
    {
        if ($payMethod->payment instanceof \App\Payment\Contracts\CreditCardDetailsInterface) {
            $legacyCardData = \App\Helpers\Cc::getClientDefaultCardDetails($client->id, "forceLegacy");
            $payment = $payMethod->payment;
            if ($legacyCardData["cardnum"]) {
                $payment->setCardNumber($legacyCardData["cardnum"]);
            }
            if ($legacyCardData["cardlastfour"]) {
                $payment->setLastFour($legacyCardData["cardlastfour"]);
            }
            if ($legacyCardData["cardtype"]) {
                $payment->setCardType($legacyCardData["cardtype"]);
            }
            if ($legacyCardData["startdate"]) {
                $payment->setStartDate(\App\Helpers\Carbon::createFromCcInput($legacyCardData["startdate"]));
            }
            if ($legacyCardData["expdate"]) {
                $payment->setExpiryDate(\App\Helpers\Carbon::createFromCcInput($legacyCardData["expdate"]));
            }
            if ($legacyCardData["issuenumber"]) {
                $payment->setIssueNumber($legacyCardData["issuenumber"]);
            }
            $payment->save();
            $client->markCardDetailsAsMigrated();
        }
    }

    // Gateways lib
    public static function isNameValid($gateway)
    {
        if (!is_string($gateway) || empty($gateway)) {
            return false;
        }
        if (!ctype_alnum(str_replace(array("_", "-"), "", $gateway))) {
            return false;
        }
        return true;
    }
    public function getDisplayNameModule($gateway)
    {
        if (empty($this->displaynames)) {
            $this->getDisplayNames();
        }
        return array_key_exists($gateway, $this->displaynames) ? $this->displaynames[$gateway] : $gateway;
    }
    public function getDisplayName()
    {
        if ($this->getLoadedModule()) {
            return (string) $this->getParam("name");
        }
        return $this->getDisplayNameModule($this->loadedmodule);
    }
    public function getDisplayNames()
    {
        $result = \App\Models\Paymentgateway::where('setting', 'name')->orderBy('order', 'ASC')->get();
        foreach ($result->toArray() as $data) {
            $this->displaynames[$data["gateway"]] = $data["value"];
        }
        return $this->displaynames;
    }

    // Abstract module
    public function getType()
    {
        return $this->type;
    }
    protected function addParam($key, $value)
    {
        $this->moduleParams[$key] = $value;
        return $this;
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
            return $this->callModule("MetaData");
        }
    }
    public function getLoadedModule()
    {
        return $this->loadedmodule;
    }
    public function setLoadedModule($module)
    {
        $this->loadedmodule = $module;
    }
    public function getModulePath($module)
    {
        return \Module::getModulePath($module);
    }
    public function functionExists($name)
    {
        $moduleName = $this->getLoadedModule();
        $module = \Module::find($moduleName);
        if (!$moduleName || !$module) {
            return false;
        }
        $modName = $module->getName();
        $className = "\\Modules\\Gateways\\{$modName}\\Http\\Controllers\\{$modName}Controller";
        $object = new $className();
        return method_exists($object, $name);
    }
    protected function callModule($function, array $params = array())
    {
        if ($this->functionExists($function)) {
            $params = array_merge($this->getParams(), $params);
            $moduleName = $this->getLoadedModule();
            $module = \Module::find($moduleName);
            if ($module) {
                $modName = $module->getName();
                $className = "\\Modules\\Gateways\\{$modName}\\Http\\Controllers\\{$modName}Controller";
                $object = new $className();

                return $object->{$function}($params);
            } else {
                return "Module not found";
            }
        }
        return self::FUNCTIONDOESNTEXIST;
    }
    public function getParams()
    {
        $moduleParams = $this->moduleParams;
        return $this->prepareParams($moduleParams);
    }
    public function getParam($key)
    {
        $moduleParams = $this->getParams();
        return isset($moduleParams[$key]) ? $moduleParams[$key] : "";
    }
    public function prepareParams($params)
    {
        return $params;
    }
    public function getBaseGatewayType()
    {
        $type = "3rdparty";
        if ($this->supportsAutoCapture()) {
            $type = "creditcard";
        }
        if ($this->supportsLocalBankDetails()) {
            $type = "bankaccount";
        }
        return $type;
    }
    public function supportsAutoCapture()
    {
        return $this->functionExists("capture");
    }
    public function supportsLocalBankDetails()
    {
        return $this->functionExists("localbankdetails");
    }
    public function getAvailableGatewayInstances($onlyStoreRemote = false)
    {
        $modules = array();
        $gatewaysAggregator = new static();
        foreach (array_keys($gatewaysAggregator->getAvailableGateways()) as $name) {
            $module = new self();
            if ($module->isActiveGateway($name) && $module->load($name)) {
                if ($onlyStoreRemote) {
                    if ($module->functionExists("storeremote")) {
                        $modules[$name] = $module;
                    }
                } else {
                    $modules[$name] = $module;
                }
            }
        }
        return $modules;
    }
    public function isLocalCreditCardStorageEnabled($client = true)
    {
        $merchantGateways = $this->getActiveMerchantGatewaysByType()[\App\Module\Gateway::WORKFLOW_MERCHANT];
        if ($client) {
            $merchantGateways = array_filter($merchantGateways);
        }
        return 0 < count($merchantGateways);
    }
    public function getActiveMerchantGatewaysByType()
    {
        $groupedGateways = array("assisted" => array(), "merchant" => array(), "remote" => array(), "thirdparty" => array(), "token" => array());
        $query = DB::table("tblpaymentgateways as gw1")->where("gw1.setting", "type")->where("gw1.value", "CC")->leftJoin("tblpaymentgateways as gw2", "gw1.gateway", "=", "gw2.gateway")->where("gw2.setting", "visible");
        $gateways = $query->get(array("gw1.gateway", "gw2.value as visible"));
        foreach ($gateways as $gatewayData) {
            $gateway = $gatewayData->gateway;
            $gatewayInterface = new \App\Module\Gateway();
            $gatewayInterface->load($gateway);
            $groupedGateways[$gatewayInterface->getWorkflowType()][$gateway] = (bool) $gatewayData->visible;
        }
        return $groupedGateways;
    }
    public function getConfiguration()
    {
        if (!$this->getLoadedModule()) {
            throw new \Exception("No module loaded to fetch configuration for");
        }
        if ($this->functionExists("config")) {
            return $this->call("config");
        }
        if ($this->functionExists("activate")) {
            $module = $this->getLoadedModule();
            $legacyDisplayName = isset($this->legacyGatewayParams[$module][$module . "visiblename"]) ? $this->legacyGatewayParams[$module][$module . "visiblename"] : ucfirst($module);
            $legacyNotes = isset($this->legacyGatewayParams[$module][$module . "notes"]) ? $this->legacyGatewayParams[$module][$module . "notes"] : "";
            $this->call("activate");
            $response = array_merge(array("FriendlyName" => array("Type" => "System", "Value" => $legacyDisplayName)), \App\Helpers\Functions::defineGatewayFieldStorage(true));
            if (!empty($legacyNotes)) {
                $response["UsageNotes"] = array("Type" => "System", "Value" => $legacyNotes);
            }
            return $response;
        }
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function getMetaDataValue($keyName)
    {
        return array_key_exists($keyName, $this->metaData) ? $this->metaData[$keyName] : "";
    }
    public function isMetaDataValueSet($keyName)
    {
        return array_key_exists($keyName, $this->metaData);
    }
    public function getOnBoardingRedirectHtml()
    {
        if (!$this->getMetaDataValue("apiOnboarding")) {
            return "";
        }
        $redirectUrl = $this->getMetaDataValue("apiOnboardingRedirectUrl");
        $callbackPath = $this->getMetaDataValue("apiOnboardingCallbackPath");
        $admin = \App\Models\Admin::getAuthenticatedUser();
        $params = [];
        if ($admin) {
            $params = array("firstname" => $admin->firstname, "lastname" => $admin->lastname, "companyname" => Cfg::getValue("CompanyName"), "email" => $admin->email, "whmcs_callback_url" => config('app.url') . $callbackPath, "return_url" => 'route("admin-setup-payments-gateways-onboarding-return")');
        }
        $buttonValue = "Click here if not redirected automatically";
        $output = "<html><head><title>Redirecting...</title></head>" . "<body onload=\"document.onboardfrm.submit()\">" . "<p>Please wait while you are redirected...</p>" . "<form method=\"post\" action=\"" . $redirectUrl . "\" name=\"onboardfrm\">";
        foreach ($params as $key => $value) {
            $output .= "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . \App\Helpers\Sanitize::makeSafeForOutput($value) . "\">";
        }
        $output .= "<input type=\"submit\" value=\"" . $buttonValue . "\" class=\"btn btn-default\">" . "</form>" . "</body></html>";
        return $output;
    }
    public function activate(array $parameters = array())
    {
        if ($this->isLoadedModuleActive()) {
            throw new \App\Exceptions\Module\NotActivated("Module already active");
        }
        $lastOrder = (int) \App\Models\Paymentgateway::select('order')->where(array("setting" => "name", "gateway" => $this->getLoadedModule()))->orderBy('order', 'DESC')->value('order');
        if (!$lastOrder) {
            $lastOrder = (int) \App\Models\Paymentgateway::select('order')->orderBy('order', 'DESC')->value('order');
            $lastOrder++;
        }
        $configData = $this->getConfiguration();
        $displayName = isset($configData["FriendlyName"]) ? $configData["FriendlyName"]["Value"] : "";
        $gatewayType = $this->functionExists("capture") ? "CC" : "Invoices";
        $this->saveConfigValue("name", $displayName, $lastOrder);
        $this->saveConfigValue("type", $gatewayType);
        $this->saveConfigValue("visible", "on");
        if (isset($configData["RemoteStorage"])) {
            $this->saveConfigValue("remotestorage", "1");
        }
        // $hookFile = $this->getModuleDirectory($this->getLoadedModule()) . DIRECTORY_SEPARATOR . "hooks.php";
        // if (file_exists($hookFile)) {
        //     $hooks = array_filter(explode(",", \WHMCS\Config\Setting::getValue("GatewayModuleHooks")));
        //     if (!in_array($this->getLoadedModule(), $hooks)) {
        //         $hooks[] = $this->getLoadedModule();
        //     }
        //     \WHMCS\Config\Setting::setValue("GatewayModuleHooks", implode(",", $hooks));
        // }
        \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Activated: '" . $displayName . "'");
        $this->load($this->getLoadedModule());
        $this->updateConfiguration($parameters);
        $module = \Module::find($this->getLoadedModule());
        if ($module) {
            $module->enable();
        }
        return true;
    }
    public function updateConfiguration(array $parameters = array())
    {
        if (!$this->isLoadedModuleActive()) {
            throw new \App\Exceptions\Module\NotActivated("Module not active");
        }
        if (0 < count($parameters)) {
            $configData = $this->getConfiguration();
            $displayName = isset($configData["FriendlyName"]) ? $configData["FriendlyName"]["Value"] : "";
            foreach ($parameters as $key => $value) {
                if (array_key_exists($key, $configData)) {
                    $this->saveConfigValue($key, $value);
                }
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Configuration Updated: '" . $displayName . "'");
        }
    }
    public function deactivate(array $parameters = array())
    {
        if (!$this->isLoadedModuleActive()) {
            throw new \App\Exceptions\Module\NotActivated("Module not active");
        }
        if (empty($parameters["newGateway"])) {
            throw new \App\Exceptions\Module\NotServicable("New Module Required");
        }
        if ($this->getLoadedModule() != $parameters["newGateway"]) {
            $tables = array("tblaccounts", "tbldomains", "tblhosting", "tblhostingaddons", "tblinvoices", "tblorders");
            foreach ($tables as $table) {
                $field = "paymentmethod";
                if ($table == "tblaccounts") {
                    $field = "gateway";
                }
                DB::table($table)->where($field, $this->getLoadedModule())->update(array($field => $parameters["newGateway"]));
            }
            $configData = $this->getConfiguration();
            $displayName = isset($configData["FriendlyName"]) ? $configData["FriendlyName"]["Value"] : "";
            DB::table("tblpaymentgateways")->where("gateway", $this->getLoadedModule())->delete();
            // $hooks = array_filter(explode(",", \WHMCS\Config\Setting::getValue("GatewayModuleHooks")));
            // if (in_array($this->getLoadedModule(), $hooks)) {
            //     $hooks = array_flip($hooks);
            //     unset($hooks[$this->getLoadedModule()]);
            //     $hooks = array_flip($hooks);
            //     \WHMCS\Config\Setting::setValue("GatewayModuleHooks", implode(",", $hooks));
            // }
            $module = \Module::find($this->getLoadedModule());
            if ($module) {
                $module->disable();
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Deactivated: '" . $displayName . "'" . " to '" . $parameters["newGatewayName"] . "'");
            return true;
        } else {
            throw new \App\Exceptions\Module\NotImplemented("Invalid New Module");
        }
    }
    protected function saveConfigValue($setting, $value, $order = 0)
    {
        \App\Models\Paymentgateway::where(array("gateway" => $this->getLoadedModule(), "setting" => $setting))->delete();
        \App\Models\Paymentgateway::insert(array("gateway" => $this->getLoadedModule(), "setting" => $setting, "value" => $value, "order" => $order));
        $this->addParam($setting, $value);
    }
    public function isTokenised()
    {
        $tokenizedWorkflows = array(static::WORKFLOW_ASSISTED, static::WORKFLOW_REMOTE, static::WORKFLOW_NOLOCALCARDINPUT, static::WORKFLOW_TOKEN);
        return in_array($this->getWorkflowType(), $tokenizedWorkflows);
    }
}
