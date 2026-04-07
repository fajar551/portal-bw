<?php

namespace App\Http\Controllers\Admin;

use DB, Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
//Models
use App\Models\Currency;
use App\Models\Emailtemplate;;

use App\Models\AdminRole;
use App\Models\Admin;
//Helpers
use App\Helpers\Password;
use Ramsey\Uuid\Uuid;
use App\Helpers\Cfg;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;
use App\Helpers\Format;
use App\Helpers\FormatterPrice;

use Validator;
use API;
use DataTables;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


use App\Models\Promotion;

class SetupController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    public function SignInIntegrations()
    {
        return view('pages.setup.signinintegrations.index');
    }
    public function AppsIntegrations()
    {
        return view('pages.setup.appsintegrations.index');
    }
    public function AutomationSettings()
    {
        return view('pages.setup.automationsettings.index');
    }
    public function MarketConnect()
    {
        return view('pages.setup.marketconnect.index');
    }
    public function Notifications()
    {
        return view('pages.setup.notifications.index');
    }
    public function StorageSettings()
    {
        return view('pages.setup.storagesettings.index');
    }
    public function StaffManagement_adminusers()
    {
        return view('pages.setup.staffmanagement.administratorusers.index');
    }
    public function StaffManagement_adminusers_form(Request $request)
    {
        $getListRole = AdminRole::all()->pluck('name', 'id')->toArray();
        return view('pages.setup.staffmanagement.administratorusers.add', ['roleList' => $getListRole]);
    }
    public function StaffManagement_adminusers_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roleid' => 'required|numeric',
            'username' => 'required|string',
            'password' =>  'required|string',
            'password2' => 'required|string',
            'authmodule' => 'nullable',
            'authdata' => 'nullable',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string',
            'signature' => 'nullable',
            'ticketnotification' => 'nullable',
            'password_reset_key' => 'nullable',
            'password_reset_date' => 'nullable',
            'hidden_widgets' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput($request->except('password', 'password2'))->withErrors($validator)->with('message', 'Can\'t create new Administrator , please fill forms correctly and try again');
        }
        // route('admin.pages.setup.staffmanagement.administratorusers.addform')
        $uuid = "";
        if (!$uuid) {
            $uuid = Uuid::uuid4();
            $uuid = $uuid->toString();
        }

        if ($request->password) {
            $password = $request->password;
            $hashPass = new Password();
            $hashedPass = $hashPass->hash($password);
        }

        $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($request->password));

        $newAdmin = new Admin();
        $newAdmin->uuid = $uuid;
        $newAdmin->roleid = $request->roleid;
        $newAdmin->username = $request->username;
        $newAdmin->password = $hashedPass;
        $newAdmin->passwordhash = $passwordHash;
        $newAdmin->firstname = $request->firstname;
        $newAdmin->lastname = $request->lastname;
        $newAdmin->email = $request->email;
        $newAdmin->signature = $request->signature;
        $newAdmin->notes = $request->notes;
        $newAdmin->template = $request->template;
        $newAdmin->language = $request->language;
        $newAdmin->save();

        return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.index', ['success' => 'Congratulation, a new Administrator has been created!']);
    }
    public function StaffManagement_2fa()
    {
        return view('pages.setup.staffmanagement.2fa.index');
    }
    public function StaffManagement_apicredentialsOLD()
    {
        $apiroles = Role::where('guard_name', 'api')->get();
        $permissions = Permission::where('guard_name', 'api')->orderBy('name', 'ASC')->get();
        $admins = \App\Models\Admin::where('disabled', 0)->orderBy('firstname', 'ASC')->get();
        $adminroles = \App\Models\Admin::whereHas("roles", function($query) {
            $query->where('guard_name', 'api');
        })->get();

        return view('pages.setup.staffmanagement.manageapicredentials.index', compact('apiroles', 'permissions', 'admins', 'adminroles'));
    }
    public function StaffManagement_apicredentials(Request $request)
    {
        $aInt = new \App\Helpers\Admin("Manage API Credentials", false);
        $controller = new DeviceConfigurationController();
        $action = $request->input("action");
        $response = "";
        if ($action == "generate") {
            $response = $controller->generate($request);
        } else {
            if ($action == "delete") {
                $response = $controller->delete($request);
            } else {
                if ($action == "savefield") {
                    $response = $controller->updateFields($request);
                } else {
                    if ($action == "getDevices") {
                        $response = $controller->getDevices($request);
                    } else {
                        // $request = $request->withAttribute("aInt", $aInt);
                        $response = $controller->index($request);
                    }
                }
            }
        }
        return $response;
    }
    public function StaffManagement_apicredentials_generate(Request $request)
    {
        $adminid = $request->input('adminid');
        $roles = $request->input('roles');

        $admin = \App\Models\Admin::findOrFail($adminid);
        // $admin->roles()->where('guard_name', 'api')->detach();
        foreach ($admin->roles()->where('guard_name', 'api')->get() as $role) {
            $admin->removeRole(Role::findByName($role->name, 'api'));
        }
        foreach ($roles as $key => $rolename) {
            $role = Role::findByName($rolename, 'api');
            if ($role) {
                $admin->assignRole($role);
            }
        }

        return redirect()->back()->with(['success' => 'API Credential has been created']);
    }
    public function StaffManagement_apicredentials_remove(Request $request)
    {
        $id = $request->input('id');
        $admin = \App\Models\Admin::findOrFail($id);
        // $admin->roles()->where('guard_name', 'api')->detach();
        foreach ($admin->roles()->where('guard_name', 'api')->get() as $role) {
            $admin->removeRole(Role::findByName($role->name, 'api'));
        }

        return redirect()->back()->with(['success' => 'API Credential has been removed']);
    }
    public function StaffManagement_apicredentials_get(Request $request)
    {
        $id = $request->input('id');
        $admin = \App\Models\Admin::findOrFail($id);
        return ResponseAPI::Success([
            'id' => $admin->id,
            'roles' => $admin->roles->where('guard_name', 'api')->pluck('name'),
        ]);
    }
    public function StaffManagement_apicredentials_get_role(Request $request)
    {
        $id = $request->input('id');
        $role = Role::find($id);

        return ResponseAPI::Success([
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }
    public function StaffManagement_apicredentials_create_role(Request $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');
        $permissions = $request->input('permissions');

        if (Role::where(['name' => $name, 'guard_name' => 'api'])->first()) {
            return redirect()->back()->with(['error' => 'A role with that name already exists. A role name must be unique.']);
        }

        $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'api', 'description' => $description]);
        foreach ($permissions as $permission) {
            $permission = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        return redirect()->back()->with(['success' => 'Role has been created']);
    }
    public function StaffManagement_apicredentials_edit_role(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $description = $request->input('description');
        $permissions = $request->input('permissions') ?? [];

        if (Role::where(['name' => $name, 'guard_name' => 'api'])->where('id', '!=', $id)->first()) {
            return redirect()->back()->with(['error' => 'A role with that name already exists. A role name must be unique.']);
        }

        $role = Role::find($id);
        $role->name = $name;
        $role->description = $description;
        $role->save();
        $perms = [];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
            $perms[] = $permission;
        }
        $role->syncPermissions($perms);

        return redirect()->back()->with(['success' => 'Role has been udpated']);
    }
    public function StaffManagement_apicredentials_delete_role(Request $request)
    {
        $id = $request->input('id');
        $role = Role::find($id);
        $role->delete();

        return redirect()->back()->with(['success' => 'Role has been deleted']);
    }
    public function Payments_currencies()
    {
        $currenciesData = API::post('GetCurrencies');
        $arrCurrencies = $currenciesData['currencies']['currency'];
        $idCurr = [];
        $formatCurr = [];
        foreach ($arrCurrencies as $key => $currency) {
            $formatCurr[] = Format::Currency(123456, NULL, ['format' => $currency['format']]);
            $idCurr[] = $currency['format'];
        }
        $formattedCurr = array_combine($idCurr, $formatCurr);
        // dd($formattedCurr);
        return view('pages.setup.payments.currencies.index', ['formattedCurr' => $formattedCurr])->with('currenciesData', $currenciesData['currencies']['currency']);
    }
    public function Payments_currencies_create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'prefix' => 'required',
            'suffix' => 'nullable',
            'default' => 'nullable'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.payments.currencies.index')->withErrors($validator)->with('message', 'Can\'t create new currency, please try again.');
        }

        $newCurrency = new Currency();
        $newCurrency->code = $request->code;
        $newCurrency->prefix = $request->prefix;
        $newCurrency->format = (int)$request->format;
        $newCurrency->rate = $request->rate;
        $newCurrency->default = 0;
        $newCurrency->save();

        return redirect()->route('admin.pages.setup.payments.currencies.index')->with(['success' => 'A new currency has been created']);
    }
    public function Payments_currencies_edit($id)
    {
        $currenciesData = API::post('GetCurrencies');
        $arrCurrencies = $currenciesData['currencies']['currency'];
        $currencyDataById = Currency::findOrfail($id);
        foreach ($arrCurrencies as $key => $currency) {
            $formatCurr[] = Format::Currency(123456, NULL, ['format' => $currency['format']]);
            $idCurr[] = $currency['format'];
        }

        $formattedCurr = array_combine($idCurr, $formatCurr);
        return view('pages.setup.payments.currencies.edit', [
            'currency' => $currencyDataById,
            'formattedCurr' => $formattedCurr
        ]);
    }
    public function Payments_currencies_update(Request $request, $id)
    {
        $data = $request->all();
        $currencyData = Currency::findOrFail($id);
        $currencyData->update($data);
        return redirect()->route('admin.pages.setup.payments.currencies.edit', ['id' => $id])->with(['success' => 'The currency has been updated.']);
    }
    public function Payments_currencies_delete(Request $request, $id)
    {
        $data = $request->ajax();
        $currencyData = Currency::findOrFail($id);
        $currencyData->delete($data);
        return redirect()->route('admin.pages.setup.payments.currencies.index')->with(['success' => 'A currency code has been successfully deleted']);
    }
    public function Payments_paymentgateways()
    {
        // $dataPaymentMethods = API::post('GetPaymentMethods');
        $numgateways = 0;
        $GatewayValues = $GatewayConfig = $ActiveGateways = array();
        $DisabledGateways = $AllGateways = $noConversion = array();
        $numgateways = 0;
        $includedmodules = array();
        $noConfigFound = array();
        $gatewayInterface = new \App\Module\Gateway();
        $AllGateways = $gatewayInterface->getList();
        $AllGatewaysData = [];
        foreach ($AllGateways as $gateway) {
            $AllGatewaysData[] = $gateway->getLowerName();
        }
        $ActiveGateways = $gatewayInterface->getActiveGateways();
        $ActiveGatewaysData = [];
        foreach ($ActiveGateways as $gateway) {
            $ActiveGatewaysData[] = $gateway->getLowerName();
        }
        $DisabledGateways = array_filter($AllGatewaysData, function ($gateway) use($ActiveGatewaysData) {
            return !in_array($gateway, $ActiveGatewaysData);
        });
        foreach ($AllGatewaysData as $gatewayModuleName) {
            if (!in_array($gatewayModuleName, $includedmodules)) {
                $gatewayInterface->load($gatewayModuleName);
                $includedmodules[] = $gatewayModuleName;
                try {
                    $GatewayConfig[$gatewayModuleName] = $gatewayInterface->getConfiguration();
                } catch (\Exception $e) {
                    $noConfigFound[] = $gatewayModuleName;
                    continue;
                }
                if (in_array($gatewayModuleName, $ActiveGatewaysData)) {
                    $noConversion[$gatewayModuleName] = $gatewayInterface->getMetaDataValue("noCurrencyConversion");
                    $GatewayValues[$gatewayModuleName] = $gatewayInterface->loadSettings();
                    if ($gatewayInterface->functionExists("admin_area_actions")) {
                        $additionalButtons = $gatewayInterface->call("admin_area_actions");
                        $additionalConfig = array();
                        $buttons = array();
                        foreach ($additionalButtons as $data) {
                            if (!is_array($data)) {
                                throw new App\Exceptions\Module\NotServicable("Invalid Function Return");
                            }
                            $methodName = $data["actionName"];
                            $buttonName = $data["label"];
                            $classes = array("btn", "btn-default", "open-modal");
                            $disabled = "";
                            $modalSize = "";
                            if (!empty($data["modalSize"])) {
                                $modalSize = "data-modal-size=\"" . $data["modalSize"] . "\"";
                            }
                            if (!empty($data["disabled"])) {
                                $disabled = " disabled=\"disabled";
                                $classes[] = "disabled";
                            }
                            $classes = implode(" ", $classes);
                            // $routePath = routePath("admin-setup-payments-gateways-action", $gatewayModuleName, $methodName);
                            $routePath = "harusnya ke mana";
                            $button = "<a href=\"" . $routePath . "\" class=\"" . $classes . "\" data-modal-title=\"" . $buttonName . "\"" . $modalSize . ">\n    " . $buttonName . "\n</a>";
                            $buttons[] = $button;
                        }
                        $additionalConfig["additional_available_actions"] = array("FriendlyName" => "Available Actions", "Type" => "html", "Description" => implode("", $buttons));
                        $GatewayConfig[$gatewayModuleName] += $additionalConfig;
                    }
                }
            }
        }
        $lastorder = count($ActiveGateways);
        $result3 = \App\Models\Paymentgateway::where(array("setting" => "name"))->whereIn('gateway', $ActiveGatewaysData)->orderBy('order', 'ASC')->get();
        $result3->transform(function($pg) {
            $pg->gateway = Str::lower($pg->gateway);
            return $pg;
        });
        $result3 = $result3->toArray();
        $result = \App\Models\Currency::orderBy('code', 'ASC')->get();

        $numgateways = count($ActiveGateways);
        return view('pages.setup.payments.paymentgateways.index', [
            'AllGateways' => $AllGatewaysData,
            'GatewayConfig' => $GatewayConfig,
            'ActiveGateways' => $ActiveGatewaysData,
            'result3' => $result3,
            'GatewayValues' => $GatewayValues,
            'result' => $result, // currency
            'noConversion' => $noConversion,
            'numgateways' => $numgateways,
            'lastorder' => $lastorder,
        ]);
    }
    public function Payments_paymentgateways_activate(Request $request)
    {
        $gateway = $request->input('gateway');

        DB::beginTransaction();
        try {
            $gatewayInterface = new \App\Module\Gateway();
            $gatewayInterface->load($gateway);
            if ($gatewayInterface->getMetaDataValue("apiOnboarding")) {
                echo $gatewayInterface->getOnBoardingRedirectHtml();
                throw new \App\Exceptions\ProgramExit();
            }
            \App\Models\Paymentgateway::where(array("gateway" => $gateway))->delete();
            // $lastorder++;
            $gatewayInterface->activate();
            try {
                $gatewayInterface->loadSettings();
                $gatewayInterface->call("post_activation");
            } catch (\Exception $e) {
            }

            DB::commit();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "activated={$gateway}#{$gateway}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "error={$gateway}#{$gateway}")->with(['error' => $e->getMessage()]);
        }
    }
    public function Payments_paymentgateways_deactivate(Request $request)
    {
        // dd($request->all());
        $GatewayValues = $GatewayConfig = $ActiveGateways = array();
        $DisabledGateways = $AllGateways = $noConversion = array();
        $numgateways = 0;
        $includedmodules = array();
        $noConfigFound = array();
        $gatewayInterface = new \App\Module\Gateway();
        $AllGateways = $gatewayInterface->getList();
        $AllGatewaysData = [];
        foreach ($AllGateways as $gateway) {
            $AllGatewaysData[] = $gateway->getLowerName();
        }
        $ActiveGateways = $gatewayInterface->getActiveGateways();
        $ActiveGatewaysData = [];
        foreach ($ActiveGateways as $gateway) {
            $ActiveGatewaysData[] = $gateway->getLowerName();
        }
        $DisabledGateways = array_filter($AllGatewaysData, function ($gateway) use($ActiveGatewaysData) {
            return !in_array($gateway, $ActiveGatewaysData);
        });
        foreach ($AllGatewaysData as $gatewayModuleName) {
            if (!in_array($gatewayModuleName, $includedmodules)) {
                $gatewayInterface->load($gatewayModuleName);
                $includedmodules[] = $gatewayModuleName;
                try {
                    $GatewayConfig[$gatewayModuleName] = $gatewayInterface->getConfiguration();
                } catch (\Exception $e) {
                    $noConfigFound[] = $gatewayModuleName;
                    continue;
                }
                if (in_array($gatewayModuleName, $ActiveGatewaysData)) {
                    $noConversion[$gatewayModuleName] = $gatewayInterface->getMetaDataValue("noCurrencyConversion");
                    $GatewayValues[$gatewayModuleName] = $gatewayInterface->loadSettings();
                    if ($gatewayInterface->functionExists("admin_area_actions")) {
                        $additionalButtons = $gatewayInterface->call("admin_area_actions");
                        $additionalConfig = array();
                        $buttons = array();
                        foreach ($additionalButtons as $data) {
                            if (!is_array($data)) {
                                throw new App\Exceptions\Module\NotServicable("Invalid Function Return");
                            }
                            $methodName = $data["actionName"];
                            $buttonName = $data["label"];
                            $classes = array("btn", "btn-default", "open-modal");
                            $disabled = "";
                            $modalSize = "";
                            if (!empty($data["modalSize"])) {
                                $modalSize = "data-modal-size=\"" . $data["modalSize"] . "\"";
                            }
                            if (!empty($data["disabled"])) {
                                $disabled = " disabled=\"disabled";
                                $classes[] = "disabled";
                            }
                            $classes = implode(" ", $classes);
                            // $routePath = routePath("admin-setup-payments-gateways-action", $gatewayModuleName, $methodName);
                            $routePath = "harusnya ke mana";
                            $button = "<a href=\"" . $routePath . "\" class=\"" . $classes . "\" data-modal-title=\"" . $buttonName . "\"" . $modalSize . ">\n    " . $buttonName . "\n</a>";
                            $buttons[] = $button;
                        }
                        $additionalConfig["additional_available_actions"] = array("FriendlyName" => "Available Actions", "Type" => "html", "Description" => implode("", $buttons));
                        $GatewayConfig[$gatewayModuleName] += $additionalConfig;
                    }
                }
            }
        }
        $gateway = $request->input('gateway');
        $friendlygateway = $request->input('friendlygateway');
        $newgateway = $request->input('newgateway');
        $gatewayInterface->load($gateway);

        DB::beginTransaction();
        try {
            $gatewayInterface->deactivate(array("newGateway" => $newgateway, "newGatewayName" => $GatewayConfig[$newgateway]["FriendlyName"]["Value"]));
            DB::commit();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "deactivated=true")->with(['success' => "The selected payment gateway has now been deactivated"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "deactivated=false")->with(['error' => $e->getMessage()]);
        }
    }
    public function Payments_paymentgateways_save(Request $request)
    {
        // dd($request->all());
        $GatewayValues = $GatewayConfig = $ActiveGateways = array();
        $DisabledGateways = $AllGateways = $noConversion = array();
        $numgateways = 0;
        $includedmodules = array();
        $noConfigFound = array();
        $gatewayInterface = new \App\Module\Gateway();
        $AllGateways = $gatewayInterface->getList();
        $AllGatewaysData = [];
        foreach ($AllGateways as $gateway) {
            $AllGatewaysData[] = $gateway->getLowerName();
        }
        $ActiveGateways = $gatewayInterface->getActiveGateways();
        $ActiveGatewaysData = [];
        foreach ($ActiveGateways as $gateway) {
            $ActiveGatewaysData[] = $gateway->getLowerName();
        }
        $DisabledGateways = array_filter($AllGatewaysData, function ($gateway) use($ActiveGatewaysData) {
            return !in_array($gateway, $ActiveGatewaysData);
        });
        foreach ($AllGatewaysData as $gatewayModuleName) {
            if (!in_array($gatewayModuleName, $includedmodules)) {
                $gatewayInterface->load($gatewayModuleName);
                $includedmodules[] = $gatewayModuleName;
                try {
                    $GatewayConfig[$gatewayModuleName] = $gatewayInterface->getConfiguration();
                } catch (\Exception $e) {
                    $noConfigFound[] = $gatewayModuleName;
                    continue;
                }
                if (in_array($gatewayModuleName, $ActiveGatewaysData)) {
                    $noConversion[$gatewayModuleName] = $gatewayInterface->getMetaDataValue("noCurrencyConversion");
                    $GatewayValues[$gatewayModuleName] = $gatewayInterface->loadSettings();
                    if ($gatewayInterface->functionExists("admin_area_actions")) {
                        $additionalButtons = $gatewayInterface->call("admin_area_actions");
                        $additionalConfig = array();
                        $buttons = array();
                        foreach ($additionalButtons as $data) {
                            if (!is_array($data)) {
                                throw new App\Exceptions\Module\NotServicable("Invalid Function Return");
                            }
                            $methodName = $data["actionName"];
                            $buttonName = $data["label"];
                            $classes = array("btn", "btn-default", "open-modal");
                            $disabled = "";
                            $modalSize = "";
                            if (!empty($data["modalSize"])) {
                                $modalSize = "data-modal-size=\"" . $data["modalSize"] . "\"";
                            }
                            if (!empty($data["disabled"])) {
                                $disabled = " disabled=\"disabled";
                                $classes[] = "disabled";
                            }
                            $classes = implode(" ", $classes);
                            // $routePath = routePath("admin-setup-payments-gateways-action", $gatewayModuleName, $methodName);
                            $routePath = "harusnya ke mana";
                            $button = "<a href=\"" . $routePath . "\" class=\"" . $classes . "\" data-modal-title=\"" . $buttonName . "\"" . $modalSize . ">\n    " . $buttonName . "\n</a>";
                            $buttons[] = $button;
                        }
                        $additionalConfig["additional_available_actions"] = array("FriendlyName" => "Available Actions", "Type" => "html", "Description" => implode("", $buttons));
                        $GatewayConfig[$gatewayModuleName] += $additionalConfig;
                    }
                }
            }
        }
        $module = $request->input('module');
        $field = $request->input("field");
        $GatewayConfig[$module]["visible"] = array("Type" => "yesno");
        $GatewayConfig[$module]["name"] = array("Type" => "text");
        $GatewayConfig[$module]["convertto"] = array("Type" => "text");
        $gateway = new \App\Module\Gateway();
        $gateway->load($module);
        $params = array();

        DB::beginTransaction();
        try {
            foreach ($field as $name => $value) {
                $params[$name] = \App\Helpers\Sanitize::decode(trim($value));
            }
            $gateway->call("config_validate", $params);
            $existingParams = $gatewayInterface->getParams();
            foreach ($GatewayConfig[$module] as $confname => $values) {
                if ($values["Type"] != "System") {
                    if (!isset($field[$confname])) {
                        $field[$confname] = "";
                    }
                    if (!isset($GatewayValues[$module][$confname])) {
                        $GatewayValues[$module][$confname] = "";
                    }
                    $valueToSave = \App\Helpers\Sanitize::decode(trim($field[$confname]));
                    if ($values["Type"] == "password") {
                        $updatedPassword = \App\Helpers\AdminFunctions::interpretMaskedPasswordChangeForStorage($valueToSave, $GatewayValues[$module][$confname]);
                        if ($updatedPassword === false) {
                            $valueToSave = $GatewayValues[$module][$confname];
                        }
                    }
                    DB::table("tblpaymentgateways")->updateOrInsert(array("gateway" => $module, "setting" => $confname), array("value" => $valueToSave));
                }
            }
            $gateway->loadSettings();
            $gateway->call("config_post_save", array("existing" => $existingParams));
            $gatewayName = $GatewayConfig[$module]["FriendlyName"]["Value"];
            \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Configuration Modified: '" . $gatewayName . "'");

            DB::commit();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "updated={$module}#{$module}");
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage();
            if (!$error) {
                $error = "An unknown error occurred with the configuration check.";
            }
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "error={$module}#{$module}")->with(['error' => $e->getMessage()]);
        }
    }
    public function Payments_paymentgateways_moveup(Request $request)
    {
        $order = $request->input('order');
        DB::beginTransaction();
        try {
            $GatewayValues = $GatewayConfig = $ActiveGateways = array();
            $DisabledGateways = $AllGateways = $noConversion = array();
            $numgateways = 0;
            $includedmodules = array();
            $noConfigFound = array();
            $gatewayInterface = new \App\Module\Gateway();
            $AllGateways = $gatewayInterface->getList();
            $AllGatewaysData = [];
            foreach ($AllGateways as $gateway) {
                $AllGatewaysData[] = $gateway->getLowerName();
            }
            $ActiveGateways = $gatewayInterface->getActiveGateways();
            $ActiveGatewaysData = [];
            foreach ($ActiveGateways as $gateway) {
                $ActiveGatewaysData[] = $gateway->getLowerName();
            }
            $DisabledGateways = array_filter($AllGatewaysData, function ($gateway) use($ActiveGatewaysData) {
                return !in_array($gateway, $ActiveGatewaysData);
            });
            foreach ($AllGatewaysData as $gatewayModuleName) {
                if (!in_array($gatewayModuleName, $includedmodules)) {
                    $gatewayInterface->load($gatewayModuleName);
                    $includedmodules[] = $gatewayModuleName;
                    try {
                        $GatewayConfig[$gatewayModuleName] = $gatewayInterface->getConfiguration();
                    } catch (\Exception $e) {
                        $noConfigFound[] = $gatewayModuleName;
                        continue;
                    }
                    if (in_array($gatewayModuleName, $ActiveGatewaysData)) {
                        $noConversion[$gatewayModuleName] = $gatewayInterface->getMetaDataValue("noCurrencyConversion");
                        $GatewayValues[$gatewayModuleName] = $gatewayInterface->loadSettings();
                        if ($gatewayInterface->functionExists("admin_area_actions")) {
                            $additionalButtons = $gatewayInterface->call("admin_area_actions");
                            $additionalConfig = array();
                            $buttons = array();
                            foreach ($additionalButtons as $data) {
                                if (!is_array($data)) {
                                    throw new App\Exceptions\Module\NotServicable("Invalid Function Return");
                                }
                                $methodName = $data["actionName"];
                                $buttonName = $data["label"];
                                $classes = array("btn", "btn-default", "open-modal");
                                $disabled = "";
                                $modalSize = "";
                                if (!empty($data["modalSize"])) {
                                    $modalSize = "data-modal-size=\"" . $data["modalSize"] . "\"";
                                }
                                if (!empty($data["disabled"])) {
                                    $disabled = " disabled=\"disabled";
                                    $classes[] = "disabled";
                                }
                                $classes = implode(" ", $classes);
                                // $routePath = routePath("admin-setup-payments-gateways-action", $gatewayModuleName, $methodName);
                                $routePath = "harusnya ke mana";
                                $button = "<a href=\"" . $routePath . "\" class=\"" . $classes . "\" data-modal-title=\"" . $buttonName . "\"" . $modalSize . ">\n    " . $buttonName . "\n</a>";
                                $buttons[] = $button;
                            }
                            $additionalConfig["additional_available_actions"] = array("FriendlyName" => "Available Actions", "Type" => "html", "Description" => implode("", $buttons));
                            $GatewayConfig[$gatewayModuleName] += $additionalConfig;
                        }
                    }
                }
            }
            $result = \App\Models\Paymentgateway::where('order', $order);
            $data = $result;
            $gateway = $data->value("gateway") ?? '';
            $order1 = $order - 1;
            \App\Models\Paymentgateway::where(array("order" => $order1))->update(array("order" => $order));
            \App\Models\Paymentgateway::where(array("gateway" => $gateway))->update(array("order" => $order1));
            \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Sorting Changed: Moved Up - '" . $GatewayConfig[$gateway]["FriendlyName"]["Value"] . "'");

            DB::commit();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "sortchange=1")->with(['success' => 'Payment gateway sorting has been updated']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index')->with(['error' => $e->getMessage()]);
        }
    }
    public function Payments_paymentgateways_movedown(Request $request)
    {
        $order = $request->input('order');
        DB::beginTransaction();
        try {
            $GatewayValues = $GatewayConfig = $ActiveGateways = array();
            $DisabledGateways = $AllGateways = $noConversion = array();
            $numgateways = 0;
            $includedmodules = array();
            $noConfigFound = array();
            $gatewayInterface = new \App\Module\Gateway();
            $AllGateways = $gatewayInterface->getList();
            $AllGatewaysData = [];
            foreach ($AllGateways as $gateway) {
                $AllGatewaysData[] = $gateway->getLowerName();
            }
            $ActiveGateways = $gatewayInterface->getActiveGateways();
            $ActiveGatewaysData = [];
            foreach ($ActiveGateways as $gateway) {
                $ActiveGatewaysData[] = $gateway->getLowerName();
            }
            $DisabledGateways = array_filter($AllGatewaysData, function ($gateway) use($ActiveGatewaysData) {
                return !in_array($gateway, $ActiveGatewaysData);
            });
            foreach ($AllGatewaysData as $gatewayModuleName) {
                if (!in_array($gatewayModuleName, $includedmodules)) {
                    $gatewayInterface->load($gatewayModuleName);
                    $includedmodules[] = $gatewayModuleName;
                    try {
                        $GatewayConfig[$gatewayModuleName] = $gatewayInterface->getConfiguration();
                    } catch (\Exception $e) {
                        $noConfigFound[] = $gatewayModuleName;
                        continue;
                    }
                    if (in_array($gatewayModuleName, $ActiveGatewaysData)) {
                        $noConversion[$gatewayModuleName] = $gatewayInterface->getMetaDataValue("noCurrencyConversion");
                        $GatewayValues[$gatewayModuleName] = $gatewayInterface->loadSettings();
                        if ($gatewayInterface->functionExists("admin_area_actions")) {
                            $additionalButtons = $gatewayInterface->call("admin_area_actions");
                            $additionalConfig = array();
                            $buttons = array();
                            foreach ($additionalButtons as $data) {
                                if (!is_array($data)) {
                                    throw new App\Exceptions\Module\NotServicable("Invalid Function Return");
                                }
                                $methodName = $data["actionName"];
                                $buttonName = $data["label"];
                                $classes = array("btn", "btn-default", "open-modal");
                                $disabled = "";
                                $modalSize = "";
                                if (!empty($data["modalSize"])) {
                                    $modalSize = "data-modal-size=\"" . $data["modalSize"] . "\"";
                                }
                                if (!empty($data["disabled"])) {
                                    $disabled = " disabled=\"disabled";
                                    $classes[] = "disabled";
                                }
                                $classes = implode(" ", $classes);
                                // $routePath = routePath("admin-setup-payments-gateways-action", $gatewayModuleName, $methodName);
                                $routePath = "harusnya ke mana";
                                $button = "<a href=\"" . $routePath . "\" class=\"" . $classes . "\" data-modal-title=\"" . $buttonName . "\"" . $modalSize . ">\n    " . $buttonName . "\n</a>";
                                $buttons[] = $button;
                            }
                            $additionalConfig["additional_available_actions"] = array("FriendlyName" => "Available Actions", "Type" => "html", "Description" => implode("", $buttons));
                            $GatewayConfig[$gatewayModuleName] += $additionalConfig;
                        }
                    }
                }
            }
            $result = \App\Models\Paymentgateway::where('order', $order);
            $data = $result;
            $gateway = $data->value("gateway") ?? '';
            $order1 = $order + 1;
            \App\Models\Paymentgateway::where(array("order" => $order1))->update(array("order" => $order));
            \App\Models\Paymentgateway::where(array("gateway" => $gateway))->update(array("order" => $order1));
            \App\Helpers\AdminFunctions::logAdminActivity("Gateway Module Sorting Changed: Moved Down - '" . $GatewayConfig[$gateway]["FriendlyName"]["Value"] . "'");

            DB::commit();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index', "sortchange=1")->with(['success' => 'Payment gateway sorting has been updated']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pages.setup.payments.paymentgateways.index')->with(['error' => $e->getMessage()]);
        }
    }
    public function Payments_taxconfiguration()
    {
        return view('pages.setup.payments.taxconfiguration.index');
    }
    public function Promotions()
    {
        $data=\App\Models\Promotion::all();
        $param=['data' => $data];
        return view('pages.setup.payments.promotions.index',$param);
    }

    public function PromotionsData(Request $request){
        $data=\App\Models\Promotion::select('*');
        if($request->filter !='all' && $request->filter !='0' ){
            if($request->filter == 'expired'){
                $data->where(function($query) {
                                $query->where('maxuses','>','0')
                                ->where('uses', '>=', 'maxuses');
                            })->orWhere(function($query) {
                                $query->where('expirationdate','!=','0000-00-00')
                                ->where('expirationdate', '<',  date("Ymd"));
                            });
            }else{
                $data->where(function($query) {
                        $query->where('maxuses','<=','0')
                        ->orWhere('uses', '<', 'maxuses');
                    })->where(function($query) {
                        $query->where('expirationdate','=','0000-00-00')
                        ->orWhere('expirationdate', '>=',  date("Ymd"));
                    });
                   // ->orWhere('expirationdate','>=', date("Ymd"));
             }
        }
        $data->orderBy('id','DESC');
        //dd($data->toSql());

        return Datatables::of($data)
            ->editColumn('startdate', function($data) {
                return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
            })
            ->editColumn('expirationdate', function($data) {
                return  Carbon::parse($data->expirationdate)->isoFormat(Cfg::get('DateFormat'));
            })
          ->orderColumn('id',false)
            ->toJson();

    }

    public function PromotionsDestroy(Request $request){
        // /dd($request->all());
        $id=(int)$request->id;
        $promotion=\App\Models\Promotion::find($id);
        LogActivity::save("Promotion Deleted: '" . $promotion->code . "' - Promotion ID: " . $id);
        $promotion->delete();
        return back()->with('success', 'Deleted Deleted successfully');
    }

    public function Gencode(Request $request){
        $numbers = "0123456789";
        $uppercase = "ABCDEFGHIJKLMNOPQRSTUVYWXYZ";
        $str = "";
        $seeds_count = strlen($numbers) - 1;
        for ($i = 0; $i < 4; $i++) {
            $str .= $numbers[rand(0, $seeds_count)];
        }
        $seeds_count = strlen($uppercase) - 1;
        for ($i = 0; $i < 8; $i++) {
            $str .= $uppercase[rand(0, $seeds_count)];
        }
        $password = "";
        for ($i = 0; $i < 10; $i++) {
            $randomnum = rand(0, strlen($str) - 1);
            $password .= $str[$randomnum];
            $str = substr($str, 0, $randomnum) . substr($str, $randomnum + 1);
        }
        return $password;
    }




    public function Promotions_create()
    {
        //\App\Models\Productconfigoption::select('id','name','optionname')->orderBy('optionname','ASC')->
        $prefix=\Database::prefix();
        $config=DB::table("{$prefix}productconfigoptions as productconfigoptions")
                ->join("{$prefix}productconfiggroups as productconfiggroups","productconfigoptions.gid","=","productconfiggroups.id")
                ->select('productconfiggroups.id','productconfiggroups.name','productconfigoptions.optionname')
                ->orderBy('productconfigoptions.optionname',"ASC")
                ->get();
        //dd($config);
        $params=[
                    'product'   => \App\Helpers\Product::getProducts(),
                    'Addons'    => \App\Models\Addon::select('id','name','description')->orderBy('name','ASC')->get(),
                    'extension' => \App\Models\Domainpricing::select('id','extension')->distinct('extension')->orderBy('extension','ASC')->get(),
                    'config'    =>  $config
                ];
        //dd($params);
        return view('pages.setup.payments.promotions.add',$params);
    }
    public function PromotionsStore(Request $request){
        //dd($request->all());
        //$id =$request->id;
        $code = trim($request->code);
        $type = $request->type;
        $recurring  = $request->recurring ?? 0;
        $pvalue  = $request->pvalue ?? 0 ;
        $requiresexisting  = $request->requiresexisting ?? 0;
        $startdate  = $request->startdate;
        $expirationdate  = $request->expirationdate ?? "0000-00-00";
        $maxuses  = $request->maxuses ?? 0;
        $lifetimepromo  = $request->lifetimepromo ?? 0;
        $applyonce  = (int) $request->applyonce;
        $newsignups  = $request->newsignups ?? 0;
        $existingclient  = $request->existingclient ??0;
        $onceperclient  = $request->onceperclient ?? 0;
        $recurfor  = $request->recurfor;
        $cycles  = $request->cycles;
        $appliesto  = $request->appliesto;
        $requires  = $request->requires;
        $upgrades  = $request->upgrades ??0;
        $upgradevalue  = $request->upgradevalue;
        $upgradetype  = $request->upgradetype;
        $upgradediscounttype  = $request->upgradediscounttype;
        $configoptionupgrades  = $request->configoptionupgrades;
        //$notes  = $request->notes;
        $startdate =!$startdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($startdate);
        $expirationdate =!$expirationdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($expirationdate);
        //dd($expirationdate);
        $cycles = is_array($cycles) ? implode(",", $cycles) : "";
        //dd($cycles);
        $appliesto = is_array($appliesto) ? implode(",", $appliesto) : "";
        $requires = is_array($requires) ? implode(",", $requires) : "";
        $upgradeconfig= (new \App\Helpers\Pwd())->safe_serialize([
                                 "value" => \App\Helpers\Functions::format_as_currency($upgradevalue),
                                 "type" => $upgradetype,
                                 "discounttype" => $upgradediscounttype,
                                 "configoptions" => $configoptionupgrades
                        ]);
        $notes=$request->notes ?? "";
        //    /$duplicates=\App\Models\Promotion::where('code',$code)->first();

        $promo=new \App\Models\Promotion();
        $promo->code =$code;
        $promo->type =$type;
        $promo->recurring =$recurring;
        $promo->value =$pvalue;
        $promo->cycles =$cycles;
        $promo->appliesto =$appliesto;
        $promo->requires =$requires;
        $promo->requiresexisting =$requiresexisting;
        $promo->startdate =$startdate;
        $promo->expirationdate =$expirationdate;
        $promo->maxuses =$maxuses;
        $promo->lifetimepromo =$lifetimepromo;
        $promo->applyonce =$applyonce;
        $promo->newsignups =$newsignups;
        $promo->existingclient =$existingclient;
        $promo->onceperclient =$onceperclient;
        $promo->recurfor =$recurfor;
        $promo->upgrades =$upgrades;
        $promo->upgradeconfig =$upgradeconfig;
        $promo->notes =$notes;
        $promo->save();
        $newid=$promo->id;
        LogActivity::Save("Promotion Created: '" . $code . "' - Promotion ID: " . $newid);
        return back()->with('success', 'Promotion Created saved successfully');
    }

    public function Promotions_Edit($id){
        $prefix=\Database::prefix();
        $config=DB::table("{$prefix}productconfigoptions as productconfigoptions")
                ->join("{$prefix}productconfiggroups as productconfiggroups","productconfigoptions.gid","=","productconfiggroups.id")
                ->select('productconfiggroups.id','productconfiggroups.name','productconfigoptions.optionname')
                ->orderBy('productconfigoptions.optionname',"ASC")
                ->get();
        //dd($config);
        $data=\App\Models\Promotion::find($id);

        $params=[
                    'product'   => \App\Helpers\Product::getProducts(),
                    'Addons'    => \App\Models\Addon::select('id','name','description')->orderBy('name','ASC')->get(),
                    'extension' => \App\Models\Domainpricing::select('id','extension')->distinct('extension')->orderBy('extension','ASC')->get(),
                    'config'    => $config,
                    'data'      => $data,
                    'upgradeconfig' => (new \App\Helpers\Client())->safe_unserialize($data->upgradeconfig)
                ];
        //dd($params);
        return view('pages.setup.payments.promotions.edit',$params);
    }

    public function PromotionsUpdate(Request $request){
        $id=(int)$request->id;
        $code = trim($request->code);
        $type = $request->type;
        $recurring  = $request->recurring;
        $pvalue  = $request->pvalue ?? 0 ;
        $requiresexisting  = $request->requiresexisting;
        $startdate  = $request->startdate;
        $expirationdate  = $request->expirationdate;
        $maxuses  = $request->maxuses;
        $lifetimepromo  = $request->lifetimepromo ?? 0;
        $applyonce  = (int) $request->applyonce;
        $newsignups  = $request->newsignups ?? 0;
        $existingclient  = $request->existingclient ??0;
        $onceperclient  = $request->onceperclient;
        $recurfor  = $request->recurfor;
        $cycles  = $request->cycles;
        $appliesto  = $request->appliesto;
        $requires  = $request->requires;
        $upgrades  = $request->upgrades ??0;
        $upgradevalue  = $request->upgradevalue;
        $upgradetype  = $request->upgradetype;
        $upgradediscounttype  = $request->upgradediscounttype;
        $configoptionupgrades  = $request->configoptionupgrades;
        //$notes  = $request->notes;
        //dd($startdate);
       // $startdate =!$startdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($startdate);

        //$expirationdate =!$expirationdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($expirationdate);
        //dd($expirationdate);
        $cycles = is_array($cycles) ? implode(",", $cycles) : "";
        //dd($cycles);
        $appliesto = is_array($appliesto) ? implode(",", $appliesto) : "";
        $requires = is_array($requires) ? implode(",", $requires) : "";
        $upgradeconfig= (new \App\Helpers\Pwd())->safe_serialize([
                                 "value" => \App\Helpers\Functions::format_as_currency($upgradevalue),
                                 "type" => $upgradetype,
                                 "discounttype" => $upgradediscounttype,
                                 "configoptions" => $configoptionupgrades
                        ]);
        $notes=$request->notes;
        $promotion = \App\Models\Promotion::find($id);
        if ($code != $promotion->code) {
            LogActivity::Save("Promotion Modified: Code Modified: '" . $promotion->code . "' to '" . $code . "' - Promotion ID: " . $newid);
        }
        $changes = array();
        if ($type != $promotion->type) {
            $changes[] = "Type Changed: '" . $promotion->type . "' to '" . $type . "'";
        }
        if ($recurring != $promotion->recurring) {
            if ($recurring) {
                $changes[] = "Recurring Enabled";
            } else {
                $changes[] = "Recurring Disabled";
            }
        }
        if ($recurfor != $promotion->recurfor) {
            $changes[] = "Recur For Modified: '" . $promotion->recurfor . "' to '" . $recurfor . "'";
        }
        if ($pvalue != $promotion->value) {
            $changes[] = "Value Modified: '" . $promotion->value . "' to '" . $pvalue . "'";
        }
        if ($appliesto != $promotion->appliesto) {
            $changes[] = "Applies To Modified";
        }
        if ($requires != $promotion->requires) {
            $changes[] = "Requires Modified";
        }
        if ($requiresexisting != $promotion->requiresexisting) {
            if ($requiresexisting) {
                $changes[] = "Requires Existing Product Allowed In Account Enabled";
            } else {
                $changes[] = "Requires Existing Product Allowed In Account Disabled";
            }
        }
        if ($cycles != $promotion->cycles) {
            $changes[] = "Cycles Modified";
        }
        if ($startdate != $promotion->startdate) {
            $changes[] = "Start Date Modified: '" . $promotion->startdate . "' to '" . $startdate . "'";
        }
        if ($expirationdate != $promotion->expirationdate) {
            $changes[] = "Expiry Date Modified: '" . $promotion->expirationdate . "' to '" . $expirationdate . "'";
        }
        if ($maxuses != $promotion->maxuses) {
            $changes[] = "Max Uses Modified: '" . $promotion->maxuses . "' to '" . $maxuses . "'";
        }
        if ($lifetimepromo != $promotion->lifetimepromo) {
            if ($lifetimepromo) {
                $changes[] = "Lifetime Promotion Enabled";
            } else {
                $changes[] = "Lifetime Promotion Disabled";
            }
        }
        if ($applyonce != $promotion->applyonce) {
            if ($applyonce) {
                $changes[] = "Apply Once Enabled";
            } else {
                $changes[] = "Apply Once Disabled";
            }
        }
        if ($newsignups != $promotion->newsignups) {
            if ($newsignups) {
                $changes[] = "New Signups Only Enabled";
            } else {
                $changes[] = "New Signups Only Disabled";
            }
        }
        if ($onceperclient != $promotion->onceperclient) {
            if ($onceperclient) {
                $changes[] = "Once Per Client Enabled";
            } else {
                $changes[] = "Once Per Client Disabled";
            }
        }
        if ($existingclient != $promotion->existingclient) {
            if ($existingclient) {
                $changes[] = "Existing Client Only Enabled";
            } else {
                $changes[] = "Existing Client Only Disabled";
            }
        }
        if ($upgrades != $promotion->upgrades) {
            if ($upgrades) {
                $changes[] = "Product Upgrade Promotion Enabled";
            } else {
                $changes[] = "Product Upgrade Promotion Disabled";
            }
        }
        if ($upgradeconfig != $promotion->upgradeconfig) {
            $changes[] = "Upgrade Promotion Configuration Modified";
        }
        if ($notes != $promotion->notes) {
            $changes[] = "Admin Notes Modified";
        }





        $promo=\App\Models\Promotion::find($id);
        $promo->code =$code;
        $promo->type =$type;
        $promo->recurring =$recurring;
        $promo->value =$pvalue;
        $promo->cycles =$cycles;
        $promo->appliesto =$appliesto;
        $promo->requires =$requires;
        $promo->requiresexisting =$requiresexisting;
        $promo->startdate =$startdate;
        $promo->expirationdate =$expirationdate;
        $promo->maxuses =$maxuses;
        $promo->lifetimepromo =$lifetimepromo;
        $promo->applyonce =$applyonce;
        $promo->newsignups =$newsignups;
        $promo->existingclient =$existingclient;
        $promo->onceperclient =$onceperclient;
        $promo->recurfor =$recurfor;
        $promo->upgrades =$upgrades;
        $promo->upgradeconfig =$upgradeconfig;
        $promo->notes =$notes;
        $promo->save();
        //$newid=$promo->id;
        if ($changes) {
            LogActivity::Save("Promotion Modified: '" . $code . "' - Changes: " . implode(". ", $changes) . " - Promotion ID: " . $id);
        }
        return back()->with('success', 'successfully Update promotion ');
    }

    public function Duplicate($id){
        $id=(int)$id;
        $prefix=\Database::prefix();
        $config=DB::table("{$prefix}productconfigoptions as productconfigoptions")
                ->join("{$prefix}productconfiggroups as productconfiggroups","productconfigoptions.gid","=","productconfiggroups.id")
                ->select('productconfiggroups.id','productconfiggroups.name','productconfigoptions.optionname')
                ->orderBy('productconfigoptions.optionname',"ASC")
                ->get();
        //dd($config);
        $data=\App\Models\Promotion::find($id);

        $params=[
                    'product'   => \App\Helpers\Product::getProducts(),
                    'Addons'    => \App\Models\Addon::select('id','name','description')->orderBy('name','ASC')->get(),
                    'extension' => \App\Models\Domainpricing::select('id','extension')->distinct('extension')->orderBy('extension','ASC')->get(),
                    'config'    => $config,
                    'data'      => $data,
                    'upgradeconfig' => (new \App\Helpers\Client())->safe_unserialize($data->upgradeconfig)
                ];
        //dd($params);
        return view('pages.setup.payments.promotions.duplicate',$params);

    }

    public function DuplicateStore(Request $request){
        //$id =$request->id;
        $code = trim($request->code);
        $type = $request->type;
        $recurring  = $request->recurring;
        $pvalue  = $request->pvalue ?? 0 ;
        $requiresexisting  = $request->requiresexisting;
        $startdate  = $request->startdate;
        $expirationdate  = $request->expirationdate;
        $maxuses  = $request->maxuses;
        $lifetimepromo  = $request->lifetimepromo ?? 0;
        $applyonce  = (int) $request->applyonce;
        $newsignups  = $request->newsignups;
        $existingclient  = $request->existingclient ??0;
        $onceperclient  = $request->onceperclient;
        $recurfor  = $request->recurfor;
        $cycles  = $request->cycles;
        $appliesto  = $request->appliesto;
        $requires  = $request->requires;
        $upgrades  = $request->upgrades ??0;
        $upgradevalue  = $request->upgradevalue;
        $upgradetype  = $request->upgradetype;
        $upgradediscounttype  = $request->upgradediscounttype;
        $configoptionupgrades  = $request->configoptionupgrades;
        //$notes  = $request->notes;
       //$startdate =!$startdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($startdate);
        //$expirationdate =!$expirationdate ? "0000-00-00" : (new \App\Helpers\SystemHelper())->toMySQLDate($expirationdate);
        $cycles = is_array($cycles) ? implode(",", $cycles) : "";
        $appliesto = is_array($appliesto) ? implode(",", $appliesto) : "";
        $requires = is_array($requires) ? implode(",", $requires) : "";
        $upgradeconfig= (new \App\Helpers\Pwd())->safe_serialize([
                                 "value" => \App\Helpers\Functions::format_as_currency($upgradevalue),
                                 "type" => $upgradetype,
                                 "discounttype" => $upgradediscounttype,
                                 "configoptions" => $configoptionupgrades
                        ]);
        $notes=$request->notes;
        //    /$duplicates=\App\Models\Promotion::where('code',$code)->first();

        $promo=new \App\Models\Promotion();
        $promo->code =$code;
        $promo->type =$type;
        $promo->recurring =$recurring;
        $promo->value =$pvalue;
        $promo->cycles =$cycles;
        $promo->appliesto =$appliesto;
        $promo->requires =$requires;
        $promo->requiresexisting =$requiresexisting ?? 0 ;
        $promo->startdate =$startdate;
        $promo->expirationdate =$expirationdate;
        $promo->maxuses =$maxuses;
        $promo->lifetimepromo =$lifetimepromo;
        $promo->applyonce =$applyonce;
        $promo->newsignups =$newsignups;
        $promo->existingclient =$existingclient;
        $promo->onceperclient =$onceperclient;
        $promo->recurfor =$recurfor;
        $promo->upgrades =$upgrades;
        $promo->upgradeconfig =$upgradeconfig;
        $promo->notes =$notes;
        $promo->save();
        $newid=$promo->id;
        LogActivity::Save("Promotion Created: '" . $code . "' - Promotion ID: " . $newid);
        //return back()->with('The new promotion code was added successfully!');
        return redirect(request()->segment(1).'/setup/payments/promotions/')->with('The new promotion code was added successfully!');

    }

    public function expired(Request $request){
       // dd($request->all());
        $id=$request->id;
        $promo=\App\Models\Promotion::find($id);
        $code=$promo->code;
        $promo->expirationdate =date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));
        $promo->save();
        LogActivity::Save("Promotion Expired: '" . $code . "' - Promotion ID: " . $id);

        return back()->with('success', 'successfully set  Expired promo  '. $code);
    }




    public function Support_ticketdepartments()
    {
        return view('pages.setup.support.supportticketdepartments.index');
    }
    public function Support_ticketdepartments_add()
    {
        return view('pages.setup.support.supportticketdepartments.add');
    }
    public function Support_ticketdepartments_edit()
    {
        return view('pages.setup.support.supportticketdepartments.edit');
    }
    public function Support_ticketstatuses()
    {
        return view('pages.setup.support.ticketstatuses.index');
    }
    public function Support_escalationrules()
    {
        return view('pages.setup.support.escalationrules.index');
    }
    public function Support_escalationrules_add()
    {
        return view('pages.setup.support.escalationrules.add');
    }
    public function Support_spamcontrol()
    {
        return view('pages.setup.support.spamcontrol.index');
    }
    public function ApplicationLinks()
    {
        return view('pages.setup.applicationlinks.index');
    }
    public function OpenIdConnect()
    {
        return view('pages.setup.openidconnect.index');
    }
    public function OpenIdConnect_add()
    {
        return view('pages.setup.openidconnect.add');
    }
    public function EmailTemplates()
    {
        //get data from API by type
        $generalTemplates = API::post('GetEmailTemplates', ['type' => 'general']);
        $productTemplates = API::post('GetEmailTemplates', ['type' => 'product']);
        $invoiceTemplates = API::post('GetEmailTemplates', ['type' => 'invoice']);
        $supportTemplates = API::post('GetEmailTemplates', ['type' => 'support']);
        $notificationTemplates = API::post('GetEmailTemplates', ['type' => 'notification']);
        $domainTemplates = API::post('GetEmailTemplates', ['type' => 'domain']);
        $adminTemplates = API::post('GetEmailTemplates', ['type' => 'admin']);
        $affiliatesTemplates = API::post('GetEmailTemplates', ['type' => 'affiliate']);

        // Email Template Array Response
        $emailGeneralTemplate = $generalTemplates['emailtemplates']['emailtemplate'];
        $emailProductTemplate = $productTemplates['emailtemplates']['emailtemplate'];
        $emailInvoiceTemplate = $invoiceTemplates['emailtemplates']['emailtemplate'];
        $emailSupportTemplate = $supportTemplates['emailtemplates']['emailtemplate'];
        $emailNotificationTemplate = $notificationTemplates['emailtemplates']['emailtemplate'];
        $emailDomainTemplate = $domainTemplates['emailtemplates']['emailtemplate'];
        $emailAdminTemplate = $adminTemplates['emailtemplates']['emailtemplate'];
        $emailAffiliatesTemplate = $affiliatesTemplates['emailtemplates']['emailtemplate'];

        //pass to view
        return view('pages.setup.emailtemplates.index', [
            'generalTemplates' => $emailGeneralTemplate,
            'productTemplates' => $emailProductTemplate,
            'invoiceTemplates' => $emailInvoiceTemplate,
            'supportTemplates' => $emailSupportTemplate,
            'notificationTemplates' => $emailNotificationTemplate,
            'domainTemplates' => $emailDomainTemplate,
            'adminTemplates' => $emailAdminTemplate,
            'affiliatesTemplates' => $emailAffiliatesTemplate
        ]);
    }
    public function EmailTemplates_create(Request $request)
    {
        $data = $request->all();
        EmailTemplate::create($data);
        return redirect()->route('admin.pages.setup.emailtemplates.index')->with(['success' => 'A new email template has been created']);
    }
    public function EmailTemplates_edit($id)
    {
        // $dataEmailTemplates = API::post('GetEmailTemplates', ['id' => $id]);
        // $emailtemplates = $dataEmailTemplates->emailtemplates->emailtemplate;
        $emailtemplate = \App\Models\Emailtemplate::findOrFail($id);
        // dd($emailtemplate->attachments);
        return view('pages.setup.emailtemplates.edit', [
            'template' =>  $emailtemplate,
            'type' => $emailtemplate->type,
            'name' => $emailtemplate->name,
            'customfields' => [],
        ]);
    }
    public function EmailTemplates_update(Request $request, $id)
    {
        $data = $request->all();

        DB::beginTransaction();

        try {
            $existingEmailtemplate = \App\Models\Emailtemplate::findOrFail($id);

            $attachments = $request->file('attachments');
            $attachmentString = [];
            if ($request->hasFile('attachments')) {
                foreach ($attachments as $attachment) {
                    $fileNameToSave = Str::random(6)."_".$attachment->getClientOriginalName();
                    $filename = $fileNameToSave;
                    $filepath = "{$filename}";

                    $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                    $attachmentString[] = $filename;
                }
            }
            $attachmentStringExisting = $existingEmailtemplate->attachments;
            $attachmentString = array_merge($attachmentStringExisting, $attachmentString);
            $attachmentString = implode(',', $attachmentString);

            DB::table($this->prefix."emailtemplates")->where('id', $id)->update([
                "fromname" => $request->input('fromname'),
                "fromemail" => $request->input('fromemail'),
                "copyto" => $request->input('copyto'),
                "blind_copy_to" => $request->input('blind_copy_to'),
                "attachments" => $attachmentString,
                "subject" => $request->input('subject'),
                "message" => $request->input('message'),
                "disabled" => $request->input('disabled') ? 1 : 0,
                "plaintext" => $request->input('plaintext') ? 1 : 0,
            ]);

            DB::commit();
            return redirect()->route('admin.pages.setup.emailtemplates.edit', ['id' => $id])->with(['success' => 'The email templates has been updated.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.pages.setup.emailtemplates.edit', ['id' => $id])->with(['error' => $e->getMessage()]);
        }
    }
    public function EmailTemplates_delete(Request $request, $id)
    {
        $data = $request->all();
        // dd($data);
        $template = EmailTemplate::findOrFail($id);
        $template->delete($data);
        return redirect()->route('admin.pages.setup.emailtemplates.index')->with(['success' => 'An email template successfully deleted!']);
    }
    public function AddonsModule(Request $request)
    {
        $data = [];
        $modules = \Module::toCollection();
        foreach ($modules as $key => $module) {
            if (strpos($module->getPath(), '/Addons') !== false) {
                // $addons = \Module::find($module->getLowerName());
                $addons = new \App\Module\Addons();
                $addons->load($module->getLowerName());
                $config = $addons->getConfig();
                $data[] = [
                    'module' => $module,
                    'config' => $config,
                    'setting' => \App\Helpers\AddonModule::getSetting($module->getLowerName()),
                ];
            }
        }
        // dd($data);
        return view('pages.setup.addonsmodule.index', [
            'addonmodules' => $data,
            'roles' => \Spatie\Permission\Models\Role::where('guard_name', 'admin')->get()->toArray(),
        ]);
    }
    public function AddonsModule_active(Request $request)
    {
        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $m = \Module::find($module);
            if (!$m) {
                return redirect()->back()->with(['error' => "Module not found"]);
            }

            $status = "success";
            $desc = "Addon Module Activated";

            $addons = new \App\Module\Addons();
            $addons->load($m->getLowerName());
            $response = $addons->activate();
            if (!$response || is_array($response) && ($response["status"] == "success" || $response["status"] == "info")) {
                // if ($addon_modules[$module]["version"] != $aInt->lang("addonmodules", "nooutput")) {
                    $config = $addons->getConfig();
                    \App\Models\AddonModule::insert(array("module" => $module, "setting" => "version", "value" => $config["version"]));
                // }
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Addon Module Activated - " . $module);

            if (is_array($response)) {
                if (isset($response["description"])) {
                    $desc = $response["description"];
                }
                if (isset($response["status"]) && in_array($response["status"], array("info", "success", "error"))) {
                    $status = $response["status"];
                }
            }

            $m->enable();
            DB::commit();
            return redirect()->back()->with([$status => $desc]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
    public function AddonsModule_deactive(Request $request)
    {
        DB::beginTransaction();

        try {
            $module = $request->input('module');
            $m = \Module::find($module);
            if (!$m) {
                return redirect()->back()->with(['error' => "Module not found"]);
            }

            $status = "success";
            $desc = "Addon Module Deactivated";

            $addons = new \App\Module\Addons();
            $addons->load($m->getLowerName());
            $response = $addons->deactivate();
            if (!$response || is_array($response) && ($response["status"] == "success" || $response["status"] == "info")) {
                \App\Models\AddonModule::where(array("module" => $module))->delete();
                // foreach ($activemodules as $k => $mod) {
                //     if ($mod == $module) {
                //         unset($activemodules[$k]);
                //     }
                // }
                // sort($activemodules);
                // update_query("tblconfiguration", array("value" => implode(",", $activemodules)), array("setting" => "ActiveAddonModules"));
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Addon Module Deactivated - " . $module);

            if (is_array($response)) {
                if (isset($response["description"])) {
                    $desc = $response["description"];
                }
                if (isset($response["status"]) && in_array($response["status"], array("info", "success", "error"))) {
                    $status = $response["status"];
                }
            }

            $m->disable();
            DB::commit();
            return redirect()->back()->with([$status => $desc]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
    public function ClientGroups(Request $request)
    {
        $action = $request->input('action');
        $added = $request->input('added');
        $update = $request->input('update');
        $deletesuccess = $request->input('deletesuccess');
        $deleteerror = $request->input('deleteerror');
        $path = $request->path();

        // vars
        $id = $request->input('id');
        $groupid = $request->input('groupid');
        $groupname = $request->input('groupname') ?? "";
        $groupcolour = $request->input('groupcolour') ?? "";
        $discountpercent = $request->input('discountpercent') ?? "";
        $susptermexempt = $request->input('susptermexempt') ?? "";
        $separateinvoices = $request->input('separateinvoices') ?? "";

        $aInt = new \App\Helpers\Admin("Configure Client Groups");
        $aInt->title = $aInt->lang("clientgroups", "title");
        $aInt->sidebar = "config";
        $aInt->icon = "clients";
        $aInt->helplink = "Client Groups";
        if ($action == "savegroup") {
            $id = \App\Models\Clientgroup::insertGetId(array("groupname" => $groupname, "groupcolour" => $groupcolour, "discountpercent" => $discountpercent, "susptermexempt" => $susptermexempt, "separateinvoices" => $separateinvoices));
            \App\Helpers\AdminFunctions::logAdminActivity("Client Group Created: " . $groupname . " - Client Group ID: " . $id);
            // redir("added=true");
            return redirect()->to("$path?added=true");
        }
        if ($action == "updategroup") {
            $changes = array();
            $group = DB::table("tblclientgroups")->find($groupid);
            \App\Models\Clientgroup::where(array("id" => $groupid))->update(array("groupname" => $groupname, "groupcolour" => $groupcolour, "discountpercent" => $discountpercent, "susptermexempt" => $susptermexempt, "separateinvoices" => $separateinvoices));
            if ($discountpercent != $group->discountpercent) {
                $changes[] = "Discount Percentage Changed from '" . $group->discountpercent . "' to '" . $discountpercent . "'";
            }
            if ($susptermexempt != $group->susptermexempt) {
                if ($susptermexempt) {
                    $changes[] = "Suspend/Termination Exemption Enabled";
                } else {
                    $changes[] = "Suspend/Termination Exemption Disabled";
                }
            }
            if ($separateinvoices != $group->separateinvoices) {
                if ($separateinvoices) {
                    $changes[] = "Separate Invoices Enabled";
                } else {
                    $changes[] = "Separate Invoices Disabled";
                }
            }
            if ($changes) {
                $changes = " - " . implode(". ", $changes);
            } else {
                $changes = "";
            }
            \App\Helpers\AdminFunctions::logAdminActivity("Client Group Modified: " . $groupname . $changes . " - Client Group ID: " . $groupid);
            // redir("update=true");
            return redirect()->to("$path?update=true");
        }
        if ($action == "delete") {
            $result = \App\Models\Client::where(array("groupid" => $id));
            $numaccounts = $result->count();
            if (0 < $numaccounts) {
                // redir("deleteerror=true");
                return redirect()->to("$path?deleteerror=true");
            } else {
                $groupName = DB::table("tblclientgroups")->find($id, array("groupname"))->groupname;
                \App\Models\Clientgroup::where(array("id" => $id))->delete();
                foreach (array("domainregister", "domaintransfer", "domainrenew") as $type) {
                    \App\Models\Pricing::where(array("type" => $type, "tsetupfee" => $id))->delete();
                }
                \App\Helpers\AdminFunctions::logAdminActivity("Client Group Deleted: " . $groupName . " - Client Group ID: " . $id);
                // redir("deletesuccess=true");
                return redirect()->to("$path?deletesuccess=true");
            }
        }
        $mergeData = [];
        if ($action == "edit") {
            $result = \App\Models\Clientgroup::where(array("id" => $id))->first();
            $data = $result;
            foreach ($data->toArray() as $name => $value) {
                ${$name} = $value;
                $mergeData[$name] = $value;
            }
        }
        $infobox = "";
        if ($added) {
            $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("clientgroups", "addsuccess"), $aInt->lang("clientgroups", "addsuccessinfo"));
        }
        if ($update) {
            $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("clientgroups", "editsuccess"), $aInt->lang("clientgroups", "editsuccessinfo"));
        }
        if ($deletesuccess) {
            $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("clientgroups", "delsuccess"), $aInt->lang("clientgroups", "delsuccessinfo"));
        }
        if ($deleteerror) {
            $infobox = \App\Helpers\AdminFunctions::infoBox($aInt->lang("", "erroroccurred"), $aInt->lang("clientgroups", "delerrorinfo"));
        }
        $jscode = "function doDelete(id) {\nif (confirm(\"" . $aInt->lang("clientgroups", "delsure") . "\")) {\nwindow.location='" . request()->url() . "?action=delete&id='+id;\n}}";
        // $aInt->sortableTableInit("nopagination");
        $result = \App\Models\Clientgroup::all();
        $tabledata = [];
        foreach ($result->toArray() as $data) {
            $suspterm = $data["susptermexempt"] == "on" ? $aInt->lang("", "yes") : $aInt->lang("", "no");
            $separateinv = $data["separateinvoices"] == "on" ? $aInt->lang("", "yes") : $aInt->lang("", "no");
            $groupcol = $data["groupcolour"] ? "<div style=\"width:75px;background-color:" . $data["groupcolour"] . "\">" . $aInt->lang("clientgroups", "sample") . "</div>" : "";
            $tabledata[] = array(
                $data["groupname"],
                $groupcol,
                $data["discountpercent"],
                $suspterm,
                $separateinv,
                "<a href=\"" . request()->url() . "?action=edit&id=" . $data["id"] . "\"><img src=\"".\Theme::asset('img/edit.gif')."\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("", "edit") . "\"></a>", "<a href=\"#\" onClick=\"doDelete('" . $data["id"] . "');return false\"><img src=\"".\Theme::asset('img/delete.gif')."\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $aInt->lang("", "delete") . "\"></a>"
            );
        }

        $setaction = $action == "edit" ? "updategroup" : "savegroup";
        return view('pages.setup.clientgroups.index', [
            'id' => $id,
            'groupname' => $groupname,
            'groupcolour' => $groupcolour,
            'discountpercent' => $discountpercent,
            'susptermexempt' => $susptermexempt,
            'separateinvoices' => $separateinvoices,
            'setaction' => $setaction,
            'infobox' => $infobox,
            'jscode' => $jscode,
            'tabledata' => $tabledata,
        ], $mergeData);
    }
    public function CustomClientFields()
    {
        $result = \App\Models\Customfield::where(array("type" => "client"))->orderBy("sortorder", "ASC")->orderBy("id", "ASC")->get();
        // dd($result);
        return view('pages.setup.customclientfields.index', [
            'result' => $result,
        ]);
    }
    public function CustomClientFields_save(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            // save fieldname
            // foreach ($request->input('fieldname') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->fieldname = $value;
            //     $customfield->save();
            // }
            // // save sortorder
            // foreach ($request->input('sortorder') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->sortorder = $value;
            //     $customfield->save();
            // }
            // // save fieldtype
            // foreach ($request->input('fieldtype') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->fieldtype = $value;
            //     $customfield->save();
            // }
            // // save description
            // foreach ($request->input('description') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->description = $value;
            //     $customfield->save();
            // }
            // // save adminonly
            // foreach ($request->input('adminonly') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->adminonly = $value;
            //     $customfield->save();
            // }
            // // save required
            // foreach ($request->input('required') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->required = $value;
            //     $customfield->save();
            // }
            // // save showorder
            // foreach ($request->input('showorder') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->showorder = $value;
            //     $customfield->save();
            // }
            // // save showinvoice
            // foreach ($request->input('showinvoice') ?? [] as $id => $value) {
            //     $customfield = \App\Models\Customfield::findOrFail($id);
            //     $customfield->showinvoice = $value;
            //     $customfield->save();
            // }

            $customfieldname = $request->input("fieldname") ?? [];
            if ($customfieldname) {
                $customfieldtype = $request->input("fieldtype");
                $customfielddesc = $request->input("description");
                $customfieldoptions = $request->input("fieldoptions");
                $customfieldregexpr = $request->input("regexpr");
                $customadminonly = $request->input("adminonly");
                $customrequired = $request->input("required");
                $customshoworder = $request->input("showorder");
                $customshowinvoice = $request->input("showinvoice");
                $customsortorder = $request->input("sortorder");
                foreach ($customfieldname as $fid => $value) {
                    $type = isset($customfieldtype[$fid]) ? $customfieldtype[$fid] : "";
                    $desc = isset($customfielddesc[$fid]) ? $customfielddesc[$fid] : "";
                    $op = isset($customfieldoptions[$fid]) ? $customfieldoptions[$fid] : "";
                    $regx = isset($customfieldregexpr[$fid]) ? $customfieldregexpr[$fid] : "";
                    $adminonly = isset($customadminonly[$fid]) ? $customadminonly[$fid] : "";
                    $required = isset($customrequired[$fid]) ? $customrequired[$fid] : "";
                    $showorder = isset($customshoworder[$fid]) ? $customshoworder[$fid] : "";
                    $showinvoice = isset($customshowinvoice[$fid]) ? $customshowinvoice[$fid] : "";
                    $sortorder = isset($customsortorder[$fid]) ? $customsortorder[$fid] : "";
                    // $thisCustomField = \App\Models\Customfield::findOrFail($fid);
                    // if ($value != $thisCustomField->fieldname) {
                    //     $changes[] = "Custom Field Name Modified: '" . $thisCustomField->fieldname . "' to '" . $value . "'";
                    // }
                    // if ($type != $thisCustomField->fieldtype || $desc != $thisCustomField->description || $op != $thisCustomField->fieldoptions || $regx != $thisCustomField->regexpr || $adminonly != $thisCustomField->adminonly || $required != $thisCustomField->required || $showorder != $thisCustomField->showorder || $showinvoice != $thisCustomField->showinvoice || $sortorder != $thisCustomField->sortorder) {
                    //     $changes[] = "Custom Field Modified: '" . $value . "'";
                    // }
                    \App\Models\Customfield::where(array("id" => $fid))->update(array("fieldname" => $value, "fieldtype" => $type, "description" => $desc, "fieldoptions" => $op, "regexpr" => \App\Helpers\Sanitize::decode($regx), "adminonly" => $adminonly, "required" => $required, "showorder" => $showorder, "showinvoice" => $showinvoice, "sortorder" => $sortorder));
                }
            }

            // add new
            if ($request->input('addfieldname')) {
                DB::table(\App\Helpers\Database::prefix()."customfields")->insert([
                    'type' => 'client',
                    'relid' => 0,
                    'fieldname' => $request->input('addfieldname'),
                    'fieldtype' => $request->input('addfieldtype') ?? '',
                    'description' => $request->input('adddescription') ?? '',
                    'regexpr' => $request->input('addregexpr') ?? '',
                    'fieldoptions' => $request->input('addfieldoptions') ?? '',
                    'adminonly' => $request->input('addadminonly') ?? '',
                    'required' => $request->input('addrequired') ?? '',
                    'showorder' => $request->input('addshoworder') ?? '',
                    'showinvoice' => $request->input('addshowinvoice') ?? '',
                    'sortorder' => $request->input('addsortorder') ?? 0,
                    'created_at' => \Carbon\Carbon::now(),
                ]);
            }

            DB::commit();
            return redirect()->back()->with(['success' => 'Your changes have been saved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
    public function CustomClientFields_delete($id)
    {
        DB::beginTransaction();
        try {
            $customField = \App\Models\Customfield::findOrFail($id);
            \App\Helpers\AdminFunctions::logAdminActivity("Client Custom Field Deleted: '" . $customField->fieldname . "' - Custom Field ID: " . $id);
            $customField->delete();
            DB::commit();
            return redirect()->back()->with(['success' => 'Your changes have been saved.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }
    }
    public function FraudProtection()
    {
        return view('pages.setup.fraudprotection.index');
    }
    public function Other_orderstatuses()
    {
        return view('pages.setup.other.orderstatuses.index');
    }
    public function Other_securityquestions(Request $request)
    {
        $id = $request->get('id');

        $tabledata = [];
        $results = \App\Models\AdminSecurityQuestion::all();
        foreach ($results->toArray() as $key => $data) {
            $count_data = \App\Models\Client::where(array("securityqid" => $data["id"]))->count();
            $cnt = $count_data <= 0 ? "0" : $count_data;
            $data['question'] = (new \App\Helpers\Pwd)->decrypt($data['question']);
            $data['uses'] = $cnt;
            $tabledata[] = $data;
        }
        // dd($tabledata);

        return view('pages.setup.other.securityquestions.index', [
            'results' => $tabledata,
        ]);
    }
    public function Other_securityquestions_post(Request $request)
    {
        $validatedData = $request->validate([
            'question' => 'required|max:100',
        ]);

        $question = $request->input('question');
        $table = new \App\Models\AdminSecurityQuestion;
        $table->question = (new \App\Helpers\Pwd)->encrypt($question);
        $table->save();

        return redirect()->back()->with(['success' => "The question has been saved successfully"]);
    }
    public function Other_securityquestions_delete(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:App\Models\AdminSecurityQuestion',
        ]);

        $id = $request->input('id');
        $table = \App\Models\AdminSecurityQuestion::find($id);

        $table->delete();

        return redirect()->back()->with(['success' => "The question has been deleted successfully"]);
    }
    public function Other_bannedips()
    {
        return view('pages.setup.other.bannedips.index');
    }
    public function Other_bannedemails()
    {
        return view('pages.setup.other.bannedemails.index');
    }
    public function Other_databasebackups()
    {
        return view('pages.setup.other.databasebackups.index');
    }

    public function dtPromotions(Request $request)
    {
        try {
            $query = Promotion::select([
                'id', 'code', 'type', 'value', 'recurring',
                'maxuses', 'uses', 'startdate', 'expirationdate',
                'appliesto', 'requires', 'cycles', 'notes'
            ]);

            // Handle main filter (Active/Expired/All)
            if ($request->filter) {
                if ($request->filter === 'expired') {
                    $query->where('expirationdate', '<', now());
                } else if ($request->filter === '0') { // Active
                    $query->where(function($q) {
                        $q->whereNull('expirationdate')
                          ->orWhere('expirationdate', '>=', now());
                    });
                }
            }

            // Handle search form filters
            if ($request->dataFiltered) {
                parse_str($request->dataFiltered, $filters);
                
                if (!empty($filters['code'])) {
                    $query->where('code', 'like', '%' . $filters['code'] . '%');
                }

                if (!empty($filters['type'])) {
                    $query->where('type', $filters['type']);
                }

                if (!empty($filters['status'])) {
                    if ($filters['status'] === 'active') {
                        $query->where(function($q) {
                            $q->whereNull('expirationdate')
                              ->orWhere('expirationdate', '>=', now());
                        });
                    } else if ($filters['status'] === 'expired') {
                        $query->where('expirationdate', '<', now());
                    }
                }
            }

            return datatables()->of($query)
                ->addColumn('uses', function($row) {
                    return $row->uses . '/' . ($row->maxuses ?: 'Unlimited');
                })
                ->editColumn('recurring', function($row) {
                    return $row->recurring ? 'Yes' : 'No';
                })
                ->editColumn('value', function($row) {
                    return $row->type === 'Percentage' ? $row->value . '%' : number_format($row->value, 2);
                })
                ->editColumn('startdate', function($row) {
                    return $row->startdate ? date('Y-m-d', strtotime($row->startdate)) : 'N/A';
                })
                ->editColumn('expirationdate', function($row) {
                    return $row->expirationdate ? date('Y-m-d', strtotime($row->expirationdate)) : 'Never';
                })
                ->rawColumns(['actions'])
                ->make(true);

        } catch (\Exception $e) {
            \Log::error('Error in dtPromotions: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }
}
