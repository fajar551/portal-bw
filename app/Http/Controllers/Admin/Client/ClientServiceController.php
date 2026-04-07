<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Cfg;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\ConfigOptions;
use App\Helpers\Customfield;
use App\Helpers\Cycles;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Orders;
use App\Helpers\Password;
use App\Helpers\ProcessInvoices;
use App\Helpers\Product;
use App\Helpers\Pwd;
use App\Helpers\ResponseAPI;
use App\Helpers\Sanitize;
use App\Helpers\Service;
use App\Helpers\SystemHelper;
use App\Helpers\Upgrade;

// Models
use App\Models\Addon;
use App\Models\Note;
use App\Models\Order;
use App\Models\Orderstatus;
use App\Models\AffiliateAccount;
use App\Models\Cancelrequest;
use App\Models\Client;
use App\Models\Emailtemplate;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Hostingconfigoption;
use App\Models\Product as ModelsProduct;
use App\Models\Promotion;
use App\Models\Server;
use App\Models\Sslstatus;
use App\Models\Upgrade as ModelsUpgrade;

// Traits
use App\Traits\DatatableFilter;

class ClientServiceController extends Controller
{
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        // Request variable
        $id = (int) $request->get("id");
        $userid = (int) $request->get("userid");
        $productselect = (int) $request->get("productselect");
        $hostingid = (int) $request->get("hostingid");
        $aid = $request->get("aid");

        if ($productselect) {
            if (substr($productselect, 0, 1) == "a") {
                $aid = (int) substr($productselect, 1);
            } else {
                $id = (int) $productselect;
            }
        }

        if (!$id && $hostingid) {
            $id = $hostingid;
        }

        if (!$id && $aid) {
            $addon = Hostingaddon::with("service")->find($aid);
            if ($addon) {
                $id = $addon->serviceId;
                if (!$addon->clientId) {
                    $addon->clientId = $addon->service->userid;
                    $addon->save();
                }
            }
        }

        if ($userid && !$id) {
            $useridExist = $this->valUserID($userid);

            if (!$useridExist) {
                $templatevars['userid'] = $userid;
                $templatevars["notesCount"] = Note::where('userid', $userid)->count();
                return view('pages.clients.viewclients.clientservices.index', [
                    'invalidClientId' => true,
                ]);
            }

            $hosting = Hosting::where("userid", $userid)->orderBy("domain", "ASC")->first();
            $id = @$hosting->id;
        }

        if (!$id) {
            $templatevars['userid'] = $userid;
            $templatevars["notesCount"] = Note::where('userid', $userid)->count();
            return view('pages.clients.viewclients.clientservices.index', [
                'invalidServiceId' => true,
            ]);
        }

        $result = Hosting::selectRaw("{$this->prefix}hosting.*, {$this->prefix}products.servertype, {$this->prefix}products.type, {$this->prefix}products.welcomeemail")
                            ->where("{$this->prefix}hosting.id", $id)
                            ->join("{$this->prefix}products", "{$this->prefix}products.id", "{$this->prefix}hosting.packageid")
                            ->first();

        $service_data = $result ? $result->toArray() : [];
        $id = @$service_data["id"];
        if (!$id) {
            $templatevars['userid'] = $userid;
            $templatevars["notesCount"] = Note::where('userid', $userid)->count();
            return view('pages.clients.viewclients.clientservices.index', [
                'invalidServiceId' => true,
            ]);
        }

        if ($service_data["userid"] != $userid) {
            $templatevars['userid'] = $userid;
            $templatevars["notesCount"] = Note::where('userid', $userid)->count();
            return view('pages.clients.viewclients.clientservices.index', [
                'invalidClientIdAndServiceId' => true,
            ]);
        }

        // Custom data
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $paymentmethodlist = (new Gateway($request))->paymentMethodsList();

        // Prepare the data for view
        $producttype = $service_data["type"];
        $module = $service_data["servertype"];
        $orderid = $service_data["orderid"];
        $packageid = $service_data["packageid"];
        $server = $service_data["server"];
        $regdate = $service_data["regdate"];
        $terminationDate = $service_data["termination_date"];
        $completedDate = $service_data["completed_date"];
        $domain = $service_data["domain"];
        $paymentmethod = $service_data["paymentmethod"];
        $createServerOptionForNone = false;
        $serverModule = new \App\Module\Server();

        if (isset($aid)) {
            $serverModule->setAddonId($aid);
        } else {
            $serverModule->setServiceId($id);
        }

        if ($module && !isset($aid)) {
            if ($serverModule->load($module)) {
                if ($serverModule->isMetaDataValueSet("RequiresServer") && !$serverModule->getMetaDataValue("RequiresServer")) {
                    $createServerOptionForNone = true;
                }
            } else {
                LogActivity::Save("Required Product Module '" . $serverModule->getServiceModule() . "' Missing - Service ID: " . $id, $userid);
            }
        }

        $gateways = new \App\Module\Gateway();
        if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
            $paymentmethod = Functions::ensurePaymentMethodIsSet($userid, $id, "{$this->prefix}hosting");
        }

        $firstpaymentamount = $service_data["firstpaymentamount"];
        $amount = $service_data["amount"];
        $billingcycle = $serviceBillingCycle = $service_data["billingcycle"];
        $nextduedate = $service_data["nextduedate"];
        // $nextduedate = $service_data["nextinvoicedate"];
        $domainstatus = $service_data["domainstatus"];
        $username = $service_data["username"];
        $password = (new \App\Helpers\Pwd())->decrypt($service_data["password"]);
        $notes = $service_data["notes"];
        $subscriptionid = $service_data["subscriptionid"];
        $promoid = $service_data["promoid"];
        $promocount = $service_data["promocount"] ?? '';
        $suspendreason = $service_data["suspendreason"];
        $overideautosuspend = $service_data["overideautosuspend"];
        $ns1 = $service_data["ns1"];
        $ns2 = $service_data["ns2"];
        $dedicatedip = $service_data["dedicatedip"];
        $assignedips = $service_data["assignedips"];
        $diskusage = $service_data["diskusage"];
        $disklimit = $service_data["disklimit"];
        $bwusage = $service_data["bwusage"];
        $bwlimit = $service_data["bwlimit"];
        $lastupdate = $service_data["lastupdate"];
        $overidesuspenduntil = $service_data["overidesuspenduntil"];
        $welcomeEmail = $service_data["welcomeemail"];
        $addonModule = "";
        $addonDetails = NULL;

        # HALAMAN UPDATE ADDONS (ADD OR UPDATE) ?
        if ($aid && is_numeric($aid)) {
            try {
                $addonDetails = Hostingaddon::with("productAddon", "service")->where("id", $aid)->whereIn("userid", [0, $userid])->firstOrFail();
                if (!$addonDetails->clientId) {
                    $addonDetails->clientId = $addonDetails->service->userid;
                    $addonDetails->save();
                }
            } catch (Exception $e) {
                // redir("userid=" . $userid . "&id=" . $id);
            }

            $addonModule = $addonDetails->productAddon->module;
        }

        // if (!count($errors)) {
        $regdate = (new HelpersClient)->fromMySQLDate($regdate);
        $terminationDate = (new HelpersClient)->fromMySQLDate($terminationDate);
        $nextduedate = (new HelpersClient)->fromMySQLDate($nextduedate);
        $overidesuspenduntil = (new HelpersClient)->fromMySQLDate($overidesuspenduntil);

        if ($disklimit == "0") {
            $disklimit = __("admin.unlimited");
        }

        if ($bwlimit == "0") {
            $bwlimit = __("admin.unlimited");
        }

        $currency = (new AdminFunctions())->getCurrency($userid);
        // $data = get_query_vals("tblcancelrequests", "id,type,reason", array("relid" => $id), "id", "DESC");
        $data = Cancelrequest::select("id", "type", "reason")->where("relid", $id)->orderBy("id", "DESC")->first();
        $cancelid = @$data->id;
        $canceltype = @$data->type;
        $autoterminatereason = @$data->reason;
        $autoterminateendcycle = false;

        if ($canceltype == "End of Billing Period") {
            $autoterminateendcycle = $cancelid ? true : false;
        }

        if (!$server) {
            // $server = get_query_val("tblservers", "id", array("type" => $module, "active" => "1"));
            $server = Server::where("type", $module)->where("active", "1")->first();
            if ($server) {
                $server = $server->id;
                // update_query("tblhosting", array("server" => $server->id), array("id" => $id));
                Hosting::where("id", $id)->update(["server" => $server]);
            }
        }

        /*
        $routePathId = $id;
        $routeName = "admin-services-cancel-subscription";
        if (App::isInRequest("aid") && $aid) {
            $routePathId = $aid;
            $routeName = "admin-addons-cancel-subscription";
        }

        $cancelRoute = routePath($routeName, $routePathId);
        */

        $addonServices = $this->getAddonServices($userid);
        $allServices = $this->getServices($userid, $addonServices)["allServices"];
        $servicesarr = $this->getServices($userid, $addonServices)["servicesarr"];

        if (isset($aid) && is_numeric($aid)) {
            $itemToSelect = "a" . $aid;
        } else {
            $itemToSelect = $id;
        }

        if (!isset($aid)) {
            $isDomain = str_replace(".", "", $domain) != $domain;
            if ($producttype == "other") {
                $isDomain = false;
            }

            // SSL Status Toggle
            $sslStatus = Sslstatus::factory($userid, $domain);
            $html = "<img src=\"%s\" class=\"%s\" data-toggle=\"tooltip\" title=\"%s\" data-domain=\"%s\" data-user-id=\"%s\" style=\"width:25px;\">";
            $sslStatusToggle = sprintf($html, $sslStatus->getImagePath(), $sslStatus->getClass(), $sslStatus->getTooltipContent(), $domain, $userid);

            // $upgradeText = AdminLang::trans("services.createupgorder");
            // $link = "clientsupgrade.php?id=" . $id;
            // $title = AdminLang::trans("services.upgradedowngrade");
            // $upgradeButton = "<button type=\"button\"\n        class=\"btn btn-default left-margin-5 open-modal\"\n        href=\"" . $link . "\"\n        data-modal-title=\"" . $title . "\"\n>\n    <i class=\"fas fa-arrow-circle-up\"></i>\n    " . $upgradeText . "\n</button>";
            // echo "<div class=\"col-sm-5\">\n            " . $output . $upgradeButton . " " . $frm->button("<i class=\"fas fa-random\"></i> " . $aInt->lang("services", "moveservice"), "window.open('clientsmove.php?type=hosting&id=" . $id . "','movewindow','width=500,height=300,top=100,left=100,scrollbars=yes')") . "\n        </div>";
        }

        $moduleInterface = new \App\Module\Server();
        $moduleInterface->loadByServiceID($id);
        $moduleParams = $moduleInterface->buildParams();
        $serversarr = $moduleInterface->getServerListForModule();
        // dd($serversarr);

        $promoarr = array();
        $promotionList = Promotion::orderBy("code", "ASC")->get()->toArray();
        foreach ($promotionList as $data) {
            $promo_id = $data["id"];
            $promo_code = $data["code"];
            $promo_type = $data["type"];
            $promo_recurring = $data["recurring"];
            $promo_value = $data["value"];
            if ($promo_type == "Percentage") {
                $promo_value .= "%";
            } else {
                $promo_value = Format::formatCurrency($promo_value);
            }
            if ($promo_type == "Free Setup") {
                $promo_value = __("admin.promosfreesetup");
            }
            $promo_recurring = $promo_recurring ? __("admin.statusrecurring") : __("admin.statusonetime");
            if ($promo_type == "Price Override") {
                $promo_recurring = __("admin.promospriceoverride");
            }
            if ($promo_type == "Free Setup") {
                $promo_recurring = "";
            }

            $promoarr[$promo_id] = $promo_code . " - " . $promo_value . " " . $promo_recurring;
        }

        $statusExtra = "";
        if ($domainstatus == "Suspended") {
            $statusExtra = " (" . __("admin.servicessuspendreason") . ": " . (!$suspendreason ? __("client.suspendreasonoverdue") : $suspendreason) . ")";
        } else if ($domainstatus == "Completed") {
            $statusExtra = $completedDate != "0000-00-00" ? " (" . (new Functions())->fromMySQLDate($completedDate) . ")" : "";
        }

        $recurLimit = $recurCountString = "";
        if (0 < $promoid) {
            $recurPromo = \DB::table("{$this->prefix}promotions")->where("id", $promoid)->first();
            if ($recurPromo && !is_null($promocount)) {
                $recurLimit = 0 < $recurPromo->recurfor ? "/" . $recurPromo->recurfor : "";
                $recurCountString = $recurPromo->recurring ? " (" . __("admin.servicesrecurCount") . ": " . $promocount . $recurLimit . ")" : "";
            }
        }

        $configoptions = array();
        $configoptions = ConfigOptions::getCartConfigOptions($packageid, "", $billingcycle, $id);

        $adminbuttonarray = "";
        if ($module && !isset($aid) && $serverModule->functionExists("AdminCustomButtonArray")) {
            $moduleParams = $serverModule->buildParams();
            $adminbuttonarray = $serverModule->call("AdminCustomButtonArray", $moduleParams);
        }

        $modulebtns = $this->buildcustommodulebuttons([], $adminbuttonarray);

        $cancelSubscription = "";
        if ($subscriptionid) {
            $gateway = new \App\Module\Gateway();
            $gateway->load($paymentmethod);

            if ($gateway->functionExists("get_subscription_info")) {
                $route = "javascript:void(0);";  // TODO: Change to route("admin.pages.clients.viewclients.clientservices.subscriptionInfo", ["id" => $id]);
                $cancelSubscription .= "<a href=\"$route\" type=\"button\" class=\"btn btn-sm btn-default mt-2 ml-1\" onclick=\"jQuery('#modalInfoSubscription').modal('show');\" id=\"btnInfo_Subscription\">\n" . __("admin.getSubscriptionInfo") . "\n </a>";
            }

            if ($gateway->functionExists("cancelSubscription")) {
                $cancelSubscription .= "<button type=\"button\" class=\"btn btn-sm btn-default mt-2 ml-1\" onclick=\"jQuery('#modalCancelSubscription').modal('show');\" id=\"btnCancel_Subscription\">\n" . __("admin.servicescancelSubscription") . "\n </button>";
            }
        }

        $suspendValue = strpos($overidesuspenduntil, "0000") === false ? $overidesuspenduntil : "";
        $customfields = \App\Helpers\Customfield::getCustomFields("product", $packageid, $id, true);

        $service = new Service($id);
        $addons = $service->getAddons();

        // Template vars for view usage
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $id;
        $templatevars["aid"] = $aid;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["paymentmethodlist"] = $paymentmethodlist;
        $templatevars["productselect"] = $allServices;
        $templatevars["itemToSelect"] = $itemToSelect;
        $templatevars["sslStatusToggle"] = $sslStatusToggle;
        $templatevars["promoarr"] = $promoarr;
        $templatevars["promoid"] = $promoid;
        $templatevars["serversarr"] = $serversarr;
        $templatevars["server"] = $server;
        $templatevars["orderid"] = $orderid;
        $templatevars["domain"] = $domain;
        $templatevars["regdate"] = $regdate;
        $templatevars["productList"] = Product::productDropDown($packageid);
        $templatevars["gateway"] = $paymentmethod;
        $templatevars["regdate"] = $regdate;
        $templatevars["oldpackageid"] = $packageid;
        $templatevars["firstpaymentamount"] = $firstpaymentamount;
        $templatevars["amount"] = $amount;
        $templatevars["autorecalcdefault"] = isset($autorecalcdefault) ? true : false;
        $templatevars["producttype"] = $producttype;
        $templatevars["dedicatedip"] = $dedicatedip;
        $templatevars["terminationDate"] = $terminationDate;
        $templatevars["billingcycle"] = $billingcycle;
        $templatevars["nextduedate"] = $nextduedate;
        $templatevars["moduleInterface"] = $moduleInterface;
        $templatevars["module"] = $module;
        $templatevars["addonModule"] = $addonModule;
        $templatevars["username"] = $username;
        $templatevars["password"] = $password;
        $templatevars["billingcycleList"] = Cycles::cyclesDropDown($billingcycle);
        $templatevars["domainstatusList"] = Product::productStatusDropDown($domainstatus, false, "domainstatus", "prodstatus");
        $templatevars["statusExtra"] = $statusExtra;
        $templatevars["recurLimit"] = $recurLimit;
        $templatevars["recurCountString"] = $recurCountString;
        $templatevars["assignedips"] = $assignedips;
        $templatevars["ns1"] = $ns1;
        $templatevars["ns2"] = $ns2;
        $templatevars["configoptions"] = $configoptions;
        $templatevars["adminbuttonarray"] = $adminbuttonarray;
        $templatevars["subscriptionid"] = $subscriptionid;
        $templatevars["cancelSubscription"] = $cancelSubscription;
        $templatevars["suspendValue"] = $suspendValue;
        $templatevars["overideautosuspend"] = $overideautosuspend;
        $templatevars["autoterminateendcycle"] = $autoterminateendcycle;
        $templatevars["autoterminatereason"] = $autoterminatereason;
        $templatevars["notes"] = $notes;
        $templatevars["customfields"] = $customfields;
        $templatevars["moduleParams"] = $moduleParams;
        $templatevars["addons"] = $addons;
        $templatevars["modulebtns"] = $modulebtns;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        // dd($templatevars);

        return view('pages.clients.viewclients.clientservices.index', $templatevars);
    }

    private function buildCustomModuleButtons($modulebtns, $adminbuttonarray)
    {
        global $frm;
        global $id;
        global $userid;
        global $aid;

        if ($adminbuttonarray) {
            if (!is_array($adminbuttonarray)) {
                return $modulebtns;
            }

            foreach ($adminbuttonarray as $displayLabel => $options) {
                if (is_array($options)) {
                    // TODO:
                    // $href = isset($options["href"]) ? $options["href"] : "?userid=" . $userid . "&id=" . $id;
                    // if ($aid) {
                    //     $href .= "&aid=" . $aid;
                    // }

                    // if (isset($options["customModuleAction"]) && $options["customModuleAction"]) {
                    //     $href .= "&modop=custom&ac=" . $options["customModuleAction"] . "&token=" . generate_token("plain");
                    // }

                    // $submitLabel = isset($options["submitLabel"]) ? $options["submitLabel"] : "";
                    // $submitId = isset($options["submitId"]) ? $options["submitId"] : "";
                    // $modalClass = isset($options["modalClass"]) ? $options["modalClass"] : "";
                    // $modalSize = isset($options["modalSize"]) ? $options["modalSize"] : "";
                    // $disabled = isset($options["disabled"]) && $options["disabled"] ? " disabled=\"disabled\"" : "";
                    // if ($disabled && isset($options["disabledTooltip"]) && $options["disabledTooltip"]) {
                    //     $disabled .= " data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $options["disabledTooltip"] . "\"";
                    // }

                    // if (isset($options["modal"]) && $options["modal"] === true) {
                    //     $modulebtns[] = "<a href=\"" . $href . "\" class=\"btn btn-default open-modal\" data-modal-title=\"" . $options["modalTitle"] . "\" data-modal-size=\"" . $modalSize . "\" data-modal-class=\"" . $modalClass . "\"" . $disabled . ($submitLabel ? " data-btn-submit-label=\"" . $submitLabel . "\" data-btn-submit-id=\"" . $submitId . "\"" : "") . ">" . $displayLabel . "</a>";
                    // } else {
                    //     $modulebtns[] = "<a href=\"" . $href . "\" class=\"btn btn-default" . $options["class"] . "\">" . $displayLabel . "</a>";
                    // }
                } else {
                    $modulebtns[] = "<button type=\"button\" class=\"btn btn-light my-1 mx-1\" data-act=\"$options\" data-label=\"$displayLabel\" onclick=\"modCommand('Custom', this);\"> $displayLabel </button>";
                }
            }
        }

        return $modulebtns;
    }

    public function subscriptionInfo(Request $request)
    {
        $relatedId = $request->get("id");
        $sub = $request->get("sub");

        try {
            if ($sub == "addons") {
                $relatedId = $request->get("aid");
                $relatedItem = \App\Models\Hostingaddon::findOrFail($relatedId);
            } else {
                $relatedItem = \App\Models\Service::findOrFail($relatedId);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }

        $result = \App\Helpers\Subscription::getInfo($relatedItem);
        if (isset($result["errorMsg"])) {
            return ResponseAPI::Error([
                'message' => $result["errorMsg"],
            ]);
        }

        return ResponseAPI::Success([
            'message' => "Success",
            'data' => $result,
        ]);
    }

    public function subscriptionCancel(Request $request)
    {
        $relatedId = $request->get("id");
        $sub = $request->get("sub");

        try {
            if ($sub == "addons") {
                $relatedId = $request->get("aid");
                $relatedItem = \App\Models\Hostingaddon::findOrFail($relatedId);
            } else {
                $relatedItem = \App\Models\Service::findOrFail($relatedId);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid Access Attempt");
        }

        $result = \App\Helpers\Subscription::cancel($relatedItem);
        if (isset($result["errorMsg"])) {
            return ResponseAPI::Error([
                'message' => $result["errorMsg"],
            ]);
        }

        return ResponseAPI::Success([
            'message' => "Success",
            'data' => $result,
        ]);
    }

    public function filterService(Request $request)
    {
        $userid = $request->userid;
        $productselect = $request->productselect;
        $id = $request->productselect;

        if ($productselect) {
            if (substr($productselect, 0, 1) == "a") {
                $aid = (int) substr($productselect, 1);
                $addon = Hostingaddon::with("service")->find($aid);
                if ($addon) {
                    $id = $addon->serviceId;
                    if (!$addon->clientId) {
                        $addon->clientId = $addon->service->userid;
                        $addon->save();
                    }
                }

                return redirect()->route("admin.pages.clients.viewclients.clientservices.editAddon", [
                    "userid" => $userid,
                    "id" => $id,
                    "aid" => $aid,
                ]);
            }
        }

        return redirect()->route("admin.pages.clients.viewclients.clientservices.index", [
            "userid" => $userid,
            "productselect" => $id,
        ]);
    }

    private function valUserID($id)
    {
        return Client::find($id);
    }

    public function getHostingData($id)
    {
        $result = Hosting::selectRaw("{$this->prefix}hosting.*, {$this->prefix}products.servertype, {$this->prefix}products.type, {$this->prefix}products.welcomeemail")
                    ->where("{$this->prefix}hosting.id", $id)
                    ->join("{$this->prefix}products", "{$this->prefix}products.id", "{$this->prefix}hosting.packageid")
                    ->first();

        return $result ? $result->toArray() : [];
    }

    public function getServices($userid, $addonServices)
    {
        $allServices = [];
        $servicesarr = [];
        $result = Hosting::selectRaw("{$this->prefix}hosting.id,{$this->prefix}hosting.domain,{$this->prefix}products.name,{$this->prefix}hosting.domainstatus")
                            ->where("userid", $userid)
                            ->orderBy("domain", "ASC")
                            ->join("{$this->prefix}products", "{$this->prefix}hosting.packageid", "{$this->prefix}products.id")
                            ->get();

        if ($result) {
            foreach ($result->toArray() as $data) {
                $servicelist_id = $data["id"];
                $servicelist_product = $data["name"];
                $servicelist_domain = $data["domain"];
                $servicelist_status = $data["domainstatus"];

                if ($servicelist_domain) {
                    $servicelist_product .= " - " . $servicelist_domain;
                }

                switch ($servicelist_status) {
                    case "Pending":
                        $color = "#FFFFCC";
                        break;
                    case "Suspended":
                        $color = "#CCFF99";
                        break;
                    case "Terminated":
                    case "Cancelled":
                    case "Fraud":
                    case "Completed":
                        $color = "#FF9999";
                        break;
                    default:
                        $color = "#FFF";
                }

                $servicesarr[$servicelist_id] = array($color, $servicelist_product);
                $allServices[$servicelist_id] = array($color, $servicelist_product);
                if (array_key_exists($servicelist_id, $addonServices)) {
                    foreach ($addonServices[$servicelist_id] as $addonServiceKey => $addonService) {
                        $allServices[$addonServiceKey] = $addonService;
                    }
                }
            }
        }

        return [
            "allServices" => $allServices,
            "servicesarr" => $servicesarr
        ];
    }

    public function getAddonServices($userid)
    {
        $addonServices = [];
        $hostingAddonCollection = Hostingaddon::leftJoin("{$this->prefix}addons", "{$this->prefix}addons.id", "{$this->prefix}hostingaddons.addonid")
                                                ->where("{$this->prefix}hostingaddons.userid", $userid)
                                                ->orderBy("name", "ASC")
                                                ->get(["{$this->prefix}hostingaddons.status", "{$this->prefix}hostingaddons.name as name", "{$this->prefix}hostingaddons.hostingid", "{$this->prefix}hostingaddons.id", "{$this->prefix}addons.name as addonName"]);
        foreach ($hostingAddonCollection as $hostingAddon) {
            switch ($hostingAddon->status) {
                case "Pending":
                    $color = "#FFFFCC";
                    break;
                case "Suspended":
                    $color = "#CCFF99";
                    break;
                case "Terminated":
                case "Cancelled":
                case "Fraud":
                case "Completed":
                    $color = "#FF9999";
                    break;
                default:
                    $color = "#FFF";
            }

            $addonName = $hostingAddon->addonName;
            if (!$addonName) {
                $addonName = $hostingAddon->name;
            }

            $value = array($color, "- " . $addonName);
            $addonServices[$hostingAddon->serviceId]["a" . $hostingAddon->id] = $value;
        }

        return $addonServices;
    }

    public function createAddon(Request $request)
    {
        // Request variable
        $id = (int) $request->get("id");
        $userid = (int) $request->get("userid");

        // Custom data
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $paymentmethodlist = (new Gateway($request))->paymentMethodsList();
        $predefaddons = Addon::getAddonDropdownValues();
        $service_data = $this->getHostingData($id);

        // Init data
        $id = @$service_data["id"];
        $serviceBillingCycle = @$service_data["billingcycle"];
        $paymentmethod = @$service_data["paymentmethod"];
        $terminationDate = @$service_data["termination_date"];
        $addonServices = $this->getAddonServices($userid);
        $servicesarr = $this->getServices($userid, $addonServices)["servicesarr"];
        $regdate = $nextduedate = (new HelpersClient)->fromMySQLDate(date("Y-m-d"), 0);
        $status = old('status', "Pending");
        $billingcycle = old('billingcycle', $serviceBillingCycle ?: "Free Account");
        $serversArray = [];
        $gateway = new \App\Module\Gateway();
        if (!$paymentmethod || !$gateway->isActiveGateway($paymentmethod)) {
            $paymentmethod = Functions::ensurePaymentMethodIsSet($userid, $id, "{$this->prefix}hosting");
        }

        // Template vars for view usage
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $id;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["paymentmethodlist"] = $paymentmethodlist;
        $templatevars["gateway"] = $paymentmethod;
        $templatevars["servicesarr"] = $servicesarr;
        $templatevars["regdate"] = $regdate;
        $templatevars["nextduedate"] = $nextduedate;
        $templatevars["terminationDate"] = $terminationDate;
        $templatevars["billingcycle"] = $billingcycle;
        $templatevars["serversArray"] = $serversArray;
        $templatevars["predefaddons"] = $predefaddons;
        $templatevars["billingcycleList"] = Cycles::cyclesDropDown($billingcycle, "", "Free");
        $templatevars["productStatusList"] = Product::productStatusDropDown($status);

        // dd($templatevars);

        return view('pages.clients.viewclients.clientservices.create-addons', $templatevars);
    }

    public function editAddon(Request $request)
    {
        // Request variable
        $id = (int) $request->get("id");
        $aid = (int) $request->get("aid");
        $userid = (int) $request->get("userid");

        // Custom data
        $addonDetails = null;
        $addonModule = null;
        if ($aid && is_numeric($aid)) {
            try {
                $addonDetails = Hostingaddon::with("productAddon", "service")->where("id", $aid)->whereIn("userid", [0, $userid])->firstOrFail();
                if (!$addonDetails->clientId) {
                    $addonDetails->clientId = $addonDetails->service->userid;
                    $addonDetails->save();
                }
            } catch (Exception $e) {
                return redirect()
                        ->back()
                        ->with('type', 'danger')
                        ->with('message', __('admin.erroroccurred'));
            }

            if($addonDetails->productAddon) {
                $addonModule = $addonDetails->productAddon->module;
            }
        }

        if (!$addonDetails) {
            return redirect()
                    ->back()
                    ->with('type', 'danger')
                    ->with('message', __('Invalid ID'));
        }

        $gateway = new \App\Module\Gateway();
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $paymentmethodlist = (new Gateway($request))->paymentMethodsList();
        $predefaddons = Addon::getAddonDropdownValues();
        $service_data = $this->getHostingData($id);
        $addonServices = $this->getAddonServices($userid);
        $servicesarr = $this->getServices($userid, $addonServices)["servicesarr"];

        // Edit data
        $id = $addonDetails->serviceId;
        $aid = $addonDetails->id;
        $addonid = $addonDetails->addonId;
        $customname = $addonDetails->name;
        $recurring = $addonDetails->recurringFee;
        $setupfee = $addonDetails->setupFee;
        $billingcycle = old('billingcycle', $addonDetails->billingCycle);
        $status = old('productstatus', $addonDetails->status);
        $regdate = $addonDetails->registrationDate;
        $nextduedate = $addonDetails->nextdueDate;
        $paymentmethod = $addonDetails->paymentGateway;
        $terminationDate = $addonDetails->terminationDate;
        if (!$paymentmethod || !$gateway->isActiveGateway($paymentmethod)) {
            $paymentmethod = Functions::ensurePaymentMethodIsSet($userid, $aid, "{$this->prefix}hostingaddons");
        }
        $tax = (int) $addonDetails->applyTax;
        $subscriptionid = $addonDetails->subscriptionId;
        $notes = $addonDetails->notes;
        $server = $addonDetails->serverId;
        $regdate = (new Functions)->fromMySQLDate($regdate);
        $nextduedate = (new Functions)->fromMySQLDate($nextduedate);
        $terminationDate = (new Functions)->fromMySQLDate($terminationDate);
        $moduleInterface = null;
        $serversArray = [];
        try {
            $moduleInterface = new \App\Module\Server();
            $moduleInterface->loadByAddonId($aid);

            $serversArray = $moduleInterface->getServerListForModule();
        } catch (\Throwable $th) {
            $moduleInterface = null;
        }

        if (!$server && $serversArray) {
            $server = key($serversArray);
            $addonDetails->serverId = $server;
            $addonDetails->save();
        }

        $adminButtonArray = array();
        $moduleParams = array();
        if ($moduleInterface) {
            if ($moduleInterface->functionExists("AdminCustomButtonArray")) {
                $moduleParams = $moduleInterface->buildParams();
                $adminButtonArray = $moduleInterface->call("AdminCustomButtonArray", $moduleParams);
            }
        }

        $moduleButtons = $this->buildcustommodulebuttons([], $adminButtonArray);

        $customFields = [];
        if ($addonid) {
            $customFields = Customfield::getCustomFields("addon", $addonid, $aid, true);
        }

        $cancelSubscription = "";
        if ($subscriptionid) {
            $gateway = new \App\Module\Gateway();
            $gateway->load($paymentmethod);

            if ($gateway->functionExists("get_subscription_info")) {
                $route = "javascript:void(0);";
                // $cancelSubscription .= "<a href=\"$route\" type=\"button\" class=\"btn btn-sm btn-default mt-2 ml-1\" onclick=\"$('#modalInfoSubscription').modal({ show: true, backdrop: 'static' });\" id=\"btnInfo_Subscription\">\n" . __("admin.getSubscriptionInfo") . "\n </a>";
                $cancelSubscription .= "<a href=\"$route\" type=\"button\" class=\"btn btn-sm btn-light mt-2 ml-1\" onclick=\"modCommand('InfoSubscription');\" id=\"btnInfo_Subscription\">\n" . __("admin.getSubscriptionInfo") . " </a>";
            }

            if ($gateway->functionExists("cancelSubscription")) {
                // $cancelSubscription .= "<button type=\"button\" class=\"btn btn-sm btn-default mt-2 ml-1\" onclick=\"$('#modalCancelSubscription').modal({ show: true, backdrop: 'static' });\" id=\"btnCancel_Subscription\">\n" . __("admin.servicescancelSubscription") . "\n </button>";
                $cancelSubscription .= "<button type=\"button\" class=\"btn btn-sm btn-primary mt-2 ml-1\" onclick=\"modCommand('CancelSubscription');\" id=\"btnCancel_Subscription\">" . __("admin.servicescancelSubscription") . " </button>";
            }
        }

        // Template vars for view usage
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["paymentmethodlist"] = $paymentmethodlist;
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $id;
        $templatevars["aid"] = $aid;
        $templatevars["addonid"] = $addonid;
        $templatevars["customname"] = $customname;
        $templatevars["subscriptionid"] = $subscriptionid;
        $templatevars["notes"] = $notes;
        $templatevars["setupfee"] = $setupfee;
        $templatevars["recurring"] = $recurring;
        $templatevars["tax"] = $tax;
        $templatevars["gateway"] = $paymentmethod;
        $templatevars["servicesarr"] = $servicesarr;
        $templatevars["regdate"] = $regdate;
        $templatevars["nextduedate"] = $nextduedate;
        $templatevars["terminationDate"] = $terminationDate;
        $templatevars["billingcycle"] = $billingcycle;
        $templatevars["server"] = $server;
        $templatevars["serversArray"] = $serversArray;
        $templatevars["predefaddons"] = $predefaddons;
        $templatevars["addonModule"] = $addonModule;
        $templatevars["billingcycleList"] = Cycles::cyclesDropDown($billingcycle, "", "Free");
        $templatevars["productStatusList"] = Product::productStatusDropDown($status);
        $templatevars["moduleInterface"] = $moduleInterface;
        $templatevars["moduleButtons"] = $moduleButtons;
        $templatevars["moduleParams"] = $moduleParams;
        $templatevars["customFields"] = $customFields;
        $templatevars["cancelSubscription"] = $cancelSubscription;

        //  dd($templatevars);

        return view('pages.clients.viewclients.clientservices.edit-addons', $templatevars);
    }

    public function storeAddons(Request $request)
    {
        // dd($request->all());
        $id = (int) $request->get("id");
        $userid = (int) $request->get("userid");

        // TODO
        // checkPermission("Add New Order");

        // Form data
        $billingcycle = $request->get("billingcycle");
        $geninvoice = $request->get("geninvoice");
        $defaultpricing = $request->get("defaultpricing");
        $addonid = $request->get("addonid");
        $name = $request->get("name");
        $setupfee = $request->get("setupfee");
        $recurring = $request->get("recurring");
        $billingcycle = $request->get("billingcycle");
        $status = $request->get("productstatus");
        $regdate = $request->get("regdate");
        $nextduedate = $request->get("nextduedate");
        $paymentmethod = $request->get("paymentmethod");
        $tax = $request->get("tax");
        $notes = $request->get("notes");
        $terminationDateValid = true;
        $queryStr = ["userid" => $userid, "id" => $id];

        if ($billingcycle == "Free" || $billingcycle == "Free Account") {
            $setupfee = $recurring = 0;
            $nextduedate = (new HelpersClient)->fromMySQLDate("0000-00-00");
        }

        $predefname = "";
        if ($addonid) {
            $productAddon = Addon::find($addonid);
            $addonid = $productAddon->id;
            $predefname = $productAddon->name;
            $tax = $productAddon->applyTax;

            if ($defaultpricing) {
                $availableCycleTypes = $productAddon->billingCycle;
                $currency = (new AdminFunctions())->getCurrency($userid);
                $pricing = new \App\Helpers\Pricing();
                $pricing->loadPricing("addon", $addonid, $currency);

                switch ($availableCycleTypes) {
                    case "recurring":
                        $availableCycles = $pricing->getAvailableBillingCycles();
                        $billingcycle = (new \App\Helpers\Cycles())->getNormalisedBillingCycle($billingcycle);
                        if (!in_array($billingcycle, $availableCycles)) {
                            $billingcycle = $pricing->getFirstAvailableCycle();
                        }
                        $setupfee = $pricing->getSetup($billingcycle);
                        $recurring = $pricing->getPrice($billingcycle);
                        $billingcycle = (new \App\Helpers\Cycles())->getPublicBillingCycle($billingcycle);
                        break;
                    case "free":
                        $billingcycle =\App\Helpers\Cycles::DISPLAY_FREE;
                        $setupfee = $recurring = 0;
                        break;
                    case "onetime":
                        $billingCycle =\App\Helpers\Cycles::DISPLAY_ONETIME;
                        $setupfee = $pricing->getSetup("monthly");
                        $recurring = $pricing->getPrice("monthly");
                        break;
                    default:
                        $billingcycle = $availableCycleTypes;
                        $setupfee = $pricing->getSetup("monthly");
                        $recurring = $pricing->getPrice("monthly");
                }
            }
        }

        $newAddon = new Hostingaddon();
        $newAddon->hostingid = $id;
        $newAddon->addonid = $addonid;
        $newAddon->userid = $userid;
        $newAddon->name = $name;
        $newAddon->setupfee = $setupfee;
        $newAddon->recurring = $recurring;
        $newAddon->billingcycle = $billingcycle;
        $newAddon->status = $status;
        $newAddon->regdate = (new SystemHelper())->toMySQLDate($regdate);
        $newAddon->nextduedate = (new SystemHelper())->toMySQLDate($nextduedate);
        $newAddon->nextinvoicedate = (new SystemHelper())->toMySQLDate($nextduedate);
        $newAddon->termination_date = in_array($status, ["Terminated", "Cancelled"]) ? date("Y-m-d") : "0000-00-00";
        $newAddon->paymentmethod = $paymentmethod;
        $newAddon->tax = (int) $tax;
        $newAddon->notes = $notes;
        $newAddon->save();

        $newaddonid = $newAddon->id;
        LogActivity::Save("Added New Addon - $name $predefname - Addon ID: $newaddonid - Service ID: $id", $userid);
        if ($geninvoice) {
            $invoiceid = ProcessInvoices::createInvoices($userid, "", "", ["addons" => [$newaddonid]]);
        }

        Hooks::run_hook("AddonAdd", ["id" => $newaddonid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid]);
        if ($terminationDateValid) {
            $queryStr["success"] = true;
        }

        $redirect = 'admin.pages.clients.viewclients.clientservices.index';
        return $this->getResponse("modifyproductservices", $queryStr, $redirect);

        // return redirect()
        //             ->route("admin.pages.clients.viewclients.clientservices.index", $queryStr)
        //             ->with('type', 'success')
        //             ->with('message', AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.changesuccessdesc")));
    }

    public function updateAddons(Request $request)
    {
        // Helpers
        $funcHelp = new Functions();
        $sysHelp = new SystemHelper();

        // dd($request->all());
        $id = (int) $request->get("id");
        $aid = (int) $request->get("aid");
        $userid = (int) $request->get("userid");

        // TODO
        // checkPermission("Add New Order");

        // Form data
        $billingcycle = $request->get("billingcycle");
        $geninvoice = $request->get("geninvoice");
        $defaultpricing = $request->get("defaultpricing");
        $addonid = $request->get("addonid");
        $name = $request->get("name");
        $setupfee = $request->get("setupfee");
        $recurring = $request->get("recurring");
        $billingcycle = $request->get("billingcycle");
        $status = $request->get("productstatus");
        $regdate = $request->get("regdate");
        $nextduedate = $request->get("nextduedate");
        $terminationDate = $request->get("termination_date");
        $paymentmethod = $request->get("paymentmethod");
        $subscriptionid = $request->get("subscriptionid");
        $tax = $request->get("tax");
        $notes = $request->get("notes");
        $server = $request->get("server");
        $terminationDateValid = true;
        $queryStr = ["userid" => $userid, "id" => $id];

        if ($billingcycle == "Free" || $billingcycle == "Free Account") {
            $setupfee = $recurring = 0;
            $nextduedate = $funcHelp->fromMySQLDate("0000-00-00");
        }

        $addonDetails = Hostingaddon::where("id", $aid)->where("userid", $userid)->first();
        $queryStr['aid'] = $aid;
        if (!$addonDetails) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('Invalid ID'));
        }

        $oldStatus = $addonDetails->status;
        $oldAddonId = $addonDetails->addonId;
        if (!in_array($sysHelp->toMySQLDate($terminationDate), array("0000-00-00", "1970-01-01")) &&
            !in_array($status, array("Terminated", "Cancelled")) &&
            !in_array($addonDetails->status, array("Terminated", "Cancelled"))
        ) {
            $terminationDateValid = false;
            $queryStr["terminationdateinvalid"] = 1;
        }

        if (in_array($status, array("Terminated", "Cancelled")) && in_array($sysHelp->toMySQLDate($terminationDate), array("0000-00-00", "1970-01-01"))) {
            $terminationDate = $funcHelp->fromMySQLDate(date("Y-m-d"));
        } else {
            if (!in_array($status, array("Terminated", "Cancelled")) && !in_array($sysHelp->toMySQLDate($terminationDate), array("0000-00-00", "1970-01-01"))) {
                $terminationDate = $funcHelp->fromMySQLDate("0000-00-00");
            }
        }

        $changelog = [];
        $forceServerReset = false;
        $newAddon = NULL;
        $newServer = 0;

        if ($id != $addonDetails->serviceId) {
            $changelog[] = "Transferred Addon from Service ID: $addonDetails->serviceId to Service ID: $id";
            $addonDetails->serviceId = $id;
        }

        if ($addonid != $addonDetails->addonId) {
            $addonsCollections = Addon::whereIn("id", [$addonid, $addonDetails->addonId])->get();
            $addonModules = [];

            foreach ($addonsCollections as $addonsCollection) {
                $addonModules[$addonsCollection->id] = $addonsCollection;
            }

            $oldServerModule = "";
            $newServerModule = "";
            if ($addonDetails->addonId) {
                $oldServerModule = $addonModules[$addonDetails->addonId]->servertype;
            }

            if ($addonid) {
                $newServerModule = $addonModules[$addonid]->servertype;
            }

            if ($oldServerModule != $newServerModule) {
                $forceServerReset = true;
                $newAddon = $addonModules[$addonid];
            }

            unset($addonModules);
            $changelog[] = "Addon Id changed from $addonDetails->addonId to $addonid";
            $addonDetails->addonId = $addonid;
        }

        if ($addonDetails->name != $name) {
            $changelog[] = "Addon Name changed from $addonDetails->name to $name";
            $addonDetails->name = $name;
        }

        if ($addonDetails->setupFee != $setupfee) {
            $changelog[] = "Setup Fee changed from $addonDetails->setupFee to $setupfee";
            $addonDetails->setupFee = $setupfee;
        }

        if ($addonDetails->recurringFee != $recurring) {
            $changelog[] = "Recurring Fee changed from $addonDetails->recurringFee to $recurring";
            $addonDetails->recurringFee = $recurring;
        }

        if ($addonDetails->billingCycle != $billingcycle) {
            $changelog[] = "Billing Cycle changed from $addonDetails->billingCycle to $billingcycle";
            $addonDetails->billingCycle = $billingcycle;
        }

        if ($addonDetails->status != $status) {
            $changelog[] = "Status changed from $addonDetails->status to $status";
            $addonDetails->status = $status;
        }

        if ($funcHelp->fromMySQLDate($addonDetails->registrationDate) != $regdate) {
            $changelog[] = "Registration Date changed from " . $funcHelp->fromMySQLDate($addonDetails->registrationDate) . " to $regdate";
            $addonDetails->registrationDate = $sysHelp->toMySQLDate($regdate);
        }

        if ($funcHelp->fromMySQLDate($addonDetails->nextDueDate) != $nextduedate) {
            $changelog[] = "Next Due Date changed from " . $funcHelp->fromMySQLDate($addonDetails->nextDueDate) . " to $nextduedate";
            $addonDetails->nextDueDate = $sysHelp->toMySQLDate($nextduedate);
            $addonDetails->nextInvoiceDate = $sysHelp->toMySQLDate($nextduedate);
        }

        if ($funcHelp->fromMySQLDate($addonDetails->terminationDate) != $terminationDate) {
            $changelog[] = "Termination Date changed from " . $funcHelp->fromMySQLDate($addonDetails->terminationDate) . " to $terminationDate";
            $addonDetails->terminationDate = $sysHelp->toMySQLDate($terminationDate);
        }

        if ($addonDetails->paymentGateway != $paymentmethod) {
            $changelog[] = "Payment Gateway changed from $addonDetails->paymentGateway to $paymentmethod";
            $addonDetails->paymentGateway = $paymentmethod;
        }

        if ($addonDetails->applyTax != (int) $tax) {
            $taxEnabledDisabled = "Disabled";
            if ($tax) {
                $taxEnabledDisabled = "Enabled";
            }
            $changelog[] = "Tax $taxEnabledDisabled";
            $addonDetails->applyTax = (int) $tax;
        }

        if ($addonDetails->subscriptionId != $subscriptionid) {
            $changelog[] = "Subscription ID Changed from $addonDetails->subscriptionId to $subscriptionid";
            $addonDetails->subscriptionId = $subscriptionid;
        }

        if ($addonDetails->notes != $notes) {
            $changelog[] = "Addon Notes changed";
            $addonDetails->notes = $notes;
        }

        $moduleInterface = null;
        try {
            $moduleInterface = new \App\Module\Server();
            $moduleInterface->loadByAddonId($aid);
        } catch (\Throwable $th) {
            $moduleInterface = null;
        }

        if ($forceServerReset) {
            $server = "";
            if ($moduleInterface) {
                $server = $moduleInterface->getServerID($newAddon->module, $newAddon->serverGroupId);
                $changelog[] = "Server Id automatically changed from $addonDetails->serverId to $server";
                $addonDetails->serverId = $server;
            }
        } else {
            if ($addonDetails->serverId != $server) {
                $changelog[] = "Server Id changed from $addonDetails->serverId to $server";
                $addonDetails->serverId = $server;
            }
        }

        \App\Helpers\Customfield::migrateCustomFieldsBetweenProductsOrAddons($aid, $addonid, $oldAddonId, true, true);
        if ($changelog) {
            $addonDetails->save();
            LogActivity::Save("Modified Addon - " . implode(", ", $changelog) . " - User ID: $userid - Addon ID: $aid", $userid);
        }

        if ($moduleInterface) {
            if ($moduleInterface->functionExists("AdminServicesTabFieldsSave")) {
                $moduleParams = $moduleInterface->buildParams();
                $adminServicesTabFieldsSaveErrors = $moduleInterface->call("AdminServicesTabFieldsSave", $moduleParams);
                if ($adminServicesTabFieldsSaveErrors && !is_array($adminServicesTabFieldsSaveErrors) && $adminServicesTabFieldsSaveErrors != "success") {
                    session()->put("adminServicesTabFieldsSaveErrors", $adminServicesTabFieldsSaveErrors);
                    // session(["adminServicesTabFieldsSaveErrors" => $adminServicesTabFieldsSaveErrors]);
                }
            }
        }

        Hooks::run_hook("AdminClientServicesTabFieldsSave", $request->all());
        if ($oldStatus == "Suspended" && $status == "Active") {
            Hooks::run_hook("AddonUnsuspended", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else if ($oldStatus != "Active" && $status == "Active") {
            Hooks::run_hook("AddonActivated", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else if ($oldStatus != "Suspended" && $status == "Suspended") {
            Hooks::run_hook("AddonSuspended", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else if ($oldStatus != "Terminated" && $status == "Terminated") {
            Hooks::run_hook("AddonTerminated", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else if ($oldStatus != "Cancelled" && $status == "Cancelled") {
            Hooks::run_hook("AddonCancelled", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else if ($oldStatus != "Fraud" && $status == "Fraud") {
            Hooks::run_hook("AddonFraud", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        } else {
            Hooks::run_hook("AddonEdit", array("id" => $aid, "userid" => $userid, "serviceid" => $id, "addonid" => $addonid));
        }

        $redirect = 'admin.pages.clients.viewclients.clientservices.editAddon';
        if ($terminationDateValid) {
            $queryStr["success"] = true;
        }

        return $this->getResponse("modifyproductservices", $queryStr, $redirect);
    }

    public function update(Request $request)
    {
        // dd($request->all());

        $userid = $request->userid;
        $id = $request->id;
        $aid = $request->aid;

        $packageid = $request->get("packageid");
        $oldserviceid = $request->get("oldserviceid");
        $addonid = $request->get("addonid");
        $name = $request->get("name");
        $setupfee = $request->get("setupfee");
        $recurring = $request->get("recurring");
        $billingcycle = $request->get("billingcycle");
        $status = $request->get("domainstatus");
        $regdate = $request->get("regdate");
        $terminationDate = $request->get("termination_date");
        $oldnextduedate = $request->get("oldnextduedate");
        $nextduedate = $request->get("nextduedate");
        $overidesuspenduntil = $request->get("overidesuspenduntil");
        $paymentmethod = $request->get("paymentmethod");
        $tax = $request->get("tax");
        $promoid = $request->get("promoid");
        $subscriptionid =  $request->get("subscriptionid");
        $notes = $request->get("notes");
        $configoption = $request->get("configoption");
        $server = $request->get("server");
        $terminationDateValid = true;
        $queryStr = ["userid" => $userid, "id" => $id];

        $result = Hosting::selectRaw("{$this->prefix}hosting.*, {$this->prefix}products.servertype, {$this->prefix}products.type, {$this->prefix}products.welcomeemail")
                            ->where("{$this->prefix}hosting.id", $id)
                            ->join("{$this->prefix}products", "{$this->prefix}products.id", "{$this->prefix}hosting.packageid")
                            ->first();

        $service_data = $result ? $result->toArray() : [];
        $id = @$service_data["id"];

        if (is_string($terminationDate) && trim($terminationDate) == "") {
            $terminationDate = preg_replace("/[MDY]/i", "0", (string) Cfg::getValue("DateFormat"));
        }

        if (is_string($overidesuspenduntil) && trim($overidesuspenduntil) == "") {
            $overidesuspenduntil = preg_replace("/[MDY]/i", "0", (string) Cfg::getValue("DateFormat"));
        }

        if ($aid) {
            // TODO
        } else{
            if ((new SystemHelper())->toMySQLDate($terminationDate) != "0000-00-00" && !in_array($status, array("Terminated", "Cancelled"))) {
                $oldstatus = $service_data["domainstatus"];
                if (!in_array($oldstatus, array("Terminated", "Cancelled"))) {
                    $terminationDateValid = false;
                    $queryStr['terminationdateinvalid'] = "1";
                }
            }
        }

        if (!$request->get("packageid") && !$request->get("billingcycle")) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with("type", 'danger')
                    ->with('message', 'Package ID and Billingcycle are Required');
        }

        $currency = (new AdminFunctions())->getCurrency($userid);
        Hooks::run_hook("PreServiceEdit", array("serviceid" => $id));
        Hooks::run_hook("PreAdminServiceEdit", array("serviceid" => $id));

        $errors = array();
        $changelog = array();
        $configoptions = ConfigOptions::getCartConfigOptions($packageid, $configoption, $billingcycle);
        $configoptionsrecurring = 0;

        foreach ($configoptions as $configoption) {
            $configoptionsrecurring += $configoption["selectedrecurring"];
            // $result = select_query("tblhostingconfigoptions", "COUNT(*)", array("relid" => $id, "configid" => $configoption["id"]));
            $data = Hostingconfigoption::where("relid", $id)->where("configid", $configoption["id"])->count();
            if (!$data) {
                // insert_query("tblhostingconfigoptions", array("relid" => $id, "configid" => $configoption["id"]));
                $newConfig = new Hostingconfigoption();
                $newConfig->relid = $id;
                $newConfig->configid = $configoption["id"];
                $newConfig->save();
            }

            // update_query("tblhostingconfigoptions", array("optionid" => $configoption["selectedvalue"], "qty" => $configoption["selectedqty"]), array("relid" => $id, "configid" => $configoption["id"]));
            Hostingconfigoption::where("relid", $id)->where("configid", $configoption["id"])->update(["optionid" => $configoption["selectedvalue"], "qty" => $configoption["selectedqty"]]);
        }

        $autorecalcrecurringprice = $request->get("autorecalcrecurringprice");
        $newamount = $autorecalcrecurringprice ? \App\Helpers\ProcessInvoices::recalcRecurringProductPrice($id, $userid, $packageid, $billingcycle, $configoptionsrecurring, $promoid) : "-1";
        // $oldCustomFieldValues = \App\Helpers\Customfield::getCustomFields("product", $service_data["packageid"], $id, true);
        // foreach ($oldCustomFieldValues as $oldVal) {
        //     $newVal = $request->input("customfield.".$oldVal["id"]);
        //     if ($oldVal["value"] != $newVal) {
        //         $values = array($oldVal["value"], $newVal);
        //         switch ($oldVal["type"]) {
        //             case "link":
        //                 $newLink = "<a href=\"" . $values[1] . "\" target=\"_blank\">" . $values[1] . "</a>";
        //                 if ($values[0] != $newLink) {
        //                     $changelog[] = $oldVal["name"] . " changed to " . $values[1];
        //                 }
        //                 break;
        //             case "password":
        //                 $changelog[] = $oldVal["name"] . " changed";
        //                 break;
        //             case "dropdown":
        //             case "tickbox":
        //                 $valueMap = array("dropdown" => "None", "tickbox" => "off");
        //                 foreach ($values as $k => $v) {
        //                     if ($v == "") {
        //                         $values[$k] = $valueMap[$oldVal["type"]];
        //                     }
        //                 }
        //             break;
        //             case 'image':
        //                 dd($oldVal, $request->get("customfield", $oldVal["id"]), $values, $request->all());
        //             break;
        //             default:
        //                 $changelog[] = $oldVal["name"] . " changed from " . $values[0] . " to " . is_array($values[1]) ? implode(",", $values[1]) : $values[1];
        //                 break;
        //         }
        //     }
        // }

        \App\Helpers\Customfield::migrateCustomFieldsBetweenProductsOrAddons($id, $packageid, $service_data["packageid"], true);
        $logchangefields = [
            "regdate" => "Registration Date",
            "packageid" => "Product/Service",
            "server" => "Server",
            "domain" => "Domain",
            "dedicatedip" => "Dedicated IP",
            "paymentmethod" => "Payment Method",
            "firstpaymentamount" => "First Payment Amount",
            "amount" => "Recurring Amount",
            "billingcycle" => "Billing Cycle",
            "nextduedate" => "Next Due Date",
            "domainstatus" => "Status",
            "termination_date" => "Termination Date",
            "username" => "Username",
            "password" => "Password",
            "notes" => "Admin Notes",
            "subscriptionid" => "Subscription ID",
            "promoid" => "Promotion Code ID",
            "overideautosuspend" => "Override Auto-Suspend",
            "overidesuspenduntil" => "Override Auto-Suspend Until Date"
        ];

        $forceServerReset = false;
        $newProduct = NULL;
        $newServer = 0;
        $moduleInterface = new \App\Module\Server();
        $moduleInterface->loadByServiceID($id);

        foreach ($logchangefields as $fieldname => $displayname) {
            $newval = $request->get($fieldname);
            $oldval = $service_data[$fieldname];
            if (($fieldname == "nextduedate" || $fieldname == "overidesuspenduntil" || $fieldname == "termination_date") && !$newval) {
                $newval = "0000-00-00";
            } else {
                if ($fieldname == "regdate" || $fieldname == "nextduedate" || $fieldname == "overidesuspenduntil" || $fieldname == "termination_date") {
                    $newval = (new SystemHelper())->toMySQLDate($newval);
                } else {
                    if ($fieldname == "password") {
                        if ($newval != (new Pwd())->decrypt($oldval)) {
                            $changelog[] = $displayname . " changed";
                        }
                        continue;
                    }
                    if ($fieldname == "amount" && 0 <= $newamount) {
                        $newval = $newamount;
                    } else {
                        if ($fieldname == "packageid" && $newval != $oldval) {
                            $productsCollections = ModelsProduct::whereIn("id", array($newval, $oldval))->get();
                            $productModules = array();
                            foreach ($productsCollections as $productsCollection) {
                                $productModules[$productsCollection->id] = $productsCollection;
                            }
                            if ($productModules[$newval]->servertype != $productModules[$oldval]->servertype) {
                                $forceServerReset = true;
                                $newProduct = $productModules[$newval];
                            }
                            unset($productModules);
                        } else {
                            if ($fieldname == "server" && $forceServerReset) {
                                if ($newProduct != null) {
                                    $newval = $moduleInterface->getServerID($newProduct->module, $newProduct->serverGroupId);
                                    $newServer = $newval;
                                }
                            } else {
                                if ($fieldname == "overideautosuspend" && $newval == "") {
                                    $newval = "0";
                                }
                            }
                        }
                    }
                }
            }

            if ($newval != $oldval) {
                $changelog[] = $displayname . " changed from " . $oldval . " to " . $newval;
            }
        }

        $updatearr = [];
        $updatefields = [
            "server",
            "packageid",
            "domain",
            "paymentmethod",
            "firstpaymentamount",
            "amount",
            "billingcycle",
            "regdate",
            "nextduedate",
            "username",
            "password",
            "notes",
            "subscriptionid",
            "promoid",
            "overideautosuspend",
            "overidesuspenduntil",
            "ns1",
            "ns2",
            "domainstatus",
            "termination_date",
            "dedicatedip",
            "assignedips"
        ];

        foreach ($updatefields as $fieldname) {
            $newval = $request->get($fieldname);
            if ($fieldname !== "password") {
                $newval = trim($newval);
            }
            if (in_array($fieldname, array("termination_date", "overidesuspenduntil")) && is_string($newval) && trim($newval) == "") {
                $newval = preg_replace("/[MDY]/i", "0", (string) Cfg::getValue("DateFormat"));
            }
            if ($fieldname == "domainstatus" && $newval == "Completed" && $service_data["domainstatus"] != "Completed") {
                $updatearr["completed_date"] = Carbon::today()->toDateString();
            }
            if ($fieldname == "regdate" || $fieldname == "nextduedate" || $fieldname == "overidesuspenduntil" || $fieldname == "termination_date") {
                if ($fieldname == "nextduedate" && in_array($billingcycle, array("Free Account", "One Time"))) {
                    $newval = "0000-00-00";
                } else {
                    if ($fieldname == "termination_date" && !in_array((new SystemHelper())->toMySQLDate($newval), array("0000-00-00", "1970-01-01")) && !in_array($status, array("Terminated", "Cancelled"))) {
                        $newval = "0000-00-00";
                        $changelog[] = "Termination Date reset to " . $newval;
                    } else {
                        if ($fieldname == "termination_date" && in_array((new SystemHelper())->toMySQLDate($newval), array("0000-00-00", "1970-01-01")) && $service_data["termination_date"] == "0000-00-00" && in_array($status, array("Terminated", "Cancelled"))) {
                            $newval = date("Y-m-d");
                            $terminationDate = date("Y-m-d");
                            $updatearr["termination_date"] = date("Y-m-d");
                        } else {
                            if (Functions::validateDateInput($newval) || in_array($fieldname, array("overidesuspenduntil", "termination_date")) && (!$newval || in_array((new SystemHelper())->toMySQLDate($newval), array("0000-00-00", "1970-01-01")))) {
                                $newval = (new SystemHelper())->toMySQLDate($newval);
                            } else {
                                $errors[] = "The " . $logchangefields[$fieldname] . " you entered is invalid";
                            }
                        }
                    }
                }
            } else {
                if ($fieldname == "password") {
                    $newval = (new Pwd())->encrypt($newval);
                } else {
                    if ($fieldname == "amount" && 0 <= $newamount) {
                        $newval = $newamount;
                    } else {
                        if ($fieldname == "server" && $forceServerReset) {
                            $newval = $newServer;
                        } else {
                            if ($fieldname == "promoid" && $newval != $service_data["promoid"]) {
                                $updatearr["promocount"] = "0";
                            }
                        }
                    }
                }
            }

            $updatearr[$fieldname] = $newval;
        }

        if ((new SystemHelper())->toMySQLDate($request->get("oldnextduedate")) != $updatearr["nextduedate"]) {
            $updatearr["nextinvoicedate"] = $updatearr["nextduedate"];
        }

        if (count($errors) == 0) {
            if ($updatearr) {
                // update_query("tblhosting", $updatearr, array("id" => $id));
                Hosting::where("id", $id)->update($updatearr);
            }
            if ($changelog) {
                LogActivity::Save("Modified Product/Service - " . implode(", ", $changelog) . " - User ID: " . $userid . " - Service ID: " . $id, $userid);
            }
            $cancelid = \DB::table("{$this->prefix}cancelrequests")->where("relid", $id)->orderBy("id", "desc")->first();
            $autoterminateendcycle = $request->get("autoterminateendcycle");
            $autoterminatereason = $request->get("autoterminatereason");
            if ($autoterminateendcycle) {
                if ($cancelid && $cancelid->type == "Immediate") {
                    \DB::table("{$this->prefix}cancelrequests")->where("id", $cancelid->id)->update(["reason" => $autoterminatereason, "type" => "End of Billing Period"]);
                } else {
                    if (!$cancelid) {
                        (new HelpersClient())->CreateCancellationRequest($userid, $id, $autoterminatereason, "End of Billing Period");
                    }
                }
            } else {
                if ($cancelid && $cancelid->type == "End of Billing Period") {
                    \DB::table("{$this->prefix}cancelrequests")->where("id", $cancelid->id)->delete($cancelid->id);
                    LogActivity::Save("Removed Automatic Cancellation for End of Current Cycle - Service ID: " . $id, $userid);
                }
            }
            $module = \DB::table("{$this->prefix}products")->where("id", $packageid)->first("servertype");
            if ($module) {
                // $moduleInterface = new WHMCS\Module\Server();
                if ($moduleInterface->loadByServiceID($id) && $moduleInterface->functionExists("AdminServicesTabFieldsSave")) {
                    $moduleParams = $moduleInterface->buildParams();
                    $adminServicesTabFieldsSaveErrors = $moduleInterface->call("AdminServicesTabFieldsSave", $moduleParams);
                    if ($adminServicesTabFieldsSaveErrors && !is_array($adminServicesTabFieldsSaveErrors) && $adminServicesTabFieldsSaveErrors != "success") {
                        session(["adminServicesTabFieldsSaveErrors" => $adminServicesTabFieldsSaveErrors]);
                    }
                }
            }

            Hooks::run_hook("AdminClientServicesTabFieldsSave", $request->all());
            Hooks::run_hook("AdminServiceEdit", array("userid" => $userid, "serviceid" => $id));
            Hooks::run_hook("ServiceEdit", array("userid" => $userid, "serviceid" => $id));

            if ($terminationDateValid) {
                $queryStr["success"] = true;
            }
        }

        if (isset($queryStr["success"])) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientservices.index", $queryStr)
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.changesuccessdesc")));
        } else if(isset($queryStr['terminationdateinvalid'])) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientservices.index", $queryStr)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.clientsterminationdateinvalid")));
        }

        $errormsg = "";
        if (count($errors)) {
            foreach ($errors as $error) {
                $errormsg .= $error . "<br />";
            }
        }

        return redirect()
                ->route("admin.pages.clients.viewclients.clientservices.index", $queryStr)
                ->withInput()
                ->with('type', 'danger')
                ->with('message', AdminFunctions::infoBoxMessage(__("admin.followingerrorsoccurred"), $errormsg));
    }

    public function delete(Request $request)
    {
        if (!AdminFunctions::checkPermission("Delete Clients Products/Services")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $userid = $request->userid;
        $id = $request->id;

        Hooks::run_hook("ServiceDelete", ["userid" => $userid, "serviceid" => $id]);

        try {
            $service = Hosting::with("product", "customFieldValues", "customFieldValues.customField", "addons", "addons.customFieldValues", "addons.customFieldValues.customField")->find($id);
            if (!$service) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Service not found!'),
                ]);
            }

            if ($service->product->stockControlEnabled) {
                $service->product->quantityInStock++;
                $service->product->save();
            }

            // Delete serviceAddon
            // dd($service->addons)
            foreach ($service->addons as $serviceAddon) {
                foreach ($serviceAddon->customFieldValues as $customFieldValue) {
                    if ($customFieldValue->customField->type == "addon") {
                        $customFieldValue->delete();
                    }
                }

                $serviceAddon->delete();
            }

            // Delete customFieldValue
            // dd($service->customFieldValues)
            foreach ($service->customFieldValues as $customFieldValue) {
                if ($customFieldValue->customField->type == "product") {
                    $customFieldValue->delete();
                }
            }

            // Delete the service
            $service->delete();

            Hostingconfigoption::where("relid", $id)->delete();
            AffiliateAccount::where("relid", $id)->delete();
            LogActivity::Save("Deleted Product/Service - User ID: $userid - Service ID: $id", $userid);
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', $e->getMessage()),
            ]);
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
        ]);
    }

    public function deleteAddon(Request $request)
    {
        if (!AdminFunctions::checkPermission("Delete Clients Products/Services")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $userid = $request->userid;
        $id = $request->id;
        $aid = $request->aid;

        Hooks::run_hook("AddonDeleted", ["id" => $aid]);
        $addon = Hostingaddon::find($aid);
        if (!$addon) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Addon not found!'),
            ]);
        }

        $addon->delete();
        LogActivity::Save("Deleted Addon - User ID: $userid - Service ID: $id - Addon ID: $aid", $userid);
        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
        ]);
    }

    public function moduleCommand(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;
        $modop = $request->modop;

        if (!AdminFunctions::checkPermission("Perform Server Operations")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        switch ($modop) {
            case 'create':
                return $this->create($request);
            case 'renew':
                return $this->renew($request);
            case 'suspend':
                return $this->suspend($request);
            case 'unsuspend':
                return $this->unsuspend($request);
            case 'terminate':
                return $this->terminate($request);
            case 'changepackage':
                return $this->changepackage($request);
            case 'changepw':
                return $this->changepw($request);
            case 'manageapplinks':
                return $this->manageapplinks($request);
            case 'singlesignon':
                return $this->singlesignon($request);
            case 'custom':
                return $this->custom($request);
            case 'delete':
                return $this->delete($request);
            case 'deleteaddons':
                return $this->deleteAddon($request);
            case 'infosubscription':
                return $this->subscriptionInfo($request);
            case 'cancelsubscription':
                return $this->subscriptionCancel($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function create(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;

        try {
            if (0 < $aid) {
                $result = Hostingaddon::find($aid)->legacyProvision();
            } else {
                $result = Hosting::find($id)->legacyProvision();
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $this->getResponse($modop, $result);
    }

    private function renew(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;

        $result = (new \App\Module\Server())->ServerRenew($id, (int) $aid);
        return $this->getResponse($modop, $result);
    }

    private function suspend(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;
        $suspreason = $request->suspreason;
        $suspemail = (bool) $request->suspemail;

        $result = (new \App\Module\Server())->ServerSuspendAccount($id, $suspreason, (int) $aid);

        if ($result == "success" && $suspemail == "true") {
            $emailTemplate = Emailtemplate::where("type", "product")->where("name", "Service Suspension Notification")->get()->first();
            if (!is_null($emailTemplate)) {
                $isDisabled = $emailTemplate->disabled;
                if ($isDisabled) {
                    $emailTemplate->disabled = 0;
                    $emailTemplate->save();
                }

                Functions::sendMessage("Service Suspension Notification", $id);
                if ($isDisabled) {
                    $emailTemplate->disabled = $isDisabled;
                    $emailTemplate->save();
                }
            }
        }

        return $this->getResponse($modop, $result);
    }

    private function unsuspend(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;
        $sendEmail = (bool) $request->unsuspended_email;

        $result = (new \App\Module\Server())->ServerUnsuspendAccount($id, (int) $aid);

        if ($result == "success" && $sendEmail == "true") {
            $emailTemplate = Emailtemplate::where("type", "product")->where("name", "Service Unsuspension Notification")->get()->first();
            if (!is_null($emailTemplate)) {
                $isDisabled = $emailTemplate->disabled;
                if ($isDisabled) {
                    $emailTemplate->disabled = 0;
                    $emailTemplate->save();
                }

                Functions::sendMessage("Service Unsuspension Notification", $id);
                if ($isDisabled) {
                    $emailTemplate->disabled = $isDisabled;
                    $emailTemplate->save();
                }
            }
        }

        return $this->getResponse($modop, $result);
    }

    private function terminate(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;
        $keepZone = (bool) $request->keep_zone;
        $invoiceUsage = (bool) $request->invoice_usage;

        $result = (new \App\Module\Server())->ModuleCallFunction("Terminate", $id, ["keepZone" => $keepZone, "invoiceUsage" => $invoiceUsage], $aid);

        return $this->getResponse($modop, $result);
    }

    private function changepackage(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;

        $result = (new \App\Module\Server())->ServerChangePackage($id, (int) $aid);

        return $this->getResponse("updown", $result);
    }

    private function changepw(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;

        $result = (new \App\Module\Server())->ServerChangePassword($id, (int) $aid);

        return $this->getResponse("pwchange", $result);
    }

    private function manageapplinks(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;

        $moduleInterface = new \App\Module\Server();
        if ((int) $aid) {
            $moduleInterface->loadByAddonId((int) $aid);
        } else {
            $moduleInterface->loadByServiceID($id);
        }

        $result = [];
        try {
            $moduleInterface->doSingleApplicationLinkCall($request->get("command"));
            // $success = true;
            // $errorMsg = [];

            $result['success'] = true;
        } catch (\Exception $e) {
            // $success = false;
            // $errorMsg = $e->getMessage();
            $result['error'] = $e->getMessage();
        }

        return $this->getResponse($modop, $result);
    }

    private function singlesignon(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;
        $server = $request->get("server");

        $serverId = (int) $server;
        $extra = "";

        $addonDetails = Hostingaddon::where("id", "=", $aid)->whereIn("userid", [0, $userid])->first();

        if ($addonDetails) {
            $serverId = $addonDetails->server;
            $extra = "&aid=" . $aid;
        }

        $allowedRoleIds = \DB::table("{$this->prefix}serversssoperms")->where("server_id", $serverId)->pluck("role_id");
        if (count($allowedRoleIds) == 0) {
            $allowAccess = true;
        } else {
            $allowAccess = false;
            // $adminAuth = new WHMCS\Auth();
            // $adminAuth->getInfobyID(WHMCS\Session::get("adminid"));
            $adminRoleId = auth()->guard('admin')->user()->roleid;
            if (in_array($adminRoleId, $allowedRoleIds)) {
                $allowAccess = true;
            }
        }

        $result = "";
        if (!$allowAccess) {
            $result = "You do not have permisson to sign-in to this server. If you feel this message to be an error, please contact the system administrator.";
            return $this->getResponse($modop, $result);
        }

        $redirectUrl = "";
        try {
            $moduleInterface = new \App\Module\Server();
            if ((int) $aid) {
                $moduleInterface->loadByAddonId((int) $aid);
            } else {
                $moduleInterface->loadByServiceID($id);
            }

            $redirectUrl = $moduleInterface->getSingleSignOnUrlForService();
            $result = ["success" => "Redirecting to... $redirectUrl", "redirectTo" => $redirectUrl];
        } catch (\App\Exceptions\Module\SingleSignOnError $e) {
            // WHMCS\Cookie::set("ModCmdResult", $e->getMessage());
            // redir("userid=" . $userid . "&id=" . $id . $extra . "&act=singlesignon&ajaxupdate=1");
            $result = ["error" => $e->getMessage()];
        } catch (\Exception $e) {
            LogActivity::Save("Single Sign-On Request Failed with a Fatal Error: " .$e->getMessage(), $userid);
            $result = ["error" => __("admin.ssofatalerror")];
            // WHMCS\Cookie::set("ModCmdResult", $aInt->lang("sso", "fatalerror"));
            // redir("userid=" . $userid . "&id=" . $id . $extra . "&act=singlesignon&ajaxupdate=1");
        }

        // $result = $redirectUrl;

        return $this->getResponse($modop, $result);
    }

    private function custom(Request $request)
    {
        $id = $request->id;
        $aid = $request->aid;
        $modop = $request->modop;
        $ac = $request->get("ac");

        $result = (new \App\Module\Server())->ServerCustomFunction($id, $ac, (int) $aid);

        if (isset($result["jsonResponse"])) {
            $result = $result["jsonResponse"];
        }

        if (is_array($result)) {
            if (count($result) == 1 && array_key_exists("error", $result)) {
                $result = $result["error"];
            } else {
                if (!(count($result) == 1 && array_key_exists("success", $result))) {
                    // $aInt->jsonResponse($result);
                }
            }
        } else {
            if (substr($result, 0, 9) == "redirect|" || substr($result, 0, 7) == "window|") {
                // echo $result;
                // throw new WHMCS\Exception\ProgramExit();
            }
        }

        return $this->getResponse($modop, $result);
    }

    public function getResponse($act, $ModCmdResult = false, $redirect = '')
    {
        $message = "Not defined!";

        if (in_array($act, ["create", "renew", "suspend", "unsuspend", "terminate", "updown", "pwchange", "custom", "singlesignon"]) && ($result = $ModCmdResult)) {
            $result2 = $ModCmdResult;

            // TODO: Check and re-check $result2
            if ($result2 && is_array($result2) && array_key_exists("error", $result2)) {
                $message = AdminFunctions::infoBoxMessage(__("admin.servicesmoduleerror"), nl2br(Sanitize::makeSafeForOutput($result2["error"])));
                request()->session()->flash('type', 'danger');
                request()->session()->flash('message', $message);

                return ResponseAPI::Error([
                    'message' => $message,
                    "modresult" => $result2,
                ]);
            } else if ($result2 && is_array($result2) && array_key_exists("success", $result2)) {
                $message = AdminFunctions::infoBoxMessage(__("admin.servicesmodulesuccess"), nl2br(Sanitize::makeSafeForOutput($result2["success"])));
                request()->session()->flash('type', 'success');
                request()->session()->flash('message', $message);

                return ResponseAPI::Success([
                    'message' => $message,
                    "modresult" => $result2,
                ]);
            } else if ($result != "success") {
                $message = AdminFunctions::infoBoxMessage(__("admin.servicesmoduleerror"), Sanitize::makeSafeForOutput($result));
                request()->session()->flash('type', 'danger');
                request()->session()->flash('message', $message);

                return ResponseAPI::Error([
                    'message' => $message,
                ]);
            } else {
                $message = AdminFunctions::infoBoxMessage(__("admin.servicesmodulesuccess"), __("admin.services{$act}success"));
                request()->session()->flash('type', 'success');
                request()->session()->flash('message', $message);

                return ResponseAPI::Success([
                    'message' => $message,
                ]);
            }
        }

        if ($act == "modifyproductservices") {
            $result = $ModCmdResult;
            $type = 'success';

            if (isset($result['success']) && $result['success']) {
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.changesuccessdesc"));
            } else if (isset($result['terminationdateinvalid']) && $result['terminationdateinvalid']) {
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.clientsterminationdateinvalid"));
            } else {
                $type = 'error';
                $errors = $result['errors'] ?? [];
                if (count($errors)) {
                    $errormsg = "";
                    foreach ($errors as $error) {
                        $errormsg .= $error . "<br />";
                    }

                    $message = AdminFunctions::infoBoxMessage(__("admin.followingerrorsoccurred"), $errormsg);
                    unset($result['errors']);
                }
            }

            unset($result['success']);
            return redirect()
                    ->route($redirect, $result)
                    ->with('type', $type)
                    ->with('message', $message);
        }

        return ResponseAPI::Error([
            'message' => $message,
        ]);
    }

    public function clientUpgrade(Request $request)
    {
        // return $request->all();
        $id = $request->get("id");
        $action = $request->get("action");
        $type = $request->get("type") ?? "product";

        // $result = select_query("tblhosting", "tblhosting.userid,tblhosting.domain,tblhosting.billingcycle,tblhosting.nextduedate,tblhosting.paymentmethod,tblproducts.id AS pid,tblproducts.name,tblproductgroups.name as groupname", array("tblhosting.id" => $id), "", "", "", "tblproducts ON tblproducts.id=tblhosting.packageid INNER JOIN tblproductgroups ON tblproductgroups.id=tblproducts.gid");
        $result = \DB::table('tblhosting')
                        ->selectRaw("tblhosting.userid,tblhosting.domain,tblhosting.billingcycle,tblhosting.nextduedate,tblhosting.paymentmethod,tblproducts.id AS pid,tblproducts.name,tblproductgroups.name as groupname")
                        ->where("tblhosting.id", $id)
                        ->join("tblproducts", "tblproducts.id", "tblhosting.packageid")
                        ->join("tblproductgroups", "tblproductgroups.id", "tblproducts.gid")
                        ->first();

        if (!$result) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'admin.erroroccurred'),
            ]);
        }

        $data = [];

        $userid = $result->userid;
        $service_groupname = $result->groupname;
        $service_pid = $result->pid;
        $service_prodname = $result->name;
        $service_domain = $result->domain;
        $service_billingcycle = $result->billingcycle;
        $service_nextduedate = $result->nextduedate;
        $service_paymentmethod = $result->paymentmethod;

        if ($service_billingcycle != "Free Account" && $service_billingcycle != "One Time" && $service_nextduedate < date("Y-m-d")) {
            $data['element'] = "modalAjaxBody";
            $data['body'] = "<div class=\"alert alert-danger\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                                <strong>" . AdminFunctions::infoBoxMessage(__("admin.servicesupgradeoverdue"), __("admin.servicesupgradeoverdueinfo")) ."</strong>
                            </div>";

            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'admin.erroroccurred'),
                'data' => $data,
            ]);
        }

        if (Upgrade::upgradeAlreadyInProgress($id)) {
            $orders = ModelsUpgrade::where("status", "Pending")->where("relid", $id)->where("userid", $userid)->first();
            $msg = AdminFunctions::infoBoxMessage(__("admin.servicesupgradealreadyinprogress"), __("admin.servicesupgradealreadyinprogressinfo"));

            if ($orders) {
                $orders = $orders->orderid;
                $route = route('admin.pages.orders.vieworder.index', ['action' => 'view', 'id' => $orders]);
                $msg .= " <a href=\"$route\" id=\"viewOrder\">" . __("admin.ordersvieworder") . "</a>";
            }

            $data['element'] = "modalAjaxBody";
            $data['body'] = "<div class=\"alert alert-danger\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                                <strong>" . $msg ."</strong>
                            </div>";

            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'admin.erroroccurred'),
                'data' => $data,
            ]);
        }

        if ($action == "getcycles") {
            $pid = $request->get("pid");
            $data['element'] = "div-getcycles";
            $data['body'] = "<div id=\"div-getcycles\">"
                            ."<div class=\"form-group row\">"
                                ."<label class=\"col-sm-12 col-lg-4 col-form-label\">Billing Cycle</label>"
                                ."<div class=\"col-sm-12 col-lg-8\">"
                                    .$this->ajax_getcycles($pid, $service_billingcycle)
                                ."</div>"
                            ."</div>"
                        ."</div>";

            return ResponseAPI::Success([
                'message' => "OK!",
                'data' => $data,
            ]);
        } else if ($action == "calcsummary") {
            $GLOBALS["uid"] = $userid;
            $formdata = $request->get("formdata");
            $promocode = $formdata["promocode"];
            $type = $formdata["type"];
            $body = "";

            try {
                if ($type == "product") {
                    $newproductid = $formdata["newproductid"];
                    $billingcycle = $formdata["billingcycle"];
                    $upgrades = Upgrade::SumUpPackageUpgradeOrder($id, $newproductid, $billingcycle, $promocode, $service_paymentmethod, false);
                    $upgrades = $upgrades[0];
                    $subtotal = $GLOBALS["subtotal"];
                    $qualifies = $GLOBALS["qualifies"];
                    $discount = $GLOBALS["discount"];
                    $total = Format::formatCurrency($subtotal - $discount);
                    $body .= __("admin.servicesdaysleft") . ": {$upgrades["daysuntilrenewal"]} / {$upgrades["totaldays"]} <br />";
                    if (0 < $discount) {
                        $body .= __("admin.fieldsdiscount") . ": " . Format::formatCurrency($GLOBALS["discount"]) . "<br />";
                    }
                    $body .= __("admin.servicesupgradedue") . ": <span style=\"font-size:16px;\"> $total </span>";
                } else if ($type == "configoptions") {
                    $configoption = $formdata["configoption"];
                    $upgrades = Upgrade::SumUpPackageUpgradeOrder($id, $service_pid, $service_billingcycle, $promocode, $service_paymentmethod, false);
                    $upgrades = $upgrades[0];
                    $body .= __("admin.servicesdaysleft") . ": {$upgrades["daysuntilrenewal"]} / {$upgrades["totaldays"]} <br />";
                    $upgrades = Upgrade::SumUpConfigOptionsOrder($id, $configoption, $promocode, $service_paymentmethod, false);
                    $subtotal = $GLOBALS["subtotal"];
                    $qualifies = $GLOBALS["qualifies"];
                    $discount = $GLOBALS["discount"];
                    $total = Format::formatCurrency($subtotal - $discount);
                    foreach ($upgrades as $upgrade) {
                        $body .= $upgrade["configname"] . ": " . $upgrade["originalvalue"] . " => " . $upgrade["newvalue"] . " (" . $upgrade["price"] . ")<br />";
                    }
                    if (0 < $discount) {
                        $body .= __("admin.fieldsdiscount") . ": " . Format::formatCurrency($GLOBALS["discount"]) . "<br />";
                    }
                    $body .= __("admin.servicesupgradedue") . ": <span style=\"font-size:16px;\"> $total </span>";
                }
            } catch (\Exception $e) {
                $body .= __("admin.error") . ": " . $e->getMessage();
            }

            $data['element'] = "div-upgradesummary";
            $data['body'] = $body;

            return ResponseAPI::Success([
                'message' => "OK!",
                'data' => $data,
            ]);
        } else if ($action == "order") {
            $GLOBALS["uid"] = $userid;
            $formdata = $request->get("formdata");
            $promocode = $formdata["promocode"];
            $type = $formdata["type"];

            $body = "";
            $data['element'] = "div-ordersresult";

            try {
                if ($type == "product") {
                    $newproductid = $formdata["newproductid"];
                    $billingcycle = $formdata["billingcycle"];
                    $upgrades = Upgrade::SumUpPackageUpgradeOrder($id, $newproductid, $billingcycle, $promocode, $service_paymentmethod, true);
                } else if ($type == "configoptions") {
                    $configoption = $formdata["configoption"];
                    $upgrades = Upgrade::SumUpConfigOptionsOrder($id, $configoption, $promocode, $service_paymentmethod, true);
                }

                $upgradedata = Upgrade::createUpgradeOrder($id, "", $promocode, $service_paymentmethod);
                $orderid = $upgradedata["orderid"];
                unset($GLOBALS["uid"]);

                $route = route('admin.pages.orders.vieworder.index', ['action' => 'view', 'id' => $orderid]);
                $redirect = " <a href=\"$route\" id=\"viewOrder\">" . __("admin.ordersvieworder") . "</a>";
                $response["redirect"] = $redirect;
                $body .= "<div class=\"alert alert-success\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                                <strong>" . "Order created successfully! " . $redirect ."</strong>
                            </div>";
            } catch (\Exception $e) {
                $body .= "<div class=\"alert alert-danger\">
                                <button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
                                <strong>" . $e->getMessage() ."</strong>
                            </div>";
            }

            $data['body'] = $body;
            return ResponseAPI::Success([
                'message' => "OK!",
                'data' => $data,
            ]);
        }

        if (!$action) {
            $configoptions = ConfigOptions::getCartConfigOptions($service_pid, [], $service_billingcycle, $id);
            $data['element'] = "modalAjaxBody";

            $body = "<p>"
                        ."<strong>" .__("admin.servicesrelated") ."</strong>"
                        ."$service_groupname - $service_prodname" . ($service_domain ? " (" . $service_domain . ")" : "")
                    ."</p>"
                    ."<div class=\"form-group row\">"
                        ."<label class=\"col-sm-12 col-lg-4 col-form-label\">Upgrade Type</label>"
                        ."<div class=\"col-sm-12 col-lg-8\">"
                            ."<div class=\"d-flex align-items-center py-2\">"
                                ."<div class=\"form-check form-check-inline\">"
                                    ."<input class=\"form-check-input upgrade-type\" type=\"radio\" name=\"type\" id=\"typeproduct\" value=\"product\"" . ($type == "product" ? " checked=\"checked\"" : "") ." onchange=\"modalUpgrade('show-productform')\">"
                                    ."<label class=\"form-check-label\" for=\"typeproduct\">Product/Billing Cycle</label>"
                                ."</div>"
                                .(count($configoptions) ?
                                "<div class=\"form-check form-check-inline\">"
                                    ."<input class=\"form-check-input upgrade-type\" type=\"radio\" name=\"type\" id=\"typeconfigoptions\" value=\"configoptions\"" . ($type == "configoptions" ? " checked=\"checked\"" : "") ." onchange=\"modalUpgrade('show-configoptionsform')\">"
                                    ."<label class=\"form-check-label\" for=\"typeconfigoptions\">Configurable Options</label>"
                                ."</div>" : "")
                            ."</div>"
                        ."</div>"
                    ."</div>";

            if ($type == "product") {
                $body .= "<div class=\"form-group row\">"
                            ."<label class=\"col-sm-12 col-lg-4 col-form-label\">New Product/Service</label>"
                            ."<div class=\"col-sm-12 col-lg-8\">"
                                ."<select class=\"select2-search-disable form-control\" name=\"newproductid\" id=\"newpid\" style=\"width: 100%;\" onchange=\"modalUpgrade('getcycles');\" >"
                                    .Product::productDropDown($service_pid)
                                ."</select>"
                            ."</div>"
                        ."</div>";

                $body .= "<div id=\"div-getcycles\">"
                            ."<div class=\"form-group row\">"
                                ."<label class=\"col-sm-12 col-lg-4 col-form-label\">Billing Cycle</label>"
                                ."<div class=\"col-sm-12 col-lg-8\">"
                                    .$this->ajax_getcycles($service_pid, $service_billingcycle)
                                ."</div>"
                            ."</div>"
                        ."</div>";
            } else if ($type == "configoptions") {
                foreach ($configoptions as $configoption) {
                    $optionid = $configoption["id"];
                    $optionhidden = $configoption["hidden"];
                    $optionname = $optionhidden ? $configoption["optionname"] . " <i>(" . __("admin.hidden") . ")</i>" : $configoption["optionname"];
                    $optiontype = $configoption["optiontype"];
                    $selectedvalue = $configoption["selectedvalue"];
                    $selectedqty = $configoption["selectedqty"];

                    if ($optiontype == "1") {
                        $onChange = "onchange=\"calctotals();\"";
                        $inputcode = "<select name=\"configoption[" . $optionid . "]\" class=\"select2-search-disable form-control\" $onChange>";
                        foreach ($configoption["options"] as $option) {
                            $inputcode .= "<option value=\"" . $option["id"] . "\"";
                            if ($option["hidden"]) {
                                $inputcode .= " style='color:#ccc;'";
                            }

                            if ($selectedvalue == $option["id"]) {
                                $inputcode .= " selected";
                            }

                            $inputcode .= ">" . $option["name"] . "</option>";
                        }

                        $inputcode .= "</select>";
                    } else if ($optiontype == "2") {
                        $inputcode = "";
                        $onClick = "onclick=\"calctotals();\"";
                        foreach ($configoption["options"] as $key => $option) {
                            $inputcode = '<div class="form-check form-check-inline mt-2">
                                <input type="radio" name="configoption["' .$optionid .'"]" id="configoption' .$key .'" class="form-check-input" value="' .$option["id"] .'"' .($selectedvalue == $option["id"] ? "checked " : " ") .$onClick .' >
                                <label class="form-check-label" for="configoption' .$key .'">' .($option["hidden"] ? '<span style="color:#ccc;">' .$option["name"] .'</span>' : $option["name"]) .'</label>
                            </div>';
                        }
                    } else if ($optiontype == "3") {
                        $onClick = "onclick=\"calctotals()\"";
                        $inputcode = "<div class=\"form-check mt-2\">
                                        <input type=\"checkbox\" name=\"configoption[" . $optionid . "]\" class=\"form-check-input\" id=\"configoption{$optionid}\" value=\"1\" " .($selectedqty ? "checked" : "") ." $onClick >
                                        <label class=\"form-check-label\" for=\"configoption{$optionid}\">" .($configoption["options"][0]["name"]) ."</label>
                                    </div>";
                    } else if ($optiontype == "4") {
                        $onClick = "onkeyup=\"calctotals()\"";
                        $inputcode = "<input type=\"text\" name=\"configoption[" . $optionid . "]\" value=\"" . $selectedqty . "\" class=\"form-control \" $onClick > x " . $configoption["options"][0]["name"];
                    }

                    $body .= '<div class="form-group row">
                            <label for="#" class="col-sm-12 col-lg-4 col-form-label">' .$optionname .'</label>
                            <div class="col-sm-12 col-lg-8">' .$inputcode .'</div>
                        </div>';
                }
            }

            $promoid = $request->get("promoid") ?? 0;
            $body .= "<div class=\"form-group row\">"
                            ."<label class=\"col-sm-12 col-lg-4 col-form-label\">Promotion Code</label>"
                            ."<div class=\"col-sm-12 col-lg-8\">"
                                ."<select class=\"select2-search-disable form-control\" name=\"promocode\" id=\"promocode\" style=\"width: 100%;\" onchange=\"calctotals();\" >"
                                    ."<option value\"\"> None </option>"
                                    .$this->getPromoList($promoid)
                                ."</select>"
                            ."</div>"
                        ."</div>";

            $body .= "<div class=\"form-group row\">"
                            ."<label class=\"col-sm-12 col-lg-4 col-form-label\">Upgrade Summary</label>"
                            ."<div class=\"col-sm-12 col-lg-8\">"
                                ."<label id=\"div-upgradesummary\" class=\"col-form-label\">"
                                    .__("admin.servicesupgradesummaryinfo")
                                ."</label>"
                            ."</div>"
                        ."</div>";

            $body .= "<div id=\"div-ordersresult\">"

                    ."</div>";

            $data['body'] = $body;

            return ResponseAPI::Success([
                'message' => "OK!",
                'data' => $data,
            ]);
        }

        return ResponseAPI::Success();
    }

    private function getPromoList($promoid = 0)
    {
        $result = Promotion::where("upgrades", "1")->orderBy("code", "ASC")->get();
        $opt = "";

        if ($result) {
            $result = $result->toArray();

            foreach ($result as $data) {
                $promo_id = $data["id"];
                $promo_code = $data["code"];
                $promo_type = $data["type"];
                $promo_recurring = $data["recurring"];
                $promo_value = $data["value"];

                if ($promo_type == "Percentage") {
                    $promo_value .= "%";
                } else {
                    $promo_value = Format::formatCurrency($promo_value);
                }

                if ($promo_type == "Free Setup") {
                    $promo_value = __("admin.promosfreesetup");
                }

                $promo_recurring = $promo_recurring ? __("admin.statusrecurring") : __("admin.statusonetime");
                if ($promo_type == "Price Override") {
                    $promo_recurring = __("admin.promospriceoverride");
                }

                if ($promo_type == "Free Setup") {
                    $promo_recurring = "";
                }

                $selected = "";
                if ($promo_id == $promoid) {
                    $selected = "selected=\"selected\"";
                }

                $opt .= "<option value=\"" . $promo_code . "\" " . $selected . ">" . (string) $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";
            }

        }

        return $opt;
    }

    private function ajax_getcycles($pid, $service_billingcycle)
    {
        global $aInt;
        global $service_billingcycle;
        $pricing = Orders::getPricingInfo($pid);
        $html = "";

        if ($pricing["type"] == "recurring") {
            $html .= "<select name=\"billingcycle\" class=\"select2-search-disable form-control select-inline\" onchange=\"calctotals()\">";
            if (isset($pricing["monthly"])) {
                $selected = "";
                if ($service_billingcycle == "Monthly") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"monthly\"" . $selected . ">" . $pricing["monthly"] . "</option>";
            }

            if (isset($pricing["quarterly"])) {
                $selected = "";
                if ($service_billingcycle == "Quarterly") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"quarterly\"" . $selected . ">" . $pricing["quarterly"] . "</option>";
            }
            if (isset($pricing["semiannually"])) {
                $selected = "";
                if ($service_billingcycle == "Semi-Annually") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"semiannually\"" . $selected . ">" . $pricing["semiannually"] . "</option>";
            }
            if (isset($pricing["annually"])) {
                $selected = "";
                if ($service_billingcycle == "Annually") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"annually\"" . $selected . ">" . $pricing["annually"] . "</option>";
            }
            if (isset($pricing["biennially"])) {
                $selected = "";
                if ($service_billingcycle == "Biennially") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"biennially\"" . $selected . ">" . $pricing["biennially"] . "</option>";
            }
            if (isset($pricing["triennially"])) {
                $selected = "";
                if ($service_billingcycle == "Triennially") {
                    $selected = " selected=\"selected\"";
                }
                $html .= "<option value=\"triennially\"" . $selected . ">" . $pricing["triennially"] . "</option>";
            }
            $html .= "</select>";
        } else {
            if ($pricing["type"] == "onetime") {
                $html .= "<input type=\"hidden\" name=\"billingcycle\" value=\"onetime\" /> " . __("admin.billingcyclesonetime");
            } else {
                $html .= "<input type=\"hidden\" name=\"billingcycle\" value=\"free\" /> " . __("admin.billingcyclesfree");
            }
        }

        return $html;
    }

}
