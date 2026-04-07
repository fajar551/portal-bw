<?php

namespace App\Http\Controllers\API\Orders;

use Validator;
use DB, Auth;
use Orders, Service, Application, Cfg;
use App\Rules\FloatValidator;

// Models
use App\Models\Order;
use App\Models\Orderstatus;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Product;
use App\Models\Productgroup;
use App\Models\Addon;
use App\Models\Domain;
use App\Models\Upgrade;
use App\Models\Productconfigoption;
use App\Models\Productconfigoptionssub;
use App\Models\Promotion;
use App\Models\Pricing;
use App\Models\Customfield;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Helper
use ResponseAPI, ProductType, Format;
use Product as ProductHelper;
use Orders as OrdersHelper;
use Invoice as InvoiceHelper;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/**
 * @group Orders
 *
 * APIs for managing orders
 */
class OrdersController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * GetOrders
     *
     * Obtain orders matching the passed criteria
     */
    public function GetOrders()
    {
        $rules = [
            // The offset for the returned order data (default: 0). Example: 0
            'limitstart' => ['nullable', 'integer'],
            // The number of records to return (default: 25). Example: 25
            'limitnum' => ['nullable', 'integer'],
            // Find orders for a specific id
            'id' => ['nullable', 'integer'],
            // Find orders for a specific client id
            'userid' => ['nullable', 'integer'],
            // Find orders for a specific status
            'status' => ['nullable', 'string'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $id = $this->request->input('id');
        $userid = $this->request->input('userid');
        $status = $this->request->input('status');
        $limitstart = $this->request->input('limitstart') ?? 0;
        $limitnum = $this->request->input('limitnum') ?? 25;

        $page = $limitstart + 1;
        $mulai = ($page > 1) ? ($page * $limitnum) - $limitnum : 0;

        // filters
        $filters = [
            'id' => $id,
            'userid' => $userid,
            'status' => $status,
        ];

        $query = Order::query();
        $query->filter($filters);
        $totalresults = $query->count();
        $query->offset($mulai);
        $query->limit($limitnum);
        $results = $query->get();
        $apiresults = array("totalresults" => $totalresults, "startnumber" => $limitstart, "numreturned" => $results->count());
        foreach ($results->toArray() as $orderdata) {
            $orderid = $orderdata["id"];
            $userid = $orderdata["userid"];
            $fraudmodule = $orderdata["fraudmodule"];
            $fraudoutput = $orderdata["fraudoutput"];
            $currency = \App\Helpers\Format::getCurrency($userid);
            $orderdata["currencyprefix"] = $currency["prefix"];
            $orderdata["currencysuffix"] = $currency["suffix"];
            $frauddata = "";
            if ($fraudmodule) {
                $fraud = new \App\Module\Fraud();
                if ($fraud->load($fraudmodule)) {
                    $fraudresults = $fraud->processResultsForDisplay($orderid, $fraudoutput);
                    if (is_array($fraudresults)) {
                        foreach ($fraudresults as $key => $value) {
                            $frauddata .= (string) $key . " => " . $value . "\n";
                        }
                    }
                }
            }
            $orderdata["fraudoutput"] = $fraudoutput;
            $orderdata["frauddata"] = $frauddata;
            $lineitems = array();
            $result2 = \App\Models\Hosting::where("orderid", $orderid)->get();
            foreach ($result2->toArray() as $data) {
                $serviceid = $data["id"];
                $domain = $data["domain"];
                $billingcycle = $data["billingcycle"];
                $hostingstatus = $data["domainstatus"];
                $firstpaymentamount = \App\Helpers\Format::formatCurrency($data["firstpaymentamount"]);
                $packageid = $data["packageid"];
                $result3 = \App\Models\Product::selectRaw("tblproducts.name,tblproducts.type,tblproducts.welcomeemail,tblproducts.autosetup," . "tblproducts.servertype,tblproductgroups.name as group_name,tblproductgroups.id AS group_id")->where("tblproducts.id", $packageid)->join("tblproductgroups", "tblproducts.gid", "=", "tblproductgroups.id");
                $data = $result3;
                $groupname = \App\Models\Productgroup::getGroupName($data->value("group_id"), $data->value("group_name"));
                $productname = \App\Models\Product::getProductName($packageid, $data->value("name"));
                $producttype = $data->value("type");
                if ($producttype == "hostingaccount") {
                    $producttype = "Hosting Account";
                } else {
                    if ($producttype == "reselleraccount") {
                        $producttype = "Reseller Account";
                    } else {
                        if ($producttype == "server") {
                            $producttype = "Dedicated/VPS Server";
                        } else {
                            if ($producttype == "other") {
                                $producttype = "Other Product/Service";
                            }
                        }
                    }
                }
                $lineitems["lineitem"][] = array("type" => "product", "relid" => $serviceid, "producttype" => $producttype, "product" => $groupname . " - " . $productname, "domain" => $domain, "billingcycle" => $billingcycle, "amount" => $firstpaymentamount, "status" => $hostingstatus);
            }
            $predefinedaddons = array();
            // $result2 = select_query("tbladdons", "", "");
            $result2 = \App\Models\Addon::all();
            foreach ($result2->toArray() as $data) {
                $addon_id = $data["id"];
                $addon_name = $data["name"];
                $addon_welcomeemail = $data["welcomeemail"];
                $predefinedaddons[$addon_id] = array("name" => $addon_name, "welcomeemail" => $addon_welcomeemail);
            }
            $result2 = \App\Models\Hostingaddon::where("orderid", $orderid)->get();
            foreach ($result2->toArray() as $data) {
                $aid = $data["id"];
                $hostingid = $data["hostingid"];
                $addonid = $data["addonid"];
                $name = $data["name"];
                $billingcycle = $data["billingcycle"];
                $addonamount = $data["recurring"] + $data["setupfee"];
                $addonstatus = $data["status"];
                $regdate = $data["regdate"];
                $nextduedate = $data["nextduedate"];
                $addonamount = \App\Helpers\Format::formatCurrency($addonamount);
                if (!$name) {
                    $name = $predefinedaddons[$addonid]["name"];
                }
                $lineitems["lineitem"][] = array("type" => "addon", "relid" => $aid, "producttype" => "Addon", "product" => $name, "domain" => "", "billingcycle" => $billingcycle, "amount" => $addonamount, "status" => $addonstatus);
            }
            $result2 = \App\Models\Domain::where("orderid", $orderid)->get();
            foreach ($result2->toArray() as $data) {
                $domainid = $data["id"];
                $type = $data["type"];
                $domain = $data["domain"];
                $registrationperiod = $data["registrationperiod"];
                $status = $data["status"];
                $regdate = $data["registrationdate"];
                $nextduedate = $data["nextduedate"];
                $domainamount = \App\Helpers\Format::formatCurrency($data["firstpaymentamount"]);
                $domainregistrar = $data["registrar"];
                $dnsmanagement = $data["dnsmanagement"];
                $emailforwarding = $data["emailforwarding"];
                $idprotection = $data["idprotection"];
                $lineitems["lineitem"][] = array("type" => "domain", "relid" => $domainid, "producttype" => "Domain", "product" => $type, "domain" => $domain, "billingcycle" => $registrationperiod, "amount" => $domainamount, "status" => $status, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection);
            }
            $renewals = $orderdata["renewals"];
            if ($renewals) {
                $renewals = explode(",", $renewals);
                foreach ($renewals as $renewal) {
                    $renewal = explode("=", $renewal);
                    list($domainid, $registrationperiod) = $renewal;
                    $renewalResult = \App\Models\Domain::findOrFail($domainid);
                    $data = $renewalResult->toArray();
                    $domainid = $data["id"];
                    $type = $data["type"];
                    $domain = $data["domain"];
                    $registrar = $data["registrar"];
                    $status = $data["status"];
                    $regdate = $data["registrationdate"];
                    $nextduedate = $data["nextduedate"];
                    $domainamount = \App\Helpers\Format::formatCurrency($data["recurringamount"]);
                    $domainregistrar = $data["registrar"];
                    $dnsmanagement = $data["dnsmanagement"];
                    $emailforwarding = $data["emailforwarding"];
                    $idprotection = $data["idprotection"];
                    $lineitems["lineitem"][] = array("type" => "renewal", "relid" => $domainid, "producttype" => "Domain", "product" => "Renewal", "domain" => $domain, "billingcycle" => $registrationperiod, "amount" => $domainamount, "status" => $status, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection);
                }
            }
            $result2 = \App\Models\Upgrade::where("orderid", $orderid)->get();
            foreach ($result2->toArray() as $data) {
                $upgradeid = $data["id"];
                $type = $data["type"];
                $relid = $data["relid"];
                $originalvalue = $data["originalvalue"];
                $newvalue = $data["newvalue"];
                $upgradeamount = \App\Helpers\Format::formatCurrency($data["amount"]);
                $newrecurringamount = $data["newrecurringamount"];
                $status = $data["status"];
                $paid = $data["paid"];
                if ($type == "package") {
                    $oldpackagename = \App\Models\Product::getProductName($originalvalue);
                    $newvalue = explode(",", $newvalue);
                    $newpackageid = $newvalue[0];
                    $newpackagename = \App\Models\Product::getProductName($newpackageid);
                    $details = "Package Upgrade: " . $oldpackagename . " => " . $newpackagename . "<br>";
                } else {
                    if ($type == "configoptions") {
                        $tempvalue = explode("=>", $originalvalue);
                        list($configid, $oldoptionid) = $tempvalue;
                        $result2 = \App\Models\Productconfigoption::findOrFail($configid);
                        $data = $result2->toArray();
                        $configname = $data["optionname"];
                        $optiontype = $data["optiontype"];
                        if ($optiontype == 1 || $optiontype == 2) {
                            $result2 = \App\Models\Productconfigoptionssub::findOrFail($oldoptionid);
                            $data = $result2->toArray();
                            $oldoptionname = $data["optionname"];
                            $result2 = \App\Models\Productconfigoptionssub::findOrFail($newvalue);
                            $data = $result2->toArray();
                            $newoptionname = $data["optionname"];
                        } else {
                            if ($optiontype == 3) {
                                if ($oldoptionid) {
                                    $oldoptionname = "Yes";
                                    $newoptionname = "No";
                                } else {
                                    $oldoptionname = "No";
                                    $newoptionname = "Yes";
                                }
                            } else {
                                if ($optiontype == 4) {
                                    $result2 = \App\Models\Productconfigoptionssub::where("configid", $configid)->first();
                                    $data = $result2->toArray();
                                    $optionname = $data["optionname"];
                                    $oldoptionname = $oldoptionid;
                                    $newoptionname = $newvalue . " x " . $optionname;
                                }
                            }
                        }
                        $details = (string) $configname . ": " . $oldoptionname . " => " . $newoptionname . "<br>";
                    }
                }
                $lineitems["lineitem"][] = array("type" => "upgrade", "relid" => $relid, "producttype" => "Upgrade", "product" => $details, "domain" => "", "billingcycle" => "", "amount" => $upgradeamount, "status" => $status);
            }
            $apiresults["orders"]["order"][] = array_merge($orderdata, array("lineitems" => $lineitems));
        }

        return ResponseAPI::Success($apiresults);
    }

    /**
     * GetOrderStatuses
     *
     * Retrieve Order Status and number in those statuses
     */
    public function GetOrderStatuses()
    {
        $statuses = array("Pending" => 0, "Active" => 0, "Fraud" => 0, "Cancelled" => 0);

        $query = Order::query();
        $query->select(DB::raw('count(*) as count, status'));
        $query->groupBy('status');
        $results = $query->get();
        foreach ($results as $order) {
            $statuses[$order->status] = $order->count;
        }

        $statusresponse = [];
        foreach ($statuses as $status => $ordercount) {
            $statusresponse[] = array("title" => $status, "count" => $ordercount);
        }

        $response = [
            'status' => $statusresponse,
        ];

        return ResponseAPI::Success([
            'totalresults' => 4,
            'statuses' => $results->count() > 0 ? $response : [],
        ]);
    }

    /**
     * GetPromotions
     *
     * Obtain promotions matching the passed criteria
     */
    public function GetPromotions()
    {
        $rules = [
            // Retrieve a specific promotion code. Do not pass to retrieve all
            'code' => ['nullable', 'string'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $code = $this->request->input('code');

        // filters
        $filters = [
            'code' => $code,
        ];

        $query = Promotion::query();
        $query->filter($filters);
        $totalresults = $query->count();
        $results = $query->get();

        $response = [
            'promotion' => $results,
        ];

        return ResponseAPI::Success([
            'totalresults' => $totalresults,
            'promotions' => $results->count() > 0 ? $response : [],
        ]);
    }

    /**
     * PendingOrder
     *
     * Sets an order, and all associated order items to Pending status
     */
    public function PendingOrder()
    {
        $rules = [
            // The order id to be accepted
            'orderid' => ['required', 'integer'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');

        $order = Order::where('id', $orderid)->first();

        if (!$order) {
            return ResponseAPI::Error([
                'message' => 'Order ID Not Found',
            ]);
        }

        // change order status
        OrdersHelper::ChangeOrderStatus($orderid, "Pending");

        return ResponseAPI::Success();
    }

    /**
     * GetProducts
     *
     * Retrieve configured products matching provided criteria
     * <aside class="notice"><b>NOTE:</b> This API method is designed to be used in the building of custom order forms.
     * As a result, only custom fields that have the ‘Show on Order Form’ setting enabled
     * will be returned for a given product.</aside>
     */
    public function GetProducts()
    {
        $rules = [
            // Obtain a specific product id configuration. Can be a list of ids comma separated. Example: 1,2,5
            'pid' => ['nullable', 'string'],
            // Retrieve products in a specific group id
            'gid' => ['nullable', 'integer'],
            // Retrieve products utilising a specific module
            'module' => ['nullable', 'string'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        global $currency;
        $currency = \App\Helpers\Format::getCurrency();
        $pid = $this->request->input("pid");
        $gid = $this->request->input("gid");
        $module = $this->request->input("module");
        $where = array();
        if ($pid) {
            if (is_numeric($pid)) {
                $where[] = "tblproducts.id=" . (int) $pid;
            } else {
                $pids = array();
                foreach (explode(",", $pid) as $p) {
                    $p = (int) trim($p);
                    if ($p) {
                        $pids[] = $p;
                    }
                }
                if ($pids) {
                    $where[] = "tblproducts.id IN (" . implode(",", $pids) . ")";
                }
            }
        }
        if ($gid) {
            $where[] = "gid=" . (int) $gid;
        }
        if ($module && preg_match("/^[a-zA-Z0-9_\\.\\-]*\$/", $module)) {
            $where[] = "servertype='" . \App\Helpers\Database::db_escape_string($module) . "'";
        }
        $result = \App\Models\Product::query();
        $result = $result->selectRaw("tblproducts.*");
        if (count($where)) {
            $result = $result->whereRaw(implode(" AND ", $where));
        }
        $result = $result->orderBy("tblproductgroups.order", "ASC");
        $result = $result->orderBy("tblproductgroups.id", "ASC");
        $result = $result->orderBy("tblproducts.order", "ASC");
        $result = $result->orderBy("tblproducts.id", "ASC");
        $result = $result->join("tblproductgroups", "tblproducts.gid", "=", "tblproductgroups.id");
        $result = $result->get();
        $apiresults = array("result" => "success", "totalresults" => $result->count());
        foreach ($result->toArray() as $data) {
            $pid = $data["id"];
            $productarray = array("pid" => $data["id"], "gid" => $data["gid"], "type" => $data["type"], "name" => $data["name"], "description" => $data["description"], "module" => $data["servertype"], "paytype" => $data["paytype"]);
            if ($language = $this->request->input("language")) {
                $productarray["translated_name"] = \App\Models\Product::getProductName($data["id"], $data["name"], $language);
                $productarray["translated_description"] = \App\Models\Product::getProductDescription($data["id"], $data["description"], $language);
            }
            if ($data["stockcontrol"]) {
                $productarray["stockcontrol"] = "true";
                $productarray["stocklevel"] = $data["qty"];
            }
            $result2 = \App\Models\Pricing::selectRaw("tblcurrencies.code,tblcurrencies.prefix,tblcurrencies.suffix,tblpricing.msetupfee,tblpricing.qsetupfee,tblpricing.ssetupfee,tblpricing.asetupfee,tblpricing.bsetupfee,tblpricing.tsetupfee,tblpricing.monthly,tblpricing.quarterly,tblpricing.semiannually,tblpricing.annually,tblpricing.biennially,tblpricing.triennially")
                ->where(array("type" => "product", "relid" => $pid))
                ->orderBy("code", "ASC")
                ->join("tblcurrencies", "tblcurrencies.id","=","tblpricing.currency")
                ->get();
            foreach ($result2->toArray() as $data) {
                $code = $data["code"];
                unset($data["code"]);
                $productarray["pricing"][$code] = $data;
            }
            $customfieldsdata = array();
            $customfields = \App\Helpers\Customfield::getCustomFields("product", $pid, "", "", "on");
            foreach ($customfields as $field) {
                $customfieldsdata[] = array("id" => $field["id"], "name" => $field["name"], "description" => $field["description"], "required" => $field["required"]);
            }
            $productarray["customfields"]["customfield"] = $customfieldsdata;
            $configoptiondata = array();
            $configurableoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, array(), "", "", "", true);
            foreach ($configurableoptions as $option) {
                $options = array();
                foreach ($option["options"] as $op) {
                    $pricing = array();
                    $result4 = \App\Models\Pricing::selectRaw("code,msetupfee,qsetupfee,ssetupfee,asetupfee,bsetupfee,tsetupfee,monthly,quarterly,semiannually,annually,biennially,triennially")
                        ->where(array("type" => "configoptions", "relid" => $op["id"]))
                        ->join("tblcurrencies", "tblcurrencies.id","=","tblpricing.currency")
                        ->get();
                    foreach ($result4->toArray() as $oppricing) {
                        $currcode = $oppricing["code"];
                        unset($oppricing["code"]);
                        $pricing[$currcode] = $oppricing;
                    }
                    $options["option"][] = array("id" => $op["id"], "name" => $op["name"], "rawName" => $op["rawName"] ?? "", "recurring" => $op["recurring"], "required" => $op["required"] ?? "", "pricing" => $pricing);
                }
                $configoptiondata[] = array("id" => $option["id"], "name" => $option["optionname"], "type" => $option["optiontype"], "options" => $options);
            }
            $productarray["configoptions"]["configoption"] = $configoptiondata;
            $apiresults["products"]["product"][] = $productarray;
        }

        return ResponseAPI::Success($apiresults);
    }

    /**
     * CancelOrder
     *
     * Cancel a Pending Order
     */
    public function CancelOrder()
    {
        $orderTable = (new Order)->getTableName();

        $rules = [
            // The ID of the pending order
            'orderid' => [
                'required',
                'integer',
                Rule::exists($orderTable, 'id')->where(function($query) {
                    $query->where('status', Order::PENDING);
                }),
            ],
            // Attempt to cancel the subscription associated with the products
            'cancelsub' => ['nullable', 'boolean'],
            // Set to true to stop the invoice payment email being sent if the invoice becomes paid
            'noemail' => ['nullable', 'boolean'],
        ];

        $messages = [
            'orderid.exists' => "Order ID not found or Status not Pending",
        ];

        $validator = Validator::make($this->request->all(), $rules, $messages);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');
        $noemail = (bool) $this->request->input('noemail');
        $cancelSubscription = (bool) $this->request->input('cancelsub');

        $msg = OrdersHelper::ChangeOrderStatus($orderid, "Cancelled", $cancelSubscription);
        if ($msg == "subcancelfailed") {
            return ResponseAPI::Error([
                'message' => 'Subscription Cancellation Failed - Please check the gateway log for further information',
            ]);
        } else {
            return ResponseAPI::Success();
        }
    }

    /**
     * DeleteOrder
     *
     * Deletes a cancelled or fraud order.
     *
     * Removes an order from the system. This cannot be undone.
     * This will remove all items associated with the order (services, addons, domains, invoices etc)
     */
    public function DeleteOrder()
    {
        $rules = [
            // The order to be deleted
            'orderid' => [
                'required',
                'integer',
                'exists:App\Models\Order,id',
            ],
        ];

        $messages = [
            'orderid.exists' => "Order ID not found",
        ];

        $validator = Validator::make($this->request->all(), $rules, $messages);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');

        if (!Orders::CanOrderBeDeleted($orderid)) {
            return ResponseAPI::Error([
                'message' => 'The order status must be in Cancelled or Fraud to be deleted',
            ]);
        }

        Orders::DeleteOrder($orderid);

        return ResponseAPI::Success();
    }

    /**
     * FraudOrder
     *
     * Marks an order as fraudulent.
     */
    public function FraudOrder()
    {
        $rules = [
            // The Order ID to set as fraud
            'orderid' => ['required', 'integer'],
            // Pass as true to cancel any PayPal Subscription(s) associated with the products & services that belong to the given order.
            'cancelsub' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');
        $cancelSubscription = (bool) $this->request->input('cancelsub');

        $order = Order::where('id', $orderid)->first();

        if (!$order) {
            return ResponseAPI::Error([
                'message' => 'Order ID Not Found',
            ]);
        }

        // change order status
        $msg = OrdersHelper::ChangeOrderStatus($orderid, "Fraud", $cancelSubscription);
        if ($msg == "subcancelfailed") {
            return ResponseAPI::Error([
                'message' => 'Subscription Cancellation Failed - Please check the gateway log for further information',
            ]);
        }

        return ResponseAPI::Success();
    }

    /**
     * AcceptOrder
     *
     * Accepts a pending order
     */
    public function AcceptOrder()
    {
        $rules = [
            // The order id to be accepted
            'orderid' => ['required', 'integer'],
            // The specific server to assign to products within the order
            'serverid' => ['nullable', 'integer'],
            // The specific username to assign to products within the order
            'serviceusername' => ['nullable', 'string'],
            // The specific password to assign to products within the order
            'servicepassword' => ['nullable', 'string'],
            // The specific registrar to assign to domains within the order
            'registrar' => ['nullable', 'string'],
            // Send the request to the registrar to register the domain.
            'sendregistrar' => ['nullable', 'boolean'],
            // Send the request to the product module to activate the service. This can override the product configuration.
            'autosetup' => ['nullable', 'boolean'],
            // Send any automatic emails. This can be Product Welcome, Domain Renewal, Domain Transfer etc.
            'sendemail' => ['nullable', 'boolean'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');
        $serverid = $this->request->input('serverid');
        $serviceusername = $this->request->input('serviceusername');
        $servicepassword = $this->request->input('servicepassword');
        $registrar = $this->request->input('registrar');
        $sendregistrar = (bool) $this->request->input('sendregistrar');
        $autosetup = (bool) $this->request->input('autosetup');
        $sendemail = (bool) $this->request->input('sendemail');

        $order = Order::where('id', $orderid)->where('status', 'Pending')->first();

        if (!$order) {
            return ResponseAPI::Error([
                'message' => 'Order ID not found or Status not Pending',
            ]);
        }

        $ordervars = array();
        if ($serverid) {
            $ordervars["api"]["serverid"] = $serverid;
        }
        if ($serviceusername) {
            $ordervars["api"]["username"] = $serviceusername;
        }
        if ($servicepassword) {
            $ordervars["api"]["password"] = $servicepassword;
        }
        if ($registrar) {
            $ordervars["api"]["registrar"] = $registrar;
        }
        if ($sendregistrar) {
            $ordervars["api"]["sendregistrar"] = $sendregistrar;
        }
        if ($autosetup) {
            $ordervars["api"]["autosetup"] = $autosetup;
        }
        if ($sendemail) {
            $ordervars["api"]["sendemail"] = $sendemail;
        }

        // accept order
        OrdersHelper::AcceptOrder($orderid, $ordervars);

        return ResponseAPI::Success();
    }

    /**
     * AddOrder
     *
     * Adds an order to a client. For more flow control, this method ignores the
     * "Automatically setup the product as soon as an order is placed."
     * option. When you call this method, you must make a subsequent explicit call to AcceptOrder.
     *
     */
    public function AddOrder()
    {
        // $auth = Auth::user();
        // $authid = $auth ? $auth->id : 0;

        $rules = [
            // Specific user id
            'clientid' => ['required', 'integer'],
            // The payment method for the order in the system format. eg. paypal, mailin. Example: mailin
            'paymentmethod' => ['required', 'string'],
            // The array of product ids to add the order for
            'pid' => ['nullable', 'array'],
            // The array of product ids to add the order for
            'pid.*' => ['nullable', 'integer', 'distinct'],

            // The array of domain names associated with the products/domains
            'domain' => ['nullable', 'array'],
            // The array of domain names associated with the products/domains
            'domain.*' => ['nullable', 'string', 'distinct'],

            // The array of billing cycles for the products
            'billingcycle' => ['nullable', 'array'],
            // The array of billing cycles for the products
            'billingcycle.*' => ['nullable', 'string', 'distinct'],

            // For domain registrations, an array of register or transfer values
            'domaintype' => ['nullable', 'array'],
            // For domain registrations, an array of register or transfer values
            'domaintype.*' => ['nullable', 'string', 'distinct'],

            // For domain registrations, the registration periods for the domains in the order
            'regperiod' => ['nullable', 'array'],
            // For domain registrations, the registration periods for the domains in the order
            'regperiod.*' => ['nullable', 'integer', 'distinct'],

            // For IDN domain registrations. The language code for the domain being registered
            'idnlanguage' => ['nullable', 'array'],
            // For IDN domain registrations. The language code for the domain being registered
            'idnlanguage.*' => ['nullable', 'string', 'distinct'],

            // For domain transfers. The epp codes for the domains being transferred in the order
            'eppcode' => ['nullable', 'array'],
            // For domain transfers. The epp codes for the domains being transferred in the order
            'eppcode.*' => ['nullable', 'string', 'distinct'],

            // The first nameserver to apply to all domains in the order
            'nameserver1' => ['nullable', 'string'],
            // The second nameserver to apply to all domains in the order
            'nameserver2' => ['nullable', 'string'],
            // The third nameserver to apply to all domains in the order
            'nameserver3' => ['nullable', 'string'],
            // The fourth nameserver to apply to all domains in the order
            'nameserver4' => ['nullable', 'string'],
            // The fifth nameserver to apply to all domains in the order
            'nameserver5' => ['nullable', 'string'],

            // an array of base64 encoded serialized array of product custom field values
            'customfields' => ['nullable', 'array'],
            // an array of base64 encoded serialized array of product custom field values
            'customfields.*' => ['nullable', 'string', 'distinct'],

            // an array of base64 encoded serialized array of product configurable options values
            'configoptions' => ['nullable', 'array'],
            // an array of base64 encoded serialized array of product configurable options values
            'configoptions.*' => ['nullable', 'string', 'distinct'],

            // Override the price of the product being ordered
            'priceoverride' => ['nullable', 'array'],
            // Override the price of the product being ordered
            'priceoverride.*' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/', 'distinct'],

            // The promotion code to apply to the order
            'promocode' => ['nullable', 'string'],
            // Should the promotion apply to the order even without matching promotional products
            'promooverride' => ['nullable', 'boolean'],
            // The affiliate id to associate with the order
            'affid' => ['nullable', 'integer'],
            // Set to true to suppress the invoice generating for the whole order
            'noinvoice' => ['nullable', 'boolean'],
            // Set to true to suppress the Invoice Created email being sent for the order
            'noinvoiceemail' => ['nullable', 'boolean'],
            // Set to true to suppress the Order Confirmation email being sent
            'noemail' => ['nullable', 'boolean'],

            // A comma separated list of addons to create on order with the products
            'addons' => ['nullable', 'array'],
            // A comma separated list of addons to create on order with the products
            'addons.*' => ['nullable', 'string', 'distinct'],

            // The hostname of the server for VPS/Dedicated Server orders
            'hostname' => ['nullable', 'array'],
            // The hostname of the server for VPS/Dedicated Server orders
            'hostname.*' => ['nullable', 'string', 'distinct'],

            // The first nameserver prefix for the VPS/Dedicated server. Eg. ns1 in ns1.hostname.com
            'ns1prefix' => ['nullable', 'array'],
            // The first nameserver prefix for the VPS/Dedicated server. Eg. ns1 in ns1.hostname.com
            'ns1prefix.*' => ['nullable', 'string', 'distinct'],

            // The second nameserver prefix for the VPS/Dedicated server. Eg. ns2 in ns2.hostname.com
            'ns2prefix' => ['nullable', 'array'],
            // The second nameserver prefix for the VPS/Dedicated server. Eg. ns2 in ns2.hostname.com
            'ns2prefix.*' => ['nullable', 'string', 'distinct'],

            // The desired root password for the VPS/Dedicated server.
            'rootpw' => ['nullable', 'array'],
            // The desired root password for the VPS/Dedicated server.
            'rootpw.*' => ['nullable', 'string', 'distinct'],

            // The id of the contact, associated with the client, that should apply to all domains in the order
            'contactid' => ['nullable', 'integer'],

            // Add DNS Management to the Domain Order
            'dnsmanagement' => ['nullable', 'array'],
            // Add DNS Management to the Domain Order
            'dnsmanagement.*' => ['nullable', 'boolean', 'distinct'],

            // an array of base64 encoded serialized array of TLD Specific Field Values
            'domainfields' => ['nullable', 'array'],
            // an array of base64 encoded serialized array of TLD Specific Field Values
            'domainfields.*' => ['nullable', 'string', 'distinct'],

            // Add Email Forwarding to the Domain Order
            'emailforwarding' => ['nullable', 'array'],
            // Add Email Forwarding to the Domain Order
            'emailforwarding.*' => ['nullable', 'boolean', 'distinct'],

            // Add ID Protection to the Domain Order
            'idprotection' => ['nullable', 'array'],
            // Add ID Protection to the Domain Order
            'idprotection.*' => ['nullable', 'boolean', 'distinct'],

            // Override the price of the registration price on the domain being ordered
            'domainpriceoverride' => ['nullable', 'array'],
            // Override the price of the registration price on the domain being ordered
            'domainpriceoverride.*' => ['nullable','regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/', 'distinct'],

            // Override the price of the renewal price on the domain being ordered
            'domainrenewoverride' => ['nullable', 'array'],
            // Override the price of the renewal price on the domain being ordered
            'domainrenewoverride.*' => ['nullable','regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/', 'distinct'],

            // A name -> value array of $domainName -> $renewalPeriod renewals to add an order for
            'domainrenewals' => ['nullable', 'array'],

            // The ip address to associate with the order
            'clientip' => ['nullable', 'string'],
            // The Addon ID for an Addon Only Order
            'addonid' => ['nullable', 'integer'],
            // The service ID for the addon only order
            'serviceid' => ['nullable', 'integer'],

            // An Array of addon ids for an Addon Only Order
            'addonids' => ['nullable', 'array'],
            // An Array of addon ids for an Addon Only Order
            'addonids.*' => ['nullable', 'integer', 'distinct'],

            // An array of service ids to associate the addons for an Addon Only order
            'serviceids' => ['nullable', 'array'],
            // An array of service ids to associate the addons for an Addon Only order
            'serviceids.*' => ['nullable', 'integer', 'distinct'],
        ];

        $messages = [
            'priceoverride.*.regex' => ':Attribute must be in decimal format: ### or ###.##',
            'domainpriceoverride.*.regex' => ':Attribute must be in decimal format: ### or ###.##',
            'domainrenewoverride.*.regex' => ':Attribute must be in decimal format: ### or ###.##',
        ];

        $validator = Validator::make($this->request->all(), $rules, $messages);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderid = $this->request->input('orderid');
        $clientid = $this->request->input('clientid');
        $paymentmethod = $this->request->input('paymentmethod');
        $pid = $this->request->input('pid');
        $domain = $this->request->input('domain');
        $billingcycle = $this->request->input('billingcycle');
        $domaintype = $this->request->input('domaintype');
        $regperiod = $this->request->input('regperiod');
        $idnlanguage = $this->request->input('idnlanguage');
        $eppcode = $this->request->input('eppcode');
        $nameserver1 = $this->request->input('nameserver1') ?? "";
        $nameserver2 = $this->request->input('nameserver2') ?? "";
        $nameserver3 = $this->request->input('nameserver3') ?? "";
        $nameserver4 = $this->request->input('nameserver4') ?? "";
        $nameserver5 = $this->request->input('nameserver5') ?? "";
        $customfields = $this->request->input('customfields');
        $configoptions = $this->request->input('configoptions');
        $priceoverride = $this->request->input('priceoverride');
        $promocode = $this->request->input('promocode') ?? "";
        $promooverride = $this->request->input('promooverride');
        $affid = $this->request->input('affid');
        $noinvoice = $this->request->input('noinvoice');
        $noinvoiceemail = $this->request->input('noinvoiceemail');
        $noemail = $this->request->input('noemail');
        $addons = $this->request->input('addons');
        $hostname = $this->request->input('hostname');
        $ns1prefix = $this->request->input('ns1prefix');
        $ns2prefix = $this->request->input('ns2prefix');
        $rootpw = $this->request->input('rootpw');
        $contactid = $this->request->input('contactid');
        $dnsmanagement = $this->request->input('dnsmanagement');
        $domainfields = $this->request->input('domainfields');
        $emailforwarding = $this->request->input('emailforwarding');
        $idprotection = $this->request->input('idprotection');
        $domainpriceoverride = $this->request->input('domainpriceoverride');
        $domainrenewoverride = $this->request->input('domainrenewoverride');
        $domainrenewals = $this->request->input('domainrenewals');
        $clientip = $this->request->input('clientip');
        $addonid = $this->request->input('addonid');
        $serviceid = $this->request->input('serviceid');
        $addonids = $this->request->input('addonids') ?? [];
        $serviceids = $this->request->input('serviceids') ?? [];
        $notes = $this->request->input('notes') ?? "";

        DB::beginTransaction();

        try {
            try {
                $client = \App\Models\Client::findOrFail($clientid);
            } catch (\Exception $e) {
                return ResponseAPI::Error([
                    'message' => 'Client ID Not Found',
                ]);
            }

            $userid = (int) $client->id;
            $blockedStatus = array("Closed");

            if (in_array($client->status, $blockedStatus)) {
                return ResponseAPI::Error([
                    'message' => "Unable to add order when client status is " . $client->status,
                ]);
            }

            // $gatewaysarray = array();
            // $result = \App\Models\Paymentgateway::where('setting', 'name')->get();
            // foreach ($result->toArray() as $data) {
            //     $gatewaysarray[] = $data["gateway"];
            // }
            $gatewaysarray = \App\Helpers\Gateway::getGatewaysArray();
            if (!in_array(strtolower($paymentmethod), array_keys($gatewaysarray))) {
                return ResponseAPI::Error([
                    'message' => "Invalid Payment Method. Valid options include " . implode(",", $gatewaysarray),
                ]);
            }

            $remote_ip = $this->request->ip();
            if ($clientip) {
                $remote_ip = $clientip;
            }
            // $this->request->session()->put('uid', $userid);
            Auth::guard('web')->loginUsingId($userid);

            $currency = \App\Helpers\Format::getCurrency($userid);
            $sessionCart = [];
            $this->request->session()->put('cart', []);
            $this->request->session()->put('cart.products', []);
            if (is_array($pid)) {
                $productarray = [];
                foreach ($pid as $i => $prodid) {
                    if ($prodid) {
                        $isProductExists = \App\Models\Product::find($prodid);
                        if (!$isProductExists) {
                            throw new \Exception("Product with id $prodid not found");
                            break;
                        }
                        $proddomain = isset($domain[$i]) ? $domain[$i] : "";
                        $prodbillingcycle = isset($billingcycle[$i]) ? $billingcycle[$i] : "";
                        $configoptionsarray = array();
                        $customfieldsarray = array();
                        $domainfieldsarray = array();
                        $addonsarray = array();
                        if (isset($addons[$i])) {
                            $addonsarray = explode(",", $addons[$i]);
                        }
                        // validate addonid
                        foreach ($addonsarray as $key => $aid) {
                            if (!Addon::find($aid)) {
                                throw new \Exception("Addon with id $aid not found");
                                break;
                            }
                        }
                        if (isset($configoptions[$i])) {
                            $configoptionsarray = (new \App\Helpers\Client())->safe_unserialize(base64_decode($configoptions[$i]));
                        }
                        if (isset($customfields[$i])) {
                            $customfieldsarray = (new \App\Helpers\Client())->safe_unserialize(base64_decode($customfields[$i]));
                        }
                        $productarray = array(
                            "pid" => $prodid,
                            "domain" => $proddomain,
                            "billingcycle" => $prodbillingcycle,
                            "server" => isset($hostname[$i]) || isset($ns1prefix[$i]) || isset($ns2prefix[$i]) || isset($rootpw[$i]) ? array(
                                "hostname" => $hostname[$i],
                                "ns1prefix" => $ns1prefix[$i] ?? "",
                                "ns2prefix" => $ns2prefix[$i] ?? "",
                                "rootpw" => $rootpw[$i]
                            ) : "",
                            "configoptions" => $configoptionsarray,
                            "customfields" => $customfieldsarray,
                            "addons" => $addonsarray,
                        );
                        if (isset($priceoverride[$i]) && strlen($priceoverride[$i])) {
                            $productarray["priceoverride"] = $priceoverride[$i];
                        }
                        // $_SESSION["cart"]["products"][] = $productarray;
                        // $this->request->session()->put('cart', [
                        //     'products' => $productarray,
                        // ]);
                    }
                }
                $this->request->session()->push('cart.products', $productarray);
            } else {
                if ($pid) {
                    $isProductExists = \App\Models\Product::find($pid);
                    if (!$isProductExists) {
                        throw new \Exception("Product with id $pid not found");
                    }
                    $configoptionsarray = array();
                    $customfieldsarray = array();
                    $domainfieldsarray = array();
                    $addonsarray = array();
                    if ($addons) {
                        $addonsarray = explode(",", $addons);
                    }
                    // validate addonid
                    foreach ($addonsarray as $key => $aid) {
                        if (!Addon::find($aid)) {
                            throw new \Exception("Addon with id $aid not found");
                            break;
                        }
                    }
                    if ($configoptions) {
                        $configoptions = base64_decode($configoptions);
                        $configoptionsarray = (new \App\Helpers\Client())->safe_unserialize($configoptions);
                    }
                    if ($customfields) {
                        $customfields = base64_decode($customfields);
                        $customfieldsarray = (new \App\Helpers\Client())->safe_unserialize($customfields);
                    }
                    $productarray = array("pid" => $pid, "domain" => $domain, "billingcycle" => $billingcycle, "server" => $hostname || $ns1prefix || $ns2prefix || $rootpw ? array("hostname" => $hostname, "ns1prefix" => $ns1prefix, "ns2prefix" => $ns2prefix, "rootpw" => $rootpw) : "", "configoptions" => $configoptionsarray, "customfields" => $customfieldsarray, "addons" => $addonsarray);
                    if (strlen($priceoverride)) {
                        $productarray["priceoverride"] = $priceoverride;
                    }
                    // $this->request->session()->put('cart', [
                    //     'products' => $productarray,
                    // ]);
                    // $_SESSION["cart"]["products"][] = $productarray;
                    $this->request->session()->push('cart.products', $productarray);
                }
            }



            $this->request->session()->put('cart.domains', []);
            if (is_array($domaintype)) {
                $domainArray = [];
                foreach ($domaintype as $i => $type) {
                    if ($type) {
                        if (isset($domainfields[$i])) {
                            $domainfields[$i] = base64_decode($domainfields[$i]);
                            $domainfieldsarray[$i] = (new \App\Helpers\Client())->safe_unserialize($domainfields[$i]);
                        }
                        $domainArray = array(
                            "type" => $type,
                            "domain" => $domain[$i] ?? "",
                            "regperiod" => $regperiod[$i] ?? "",
                            "dnsmanagement" => $dnsmanagement[$i] ?? 0,
                            "emailforwarding" => $emailforwarding[$i] ?? 0,
                            "idprotection" => $idprotection[$i] ?? 0,
                            "eppcode" => $eppcode[$i] ?? "",
                            "fields" => $domainfieldsarray[$i] ?? "",
                        );
                        if (isset($domainpriceoverride[$i]) && 0 < strlen($domainpriceoverride[$i])) {
                            $domainArray["domainpriceoverride"] = $domainpriceoverride[$i];
                        }
                        if (isset($domainrenewoverride[$i]) && 0 < strlen($domainrenewoverride[$i])) {
                            $domainArray["domainrenewoverride"] = $domainrenewoverride[$i];
                        }
                        // $_SESSION["cart"]["domains"][] = $domainArray;
                        // $this->request->session()->put('cart', [
                        //     'domains' => $domainArray,
                        // ]);
                    }
                }
                $this->request->session()->push('cart.domains', $domainArray);
            } else {
                if ($domaintype) {
                    if ($domainfields) {
                        $domainfields = base64_decode($domainfields);
                        $domainfieldsarray = (new \App\Helpers\Client())->safe_unserialize($domainfields);
                    }
                    $domainArray = array("type" => $domaintype, "domain" => $domain, "regperiod" => $regperiod, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppcode" => $eppcode, "fields" => $domainfieldsarray);
                    if (isset($domainpriceoverride) && 0 < strlen($domainpriceoverride)) {
                        $domainArray["domainpriceoverride"] = $domainpriceoverride;
                    }
                    if (isset($domainrenewoverride) && 0 < strlen($domainrenewoverride)) {
                        $domainArray["domainrenewoverride"] = $domainrenewoverride;
                    }
                    // $_SESSION["cart"]["domains"][] = $domainArray;
                    // $this->request->session()->put('cart', [
                    //     'domains' => $domainArray,
                    // ]);
                    $this->request->session()->push('cart.domains', $domainArray);
                }
            }

            $this->request->session()->put('cart.addons', []);
            if ($addonid) {
                $addonid = Addon::find($addonid);
                if (!$addonid) {
                    return ResponseAPI::Error([
                        'message' => 'Addon ID invalid',
                    ]);
                }
                $serviceid = Hosting::where('userid', $userid)->where('id', $serviceid)->first();
                if (!$serviceid) {
                    return ResponseAPI::Error([
                        'message' => 'Service ID not owned by Client ID provided',
                    ]);
                }
                // $_SESSION["cart"]["addons"][] = array("id" => $addonid, "productid" => $serviceid);
                // $this->request->session()->put('cart', [
                //     'addons' => array("id" => $addonid, "productid" => $serviceid),
                // ]);
                $this->request->session()->push('cart.addons', array("id" => $addonid, "productid" => $serviceid));
            }

            if ($addonids) {
                $cartAddonArray = [];
                foreach ($addonids as $i => $addonid) {
                    $addonid = Addon::find($addonid);
                    if (!$addonid) {
                        return ResponseAPI::Error([
                            'message' => 'Addon ID invalid',
                        ]);
                    }
                    $serviceid = Hosting::where('userid', $userid)->where('id', isset($serviceids[$i]) ? $serviceids[$i] : 0)->first();
                    if (!$serviceid) {
                        return ResponseAPI::Error([
                            'message' => sprintf("Service ID %s not owned by Client ID provided", (int) isset($serviceids[$i]) ? $serviceids[$i] : 0),
                        ]);
                    }
                    // $_SESSION["cart"]["addons"][] = array("id" => $addonid, "productid" => $serviceid);
                    // $this->request->session()->put('cart', [
                    //     'addons' => array("id" => $addonid, "productid" => $serviceid),
                    // ]);
                    $cartAddonArray[] = array("id" => $addonid, "productid" => $serviceid);
                }
                $this->request->session()->push('cart.addons', $cartAddonArray);
            }

            if ($domainrenewals) {
                foreach ($domainrenewals as $domain => $regperiod) {
                    $domainResult = Domain::where('userid', $userid)->where('domain', $domain)->whereIn('status', ['Active', 'Expired', 'Grace', 'Redemption'])->first();
                    $domainData = $domainResult->toArray();
                    if (isset($domainData["id"])) {
                        $domainid = $domainData["id"];
                    }
                    if (!$domainid) {
                        $domainResult = Domain::where('userid', $userid)->where('domain', $domain)->first();
                        $domainData = $domainResult->toArray();
                        if (isset($domainData["status"])) {
                            return ResponseAPI::Error([
                                'message' => "Domain status is set to '" . $domainData["status"] . "' and cannot be renewed",
                            ]);
                        } else {
                            return ResponseAPI::Error([
                                'message' => "Domain not owned by Client ID provided",
                            ]);
                        }
                    }
                    // $_SESSION["cart"]["renewals"][$domainid] = $regperiod;
                    // $this->request->session()->put('cart', [
                    //     'renewals' => [$domainid => $regperiod],
                    // ]);
                    $this->request->session()->put("cart.renewals.$domainid", $regperiod);
                }
            }

            $cartitems = count($this->request->session()->get('cart.products') ?? []) + count($this->request->session()->get('cart.addons') ?? []) + count($this->request->session()->get('cart.domains') ?? []) + count($this->request->session()->get('cart.renewals') ?? []);
            // $cartitems = (isset($sessionCart["products"]) ? count($sessionCart["products"]) : 0) + (isset($sessionCart["addons"]) ? count($sessionCart["addons"]) : 0) + (isset($sessionCart["domains"]) ? count($sessionCart["domains"]) : 0) + (isset($sessionCart["renewals"]) ? count($sessionCart["renewals"]) : 0);

            if (!$cartitems) {
                return ResponseAPI::Error([
                    'message' => "No items added to cart so order cannot proceed",
                ]);
            }

            $this->request->session()->put("cart.ns1", $nameserver1);
            $this->request->session()->put("cart.ns2", $nameserver2);
            $this->request->session()->put("cart.ns3", $nameserver3);
            $this->request->session()->put("cart.ns4", $nameserver4);
            $this->request->session()->put("cart.paymentmethod", $paymentmethod);
            $this->request->session()->put("cart.promo", $promocode);
            $this->request->session()->put("cart.notes", $notes);

            $this->request->session()->put("cart.contact", "");
            if ($contactid) {
                $this->request->session()->put("cart.contact", $contactid);
            }

            $this->request->session()->put("cart.geninvoicedisabled", false);
            if ($noinvoice) {
                $this->request->session()->put("cart.geninvoicedisabled", true);
            }

            if ($noinvoiceemail) {
                $CONFIG["NoInvoiceEmailOnOrder"] = true;
            }

            $this->request->session()->put("cart.orderconfdisabled", false);
            if ($noemail) {
                $this->request->session()->put("cart.orderconfdisabled", true);
            }
            // dd(session('cart'));

            $cartdata = \App\Helpers\Orders::calcCartTotals(true, false, $currency);
            if (is_array($cartdata) && isset($cartdata["result"]) && $cartdata["result"] == "error") {
                $apiresults = $cartdata;
                return ResponseAPI::Error($cartdata);
            }
            if ($affid) {
                $verifyAffId = DB::table("tblaffiliates")->where("id", $affid)->first();
            }
            $sessionOrderdetails = session('orderdetails');
            if ($affid && ($sessionOrderdetails && is_array($sessionOrderdetails["Products"])) && !empty($verifyAffId) && $authid != $verifyAffId->clientid) {
                foreach ($sessionOrderdetails["Products"] as $productid) {
                    \App\Models\Affiliatesaccount::insert(array("affiliateid" => $affid, "relid" => $productid));
                }
            } else {
                unset($affid);
            }
            $productids = $addonids = $domainids = "";
            if ($sessionOrderdetails && is_array($sessionOrderdetails["Products"])) {
                $productids = implode(",", $sessionOrderdetails["Products"]);
            }
            if ($sessionOrderdetails && is_array($sessionOrderdetails["Addons"])) {
                $addonids = implode(",", $sessionOrderdetails["Addons"]);
            }
            if ($sessionOrderdetails && is_array($sessionOrderdetails["Domains"])) {
                $domainids = implode(",", $sessionOrderdetails["Domains"]);
            }
            $apiresults = array("orderid" => $sessionOrderdetails ? $sessionOrderdetails["OrderID"] : "", "productids" => $productids, "serviceids" => $productids, "addonids" => $addonids, "domainids" => $domainids);
            if (!$noinvoice) {
                if ($sessionOrderdetails) {
                    $o = \App\Models\Order::find($sessionOrderdetails["OrderID"]);
                    $apiresults["invoiceid"] = $sessionOrderdetails["InvoiceID"] ? $sessionOrderdetails["InvoiceID"] : $o->invoiceid;
                }
            }

            DB::commit();
            return ResponseAPI::Success($apiresults);
        } catch (\Exception $e) {
            // HOTFIX: this
            // dd($e);
            DB::rollBack();
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * OrderFraudCheck
     *
     * Run a fraud check on a passed Order ID using the active fraud module.
     */
    public function OrderFraudCheck()
    {
        $rules = [
            // The order id to complete the fraud check on
            'orderid' => ['required', 'integer'],
            // To override the IP address on the fraud check. Example: 127.0.0.1
            'ipaddress' => ['nullable', 'string'],
        ];

        $validator = Validator::make($this->request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $orderId = $this->request->input('orderid');
        $ipaddress = $this->request->input('ipaddress');

        $order = new \App\Helpers\OrderClass();
        $order->setID($orderId);
        $fraudModule = $order->getActiveFraudModule();
        $orderId = $order->getData("id");

        if (!$orderId) {
            return ResponseAPI::Error([
                'message' => "Order ID Not Found",
            ]);
        }
        if (!$fraudModule) {
            return ResponseAPI::Error([
                'message' => "No Active Fraud Module",
            ]);
        }

        $userId = $order->getData("userid");
        $ipAddress = $order->getData("ipaddress");
        $invoiceId = $order->getData("invoiceid");
        if ($ipaddress) {
            $ipAddress = $ipaddress;
        }

        $results = $fraudResults = "";
        $fraud = new \App\Module\Fraud();
        if ($fraud->load($fraudModule)) {
            $results = $fraud->doFraudCheck($orderId, $userId, $ipAddress);
            $fraudResults = $fraud->processResultsForDisplay($orderId, $results["fraudoutput"]);
        }
        if (!is_array($results)) {
            $results = array();
        }
        $error = $results["error"];
        if ($results["userinput"]) {
            $status = "User Input Required";
        } else {
            if ($results["error"]) {
                $status = "Fail";
                DB::table("tblorders")->where("id", "=", $orderId)->update(array("status" => "Fraud"));
                DB::table("tblhosting")->where("orderid", "=", $orderId)->where("domainstatus", "=", "Pending")->update(array("domainstatus" => "Fraud"));
                DB::table("tblhostingaddons")->where("orderid", "=", $orderId)->where("status", "=", "Pending")->update(array("status" => "Fraud"));
                DB::table("tbldomains")->where("orderid", "=", $orderId)->where("status", "=", "Pending")->update(array("status" => "Fraud"));
                DB::table("tblinvoices")->where("id", "=", $invoiceId)->where("status", "=", "Unpaid")->update(array("status" => "Cancelled"));
            } else {
                $status = "Pass";
                DB::table("tblorders")->where("id", "=", $orderId)->update(array("status" => "Pending"));
                DB::table("tblhosting")->where("orderid", "=", $orderId)->where("domainstatus", "=", "Fraud")->update(array("domainstatus" => "Pending"));
                DB::table("tblhostingaddons")->where("orderid", "=", $orderId)->where("status", "=", "Fraud")->update(array("status" => "Pending"));
                DB::table("tbldomains")->where("orderid", "=", $orderId)->where("status", "=", "Fraud")->update(array("status" => "Pending"));
                DB::table("tblinvoices")->where("id", "=", $invoiceId)->where("status", "=", "Cancelled")->update(array("status" => "Unpaid"));
            }
        }
        $apiresults = array("result" => "success",
        "status" => $status,
        "module" => $fraudModule,
        "results" => safe_serialize($fraudResults));

        return ResponseAPI::Success([
            "status" => $status,
            "module" => $fraudModule,
            "results" => (new \App\Helpers\Pwd())->safe_serialize($fraudResults),
        ]);
    }
}
