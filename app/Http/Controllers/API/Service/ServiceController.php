<?php

namespace App\Http\Controllers\API\Service;

use DB, Validator, Auth;
use ResponseAPI;

use App\Models\Hosting;
use App\Models\Paymentgateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    //
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * ModuleCreate
     * 
     * Runs the module create action for a given service.
     */
    public function ModuleCreate()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerCreateAccount($serviceId);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleTerminate
     * 
     * Runs a terminate action for a given service.
     */
    public function ModuleTerminate()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerTerminateAccount($serviceId);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleSuspend
     * 
     * Runs the module suspend action for a given service.
     */
    public function ModuleSuspend()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
            'suspendreason' => ['nullable', 'string'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');
        $suspendReason = $this->request->input('suspendreason');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerSuspendAccount($serviceId, $suspendReason);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleUnsuspend
     * 
     * Runs an unsuspend action for a given service.
     */
    public function ModuleUnsuspend()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerUnsuspendAccount($serviceId);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleCustom
     * 
     * Runs a custom module action for a given service.
     */
    public function ModuleCustom()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
            'func_name' => ['required', 'string'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');
        $functionToRun = $this->request->input('func_name');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerCustomFunction($serviceId, $functionToRun);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleChangePackage
     * 
     * Runs a change package action for a given service.
     */
    public function ModuleChangePackage()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        $m = new \App\Module\Server();
        $result = $m->ServerChangePackage($serviceId);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * ModuleChangePw
     * 
     * Runs a change password action for a given service.
     */
    public function ModuleChangePw()
    {
        $rules = [
            'serviceid' => ['required', 'integer'],
            'servicepassword' => ['nullable', 'string'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceId = $this->request->input('serviceid');
        $servicepassword = $this->request->input('servicepassword');

        $data = DB::table("tblhosting")->leftJoin("tblproducts", "tblhosting.packageid", "=", "tblproducts.id")->where("tblhosting.id", $serviceId)->first(array("tblhosting.id as service_id", "tblproducts.servertype as module"));
        
        if (!$data) {
            return ResponseAPI::Error([
                'message' => 'Service ID not found',
            ]);
        }

        if (!$data->module) {
            return ResponseAPI::Error([
                'message' => 'Service not assigned to a module',
            ]);
        }

        $serviceId = $data->service_id;
        if ($servicepassword) {
            $hosting = Hosting::find($serviceId);
            $hosting->password = (new \App\Helpers\Pwd())->encrypt($servicepassword);
            $hosting->save();
        }
        $m = new \App\Module\Server();
        $result = $m->ServerChangePassword($serviceId);
        
        if ($result == "success") {
            return ResponseAPI::Success();
        } else {
            return ResponseAPI::Error([
                'message' => $result,
            ]);
        }
    }

    /**
     * UpgradeProduct
     * 
     * Upgrade, or calculate an upgrade on, a product
     */
    public function UpgradeProduct()
    {
        $gatewaysarray = array();
        $result = Paymentgateway::where('setting', 'name')->get();
        foreach ($result->toArray() as $data) {
            $gatewaysarray[] = $data["gateway"];
        }

        $rules = [
            // The ID of the service to update
            'serviceid' => ['required', 'integer', 'exists:App\Models\Hosting,id'],
            // Only calculate the upgrade amount
            'calconly' => ['nullable', 'boolean'],
            // The upgrade payment method in system format (e.g. paypal)
            'paymentmethod' => ['required', 'string', Rule::in($gatewaysarray)],
            // The type of upgrade (‘product’, ‘configoptions’)
            'type' => ['required', 'string', Rule::in(['product', 'configoptions'])],
            // The ID of the new product
            'newproductid' => ['nullable', 'string'],
            // The new products billing cycle
            'newproductbillingcycle' => ['nullable', 'string'],
            // The promotion code to apply to the upgrade
            'promocode' => ['nullable', 'string'],
            // If type=configoptions - Config options to include in the upgrade. Keys represent config option IDs while their values represent the config option choice ID or value (depending on type). In the example provided, config option ID=1 is a dropdown specifying option ID 4, and config option ID=2 is a quantity specifying a desire for 5 units.
            'configoptions' => ['nullable', 'array'],
            // If type=configoptions - Config options to include in the upgrade. Keys represent config option IDs while their values represent the config option choice ID or value (depending on type). In the example provided, config option ID=1 is a dropdown specifying option ID 4, and config option ID=2 is a quantity specifying a desire for 5 units.
            'configoptions.*' => ['nullable', 'string', 'distinct'],
        ];

        $messages = [
            'serviceid.exists' => "Service ID Not Found",
            'type.in' => "Invalid Upgrade Type",
            'paymentmethod.in' => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray),
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $serviceid = $this->request->input('serviceid');
        $calconly = (bool) $this->request->input('calconly');
        $paymentmethod = $this->request->input('paymentmethod');
        $type = $this->request->input('type');
        $newproductid = $this->request->input('newproductid');
        $newproductbillingcycle = $this->request->input('newproductbillingcycle');
        $promocode = $this->request->input('promocode');
        $configoptions = $this->request->input('configoptions');

        $result = Hosting::find($serviceid);
        $data = $result->toArray();
        $serviceid = $data["id"];
        $clientid = $data["userid"];

        Auth::guard('web')->login(\App\Models\Client::find($clientid));

        Db::beginTransaction();
        try {
            $currency = (new \App\Helpers\AdminFunctions())->getCurrency($clientid);
            $checkout = $calconly ? false : true;
            
            $upgradeAlreadyInProgress = \App\Helpers\Upgrade::upgradeAlreadyInProgress($serviceid);

            if ($checkout) {
                if ($upgradeAlreadyInProgress) {
                    return ResponseAPI::Error([
                        'message' => "Unable to accept upgrade order. Previous upgrade invoice for service is still unpaid.",
                    ]);
                }
                $gatewaysarray = array();
                $result = \App\Models\Paymentgateway::where('setting', 'name')->get();
                foreach ($result->toArray() as $key => $data) {
                    $gatewaysarray[] = $data["gateway"];
                }
                if (!in_array($paymentmethod, $gatewaysarray)) {
                    return ResponseAPI::Error([
                        'message' => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray),
                    ]);
                }
            }

            $apiresults = [];
            if ($type == "product") {
                $upgrades = \App\Helpers\Upgrade::SumUpPackageUpgradeOrder($serviceid, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod, $checkout);
                $apiresults = array_merge($apiresults, $upgrades[0]);
            } else {
                if ($type == "configoptions") {
                    $subtotal = 0;
                    $result = select_query("tblhosting", "", array("id" => $serviceid));
                    $result = \App\Models\Hosting::select("packageid","billingcycle")->where('id', $serviceid);
                    $data = $result->toArray();
                    list($pid, $billingcycle) = $data;
                    $configoption = getCartConfigOptions($pid, "", $billingcycle, $serviceid);
                    $configoptions = $configoptions;
                    if (!is_array($configoptions)) {
                        $configoptions = array();
                    }
                    foreach ($configoption as $option) {
                        $id = $option["id"];
                        $optiontype = $option["optiontype"];
                        $selectedvalue = $option["selectedvalue"];
                        $selectedqty = $option["selectedqty"];
                        if (!isset($configoptions[$id])) {
                            if ($optiontype == "3" || $optiontype == "4") {
                                $selectedvalue = $selectedqty;
                            }
                            $configoptions[$id] = $selectedvalue;
                        }
                    }
                    $upgrades = \App\Helpers\Upgrade::SumUpConfigOptionsOrder($serviceid, $configoptions, $promocode, $paymentmethod, $checkout);
                    foreach ($upgrades as $i => $vals) {
                        foreach ($vals as $k => $v) {
                            $apiresults[$k . ($i + 1)] = $v;
                        }
                    }
                    $subtotal = $GLOBALS["subtotal"];
                    $discount = $GLOBALS["discount"];
                    $apiresults["subtotal"] = \App\Helpers\Format::formatCurrency($subtotal);
                    $apiresults["discount"] = \App\Helpers\Format::formatCurrency($discount);
                    $apiresults["total"] = \App\Helpers\Format::formatCurrency($subtotal - $discount);
                } else {
                    return ResponseAPI::Error([
                        'message' => "Invalid Upgrade Type",
                    ]);
                }
            }
            
            if (!$checkout) {
                $apiresults["upgradeinprogress"] = (int) $upgradeAlreadyInProgress;
            } else {
                $upgradedata = \App\Helpers\Upgrade::createUpgradeOrder($serviceid, $ordernotes, $promocode, $paymentmethod);
                $apiresults = array_merge($apiresults, $upgradedata);
            }

            DB::commit();
            return ResponseAPI::Success($apiresults);
        } catch (\Exception $e) {
            // TODO: remove dd()
            dd($e);
            DB::rollBack();
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * UpdateClientProduct
     * 
     * Updates a Client Service
     */
    public function UpdateClientProduct()
    {
        $rules = [
            // The ID of the client service to update.
            'serviceid' => ['required', 'integer'],
            // The package ID to associate with the service.
            'pid' => ['nullable', 'integer'],
            // The server ID to associate with the service.
            'serverid' => ['nullable', 'integer'],
            // The registration date of the service. Format: Y-m-d
            'regdate' => ['nullable', 'string', 'date_format:Y-m-d'],
            // The next due date of the service. Format: Y-m-d
            'nextduedate' => ['nullable', 'string', 'date_format:Y-m-d'],
            // Update the termination date of the service. Format: Y-m-d
            'terminationdate' => ['nullable', 'string', 'date_format:Y-m-d'],
            // The domain name to be changed to.
            'domain' => ['nullable', 'string'],
            // The first payment amount on the service.
            'firstpaymentamount' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            // The recurring amount for automatic renewal invoices.
            'recurringamount' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            // The payment method to associate, in system format (for example, paypal).
            'paymentmethod' => ['nullable', 'string'],
            // The term in which the product is billed on (for example, One-Time, Monthly, or Quarterly).
            'billingcycle' => ['nullable', 'string'],
            // The subscription ID to associate with the service.
            'subscriptionid' => ['nullable', 'string'],
            // The status to change the service to.
            'status' => ['nullable', 'string'],
            // The admin notes for the service.
            'notes' => ['nullable', 'string'],
            // The service username.
            'serviceusername' => ['nullable', 'string'],
            // The service password.
            'servicepassword' => ['nullable', 'string'],
            // Whether to provide override auto suspend (‘on’ or ‘off’).
            'overideautosuspend' => ['nullable', 'string'],
            // Update the Override Suspend date of the service. Format: Y-m-d
            'overidesuspenduntil' => ['nullable', 'string', 'date_format:Y-m-d'],
            // (VPS/Dedicated servers only)
            'ns1' => ['nullable', 'string'],
            // (VPS/Dedicated servers only)
            'ns2' => ['nullable', 'string'],
            // dedicatedip
            'dedicatedip' => ['nullable', 'string'],
            // (VPS/Dedicated servers only)
            'assignedips' => ['nullable', 'string'],
            // The disk usage in megabytes.
            'diskusage' => ['nullable', 'integer'],
            // The disk limit in megabytes.
            'disklimit' => ['nullable', 'integer'],
            // The bandwidth usage in megabytes.
            'bwusage' => ['nullable', 'integer'],
            // The bandwidth limit in megabytes.
            'bwlimit' => ['nullable', 'integer'],
            // suspendreason
            'suspendreason' => ['nullable', 'string'],
            // he promotion ID to associate.
            'promoid' => ['nullable', 'integer'],
            // An array of items to unset. Can be one of: ‘domain’, ‘serviceusername’, ‘servicepassword’, ‘subscriptionid’, ‘ns1’, ‘ns2’, ‘dedicatedip’, ‘assignedips’, ‘notes’, or ‘suspendreason’
            'unset' => ['nullable', 'array'],
            // Whether to automatically recalculate the recurring amount of the service (this will ignore any passed $recurringamount).
            'autorecalc' => ['nullable', 'boolean'],
            // Base64 encoded serialized array of custom field values - base64_encode(serialize(array(“1”=>“Yahoo”)));
            'customfields' => ['nullable', 'string'],
            // Base64 encoded serialized array of configurable option field values - base64_encode(serialize(array(configoptionid => dropdownoptionid, XXX => array(‘optionid’ => YYY, ‘qty’ => ZZZ)))) - XXX is the ID of the configurable option - YYY is the optionid found in tblhostingconfigoption.optionid - ZZZ is the quantity you want to use for that option
            'configoptions' => ['nullable', 'string'],
        ];

        $messages = [
            'firstpaymentamount.regex' => ':Attribute must be in decimal format: ### or ###.##',
            'recurringamount.regex' => ':Attribute must be in decimal format: ### or ###.##',
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $configoptions = $this->request->input("configoptions");
        $serviceid = $this->request->input("serviceid");
        $pid = $this->request->input("pid");
        $serverid = $this->request->input("serverid");
        $regdate = $this->request->input("regdate");
        $nextduedate = $this->request->input("nextduedate");
        $terminationDate = $this->request->input("terminationdate");
        $domain = $this->request->input("domain");
        $firstpaymentamount = $this->request->input("firstpaymentamount");
        $recurringamount = $this->request->input("recurringamount");
        $paymentmethod = $this->request->input("paymentmethod");
        $billingcycle = $this->request->input("billingcycle");
        $subscriptionid = $this->request->input("subscriptionid");
        $status = $this->request->input("status");
        $notes = $this->request->input("notes");
        $serviceusername = $this->request->input("serviceusername");
        $servicepassword = $this->request->input("servicepassword");
        $overideautosuspend = $this->request->input("overideautosuspend");
        $overidesuspenduntil = $this->request->input("overidesuspenduntil");
        $ns1 = $this->request->input("ns1");
        $ns2 = $this->request->input("ns2");
        $dedicatedip = $this->request->input("dedicatedip");
        $assignedips = $this->request->input("assignedips");
        $diskusage = $this->request->input("diskusage");
        $disklimit = $this->request->input("disklimit");
        $bwusage = $this->request->input("bwusage");
        $bwlimit = $this->request->input("bwlimit");
        $suspendreason = $this->request->input("suspendreason");
        $promoid = $this->request->input("promoid");
        $unset = $this->request->input("unset");
        $autorecalc = $this->request->input("autorecalc");
        $customfields = $this->request->input("customfields");
        $configoptions = $this->request->input("configoptions");

        $result = \App\Models\Hosting::find($serviceid);
        if (!$result) {
            return ResponseAPI::Error([
                'message' => "Service ID Not Found",
            ]);
        }
        $data = $result->toArray();
        $serviceid = $data["id"];
        $storedStatus = $data["domainstatus"];
        $completedDate = NULL;
        $updateqry = array();
        if ($pid) {
            $updateqry["packageid"] = $pid;
        }
        if ($serverid) {
            $updateqry["server"] = $serverid;
        }
        if ($regdate) {
            $updateqry["regdate"] = $regdate;
        }
        if ($nextduedate) {
            $updateqry["nextduedate"] = $nextduedate;
            $updateqry["nextinvoicedate"] = $nextduedate;
        }
        if ($domain) {
            $updateqry["domain"] = $domain;
        }
        if ($firstpaymentamount) {
            $updateqry["firstpaymentamount"] = $firstpaymentamount;
        }
        if ($recurringamount) {
            $updateqry["amount"] = $recurringamount;
        }
        if ($billingcycle) {
            $updateqry["billingcycle"] = $billingcycle;
        }
        if ($status && $status != $storedStatus) {
            switch ($status) {
                case "Terminated":
                case "Cancelled":
                    if ((!$terminationDate || $terminationDate == "0000-00-00") && !in_array($storedStatus, array("Terminated", "Cancelled"))) {
                        $terminationDate = date("Y-m-d");
                    }
                    $completedDate = "0000-00-00";
                    break;
                case "Completed":
                    $completedDate = \Carbon\Carbon::today()->toDateString();
                    $terminationDate = "0000-00-00";
                    break;
                default:
                    $terminationDate = "0000-00-00";
                    $completedDate = "0000-00-00";
            }
            $updateqry["domainstatus"] = $status;
        }
        if ($terminationDate) {
            if (!$status) {
                switch ($storedStatus) {
                    case "Terminated":
                    case "Cancelled":
                        if ($terminationDate == "0000-00-00") {
                            unset($terminationDate);
                        }
                        break;
                    default:
                        $terminationDate = "0000-00-00";
                }
            }
            if ($terminationDate) {
                $updateqry["termination_date"] = $terminationDate;
            }
        }
        if ($completedDate) {
            $updateqry["completed_date"] = $completedDate;
        }
        if ($serviceusername) {
            $updateqry["username"] = $serviceusername;
        }
        if ($servicepassword) {
            $updateqry["password"] = (new \App\Helpers\Pwd())->encrypt($servicepassword);
        }
        if ($subscriptionid) {
            $updateqry["subscriptionid"] = $subscriptionid;
        }
        if ($paymentmethod) {
            $updateqry["paymentmethod"] = $paymentmethod;
        }
        if ($promoid) {
            $updateqry["promoid"] = $promoid;
        }
        if ($overideautosuspend && $overideautosuspend != "off") {
            $updateqry["overideautosuspend"] = "1";
        } else {
            if ($overideautosuspend == "off") {
                $updateqry["overideautosuspend"] = "0";
            }
        }
        if ($overidesuspenduntil) {
            $updateqry["overidesuspenduntil"] = $overidesuspenduntil;
        }
        if ($ns1) {
            $updateqry["ns1"] = $ns1;
        }
        if ($ns2) {
            $updateqry["ns2"] = $ns2;
        }
        if ($dedicatedip) {
            $updateqry["dedicatedip"] = $dedicatedip;
        }
        if ($assignedips) {
            $updateqry["assignedips"] = $assignedips;
        }
        if ($notes) {
            $updateqry["notes"] = $notes;
        }
        if ($diskusage) {
            $updateqry["diskusage"] = $diskusage;
        }
        if ($disklimit) {
            $updateqry["disklimit"] = $disklimit;
        }
        if ($bwusage) {
            $updateqry["bwusage"] = $bwusage;
        }
        if ($bwlimit) {
            $updateqry["bwlimit"] = $bwlimit;
        }
        if (isset($lastupdate)) {
            $updateqry["lastupdate"] = $lastupdate;
        }
        if ($suspendreason) {
            $updateqry["suspendreason"] = $suspendreason;
        }
        $unsetAttributes = $this->request->input("unset");
        if (is_array($unsetAttributes) && !empty($unsetAttributes)) {
            $allowedVariables = array("domain", "serviceusername", "servicepassword", "subscriptionid", "ns1", "ns2", "dedicatedip", "assignedips", "notes", "suspendreason");
            foreach ($unsetAttributes as $unsetAttribute) {
                if (in_array($unsetAttribute, $allowedVariables)) {
                    switch ($unsetAttribute) {
                        case "serviceusername":
                            $unsetAttribute = "username";
                            break;
                        case "servicepassword":
                            $unsetAttribute = "password";
                            break;
                    }
                    $updateqry[$unsetAttribute] = "";
                }
            }
        }
        if (0 < count($updateqry)) {
            \App\Models\Hosting::where('id', $serviceid)->update($updateqry);
        }
        if ($customfields) {
            if (!is_array($customfields)) {
                $customfields = base64_decode($customfields);
                $customfields = (new \App\Helpers\Client())->safe_unserialize($customfields);
            }
            \App\Helpers\Customfield::saveCustomFields($serviceid, $customfields, "product", true);
        }
        if ($configoptions) {
            if (!is_array($configoptions)) {
                $configoptions = base64_decode($configoptions);
                $configoptions = (new \App\Helpers\Client())->safe_unserialize($configoptions);
            }
            foreach ($configoptions as $cid => $vals) {
                if (is_array($vals)) {
                    $oid = $vals["optionid"];
                    $qty = $vals["qty"];
                } else {
                    $oid = $vals;
                    $qty = 0;
                }
                $v = \App\Models\Hostingconfigoption::where('relid', $serviceid)->where('configid', $cid)->count();
                if ($v) {
                    \App\Models\Hostingconfigoption::where('relid', $serviceid)->where('configid', $cid)->update(array("optionid" => $oid, "qty" => $qty));
                } else {
                    \App\Models\Hostingconfigoption::insert(array("relid" => $serviceid, "configid" => $cid, "optionid" => $oid, "qty" => $qty));
                }
            }
        }
        if ($autorecalc) {
            if (!$pid) {
                $pid = $data["packageid"];
            }
            if (!$billingcycle) {
                $billingcycle = $data["billingcycle"];
            }
            if (!$promoid) {
                $promoid = $data["promoid"];
            }
            $recurringamount = \App\Helpers\ProcessInvoices::recalcRecurringProductPrice($serviceid, "", $pid, $billingcycle, "empty", $promoid);
            \App\Models\Hosting::where('id', $serviceid)->update(array("amount" => $recurringamount));
        }

        return ResponseAPI::Success([
            "serviceid" => $serviceid,
        ]);
    }
}
