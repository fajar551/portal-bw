<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;

use App\Helpers\AdminFunctions;
use App\Helpers\Cfg;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Country;
use App\Helpers\Database;
use App\Helpers\Format;
use App\Helpers\GeoIp;
use App\Helpers\Hooks;
use App\Helpers\Orders;
use App\Helpers\ResponseAPI;
use App\Models\Order;
use App\Models\Orderstatus;
use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\Client;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Upgrade;
use App\Traits\DatatableFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ViewOrderController extends Controller
{
    use DatatableFilter;

    protected $prefix;

    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = Database::prefix();
    }

    public function ViewOrderNEW(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        if ($id) {
            return redirect()->route('admin.pages.orders.vieworder.index', ['action' => 'view', 'id' => $id]);
        } else {
            return redirect()->route('admin.pages.orders.listallorders.index');
        }
    }

    public function index(Request $request)
    {
        $id = $request->id;
        $pfx = $this->prefix;

        if (!$id) {
            return abort(404, "Order not found... Exiting...");
        }

        $orderStatus = Orderstatus::orderBy("sortorder", "ASC")->get();
        $gatewaysarray = \App\Helpers\Gateway::GetGatewaysArray();
        $countries = new Country();
        // $data = Order::selectRaw("{$pfx}orders.*, {$pfx}clients.firstname, {$pfx}clients.lastname, {$pfx}clients.email, {$pfx}clients.companyname, {$pfx}clients.address1, {$pfx}clients.address2, {$pfx}clients.city, {$pfx}clients.state, {$pfx}clients.postcode, {$pfx}clients.country, {$pfx}clients.groupid, (SELECT status FROM {$pfx}invoices WHERE id={$pfx}orders.invoiceid) AS invoicestatus")
        //                 ->where("{$pfx}orders.id", $id)
        //                 ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}orders.userid")
        //                 ->first();

        // 1. Enable query logging
        DB::enableQueryLog();

        // 2. Cek data order terlebih dahulu
        $orderCheck = DB::table("{$pfx}orders")
            ->where('id', $id)
            ->first();
        Log::info('Order check:', ['order' => $orderCheck]);

        if ($orderCheck) {
            // 3. Jika order ditemukan, cek data client
            $clientCheck = DB::table("{$pfx}clients")
                ->where('id', $orderCheck->userid)
                ->first();
            Log::info('Client check:', ['client' => $clientCheck]);
            
            // 4. Coba query dengan DB facade dulu
            $data = DB::table("{$pfx}orders")
                ->select(
                    "{$pfx}orders.*",
                    "{$pfx}clients.firstname",
                    "{$pfx}clients.lastname",
                    "{$pfx}clients.email",
                    "{$pfx}clients.companyname",
                    "{$pfx}clients.address1",
                    "{$pfx}clients.address2", 
                    "{$pfx}clients.city",
                    "{$pfx}clients.state",
                    "{$pfx}clients.postcode",
                    "{$pfx}clients.country",
                    "{$pfx}clients.groupid",
                    DB::raw("(SELECT status FROM {$pfx}invoices WHERE id={$pfx}orders.invoiceid) AS invoicestatus")
                )
                ->where("{$pfx}orders.id", $id)
                ->join("{$pfx}clients", "{$pfx}clients.id", "=", "{$pfx}orders.userid")
                ->first();
            
            // 5. Log query yang dijalankan
            Log::info('SQL Query:', [
                'queries' => DB::getQueryLog(),
                'result' => $data
            ]);

            if ($data) {
                $data = (array)$data;
            } else {
                return abort(404, "Order data not found after join... Exiting...");
            }
        } else {
            return abort(404, "Order not found... Exiting...");
        }
        $id = $data["id"];
        if (!$id) {
            return abort(404, "Order not found... Exiting...");
        }

        // Ambil invoice terbaru berdasarkan user ID
        $latestInvoice = DB::table('tblinvoices')
            ->where('userid', $data['userid'])
            ->orderBy('id', 'desc')
            ->first();

        if ($latestInvoice) {
            $subtotal = $latestInvoice->subtotal;
            $total = $latestInvoice->total;

            Log::info('Latest Invoice Data:', [
                'invoiceid' => $latestInvoice->id,
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
        } else {
            $subtotal = 0;
            $total = 0;
            Log::info('No invoice found for user ID:', ['userid' => $data['userid']]);
        }

        // Debugging
        Log::info('Invoice Data:', [
            'subtotal' => $subtotal,
            'total' => $total,
        ]);

        $ordernum = $data["ordernum"] ?? 0;
        $userid = $data["userid"];
        // $aInt->assertClientBoundary($userid);
        $verifyEmailAddressEnabled = Cfg::get("EnableEmailVerification");
        $client = Client::find($userid);
        $btnResendVerificationEmail = false;
        $isEmailAddressVerified = $client ? $client->isEmailAddressVerified() : false;
        if ($verifyEmailAddressEnabled && !$isEmailAddressVerified) {
            // Display button email verification
            $btnResendVerificationEmail = true;
        }
        $date = $data["date"];
        $amount = $data["amount"];
        $paymentmethod = $data["paymentmethod"];
        $paymentmethod = in_array($paymentmethod, array_keys($gatewaysarray)) ? $gatewaysarray[$paymentmethod] : "Invalid";
        $orderstatus = $data["status"];
        // $showpending = get_query_val("tblorderstatuses", "showpending", array("title" => $orderstatus));
        $showpending = Orderstatus::where("title", $orderstatus)->value("showpending");
        $amount = $data["amount"];
        $client = ClientHelper::outputClientLink($userid, $data["firstname"], $data["lastname"], $data["companyname"], $data["groupid"]);
        $address = $data["address1"];
        if ($data["address2"]) {
            $address .= ", " . $data["address2"];
        }
        $address .= "<br />" . $data["city"] . ", " . $data["state"] . ", " . $data["postcode"] . "<br />" . $countries->getName($data["country"]);
        $ipaddress = $data["ipaddress"];
        $clientemail = $data["email"];
        $invoiceid = $data["invoiceid"];
        $nameservers = $data["nameservers"];
        $nameservers = explode(",", $nameservers);
        $transfersecret = $data["transfersecret"];
        $transfersecret = $transfersecret ? (new \App\Helpers\Client())->safe_unserialize($transfersecret) : array();
        $renewals = $data["renewals"];
        $promocode = $data["promocode"];
        $promotype = $data["promotype"];
        $promovalue = $data["promovalue"];
        $orderdata = $data["orderdata"];
        $fraudmodule = $data["fraudmodule"];
        $fraudoutput = $data["fraudoutput"];
        $notes = $data["notes"];
        $contactid = $data["contactid"];
        $invoicestatus = $data["invoicestatus"];
        $date = (new HelpersClient())->fromMySQLDate($date, "time");
        // $jscode = "function cancelOrder() {\n    if (confirm(\"" . $aInt->lang("orders", "confirmcancel") . "\"))\n        window.location=\"" . $_SERVER["PHP_SELF"] . "?action=view&id=" . $id . "&cancel=true" . generate_token("link") . "\";\n}\nfunction cancelRefundOrder() {\n    if (confirm(\"" . $aInt->lang("orders", "confirmcancelrefund") . "\"))\n        window.location=\"" . $_SERVER["PHP_SELF"] . "?action=view&id=" . $id . "&cancelrefund=true" . generate_token("link") . "\";\n}\nfunction fraudOrder() {\n    if (confirm(\"" . $aInt->lang("orders", "confirmfraud") . "\"))\n        window.location=\"" . $_SERVER["PHP_SELF"] . "?action=view&id=" . $id . "&fraud=true" . generate_token("link") . "\";\n}\nfunction pendingOrder() {\n    if (confirm(\"" . $aInt->lang("orders", "confirmpending") . "\"))\n        window.location=\"" . $_SERVER["PHP_SELF"] . "?action=view&id=" . $id . "&pending=true" . generate_token("link") . "\";\n}\nfunction deleteOrder() {\n    WHMCS.http.jqClient.post(\n        \"" . $_SERVER["PHP_SELF"] . "?action=ajaxCanOrderBeDeleted&id=" . $id . "\",\n            { token: \"" . generate_token("plain") . "\" },\n           function (data) {\n                if (data == 1) {\n                    if (confirm(\"" . $aInt->lang("orders", "confirmdelete") . "\")) {\n                        window.location=\"" . $_SERVER["PHP_SELF"] . "?action=delete&id=" . $id . "" . generate_token("link") . "\";\n                    }\n                } else {\n                    alert(\"" . $aInt->lang("orders", "noDelete") . "\");\n                }\n           }\n    )\n}\n";
        $currency = Format::GetCurrency($userid);
        $amount = Format::formatCurrency($amount);
        // $jquerycode .= "\$(\"#ajaxchangeorderstatus\").change(function() {\n    var newstatus = \$(\"#ajaxchangeorderstatus\").val();\n    WHMCS.http.jqClient.post(\"" . $_SERVER["PHP_SELF"] . "?action=ajaxchangeorderstatus&id=" . $id . "\",\n    { status: newstatus, token: \"" . generate_token("plain") . "\" },\n   function(data) {\n     if(data == " . $id . "){\n         \$(\"#orderstatusupdated\").fadeIn().fadeOut(5000);\n     }\n   });\n});";
        $statusoptions = "<select id=\"ajaxchangeorderstatus\" class=\"form-control select-inline\" onchange=\"actionCommand('ajaxChangeOrderStatus');\">";
        $result = Orderstatus::orderBy("sortorder", "ASC")->get()->toArray();
        foreach ($result as $data) {
            $statusoptions .= "<option style=\"color:" . $data["color"] . "\" value=\"" . $data["title"] . "\"";
            if ($orderstatus == $data["title"]) {
                $statusoptions .= " selected";
            }

            $statusoptions .= ">" . (__("admin.status" . strtolower($data["title"])) ? __("admin.status" . strtolower($data["title"])) : $data["title"]) . "</option>";
        }
        $statusoptions .= "</select>&nbsp;<span id=\"orderstatusupdated\" style=\"display:none;padding-top:14px;\"><img src=\"images/icons/tick.png\" /></span>";
        $orderdata = (new \App\Helpers\Client())->safe_unserialize($orderdata);
        $paymentstatus = Orders::getFormatedPaymentStatus($invoiceid, $invoicestatus);
        Hooks::run_hook("ViewOrderDetailsPage", [
            "orderid" => $id,
            "ordernum" => $ordernum,
            "userid" => $userid,
            "amount" => $amount,
            "paymentmethod" => $paymentmethod,
            "invoiceid" => $invoiceid,
            "status" => $orderstatus
        ]);

        /* TODO: Client note with markdown?
        $markup = new WHMCS\View\Markup\Markup();
        $clientnotes = array();
        $result = select_query("tblnotes", "tblnotes.*,(SELECT CONCAT(firstname,' ',lastname) FROM tbladmins WHERE tbladmins.id=tblnotes.adminid) AS adminuser", array("userid" => $userid, "sticky" => "1"), "modified", "DESC");
        while ($data = mysql_fetch_assoc($result)) {
            $markupFormat = $markup->determineMarkupEditor("client_note", "", $data["modified"]);
            $data["note"] = $markup->transform($data["note"], $markupFormat);
            $data["created"] = fromMySQLDate($data["created"], 1);
            $data["modified"] = fromMySQLDate($data["modified"], 1);
            $clientnotes[] = $data;
        }

        if ($clientnotes) {
            echo $aInt->formatImportantClientNotes($clientnotes);
        }
        */

        // $affid = get_query_val("tblaffiliatesaccounts", "affiliateid", array("tblhosting.orderid" => $id), "", "", "1", "tblhosting on tblhosting.id = tblaffiliatesaccounts.relid");
        // $result = select_query("tblaffiliates", "tblaffiliates.id,firstname,lastname", array("tblaffiliates.id" => $affid), "", "", "", "tblclients ON tblclients.id=tblaffiliates.clientid");
        $affid = AffiliateAccount::where("{$pfx}hosting.orderid", $id)
            ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}affiliatesaccounts.relid")
            ->first();

        if ($affid) {
            $affid = $affid->affiliateid;
            $data = Affiliate::select("{$pfx}affiliates.id", "firstname", "lastname")
                ->where("{$pfx}affiliates.id", $affid)
                ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}affiliates.clientid")
                ->first()
                ->toArray();

            $affid = $data["id"];
            $afffirstname = $data["firstname"];
            $afflastname = $data["lastname"];

            $affEditRoute = route("admin.pages.clients.manageaffiliates.edit", [
                "action" => "edit",
                "id" => $affid,
            ]);

            $affiliate = "<a href=\"$affEditRoute\">$afffirstname $afflastname</a>";
        } else {
            $affiliate = __("admin.ordersaffnone") . " - <a href=\"#\" id=\"showaffassign\" onclick=\"showaffassign()\">" . __("admin.ordersaffmanualassign") . "</a>";
        }

        // IP Field
        $listOrderRoute = route("admin.pages.orders.listallorders.index", [
            "orderip" => $ipaddress,
        ]);

        // TODO: configbannedips
        $configbannedipsRoute = "configbannedips.php?ip=$ipaddress&reason=Banned due to Orders&year=2020&month=12&day=31&hour=23&minutes=59";
        $ipaddressField = $ipaddress . " - " . GeoIp::getLookupHtmlAnchor($ipaddress, NULL, __("admin.ordersiplookup")) . " | <a href=\"$listOrderRoute\">" . __("admin.gatewaytranslogfilter") . "</a>" . " | <a href=\"$configbannedipsRoute\">" . __("admin.ordersipban") . "</a>";

        // Order Items / HOSTING LIST
        $serverList = array();
        $orderHasASubscription = false;
        $services = Hosting::with("product", "product.productGroup", "client")->where("orderid", $id)->get();

        // Order Items / HOSTING ADDONS LIST
        $hostingAddons = Hostingaddon::with("productAddon", "service")->where("orderid", $id)->get();
        $lang = array(
            "ordersaddon" => __("admin.ordersaddon"),
            "orderssendwelcome" => __("admin.orderssendwelcome"),
            "ordersrunmodule" => __("admin.ordersrunmodule"),
            "fieldspassword" => __("admin.fieldspassword"),
            "fieldsusername" => __("admin.fieldsusername"),
            "fieldsserver" => __("admin.fieldsserver"),
            "none" => __("admin.none")
        );

        // Order Items / DOMAINS LIST
        $result = Domain::where("orderid", $id)->get();
        $domains = $result ? $result->toArray() : [];

        // Order Items / UPGRADE LIST
        $upgrades = Upgrade::where("orderid", $id)->get();

        global $CONFIG;

        $templatevars = [
            'pfx' => $this->prefix,
            'orderStatus' => $orderStatus,
            'orderstatus' => $orderstatus,
            'date' => $date,
            'paymentmethod' => $paymentmethod,
            'id' => $id,
            'ordernum' => $ordernum,
            'amount' => $amount,
            'userid' => $userid,
            'client' => $client,
            'isEmailAddressVerified' => $isEmailAddressVerified,
            'verifyEmailAddressEnabled' => $verifyEmailAddressEnabled,
            'address' => $address,
            'invoiceid' => $invoiceid,
            'statusoptions' => $statusoptions,
            'ipaddressField' => $ipaddressField,
            'promocode' => $promocode,
            'promovalue' => $promovalue,
            'promotype' => $promotype,
            'orderdata' => $orderdata,
            'affiliateField' => $affiliate,
            'notes' => $notes,
            'note_toggle' => __("admin.orders" . ($notes ? "hideNotes" : "addNotes")),
            'serverList' => $serverList,
            'orderHasASubscription' => $orderHasASubscription,
            'services' => $services,
            'paymentstatus' => $paymentstatus,
            'showpending' => $showpending,
            'hostingAddons' => $hostingAddons,
            'lang' => $lang,
            'domains' => $domains,
            'contactid' => $contactid,
            'transfersecret' => $transfersecret,
            'renewals' => $renewals,
            'upgrades' => $upgrades,
            'CONFIG' => $CONFIG,
            'invoicestatus' => $invoicestatus,
            'subtotal' => $subtotal,
            'total' => $total,
        ];

        return view('pages.orders.vieworder.index', $templatevars);
    }

    public function actionCommand(Request $request)
    {
        $action = $request->action;

        switch ($action) {
            case 'activate':
                return $this->activate($request);
            case 'cancel':
                return $this->cancel($request);
            case 'cancelrefund':
                return $this->cancelRefund($request);
            case 'fraud':
                return $this->fraud($request);
            case 'pending':
                return $this->pending($request);
            case 'ajaxCanOrderBeDeleted':
                return $this->ajaxCanOrderBeDeleted($request);
            case 'ajaxChangeOrderStatus':
                return $this->ajaxChangeOrderStatus($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function activate(Request $request)
    {
        $id = $request->id;
        $formData = $request->formData;
        $vars = $formData["vars"] ?? array();
        $vars = !empty($vars) && is_array($vars) ? $vars : array();

        try {
            $errors = Orders::AcceptOrder($id, $vars);
        } catch (\Throwable $th) {
            $errors["error"] = $th->getMessage();
        }

        if (count($errors)) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusaccepterror", implode("<br>", $errors)),
            ]);
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusaccept", "admin.ordersstatusacceptmsg"),
        ]);
    }

    private function cancel(Request $request)
    {
        $id = $request->id;
        $cancelSubscription = (bool) $request->cancelsub;

        $errMsg = Orders::ChangeOrderStatus($id, "Cancelled", $cancelSubscription);
        if (0 < strlen($errMsg)) {
            if ($errMsg == "subcancelfailed") {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusCancelledFailed", "admin.orderssubCancelFailed"),
                ]);
            }
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("admin.ordersstatuscancelled", "admin.ordersstatuschangemsg"),
        ]);
    }

    private function fraud(Request $request)
    {
        $id = $request->id;
        $cancelSubscription = (bool) $request->cancelsub;

        $errMsg = Orders::ChangeOrderStatus($id, "Fraud", $cancelSubscription);
        if (0 < strlen($errMsg)) {
            if ($errMsg == "subcancelfailed") {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusCancelledFailed", "admin.orderssubCancelFailed"),
                ]);
            }
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusfraud", "admin.ordersstatuschangemsg"),
        ]);
    }

    private function pending(Request $request)
    {
        $id = $request->id;

        $errMsg = Orders::ChangeOrderStatus($id, "Pending");

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("admin.ordersstatuspending", "admin.ordersstatuschangemsg"),
        ]);
    }

    private function cancelRefund(Request $request)
    {
        if (!AdminFunctions::checkPermission("Refund Invoice Payments")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $id = $request->id;
        $message = "";
        $error = Orders::CancelRefundOrder($id);

        switch ($error) {
            case 'noorder':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusnotfound");
                break;
            case 'noinvoice':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusrefundnoinvoice");
                break;
            case 'notpaid':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusrefundnotpaid");
                break;
            case 'alreadyrefunded':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusrefundalready");
                break;
            case 'refundfailed':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusrefundfailedmsg");
                break;
            case 'manual':
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundfailed", "admin.ordersstatusrefundnoauto");
                break;
            default:
                $message = AdminFunctions::infoBoxMessage("admin.ordersstatusrefundsuccess", "admin.ordersstatusrefundsuccessmsg");
                break;
        }

        if ($error) {
            return ResponseAPI::Error([
                'message' => $message,
            ]);
        }

        return ResponseAPI::Success([
            'message' => $message,
        ]);
    }

    public function updateNotes(Request $request)
    {
        if (!AdminFunctions::checkPermission("View Order Details")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
                'data' => [],
            ]);
        }

        $id = $request->id;
        $order = Order::find($id);

        if (!$order) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
                'data' => [],
            ]);
        }

        $order->notes = $request->notes;
        $order->save();

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data updated successfully!"),
            'data' => ['notes' => $order->notes],
        ]);
    }

    public function affAssign(Request $request)
    {
        # code...
    }

    public function ajaxChangeOrderStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        $statusesarr = [];
        $order = Order::findOrFail($id);
        if (!$order) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        $id = $order->id;
        $statusesarr = Orderstatus::select("title")->orderBy("sortorder", "ASC")->get()->pluck("title")->toArray();
        if (in_array($status, $statusesarr) && $id) {
            $order->status = $status;
            $order->save();

            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage("<b>Well Done!</b>", "admin.ordersstatuschangemsg"),
            ]);
        }

        return ResponseAPI::Error([
            'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Something went wrong.'),
        ]);
    }

    public function ajaxCanOrderBeDeleted(Request $request)
    {
        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => ["candelete" => Orders::CanOrderBeDeleted($request->id)]
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        # code...
    }
}
