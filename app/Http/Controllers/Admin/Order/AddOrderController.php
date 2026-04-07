<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\ConfigOptions;
use App\Helpers\CoreDomains;
use App\Helpers\Customfield;
use App\Helpers\Cycles;
use App\Helpers\Database;
use App\Helpers\Format;
use App\Helpers\FormatterPrice;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Orders;
use App\Helpers\Product;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Client;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\Domainpricing;
use App\Models\Hosting;
use App\Models\Order;
use App\Models\Promotion;

// Traits
use App\Traits\DatatableFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddOrderController extends Controller
{
    use DatatableFilter;
    protected $prefix;

    public function __construct()
    {
        $this->middleware('auth:admin');
        // $this->middleware('auth:web', ['only' => ['submitOrder']]);
        $this->prefix = Database::prefix();
    }

    public function index()
    {
        $gateway = Gateway::GetGatewaysArray();
        $gateway = Orders::filterGatewaysByRestrictionPeriod($gateway);
        $activePromo = Promotion::where(function ($query) {
            $query->where('maxuses', '<=', '0')->orWhere('uses', '<', 'maxuses');
        })
            ->where(function ($query) {
                $query->where('expirationdate', '=', '0000-00-00')
                    ->orWhere('expirationdate', '>=',  date("Ymd"))
                    ->orWhereNull('expirationdate');
            })
            ->orderBy('code')
            ->get()
            ->toArray();

        $inactivePromo = Promotion::where(function ($query) {
            $query->where('maxuses', '>', '0')->where('uses', '>=', 'maxuses');
        })
            ->orWhere(function ($query) {
                $query->where('expirationdate', '!=', '0000-00-00')
                    ->where('expirationdate', '<',  date("Ymd"));
            })
            ->orderBy('code')
            ->get()
            ->toArray();

        $activePromo = $this->getSelectPromo($activePromo, 0, true);
        $inactivePromo = $this->getSelectPromo($inactivePromo, 0, true);
        $products = Product::productDropDown(0, true);

        if (!isset($billingcycle)) {
            $billingcycle = "Monthly";
        }
        $cycles = Cycles::cyclesDropDown($billingcycle, "", "", "billingcycle[]", "updatesummary();loadproductoptions(jQuery('#pid' + this.id.substring(12))[0]);return false;", "billingcycle0");

        $client = null;
        $userid = request()->clientid;
        if ($userid) {
            Auth::guard("web")->loginUsingId($userid);
            $client = Client::find($userid);
        }

        $templatevars = [
            'gateway' => $gateway,
            'products' => $products,
            'cycles' => $cycles,
            'activePromo' => $activePromo,
            'inactivePromo' => $inactivePromo,
            'client' => $client,
        ];

        return view('pages.orders.addneworder.index', $templatevars);
    }

    public function getCycle()
    {
        dd('woo');
    }

    private function getSelectPromo($promotions, $promoid = 0, $useCode = false)
    {
        $option = "";
        foreach ($promotions as $key => $data) {
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

            $option .= "<option value=\"" . ($useCode ? $promo_code : $promo_id) . "\"";
            if ($promo_id == $promoid) {
                $option .= " selected";
            }
            $option .= ">" . "$promo_code - $promo_value $promo_recurring" . "</option>";
        }

        return $option;
    }

    public function actionCommand(Request $request)
    {
        $action = $request->action;

        switch ($action) {
            case 'getcontacts':
                return $this->getContacts($request);
            case 'createpromo':
                return $this->createPromo($request);
            case 'getconfigoptions':
                return $this->getConfigOptions($request);
            case 'getdomainaddlfields':
                return $this->getDomainAddlFields($request);
            case 'validateOrder':
                return $this->validateOrder($request);
            case 'submitorder':
                return $this->submitOrder($request);
            case 'setclientsession':
                return $this->setClientSession($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function setClientSession(Request $request)
    {
        if (Auth::guard("web")->loginUsingId($request->userid)) {
            return true;
        }
        return false;
    }

    private function submitOrder(Request $request)
    {
        DB::beginTransaction();

        try {
            $userid = $request->get("userid");
            $user = Client::findOrFail($userid);

            if (!$user) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage('<b>Invalid Client ID</b>', "Please enter or select a valid client to add the order to"),
                ]);
            }

            $userid = $user->id;
            $addons = $request->get("addons");
            $addons_radio = $request->get("addons_radio");
            $paymentmethod = $request->get("paymentmethod");
            $pid = $request->get("pid");
            $qty = $request->get("qty");
            $domain = $request->get("domain");
            $billingcycle = $request->get("billingcycle");
            $configoption = $request->get("configoption");
            $customfield = $request->get("customfield");
            $regaction = $request->get("regaction");
            $regdomain = $request->get("regdomain");

            $regperiod = $request->get("regperiod");
            $dnsmanagement = $request->get("dnsmanagement");
            $emailforwarding = $request->get("emailforwarding");
            $idprotection = $request->get("idprotection");
            $eppcode = $request->get("eppcode");
            $domainfield = $request->get("regdomain");

            $previousSessionUserId = null;
            if (session()->get("uid")) {
                $previousSessionUserId = session()->get("uid");
            }
            session()->put("uid", $userid);

            // Note: tidak bisa langsung hit disini! page akan expire 
            // Auth::guard("web")->logout();
            // Auth::guard("web")->loginUsingId($userid);

            $userLang = Functions::getUsersLang($userid);
            $currency = Format::GetCurrency($userid);
            global $CONFIG;

            $sessionArray["cart"] = array();
            $sessionArray["cart"]["paymentmethod"] = $paymentmethod;

            $pid = $pid ?? [];
            foreach ($pid as $k => $prodid) {
                if ($prodid) {
                    if ($addons && isset($addons[$k])) {
                        $addons[$k] = array_keys($addons[$k]);
                    }

                    if (empty($addons)) {
                        $addons = array();
                    }

                    if ($addons_radio && isset($addons_radio[$k])) {
                        foreach ($addons_radio[$k] as $addon_value) {
                            if ($addon_value) {
                                if (empty($addons[$k])) {
                                    $addons[$k] = array();
                                }
                                $addons[$k][] = $addon_value;
                            }
                        }
                    }

                    if (!isset($qty[$k])) {
                        $qty[$k] = 1;
                    }

                    $productarray = array(
                        "pid" => $prodid,
                        "domain" => isset($domain[$k]) ? trim($domain[$k]) : "",
                        "billingcycle" => isset($billingcycle[$k]) ? str_replace(array("-", " "), "", strtolower($billingcycle[$k])) : "",
                        "server" => "",
                        "configoptions" => $configoption[$k] ?? [],
                        "customfields" => $customfield[$k] ?? [],
                        "addons" => $addons[$k] ?? []
                    );

                    if (isset($request->get("priceoverride")[$k])) {
                        if (strlen($request->get("priceoverride")[$k])) {
                            $productarray["priceoverride"] = $request->get("priceoverride")[$k];
                        }
                    }

                    for ($count = 1; $count <= $qty[$k]; $count++) {
                        $sessionArray["cart"]["products"][] = $productarray;
                    }
                }
            }

            $validtlds = array();
            $result = Domainpricing::select("extension")->get();
            foreach ($result as $data) {
                $validtlds[] = $data->extension;
            }

            $orderContainsInvalidTlds = false;
            $domains = new CoreDomains();
            $regaction = $regaction ?? [];
            foreach ($regaction as $k => $regact) {
                if ($regact) {
                    $domainparts = explode(".", $domains->clean($regdomain[$k]), 2);
                    if (isset($domainparts[1]) && in_array("." . $domainparts[1], $validtlds)) {
                        $domainArray = array(
                            "type" => $regact,
                            "domain" => trim($regdomain[$k]),
                            "regperiod" => $regperiod[$k],
                            "dnsmanagement" => isset($dnsmanagement[$k]) ? 1 : 0,
                            "emailforwarding" => isset($emailforwarding[$k]) ? 1 : 0,
                            "idprotection" => isset($idprotection[$k]) ? 1 : 0,
                            "eppcode" => isset($eppcode[$k]) ? $eppcode[$k] : "",
                            "fields" => isset($domainfield[$k]) ? $domainfield[$k] : null,
                        );
                        if (isset($request->get("domainpriceoverride")[$k])) {
                            if (strlen($request->get("domainpriceoverride")[$k])) {
                                $domainArray["domainpriceoverride"] = $request->get("domainpriceoverride")[$k];
                            }
                        }
                        if (isset($request->get("domainrenewoverride")[$k])) {
                            if (strlen($request->get("domainrenewoverride")[$k])) {
                                $domainArray["domainrenewoverride"] = $request->get("domainrenewoverride")[$k];
                            }
                        }

                        $sessionArray["cart"]["domains"][] = $domainArray;
                    } else {
                        if (!empty($regdomain[$k])) {
                            $orderContainsInvalidTlds = true;
                        }
                    }
                }
            }

            $promocode = $request->get("promocode");
            if ($promocode) {
                $sessionArray["cart"]["promo"] = $promocode;
            }

            $adminorderconf = $request->get("adminorderconf");
            $admingenerateinvoice = $request->get("admingenerateinvoice");
            $sessionArray["cart"]["orderconfdisabled"] = $adminorderconf ? false : true;
            $sessionArray["cart"]["geninvoicedisabled"] = $admingenerateinvoice ? false : true;

            $adminsendinvoice = $request->get("adminsendinvoice");
            if (!$adminsendinvoice) {
                $CONFIG["NoInvoiceEmailOnOrder"] = true;
            }

            $contactid = $request->get("contactid");
            if ($contactid) {
                $sessionArray["cart"]["contact"] = $contactid;
            }

            // Put the session cart
            session()->put("cart", $sessionArray["cart"]);

            // Calc only process
            $calconly = $request->get("calconly");

            if ($calconly) {
                ob_start();

                $ordervals = Orders::calcCartTotals(false, false, $currency);
                // \Log::debug($ordervals);
                echo "<div class=\"card-title mb-3 ordersummarytitle\">" . __("admin.ordersorderSummary") . " <img class=\"ml-0\" src=\"" . \Theme::asset('img/loading.gif') . "\" id=\"loaderToggle\" alt=\"loading\" hidden> </div>";
                if ($orderContainsInvalidTlds) {
                    echo "<div class=\"alert alert-info text-center\" style=\"margin:15px 0;\">" . __("admin.domainsorderContainsInvalidTlds") . "</div>";
                }

                echo "<div id=\"ordersummary\">\n<table>\n";
                if (isset($ordervals["products"]) && is_array($ordervals["products"])) {
                    foreach ($ordervals["products"] as $cartprod) {
                        echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . $cartprod["productinfo"]["groupname"] . " - " . $cartprod["productinfo"]["name"] . "</div>";
                        echo __("admin.billingcycles" . $cartprod["billingcycle"]);
                        if (isset($cartprod["domain"])) {
                            echo " - " . $cartprod["domain"];
                        }

                        echo "<div class=\"itempricing\">";
                        if (isset($cartprod["priceoverride"])) {
                            echo Format::formatCurrency($cartprod["priceoverride"]) . "*";
                        } else {
                            echo $cartprod["pricingtext"];
                        }

                        echo "</div>";
                        if (isset($cartprod["configoptions"])) {
                            foreach ($cartprod["configoptions"] as $cartcoption) {
                                if (!empty($cartcoption["optionname"]) && empty($cartcoption["value"])) {
                                    $cartcoption["value"] = $cartcoption["optionname"];
                                }
                                if ($cartcoption["type"] == "1" || $cartcoption["type"] == "2") {
                                    echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption["name"] . ": " . $cartcoption["value"];
                                } else {
                                    if ($cartcoption["type"] == "3") {
                                        echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption["name"] . ": ";
                                        if ($cartcoption["qty"]) {
                                            echo __("admin.yes");
                                        } else {
                                            echo __("admin.no");
                                        }
                                    } else {
                                        if ($cartcoption["type"] == "4") {
                                            echo "<br />&nbsp;&raquo;&nbsp;" . $cartcoption["name"] . ": " . $cartcoption["qty"] . " x " . $cartcoption["option"];
                                        }
                                    }
                                }
                            }
                        }

                        echo "</td></tr>";
                        if (isset($cartprod["addons"])) {
                            foreach ($cartprod["addons"] as $addondata) {
                                echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . $addondata["name"] . "</div><div class=\"itempricing\">" . $addondata["pricingtext"] . "</div></td></tr>";
                            }
                        }
                    }
                }

                if (isset($ordervals["domains"]) && is_array($ordervals["domains"])) {
                    foreach ($ordervals["domains"] as $cartdom) {
                        echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\">" . __("admin.fieldsdomain") . " " . __("admin.domains" . $cartdom["type"]) . "</div>" . $cartdom["domain"] . " (" . $cartdom["regperiod"] . " " . __("admin.domainsyears") . ")";
                        if (isset($cartdom["dnsmanagement"]) && $cartdom["dnsmanagement"]) {
                            echo "<br />&nbsp;&raquo;&nbsp;" . __("admin.domainsdnsmanagement");
                        }
                        if (isset($cartdom["emailforwarding"]) && $cartdom["emailforwarding"]) {
                            echo "<br />&nbsp;&raquo;&nbsp;" . __("admin.domainsemailforwarding");
                        }
                        if (isset($cartdom["idprotection"]) && $cartdom["idprotection"]) {
                            echo "<br />&nbsp;&raquo;&nbsp;" . __("admin.domainsidprotection");
                        }
                        echo "<div class=\"itempricing\">";
                        if (isset($cartdom["priceoverride"]) && $cartdom["priceoverride"]) {
                            echo Format::formatCurrency($cartdom["priceoverride"]) . "*";
                        } else {
                            echo $cartdom["price"];
                        }
                        echo "</div>";
                    }
                }

                $cartitems = 0;
                foreach (array("products", "addons", "domains", "renewals") as $k) {
                    if (array_key_exists($k, $ordervals)) {
                        $cartitems += count($ordervals[$k]);
                    }
                }

                if (!$cartitems) {
                    echo "<tr class=\"item\"><td colspan=\"2\"><div class=\"itemtitle\" align=\"center\">" . __("admin.ordersnoItemsSelected") . "</div></td></tr>";
                }

                echo "<tr class=\"subtotal\"><td>" . __("admin.fieldssubtotal") . "</td><td class=\"alnright\">" . $ordervals["subtotal"] . "</td></tr>";
                if (isset($ordervals["promotype"]) && $ordervals["promotype"]) {
                    echo "<tr class=\"promo\"><td>" . __("admin.orderspromoDiscount") . "</td><td class=\"alnright\">" . $ordervals["discount"] . "</td></tr>";
                }

                if (isset($ordervals["taxrate"]) && $ordervals["taxrate"]) {
                    echo "<tr class=\"tax\"><td>" . $ordervals["taxname"] . " @ " . $ordervals["taxrate"] . "%</td><td class=\"alnright\">" . $ordervals["taxtotal"] . "</td></tr>";
                }

                if (isset($ordervals["taxrate2"]) && $ordervals["taxrate2"]) {
                    echo "<tr class=\"tax\"><td>" . $ordervals["taxname2"] . " @ " . $ordervals["taxrate2"] . "%</td><td class=\"alnright\">" . $ordervals["taxtotal2"] . "</td></tr>";
                }

                echo "<tr class=\"total\"><td width=\"140\">" . __("admin.fieldstotal") . "</td><td class=\"alnright\">" . $ordervals["total"] . "</td></tr>";
                if (isset($ordervals["totalrecurringmonthly"]) || isset($ordervals["totalrecurringquarterly"]) || isset($ordervals["totalrecurringsemiannually"]) || isset($ordervals["totalrecurringannually"]) || isset($ordervals["totalrecurringbiennially"]) || isset($ordervals["totalrecurringtriennially"])) {
                    echo "<tr class=\"recurring\"><td>Recurring</td><td class=\"alnright\">";
                    if (isset($ordervals["totalrecurringmonthly"]) && $ordervals["totalrecurringmonthly"]) {
                        echo "" . $ordervals["totalrecurringmonthly"] . " Monthly<br />";
                    }
                    if (isset($ordervals["totalrecurringquarterly"]) && $ordervals["totalrecurringquarterly"]) {
                        echo "" . $ordervals["totalrecurringquarterly"] . " Quarterly<br />";
                    }
                    if (isset($ordervals["totalrecurringsemiannually"]) && $ordervals["totalrecurringsemiannually"]) {
                        echo "" . $ordervals["totalrecurringsemiannually"] . " Semi-Annually<br />";
                    }
                    if (isset($ordervals["totalrecurringannually"]) && $ordervals["totalrecurringannually"]) {
                        echo "" . $ordervals["totalrecurringannually"] . " Annually<br />";
                    }
                    if (isset($ordervals["totalrecurringbiennially"]) && $ordervals["totalrecurringbiennially"]) {
                        echo "" . $ordervals["totalrecurringbiennially"] . " Biennially<br />";
                    }
                    if (isset($ordervals["totalrecurringtriennially"]) && $ordervals["totalrecurringtriennially"]) {
                        echo "" . $ordervals["totalrecurringtriennially"] . " Triennially<br />";
                    }
                    echo "</td></tr>";
                }

                $client = Client::find($userid);
                $amountOfCredit = 0;
                $canUseCreditOnCheckout = false;
                $amountOfCredit = $client->credit;
                if (0 < $ordervals["total"]->toNumeric() && 0 < $amountOfCredit) {
                    $creditBalance = new FormatterPrice($amountOfCredit, $currency);
                    $checked = $request->has("applycredit") ? (bool) $request->get("applycredit") : true;
                    if ($ordervals["total"]->toNumeric() <= $creditBalance->toNumeric()) {
                        $applyCredit = __("admin.ordersapplyCreditAmountNoFurtherPayment", array("amount" => $ordervals["total"]));
                    } else {
                        $applyCredit = __("admin.ordersapplyCreditAmount", array("amount" => $creditBalance));
                    }
                    echo "<tr class=\"apply-credit\"><td colspan=\"2\"><div class=\"apply-credit-container\">\n<p>" . __("admin.ordersavailableCreditBalance", array("amount" => $creditBalance)) . "</p>\n<label class=\"radio\">\n<input type=\"radio\" name=\"applycredit\" value=\"1\" " . ($checked ? "checked=\"checked\"" : "") . ">\n" . $applyCredit . "\n</label>\n<label class=\"radio\">\n<input id=\"skipCreditOnCheckout\" type=\"radio\" name=\"applycredit\" value=\"0\" " . (!$checked ? "checked=\"checked\"" : "") . ">\n" . __("admin.ordersapplyCreditSkip", array("amount" => $creditBalance)) . "\n</label>\n</div></td></tr>";
                }

                echo "</table>\n</div>";
                if ($previousSessionUserId) {
                    session()->put("uid", $previousSessionUserId);
                } else {
                    session()->forget("uid");
                }

                $content = ob_get_contents();
                ob_end_clean();

                DB::commit();
                // Return response for calconly ajax call
                return ResponseAPI::Success([
                    'message' => "OK!",
                    'data' => ['body' => $content],
                ]);
            }

            // Submit order start

            $forceSubmit = (bool) $request->get("forceSubmit");
            if (!$forceSubmit) {
                $validate = $this->validateOrder($request);
                if (!$validate["success"]) {
                    return ResponseAPI::Error([
                        'message' => AdminFunctions::infoBoxMessage($validate["title"], $validate["message"]),
                        'data' => [
                            'containInvalid' => true,
                            'title' => $validate["title"],
                            'message' => $validate["message"],
                        ],
                    ]);
                }
            }

            $sessionArray["cart"] = session()->get("cart");
            $cartitems = count($sessionArray["cart"]["products"] ?? []) + count($sessionArray["cart"]["addons"] ?? []) + count($sessionArray["cart"]["domains"] ?? []) + count($sessionArray["cart"]["renewals"] ?? []);
            if (!$cartitems) {
                //redir("noselections=1");
                DB::rollBack();
                return ResponseAPI::Error([
                    'message' =>  AdminFunctions::infoBoxMessage(__("admin.validationerror"), __("admin.ordersnoselections")),
                ]);

                // return redirect()
                //         ->route("admin.pages.orders.addneworder.index")
                //         ->with("type", "danger")
                //         ->with("message", AdminFunctions::infoBoxMessage(__("admin.validationerror"), __("admin.ordersnoselections")))
                //         ->withInput();
            }

            Orders::calcCartTotals(true, false, $currency);
            unset($sessionArray["uid"]);
            session()->forget("uid");

            $orderstatus = $request->get("orderstatus");
            $sessionArray["orderdetails"] = session()->get("orderdetails");
            if ($orderstatus == "Active") {
                Order::where("id", $sessionArray["orderdetails"]["OrderID"])->update(["status" => "Active"]);
                if (isset($sessionArray["orderdetails"]["Products"]) && is_array($sessionArray["orderdetails"]["Products"])) {
                    foreach ($sessionArray["orderdetails"]["Products"] as $productid) {
                        Hosting::where("id", $productid)->update(["domainstatus" => "Active"]);
                    }
                }

                if (isset($sessionArray["orderdetails"]["Domains"]) && is_array($sessionArray["orderdetails"]["Domains"])) {
                    foreach ($sessionArray["orderdetails"]["Domains"] as $domainid) {
                        Domain::where("id", $domainid)->update(["status" => "Active"]);
                    }
                }
            }

            $userLang = Functions::getUsersLang(0);
            if ($previousSessionUserId) {
                session()->put("uid", $previousSessionUserId);
            } else {
                session()->forget("uid");
            }

            DB::commit();
            event(new \App\Events\AdminAreaRegister($user));
               \Log::info("AdminAreaRegister event dispatched", (array)$user);
            return ResponseAPI::Success([
                'message' => "Order created successfully!",
                'data' => ['redirect' => route("admin.pages.orders.vieworder.index", ["action" => "view", "id" => $sessionArray["orderdetails"]["OrderID"]])],
            ]);

            // return redirect()
            //         ->route("admin.pages.orders.vieworder.index", ["action" => "view", "id" => $sessionArray["orderdetails"]["OrderID"] ])
            //         ->with("type", "success")
            //         ->with("message", "Order created successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function getContacts(Request $request)
    {
        $userid = $request->get("userid");

        $contacts = array();
        $result = Contact::selectRaw("id, firstname, lastname, companyname, email")
            ->where("userid", (int) $userid)
            ->orderBy("firstname", "ASC")
            ->get();
        if (!$result) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
                'data' => [],
            ]);
        }

        $result = $result->toArray();
        foreach ($result as $data) {
            $contacts[$data["id"]] = $data["firstname"] . " " . $data["lastname"];
        }

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => ['contacts' => $contacts],
        ]);
    }

    private function createPromo(Request $request)
    {
        if (!AdminFunctions::checkPermission("Create/Edit Promotions")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "You do not have permission to create promotional codes. If you feel this message to be an error, please contact the administrator."),
            ]);
        }

        $code = $request->get("code");
        $pvalue = $request->get("pvalue");
        $type = $request->get("type");
        $recurring = $request->get("recurring");
        $recurfor = $request->get("recurfor");

        if (!$code) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Promotion Code is Required."),
            ]);
        }
        if ($pvalue <= 0) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Promotion Value must be greater than zero."),
            ]);
        }

        $duplicates = Promotion::where("code", $code)->count();
        if ($duplicates) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Promotion Code already exists. Please try another."),
            ]);
        }

        $newPromo = new Promotion();
        $newPromo->code = $code;
        $newPromo->type = $type;
        $newPromo->recurring = $recurring;
        $newPromo->value = $pvalue;
        $newPromo->maxuses = "1";
        $newPromo->recurfor = $recurfor;
        $newPromo->expirationdate = "0000-00-00";
        $newPromo->notes = "Order Process One Off Custom Promo";
        $newPromo->save();

        $promoid = $newPromo->id;
        $promo_type = $type;
        $promo_value = $pvalue;
        $promo_recurring = $recurring;
        $promo_code = $code;
        if ($promo_type == "Percentage") {
            $promo_value .= "%";
        } else {
            $promo_value = Format::formatCurrency($promo_value);
        }
        $promo_recurring = $promo_recurring ? "Recurring" : "One Time";

        $option = "<option value=\"" . $promo_code . "\">" . $promo_code . " - " . $promo_value . " " . $promo_recurring . "</option>";

        return ResponseAPI::Success([
            'message' => "Custom promo created successfully!",
            'data' => ['option' => $option, 'selected' => $promo_code],
        ]);
    }

    private function getConfigOptions(Request $request)
    {
        $pid = $request->get("pid");
        if (!trim($pid)) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID."),
            ]);
        }

        $html = "";
        $options = "";
        $orderid = $request->get("orderid");
        $cycle = $request->get("cycle");
        $cycles = new Cycles();
        $cycle = $cycles->getNormalisedBillingCycle($cycle);
        $configoptions = ConfigOptions::getCartConfigOptions($pid, "", $cycle);
        if (count($configoptions)) {
            $options .= "<h4 class=\"card-title mt-2 my-3\"><strong>" . __("admin.setupconfigoptions") . "</strong></h4>";
            foreach ($configoptions as $configoption) {
                $optionid = $configoption["id"];
                $optionhidden = $configoption["hidden"];
                $optionname = $optionhidden ? $configoption["optionname"] . " <i>(" . __("admin.hidden") . ")</i>" : $configoption["optionname"];
                $optiontype = $configoption["optiontype"];
                $selectedvalue = $configoption["selectedvalue"];
                $selectedqty = $configoption["selectedqty"];

                if ($optiontype == "1") {
                    $inputcode = "<select name=\"configoption[" . $orderid . "][" . $optionid . "]\" class=\"select2-search-disable form-control\"> onchange=\"updatesummary()\"";
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
                    foreach ($configoption["options"] as $key => $option) {
                        $inputcode = "<div class=\"form-check form-check-inline mt-2\">
                            <input type=\"radio\" name=\"configoption[" . $orderid . "][" . $optionid . "]\" onclick=\"updatesummary()\" id=\"configoption{$key}\" class=\"form-check-input\" value=\"{$option["id"]}\"" . ($selectedvalue == $option["id"] ? "checked" : "") . ">
                            <label class=\"form-check-label\" for=\"configoption{$key}\">" . ($option["hidden"] ? "<span style=\"color:#ccc;\"> {$option["name"]}</span>" : $option["name"]) . "</label>
                        </div>";
                    }
                } else if ($optiontype == "3") {
                    $inputcode = "<div class=\"form-check mt-2\">
                                    <input type=\"checkbox\" name=\"configoption[" . $orderid . "][" . $optionid . "]\" onclick=\"updatesummary()\" class=\"form-check-input\" id=\"configoption{$optionid}\" value=\"1\" " . ($selectedqty ? "checked" : "") . ">
                                    <label class=\"form-check-label\" for=\"configoption{$optionid}\">" . ($configoption["options"][0]["name"]) . "</label>
                                </div>";
                } else if ($optiontype == "4") {
                    $inputcode = "<input type=\"text\" name=\"configoption[" . $orderid . "][" . $optionid . "]\" onchange=\"updatesummary()\"  value=\"" . $selectedqty . "\" class=\"form-control \"> x " . $configoption["options"][0]["name"];
                }

                $options .= '<div class="form-group row">
                                <label for="#" class="col-sm-2 col-form-label">' . $optionname . '</label>
                                <div class="col-sm-10">' . $inputcode . '</div>
                            </div>';
            }
        }

        $customfields = $request->customfields;
        $customfields = Customfield::getCustomFields("product", $pid, "", true, "", $customfields);
        if (count($customfields)) {
            $options .= "<h4 class=\"card-title mt-2 my-3\"><strong>" . __("admin.setupcustomfields") . "</strong></h4>";
            foreach ($customfields as $customfield) {
                $inputfield = str_replace("name=\"customfield", "name=\"customfield[" . $orderid . "]", $customfield["input"]);
                $options .= "<div class=\"form-group row\">
                                <label for=\"#\" class=\"col-sm-3 col-form-label\">{$customfield["name"]}</label>
                                <div class=\"col-sm-9\">
                                    $inputfield
                                </div>
                            </div>";
            }
        }

        $addonshtml = "";
        $addonsarray = Orders::getAddons($pid);
        $orderItemId = $request->get("orderid");

        /* TODO: $marketConnect 
        $marketConnect = new WHMCS\MarketConnect\MarketConnect();
        $addonsPromoOutput = $marketConnect->getAdminMarketplaceAddonPromo($addonsarray, $cycle, $orderItemId);
        $addonsarray = $marketConnect->removeMarketplaceAddons($addonsarray);
        */

        if (count($addonsarray)) {
            $addonCb = "";
            foreach ($addonsarray as $addon) {
                $description = "";
                if (isset($addon["description"])) {
                    $description .= " - " . $addon["description"];
                }
                $addonCb .= "<div class=\"form-check mt-2\">"
                    . str_replace("<input type=\"checkbox\" name=\"addons", "<input type=\"checkbox\" onclick=\"updatesummary()\" name=\"addons[" . $orderid . "]", $addon["checkbox"])
                    // ."<label class=\"form-check-label\" for=\"a{$addon["id"]}\">" .$addon["name"] .$description ."</label>
                    . "<label class=\"form-check-label\" for=\"#\">" . $addon["name"] . $description . "</label>
                            </div>";
            }

            $addonshtml = "<div class=\"form-group row\">
                            <div class=\"col-sm-3 align-self-center\">Addons</div>
                            <div class=\"col-sm-9\">
                                $addonCb
                            </div>
                        </div>";
        }

        /* TODO
        if (count($addonsPromoOutput)) {
            foreach ($addonsPromoOutput as $addon) {
                if ($addon) {
                    $addonshtml .= implode("<br>", $addon) . "<br>";
                }
            }
        }
        */

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => [
                "options" => $options,
                "addons" => $addonshtml
            ],
        ]);
    }

    private function getDomainAddlFields(Request $request)
    {
        $userInputDomain = trim($request->get("domain"));
        $domainCounter = (int) $request->get("domainnum");

        $domain = new Domain();
        $domain->domain = $userInputDomain;

        $additionalFieldsOutput = array();
        foreach ($domain->getAdditionalFields()->getFieldsForOutput($domainCounter) as $fieldLabel => $inputHTML) {
            // $additionalFieldsOutput[] = "
            //     <tr class=\"domain-addt-fields\">
            //         <td width=\"130\" class=\"fieldlabel\">" . $fieldLabel . "</td>
            //         <td class=\"fieldarea\">" . $inputHTML . "</td>
            //     </tr>" . PHP_EOL;

            $additionalFieldsOutput[] = "
                <div class=\"form-group row\">
                    <div class=\"col-sm-3 align-self-center\">$fieldLabel</div>
                    <div class=\"col-sm-9\">
                        $inputHTML
                    </div>
                </div>";
        }

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => [
                "invalidTld" => !$domain->isConfiguredTld(),
                "additionalFields" => implode($additionalFieldsOutput)
            ],
        ]);
    }

    private function validateOrder(Request $request)
    {
        $missingFields = "";
        $domains = new CoreDomains();

        $regaction = $request->get("regaction");
        $regdomain = $request->get("regdomain");
        $domainfield = $request->get("domainfield");

        foreach ($regaction as $key => $regAct) {
            if ($regAct) {
                $cleanDomain = $domains->clean($regdomain[$key] ?? "");
                $domainParts = explode(".", $cleanDomain, 2);
                // $additionalFields = new AdditionalFields();
                // $additionalFields->setTLD($domainParts[1] ?? "");
                // $additionalFields->setFieldValues($domainfield[$key]);
                // $missingFields = $additionalFields->getMissingRequiredFields();
            }
        }

        if ($missingFields) {
            return array(
                "success" => false,
                "title" => __("admin.orderserrorsrequiredDomainFieldsTitle"),
                "message" => "<p>" . __("admin.orderserrorsrequiredDomainFieldsMsg") . "</p> <p>" . __("admin.orderserrorsrequiredDomainFieldsAction") . "</p>"
            );
        }

        return array(
            "success" => true,
        );
    }
}
