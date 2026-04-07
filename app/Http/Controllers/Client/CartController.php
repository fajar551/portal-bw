<?php

namespace App\Http\Controllers\Client;

// Import Controller Class here
// Import Helpers Class here
// Import Model Class here
// Import Package Class here
// Import Laravel Class here

use App\Http\Controllers\Controller;

use App\Helpers\Cfg;
use App\Helpers\Hooks;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Brick\PhoneNumber\PhoneNumberFormat;
use Hexadog\ThemesManager\Facades\ThemesManager;

class CartController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware(['auth']);
    }

    public function setTheme($theme = '')
    {
        $defaultTheme = \App\Helpers\ThemeManager::orderformThemeDefault();
        if (!$theme) {
            $theme = $defaultTheme;
        }
        $newtheme = \App\Helpers\ThemeManager::orderformThemeVendor() . "/$theme";
        $oldtheme = \App\Helpers\ThemeManager::orderformThemeVendor() . "/$defaultTheme";
        try {
            ThemesManager::set($newtheme);
        } catch (\Exception $e) {
            ThemesManager::set($oldtheme);
        }
    }

    public function index(Request $request)
    {
        global $CONFIG, $_LANG;

        define("CLIENTAREA", true);
        define("SHOPPING_CART", true);

        $templatefile = "";
        $auth = Auth::guard('web')->user();
        $nameserverRegexPattern = "/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){2,126}(?!\d+)[a-zA-Z\d]{1,63}\$/";

        $orderfrm = new \App\Helpers\OrderForm();
        $orderFormTemplateName = \App\Helpers\ThemeManager::orderformThemeDefault();
        if ($orderFormTemplateName == 'relabscart') {
            $orderFormTemplateName = $orderFormTemplateName;
        } else if (!file_exists(public_path('themes/orderform/' . $orderFormTemplateName))) {
            $orderFormTemplateName = "standard-cart2";
        }

        $securityqans = $request->input("securityqans") ?? "";
        $securityqid = $request->input("securityqid") ?? "";
        $custtype = $request->input("custtype");
        $a = $request->input("a");
        $gid = $request->input("gid");
        $pid = $request->input("pid");

        if (substr($pid, 0, 1) == "b") {
            $bid = (int) substr($pid, 1);
            return redirect()->route('cart', ['a' => 'add', 'bid' => $bid]);
        } else {
            $pid = (int) $pid;
        }

        $aid = (int) $request->input("aid");
        $ajax = $request->input("ajax");
        $sld = $request->input("sld");
        $tld = $request->input("tld");
        $domains = $request->input("domains");
        $remote_ip = $request->ip();

        $productInfoKey = (int) $request->input("i");
        if ($productInfoKey < 0) {
            $productInfoKey = null;
        }

        $orderfrmtpl = Cfg::get("OrderFormTemplate");
        $orderconf = array();

        $orderform = true;
        $nowrapper = false;
        $errormessage = $allowcheckout = "";
        $userid = $auth ? $auth->id : "";
        $currencyid = $request->session()->get("currency") ? $request->session()->get("currency") : "";
        $currency = \App\Helpers\Format::getCurrency($userid, $currencyid);

        $smartyvalues = [
            "loggedin" => false,
            "currency" => $currency,
            "ipaddress" => $remote_ip,
            "ajax" => $ajax ? true : false,
            "inShoppingCart" => true,
            "action" => $a,
            "numitemsincart" => $orderfrm->getNumItemsInCart(),
            "gid" => "",
            "domain" => "",
        ];

        $cartSession = $orderfrm->getCartData();
        // if ($cartSession) {
        //     dump('$cartSession : ');
        //     dump($cartSession);
        // }

        $lastconfigured = $request->session()->get('cart.lastconfigured');
        if ($lastconfigured) {
            \App\Helpers\Cart::bundlesStepCompleteRedirect($request->session()->get('cart.lastconfigured'));
            $request->session()->forget("cart.lastconfigured");
        }

        $step = $request->input("step");
        if ($step == "fraudcheck") {
            $a = "fraudcheck";
        }

        $configure = $request->input("configure");
        if ($configure && !$a) {
            return redirect()->route('cart')->with('errormessage', 'Terjadi kesalahan page configure');
        }

        $promocode = $request->input("promocode");
        if ($promocode) {
            $resultpromo = \App\Helpers\Orders::SetPromoCode($promocode);
            // dump('$promocode : ');
            // dump($promocode);
        }

        if ($a == "empty") {
            $request->session()->forget("cart");
            return redirect()->route('cart', ['a' => 'view']);
        }

        if ($a == "startover") {
            $request->session()->forget("cart");
            return redirect()->route('cart');
        }

        if ($a == "clearall") {
            $request->session()->forget("cart");
            return redirect()->route('pages.domain.lelangdomains.index');
        }
        
        if ($a == 'cyclechange') {
            $billingcycle = $request->input('billingcycle');
            if (!is_int($productInfoKey) || !$billingcycle) {
                if ($ajax) {
                    throw new \App\Exceptions\ProgramExit($_LANG["invoiceserror"]);
                }
                return redirect()->route('cart')>with('errormessage', 'Tidak ada productInfoKey atau billingcycle');
            }
            if ($orderfrm->validateBillingCycle($billingcycle)) {
                $request->session()->put("cart.products.$productInfoKey.billingcycle", $billingcycle);
            }
            $a = "confproduct";
        }

        if ($a == 'checkout') {
            $getProducts = session()->get('cart');
            $addressChecks = [];
            $getAddressOnly = [];
            foreach ($getProducts['products'] as $key) {
                $addressChecks[] = $key['customfields'];
                foreach ($addressChecks as $i => $value) {
                    $getAddressOnly[$i] = reset($value);
                }
            }

            $domainconfigerror = false;
            $domains = $orderfrm->getCartDataByKey("domains");
            if ($domains) {
                foreach ($domains as $key => $domaindata) {
                    $domainparts = explode(".", $domaindata["domain"], 2);
                    $additflds = new \App\Helpers\Domain\AdditionalFields();
                    $additflds->setTLD($domainparts[1]);
                    // $additflds->setFieldValues($domaindata["fields"]);
                    if ($additflds->isMissingRequiredFields()) {
                        $domainconfigerror = true;
                    }
                    if ($domaindata["type"] !== "register") {
                        $result = \App\Models\Domainpricing::where(array("extension" => "." . $domainparts[1]));
                        $data = $result;
                        if ($data->value("eppcode") && !$domaindata["eppcode"]) {
                            $domainconfigerror = true;
                        }
                    }
                }
            }
            if ($domainconfigerror) {
                if ($ajax) {
                    $errormessage .= "<li>" . $_LANG["carterrordomainconfigskipped"];
                } else {
                    return redirect()->route('cart', ['a' => 'confdomains', 'validate' => 1])>with('errormessage', 'Terjadi kesalahan : domainconfigerror');
                }
            }
            $credit_card_input = "";
            foreach (\App\Helpers\Orders::getAvailableOrderPaymentGateways(true) as $moduleName => $moduleConfiguration) {
                $gateway = new \App\Module\Gateway();
                if ($gateway->load($moduleName) && $gateway->functionExists("credit_card_input")) {
                    $credit_card_input .= $gateway->call("credit_card_input", \App\Helpers\Orders::calcCartTotals(false, false, $currency));
                }
            }
            $smartyvalues["credit_card_input"] = $credit_card_input;
            $smartyvalues["installAddress"] = $getAddressOnly;
            // $remoteAuth = DI::make("remoteAuth");
            // $remoteAuthData = $remoteAuth->getRegistrationFormData();
            // $remoteAuthData = (new WHMCS\Authentication\Remote\Management\Client\ViewHelper())->getTemplateData(WHMCS\Authentication\Remote\Providers\AbstractRemoteAuthProvider::HTML_TARGET_CHECKOUT);
            $remoteAuth = "";
            $remoteAuthData = [];
            $remoteAuthData = [];
            foreach ($remoteAuthData as $key => $value) {
                $smartyvalues[$key] = $value;
            }
            if (!empty($remoteAuthData)) {
                $userData = $request->session()->get("cart.user");
                if (empty($userData["email"]) && isset($remoteAuthData["email"])) {
                    $userData["email"] = $remoteAuthData["email"];
                }
                if (empty($userData["firstname"]) && isset($remoteAuthData["firstname"])) {
                    $userData["firstname"] = $remoteAuthData["firstname"];
                }
                if (empty($userData["lastname"]) && isset($remoteAuthData["lastname"])) {
                    $userData["lastname"] = $remoteAuthData["lastname"];
                }
                $request->session()->put("cart.user", $userData);
            }
            $allowcheckout = true;
            $a = "view";
        }

        if ($a == "addcontact") {
            $allowcheckout = true;
            $addcontact = true;
            $a = "view";
        }

        if ($a == "remove" && !is_null($productInfoKey)) {
            $r = $request->input('r');

            if ($r == "p" && $request->session()->get("cart.products.$productInfoKey")) {
                $request->session()->forget("cart.products.$productInfoKey");
                $request->session()->put("cart.products", array_values($request->session()->get("cart.products")));
            } elseif ($r == "a" && $request->session()->get("cart.addons.$productInfoKey")) {
                $request->session()->forget("cart.addons.$productInfoKey");
                $request->session()->put("cart.addons", array_values($request->session()->get("cart.addons")));
            } elseif ($r == "d" && $request->session()->get("cart.domains.$productInfoKey")) {
                $request->session()->forget("cart.domains.$productInfoKey");
                $request->session()->put("cart.domains", array_values($request->session()->get("cart.domains")));
            } elseif ($r == "r" && $request->session()->get("cart.renewals.$productInfoKey")) {
                $request->session()->forget("cart.renewals.$productInfoKey");
            } elseif ($r == "u" && $request->session()->get("cart.upgrades.$productInfoKey")) {
                $request->session()->forget("cart.upgrades.$productInfoKey");
                $request->session()->put("cart.upgrades", array_values($request->session()->get("cart.upgrades")));
            }

            if ($ajax) {
                $response = new \Illuminate\Http\JsonResponse(["success" => true, "r" => $r, "i" => $productInfoKey]);
                $response->send();
                \App\Helpers\Terminus::getInstance()->doExit();
            }

            return redirect()->route('cart', ['a' => 'view']);
        }

        if ((!$a || $a == "add" && $pid) && ($sld && $tld && !is_array($sld) || is_array($domains))) {
            if (is_array($domains)) {
                $tempdomain = $domains[0];
                $tempdomain = explode(".", $tempdomain, 2);
                $sld = $tempdomain[0];
                $tld = "." . $tempdomain[1];
            }
            $request->session()->put("cartdomain.sld", $sld);
            $request->session()->put("cartdomain.tld", $tld);
        }

        $productgroups = $orderfrm->getProductGroups();
        $smartyvalues["productgroups"] = $productgroups;
        $smartyvalues["registerdomainenabled"] = (bool) Cfg::getValue("AllowRegister");
        $smartyvalues["transferdomainenabled"] = (bool) Cfg::getValue("AllowTransfer");
        $smartyvalues["renewalsenabled"] = (bool) Cfg::getValue("EnableDomainRenewalOrders");

        if (!$a) {
            switch ($gid) {
                case 'viewcart':
                    return redirect()->route('cart', ['a' => 'view']);
                break;
                case 'transferdomain':
                    return redirect()->route('cart', ['a' => 'add', 'domain' => 'transfer']);
                break;
                case 'registerdomain':
                    return redirect()->route('cart', ['a' => 'add', 'domain' => 'register']);
                break;
                case 'domains':
                    return redirect()->route('cart', ['a' => 'add', 'domain' => 'register']);
                break;
                case 'addons':
                    if (!$auth) {
                        $orderform = false;
                        return redirect()->route('login'>with('errormessage', 'Silahkan login terlebih dahulu'));
                    }

                    $smartyvalues["gid"] = "addons";
                    $templatefile = "addons";

                    $where = [
                        "userid" => $auth->id,
                        "domainstatus" => "Active",
                    ];

                    if ($pid) {
                        $where["tblhosting.id"] = $pid;
                    }

                    $productids = [];
                    $productstoids = [];
                    $result = \App\Models\Hosting::selectRaw("tblhosting.id, billingcycle, domain, packageid, tblproducts.name as product_name")
                        ->where($where)
                        ->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
                        ->get();

                    foreach ($result->toArray() as $data) {
                        $productstoids[$data["packageid"]][] = [
                            "id" => $data["id"],
                            "product" => \App\Models\Product::getProductName($data["packageid"], $data["product_name"]),
                            "domain" => $data["domain"],
                        ];
                        if (!in_array($data["packageid"], $productids)) {
                            $productids[] = $data["packageid"];
                        }
                    }

                    $addonids = [];
                    $result = \App\Models\Addon::all();
                    foreach ($result->toArray() as $data) {
                        if ($data["hidden"]) {
                            continue;
                        }
                        $id = $data["id"];
                        $packages = explode(",", $data["packages"]);
                        foreach ($productids as $productid) {
                            if (in_array($productid, $packages) && !in_array($id, $addonids)) {
                                $addonids[] = $id;
                            }
                        }
                    }

                    $addons = [];
                    if (count($addonids)) {
                        $addonModels = \App\Models\Addon::availableOnOrderForm($addonids)
                            ->orderBy("weight", "ASC")
                            ->orderBy("name", "ASC")
                            ->get();

                        foreach ($addonModels as $addonModel) {
                            $addonid = $addonModel->id;
                            $packages = $addonModel->packages;
                            $name = $addonModel->name;
                            $description = $addonModel->description;
                            $billingcycle = \App\Helpers\ClientArea::getRawStatus($addonModel->billingCycle);
                            $free = false;
                            $recurring = 0;
                            $setupfee = 0;

                            $data = \App\Models\Pricing::where([
                                "type" => "addon",
                                "currency" => $currency["id"],
                                "relid" => $addonid,
                            ])->first();

                            switch ($billingcycle) {
                                case "free":
                                case "freeaccount":
                                    $free = true;
                                break;
                                case "onetime":
                                case "monthly":
                                case "quarterly":
                                case "semiannually":
                                case "annually":
                                case "biennially":
                                case "triennially":
                                    $setupfee = $data->msetupfee ?? 0;
                                    $recurring = $data->monthly ?? 0;
                                break;
                                case "recurring":
                                default:
                                    if (($data->monthly ?? 0) >= 0) {
                                        $setupfee = $data->msetupfee ?? 0;
                                        $recurring = $data->monthly ?? 0;
                                        $billingcycle = "monthly";
                                    } elseif (($data->quarterly ?? 0) >= 0) {
                                        $setupfee = $data->qsetupfee ?? 0;
                                        $recurring = $data->quarterly ?? 0;
                                        $billingcycle = "quarterly";
                                    } elseif (($data->semiannually ?? 0) >= 0) {
                                        $setupfee = $data->ssetupfee ?? 0;
                                        $recurring = $data->semiannually ?? 0;
                                        $billingcycle = "semiannually";
                                    } elseif (($data->annually ?? 0) >= 0) {
                                        $setupfee = $data->asetupfee ?? 0;
                                        $recurring = $data->annually ?? 0;
                                        $billingcycle = "annually";
                                    } elseif (($data->biennially ?? 0) >= 0) {
                                        $setupfee = $data->bsetupfee ?? 0;
                                        $recurring = $data->biennially ?? 0;
                                        $billingcycle = "biennially";
                                    } elseif (($data->triennially ?? 0) >= 0) {
                                        $setupfee = $data->tsetupfee ?? 0;
                                        $recurring = $data->triennially ?? 0;
                                        $billingcycle = "triennially";
                                    }
                                break;
                            }

                            $setupfee = empty($setupfee) || $setupfee == "0.00" ? "" : new \App\Helpers\FormatterPrice($setupfee, $currency);
                            $billingcycle = $_LANG["orderpaymentterm" . $billingcycle];

                            $packageids = [];
                            foreach ($packages as $packageid) {
                                $thisaddonspackages = $productstoids[$packageid] ?? "";
                                if ($thisaddonspackages) {
                                    $packageids = array_merge($packageids, $thisaddonspackages);
                                }
                            }

                            if (count($packageids)) {
                                $addons[] = [
                                    "id" => $addonid,
                                    "name" => $name,
                                    "description" => $description,
                                    "free" => $free,
                                    "setupfee" => $setupfee,
                                    "recurringamount" => new \App\Helpers\FormatterPrice($recurring, $currency),
                                    "billingcycle" => $billingcycle,
                                    "productids" => $packageids,
                                ];
                            }
                        }
                    }

                    $smartyvalues["addons"] = $addons;
                    $smartyvalues["noaddons"] = count($addons) <= 0;
                break;
                case 'renewals':
                    if (!$CONFIG["EnableDomainRenewalOrders"]) {
                        return redirect()->route('home')->with('errormessage', 'Pembayaran domain tidak aktif');
                    }
                    if (!$auth) {
                        $orderform = false;
                        return redirect()->route('login')->with('errormessage', 'Silahkan login terlebih dahulu');
                    }
                    return abort(404);
                    // try {
                    //     WHMCS\View\Template\OrderForm::factory("domain-renewals.tpl", $orderFormTemplateName);
                    //     header("Location: " . routePath("cart-domain-renewals"));
                    //     WHMCS\Terminus::getInstance()->doExit();
                    // } catch (WHMCS\Exception\View\TemplateNotFound $e) {
                    // } catch (Exception $e) {
                    //     App::redirect("clientarea.php");
                    // }
                    $smartyvalues["gid"] = "renewals";
                    $templatefile = "domainrenewals";
                    $smartyvalues["productgroups"] = $productgroups;
                    $renewals = \App\Helpers\DomainsClass::getRenewableDomains($auth ? $auth->id : 0);
                    $smartyvalues["renewals"] = $renewals["renewals"];
                break;
                default:
                    if (session()->has('errormessage')) {
                        $smartyvalues["errormessage"] = session('errormessage');
                        session()->forget('errormessage');
                    }
                    $templatefile = "products";
                    $smartyvalues["showSidebarToggle"] = (bool) Cfg::getValue("OrderFormSidebarToggle");
                    $hookResponses = Hooks::run_hook("ShoppingCartViewCategoryAboveProductsOutput", array("cart" => Session::get("cart")));
                    $smartyvalues["hookAboveProductsOutput"] = $hookResponses;
                    $hookResponses = Hooks::run_hook("ShoppingCartViewCategoryBelowProductsOutput", array("cart" => Session::get("cart")));
                    $smartyvalues["hookBelowProductsOutput"] = $hookResponses;
                    if ($pid) {
                        $result = \App\Models\Product::where(array("id" => $pid));
                        $data = $result;
                        $pid = $data->value("id");
                        $gid = $data->value("gid");
                        $smartyvalues["pid"] = $pid;
                    } else {
                        if (!$gid) {
                            $gid = $productgroups[0]["gid"];
                        }
                    }
                    $productGroup = \App\Models\Productgroup::find($gid);
                    $groupinfo = $orderfrm->getProductGroups($gid);

                    // $groupinfo = true;
                    if (count($productgroups) && !$groupinfo) {
                        return redirect()->to('/');
                    }
                    $orderFormTemplateName = isset($groupinfo["orderfrmtpl"]) && $groupinfo["orderfrmtpl"] !== "" 
                        ? $groupinfo["orderfrmtpl"] 
                        : $orderFormTemplateName;

                    if(isset($groupinfo["id"])){
                        $smartyvalues["gid"] = $groupinfo["id"];
                    } else {
                        $groupinfo["id"] = null;
                    }
                    $smartyvalues["productGroup"] = $productGroup;
                    $smartyvalues["groupname"] = \App\Models\Productgroup::getGroupName($groupinfo["id"], $productGroup->name);
                    $products = array();
                    try {
                        $products = $orderfrm->getProducts($productGroup, true, true);
                    } catch (\Exception $e) {
                        $smartyvalues["errormessage"] = Lang::get("orderForm.error" . $e->getMessage());
                    }
                    $regex = "/[0-9]*\\.?[0-9]+/";
                    $featureValues = array();
                    foreach ($products as $productKey => $product) {
                        foreach ($product["features"] as $featureKey => $feature) {
                            $matches = array();
                            if (preg_match($regex, $feature, $matches)) {
                                $featureAmount = $matches[0];
                            } else {
                                $featureAmount = PHP_INT_MAX;
                            }
                            $featureValues[$featureKey][$productKey] = $featureAmount;
                            asort($featureValues[$featureKey]);
                        }
                    }
                    foreach ($featureValues as $featureKey => $feature) {
                        if (!in_array(PHP_INT_MAX, $feature)) {
                            continue;
                        }
                        $highestValue = 1;
                        foreach ($feature as $value) {
                            if ($value != PHP_INT_MAX) {
                                $highestValue = $value;
                            } else {
                            break;
                            }
                        }
                        $featureValues[$featureKey] = str_replace(PHP_INT_MAX, $highestValue * 2, $feature);
                    }
                    foreach ($featureValues as $featureKey => $feature) {
                        list($highestValue) = array_slice($feature, -1);
                        foreach ($feature as $productKey => $value) {
                            $featureValues[$featureKey][$productKey] = (int) ($value / $highestValue * 100);
                        }
                    }
                    $smartyvalues["featurePercentages"] = $featureValues;
                    $smartyvalues["products"] = $products;
                    $smartyvalues["productscount"] = count($products);
                break;
            }
        }

        switch ($a) {
            case 'add':
                if ($pid) {
                    $templatefile = "configureproductdomain";

                    if($pid == 382){
                        $product = \App\Models\Product::find($pid);
                        if (!$product) {
                            try {
                                DB::table('tblproducts')->insert([
                                    'id' => 382,
                                    'type' => 'other',
                                    'gid' => 680,
                                    'name' => 'Backorder Domain',
                                    'slug' => '',
                                    'description' => '',
                                    'hidden' => 1,
                                    'showdomainoptions' => 0,
                                    'welcomeemail' => 1688,
                                    'stockcontrol' => 0,
                                    'qty' => 0,
                                    'proratabilling' => 0,
                                    'proratadate' => 0,
                                    'proratachargenextmonth' => 0,
                                    'paytype' => 'onetime',
                                    'allowqty' => 0,
                                    'subdomain' => '',
                                    'autosetup' => 'payment',
                                    'servertype' => 'backorderdomain',
                                    'servergroup' => 0,
                                    'configoption1' => '',
                                    'configoption2' => '',
                                    'configoption3' => '',
                                    'configoption4' => '',
                                    'configoption5' => '',
                                    'configoption6' => '',
                                    'configoption7' => '',
                                    'configoption8' => '',
                                    'configoption9' => '',
                                    'configoption10' => '',
                                    'configoption11' => '',
                                    'configoption12' => '',
                                    'configoption13' => '',
                                    'configoption14' => '',
                                    'configoption15' => '',
                                    'configoption16' => '',
                                    'configoption17' => '',
                                    'configoption18' => '',
                                    'configoption19' => '',
                                    'configoption20' => '',
                                    'configoption21' => '',
                                    'configoption22' => '',
                                    'configoption23' => '',
                                    'configoption24' => '',
                                    'freedomain' => '',
                                    'freedomainpaymentterms' => '',
                                    'freedomaintlds' => '',
                                    'recurringcycles' => 0,
                                    'autoterminatedays' => 0,
                                    'autoterminateemail' => 0,
                                    'configoptionsupgrade' => 0,
                                    'billingcycleupgrade' => '',
                                    'upgradeemail' => 0,
                                    'overagesenabled' => '',
                                    'overagesdisklimit' => 0,
                                    'overagesbwlimit' => 0,
                                    'overagesdiskprice' => '0.0000',
                                    'overagesbwprice' => '0.0000',
                                    'tax' => 1,
                                    'affiliateonetime' => 0,
                                    'affiliatepaytype' => '',
                                    'affiliatepayamount' => '0.00',
                                    'order' => 1,
                                    'retired' => 0,
                                    'is_featured' => 0,
                                    'created_at' => '2018-07-18 10:30:48',
                                    'updated_at' => '2018-07-18 10:30:48'
                                ]);
                            } catch (\Exception $e) {
                                return redirect()->route('cart')->with('errormessage', 'Gagal insert produk backorder domain: ' . $e->getMessage());
                            }
                        }

                        
                    }
                    $productGroup = DB::table('tblproductgroups')->where('id', 680)->first();
                    if (!$productGroup) {
                        try {
                            DB::table('tblproductgroups')->insert([
                                'id' => 680,
                                'name' => 'Domain Product',
                                'slug' => 'domain-product',
                                'headline' => '',
                                'tagline' => '',
                                'orderfrmtpl' => '',
                                'disabledgateways' => '',
                                'hidden' => 1,
                                'order' => 35,
                                'created_at' => '2018-07-19 07:37:48',
                                'updated_at' => '2023-06-17 09:04:27'
                            ]);
                        } catch (\Exception $e) {   
                            return redirect()->route('cart')->with('errormessage', 'Gagal insert produk backorder domain: ' . $e->getMessage());
                        }
                    }

                    $productinfo = $orderfrm->setPid($pid);
                    if (!$productinfo) {
                        // return redirect()->route('cart')->with('errormessage', 'Produk dengan id : ' . $pid . ' tidak ditemukan');
                    }

                    // $orderFormTemplateName = $productinfo["orderfrmtpl"] == "" ? $orderFormTemplateName : $productinfo["orderfrmtpl"];
                    $orderFormTemplateName = isset($groupinfo["orderfrmtpl"]) && $groupinfo["orderfrmtpl"] !== "" 
                        ? $groupinfo["orderfrmtpl"] 
                        : $orderFormTemplateName;

                    $request->session()->put('cart.domainoptionspid', $productinfo["pid"]);

                    $smartyvalues["productinfo"] = $productinfo;
                    $smartyvalues["pid"] = $productinfo["pid"];
                    $pid = $smartyvalues["pid"];
                    $type = $productinfo["type"];
                    $subdomains = $productinfo["subdomain"] ?? [];
                    $freedomain = $productinfo["freedomain"];
                    $freedomaintlds = $productinfo["freedomaintlds"];
                    $showdomainoptions = $productinfo["showdomainoptions"];
                    $stockcontrol = $productinfo["stockcontrol"];
                    $qty = $productinfo["qty"];
                    $module = $productinfo["module"];

                    if ($stockcontrol && $qty <= 0) {
                        $templatefile = "error";
                        $smartyvalues["errortitle"] = $_LANG["outofstock"];
                        $smartyvalues["errormsg"] = $_LANG["outofstockdescription"];
                        $this->setTheme($orderFormTemplateName);
                        return view($templatefile, $smartyvalues);
                    }

                    $passedvariables = $request->session()->get('cart.passedvariables');
                    $bundle = false;

                    if (is_array($passedvariables) && (isset($passedvariables["bnum"]) || isset($passedvariables["bitem"]))) {
                        $bundle = true;
                    }

                    if ($module == "marketconnect" && !$bundle) {
                        // TODO: App::redirectToRoutePath("store-order", array(), array("pid" => $pid));
                    }

                    $passedvariables = [];
                    $skipconfig = $request->input("skipconfig");
                    $billingcycle = $request->input("billingcycle");
                    $configoption = $request->input("configoption");

                    // INI REQUEST DOMAIN DAN DOMAIN
                    $customfield = $request->input("customfield");
                    $addons = $request->input("addons");

                    if ($skipconfig) {
                        $passedvariables["skipconfig"] = $skipconfig;
                    }
                    if ($billingcycle) {
                        $passedvariables["billingcycle"] = $billingcycle;
                    }
                    if ($configoption) {
                        $passedvariables["configoption"] = $configoption;
                    }
                    if ($customfield) {
                        $passedvariables["customfield"] = $customfield;
                    }
                    if ($addons) {
                        if (!is_array($addons)) {
                            $passedvariables["addons"] = explode(",", $addons);
                        } else {
                            foreach ($addons as $k => $v) {
                                $passedvariables["addons"][] = trim($k);
                            }
                        }
                    }

                    $customFields = \App\Helpers\Customfield::getCustomFields("product", $productinfo["pid"], "", true);
                    if ($customFields) {
                        // dump('$customFields :');
                        // dd($customFields);
                        foreach ($customFields as $customField) {
                            $cfValue = $request->input("cf_" . $customField["textid"]);
                            if ($cfValue) {
                                $passedvariables["customfield"][$customField["id"]] = $cfValue;
                            }
                        }
                    }

                    // Di bagian case 'add' saat menangani produk backorder (pid 382)
                    if ($pid == 382 && $request->input('cf_domain')) {
                        $domainName = $request->input('cf_domain');

                        $backorderCheck = $this->checkWhoisBackorder($domainName);
                        if ($pid == 382 && $backorderCheck !== true) {
                            return redirect()->route('pages.domain.lelangdomains.index')->with([
                                'alert-type' => 'danger',
                                'alert-message' => $backorderCheck
                            ]);
                            // return redirect()->route('cart')->with(
                            //     ['error' => $backorderCheck]
                            // );
                        }

                        if (isset($backorderCheck['error'])) {
                            return redirect()->route('cart')->with('errormessage', $backorderCheck['error']);
                        }
                        // Simpan domain ke custom fields
                        $customfields = [
                            '1215' => $domainName 
                        ];
                        
                        $prodarray = [
                            "pid" => $pid,
                            "domain" => $domainName,
                            "billingcycle" => $passedvariables["billingcycle"] ?? "",
                            "configoptions" => $passedvariables["configoption"] ?? [],
                            "customfields" => $customfields,
                            "addons" => $passedvariables["addons"] ?? [],
                            "server" => "",
                            "noconfig" => true
                        ];

                        // Hitung biaya backorder
                        $backorderFee = $this->hook_domainFeeBackorder([
                            'products' => [
                                [
                                    'customfields' => $customfields,
                                    'domain' => $domainName
                                ]
                            ]
                        ]);

                        // Simpan adjustment fee ke session dengan format yang benar
                        if (!empty($backorderFee)) {
                            $currentAdjustments = $request->session()->get('cart.adjustments', []);
                            if (!is_array($currentAdjustments)) {
                                $currentAdjustments = [];
                            }
                            
                            $currentAdjustments[] = [
                                'description' => $backorderFee['description'],
                                'amount' => $backorderFee['amount'],
                                'taxed' => $backorderFee['taxed'],
                                'pid' => $pid   
                            ];
                            
                            $request->session()->put('cart.adjustments', $currentAdjustments);
                        }
                    }
                    
                    if (count($passedvariables)) {
                        $request->session()->put('cart.passedvariables', $passedvariables);
                    }

                    if (isset($orderconf["directpidstep1"]) && $orderconf["directpidstep1"] && !$ajax) {
                        return redirect()->route('cart', ['pid' => $pid]);
                    }

                    $domainselect = $request->input("domainselect");
                    $domainoption = $request->input("domainoption");

                    if ($domainselect && !$domains && $ajax && $domainoption != "incart" && $domainoption != "owndomain" && $domainoption != "subdomain") {
                        return "nodomains";
                    }

                    $productconfig = false;
                    if ($orderfrm->getProductInfo("showdomainoptions") && !$domains) {
                        $cartproducts = $orderfrm->getCartDataByKey("products");
                        $cartdomains = $orderfrm->getCartDataByKey("domains");
                        $incartdomains = [];

                        if ($cartdomains) {
                            foreach ($cartdomains as $cartdomain) {
                                $domainname = $cartdomain["domain"];
                                if ($cartproducts) {
                                    foreach ($cartproducts as $cartproduct) {
                                        if ($cartproduct["domain"] == $domainname) {
                                            $domainname = "";
                                        }
                                    }
                                }
                                if ($domainname) {
                                    $incartdomains[] = $domainname;
                                }
                            }
                        }

                        if (!in_array($domainoption, ["incart", "register", "transfer", "owndomain", "subdomain"])) {
                            $domainoption = "";
                        }
                        if ($incartdomains && !$domainoption) {
                            $domainoption = "incart";
                        }
                        if ($CONFIG["AllowRegister"] && !$domainoption) {
                            $domainoption = "register";
                        }
                        if ($CONFIG["AllowTransfer"] && !$domainoption) {
                            $domainoption = "transfer";
                        }
                        if ($CONFIG["AllowOwnDomain"] && !$domainoption) {
                            $domainoption = "owndomain";
                        }
                        if (count($subdomains) && !$domainoption) {
                            $domainoption = "subdomain";
                        }

                        $registerTlds = \App\Helpers\DomainFunctions::getTLDList();
                        $transferTlds = \App\Helpers\DomainFunctions::getTLDList("transfer");

                        $smartyvalues["listtld"] = $registerTlds;
                        $smartyvalues["registertlds"] = $registerTlds;
                        $smartyvalues["transfertlds"] = $transferTlds;
                        $smartyvalues["showdomainoptions"] = true;
                        $smartyvalues["domainoption"] = $domainoption;
                        $smartyvalues["registerdomainenabled"] = $CONFIG["AllowRegister"];
                        $smartyvalues["transferdomainenabled"] = $CONFIG["AllowTransfer"];
                        $smartyvalues["owndomainenabled"] = $CONFIG["AllowOwnDomain"];
                        $smartyvalues["subdomain"] = $subdomains[0] ?? "";
                        $smartyvalues["subdomains"] = $subdomains;
                        $smartyvalues["incartdomains"] = $incartdomains;
                        $smartyvalues["availabilityresults"] = [];
                        $smartyvalues["freedomaintlds"] = $freedomain && !empty($freedomaintlds) ? implode(", ", $freedomaintlds) : "";
                        $smartyvalues["spotlightTlds"] = \App\Helpers\DomainFunctions::getSpotlightTldsWithPricing();

                        if (is_array($tld)) {
                            if ($domainoption == "register") {
                                $tld = $tld[0];
                                $sld = $sld[0];
                            } elseif ($domainoption == "transfer") {
                                $tld = $tld[1];
                                $sld = $sld[1];
                            } elseif ($domainoption == "owndomain") {
                                $tld = $tld[2];
                                $sld = $sld[2];
                            } elseif ($domainoption == "subdomain") {
                                if (!isset($subdomains[$tld[3]])) {
                                    $tld[3] = 0;
                                }
                                $tld = $subdomains[$tld[3]];
                                $sld = $sld[3];
                            } elseif ($domainoption == "incart") {
                                $incartdomain = "";
                                $incartdomain = explode(".", $incartdomain, 2);
                                list($sld, $tld) = $incartdomain;
                            }
                        }

                        $nocontinue = false;
                        if (!$sld && !$tld && $request->session()->get('cartdomain.sld') && $request->session()->get('cartdomain.tld') && in_array($request->session()->get('cartdomain.tld'), $registerTlds)) {
                            $sld = $request->session()->get('cartdomain.sld');
                            $tld = $request->session()->get('cartdomain.tld');
                            $nocontinue = true;
                            $request->session()->forget('cartdomain');
                        }

                        $sld = \App\Helpers\DomainFunctions::cleanDomainInput($sld);
                        $tld = \App\Helpers\DomainFunctions::cleanDomainInput($tld);

                        if (substr($sld, -1) == ".") {
                            $sld = substr($sld, 0, -1);
                        }
                        if ($sld && $tld && (($domainoption == "register" && !in_array($tld, $registerTlds)) || ($domainoption == "transfer" && !in_array($tld, $transferTlds)))) {
                            $sld = "";
                            $tld = "";
                        }
                        if ($tld && substr($tld, 0, 1) != ".") {
                            $tld = "." . $tld;
                        }

                        $smartyvalues["sld"] = $sld;
                        $smartyvalues["tld"] = $tld;

                        if ($request->get('sld') || $request->get('tld') || $sld) {
                            $validate = new \App\Helpers\Validate();
                            if ($domainoption == "subdomain") {
                                $BannedSubdomainPrefixes = [];
                                if (!is_array($BannedSubdomainPrefixes)) {
                                    $BannedSubdomainPrefixes = [];
                                }
                                if (Cfg::get("BannedSubdomainPrefixes")) {
                                    $bannedPrefixesString = (string) Cfg::get("BannedSubdomainPrefixes");
                                    $bannedPrefixes = explode(",", $bannedPrefixesString);
                                    $BannedSubdomainPrefixes = array_merge($BannedSubdomainPrefixes, $bannedPrefixes);
                                }
                                if (!\App\Helpers\Domain\Domain::isValidDomainName($sld, ".com")) {
                                    $errormessage .= "<li>" . $_LANG["ordererrordomaininvalid"];
                                } elseif (in_array($sld, $BannedSubdomainPrefixes)) {
                                    $errormessage .= "<li>" . $_LANG["ordererrorsbudomainbanned"];
                                } else {
                                    $result = \App\Models\Hosting::whereRaw("domain='" . \App\Helpers\Database::db_escape_string($sld . $tld) . "' AND (domainstatus!='Terminated' AND domainstatus!='Cancelled' AND domainstatus!='Fraud')");
                                    $subchecks = $result->count();
                                    if ($subchecks) {
                                        $errormessage = "<li>" . $_LANG["ordererrorsubdomaintaken"];
                                    }
                                }
                                Hooks::run_validate_hook($validate, "CartSubdomainValidation", ["subdomain" => $sld, "domain" => $tld]);
                            } else {
                                if (!\App\Helpers\Domain\Domain::isValidDomainName($sld, $tld) || ($domainoption == "owndomain" && !\App\Helpers\Domain\Domain::isSupportedTld($tld))) {
                                    $errormessage .= $_LANG["ordererrordomaininvalid"];
                                }
                                if (($domainoption == "register" || $domainoption == "transfer") && $CONFIG["AllowDomainsTwice"]) {
                                    if (substr($tld, 0, 1) != ".") {
                                        $tld = "." . $tld;
                                    }
                                    $domainObject = new \App\Helpers\Domain\Domain($sld . $tld);
                                    if (\App\Helpers\Cart::cartCheckIfDomainAlreadyOrdered($domainObject)) {
                                        $errormessage = "<li>" . $_LANG["ordererrordomainalreadyexists"];
                                    }
                                } elseif ($domainoption == "owndomain" && $CONFIG["AllowDomainsTwice"]) {
                                    $result = \App\Models\Hosting::whereRaw("domain='" . \App\Helpers\Database::db_escape_string($sld . $tld) . "' AND (domainstatus!='Terminated' AND domainstatus!='Cancelled' AND domainstatus!='Fraud')")->get();
                                    foreach ($result as $data) {
                                        if ($data->domain == $sld . $tld) {
                                            $errormessage = "<li>" . $_LANG["ordererrordomainalreadyexists"];
                                        break;
                                        }
                                    }
                                }
                                Hooks::run_validate_hook($validate, "ShoppingCartValidateDomain", ["domainoption" => $domainoption, "sld" => $sld, "tld" => $tld]);
                            }
                            if ($validate->hasErrors()) {
                                $errormessage .= $validate->getHTMLErrorOutput();
                            }
                            $smartyvalues["errormessage"] = $errormessage;
                        }

                        if (!$errormessage && !$nocontinue) {
                            if (in_array($domainoption, ["register", "transfer"]) && $sld && $tld) {
                                $check = new \App\Helpers\Domain\Checker();
                                $check->cartDomainCheck(new \App\Helpers\Domain\Domain($sld), [$tld]);
                                $check->populateCartWithDomainSmartyVariables($domainoption, $smartyvalues);
                                $smartyvalues["domains"] = $domains;
                            }
                            if (in_array($domainoption, ["owndomain", "subdomain", "incart"]) && $sld && $tld) {
                                $smartyvalues["showdomainoptions"] = false;
                                $domains = [$sld . $tld];
                                $productconfig = true;
                            }
                        }
                    } else {
                        $productconfig = true;
                    }

                    if ($productconfig) {
                        $passedvariables = $request->session()->get("cart.passedvariables");
                        $request->session()->forget("cart.passedvariables");

                        $cartPreventDuplicateProduct = \App\Helpers\Cart::cartPreventDuplicateProduct($domains[0] ?? "");
                        // if ($cartPreventDuplicateProduct) {
                        //     dump('$cartPreventDuplicateProduct : ');
                        //     dump($cartPreventDuplicateProduct);
                        // } else {
                        //     return redirect()->route('cart')->with('errormessage', 'Domain sudah ada di keranjang');
                        // }

                        $prodarray = [
                            "pid" => $pid,
                            "domain" => $domains[0] ?? "",
                            "billingcycle" => $passedvariables["billingcycle"] ?? "",
                            "configoptions" => $passedvariables["configoption"] ?? [],
                            "customfields" => $passedvariables["customfield"] ?? [],
                            "addons" => $passedvariables["addons"] ?? [],
                            "server" => "",
                            "noconfig" => true
                        ];

                        if (isset($passedvariables["bnum"])) {
                            $prodarray["bnum"] = $passedvariables["bnum"];
                        }

                        if (isset($passedvariables["bitem"])) {
                            $prodarray["bitem"] = $passedvariables["bitem"];
                        }

                        $updatedexistingqty = false;
                        if ($productinfo["allowqty"]) {
                            foreach ($request->session()->get("cart.products") ?? [] as &$cart_prod) {
                                if ($pid == $cart_prod["pid"]) {
                                    if (empty($cart_prod["qty"])) {
                                        $cart_prod["qty"] = 1;
                                    }
                                    if (empty($cart_prod["noconfig"])) {
                                        $cart_prod["qty"]++;
                                        if ($stockcontrol && $qty < $cart_prod["qty"]) {
                                            $cart_prod["qty"] = $qty;
                                        }
                                        $updatedexistingqty = true;
                                    }
                                break;
                                }
                            }
                        }

                        if (!$updatedexistingqty) {
                            $request->session()->push("cart.products", $prodarray);
                        }

                        $newprodnum = count($request->session()->get("cart.products")) - 1;
                        if ($request->session()->get("cart.products.$newprodnum.pid") != $pid) {
                            $newprodnum = 0;
                            $index = count($request->session()->get("cart.products"));
                            while (0 < $index) {
                                $product = $request->session()->get("cart.products")[--$index];
                                if ($product["pid"] == $pid) {
                                    $newprodnum = $index;
                                break;
                                }
                            }
                        }

                        if ($domainoption == "register" || $domainoption == "transfer") {
                            $domainsregperiod = $request->input('domainsregperiod');
                            foreach ($domains as $domainname) {
                                \App\Helpers\Cart::cartPreventDuplicateDomain($domainname);
                                $regperiod = $domainsregperiod[$domainname] ?? 1;
                                $domainparts = explode(".", $domainname, 2);
                                $temppricelist = \App\Helpers\Domain::getTLDPriceList("." . $domainparts[1]);

                                if (!isset($temppricelist[$regperiod][$domainoption])) {
                                    if (isset($regperiods) && is_array($regperiods)) {
                                        foreach ($regperiods as $period) {
                                            if (substr($period, 0, strlen($domainname)) == $domainname) {
                                                $regperiod = substr($period, strlen($domainname));
                                            }
                                        }
                                    }
                                    if (!$regperiod) {
                                        $tldyears = array_keys($temppricelist);
                                        $regperiod = $tldyears[0];
                                    }
                                }

                                $domainArray = [
                                    "type" => $domainoption,
                                    "domain" => $domainname,
                                    "regperiod" => $regperiod,
                                    "isPremium" => false
                                ];

                                if (isset($passedvariables["bnum"])) {
                                    $domainArray["bnum"] = $passedvariables["bnum"];
                                }

                                if (isset($passedvariables["bitem"])) {
                                    $domainArray["bitem"] = $passedvariables["bitem"];
                                }

                                // Tambahkan pengecekan null untuk $premiumData
                                $premiumData = Session::get("PremiumDomains");
                                if ((bool)(int)Cfg::getValue("PremiumDomains") && !is_null($premiumData) && array_key_exists($domainname, $premiumData)) {
                                    $premiumPrice = $premiumData[$domainname];
                                    if (array_key_exists("register", $premiumPrice["cost"])) {
                                        $domainArray["isPremium"] = true;
                                        $domainArray["domainpriceoverride"] = $premiumPrice["markupPrice"][1]["register"];
                                        $domainArray["registrarCostPrice"] = $premiumPrice["cost"]["register"];
                                        $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                                        $domainArray["domainpriceoverride"] = $domainArray["domainpriceoverride"]->toNumeric();
                                    }
                                    if (array_key_exists("renew", $premiumPrice["cost"])) {
                                        $domainArray["domainrenewoverride"] = $premiumPrice["markupPrice"][1]["renew"];
                                        $domainArray["registrarRenewalCostPrice"] = $premiumPrice["cost"]["renew"];
                                        $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                                        $domainArray["domainrenewoverride"] = $domainArray["domainrenewoverride"]->toNumeric();
                                    } else {
                                        $domainArray["isPremium"] = false;
                                    }
                                }

                                $request->session()->push("cart.domains", $domainArray);
                            }
                        }

                        //Tanda bahwa ini adalah product baru
                        $request->session()->put("cart.newproduct", true);

                        if ($ajax) {
                            $ajax = "1";
                        } else {
                            if (isset($passedvariables["skipconfig"]) && $passedvariables["skipconfig"]) {
                                $request->session()->forget("cart.products.$newprodnum.noconfig");
                                $request->session()->put("cart.lastconfigured", ["type" => "product", "i" => $newprodnum]);
                                return redirect()->route("cart", ['a' => 'view']);
                            }
                        }
                    
                        return redirect()->route("cart", ['a' => 'confproduct', 'i' => $newprodnum, 'ajax' => $ajax]);
                    }
                } else {
                    if ($aid) {
                        $requestAddonID = (int) $request->get("aid");
                        $requestServiceID = (int) $request->get("serviceid");
                        $requestProductID = (int) $request->get("productid");

                        if (!$requestServiceID && $requestProductID) {
                            $requestServiceID = $requestProductID;
                        }

                        if (!$requestAddonID || !$requestServiceID) {
                            return redirect()->route('cart', ['gid' => 'addons'])->with('errormessage', 'tidak ada requestAddonID atau requestServiceID');
                        }

                        $data = \App\Models\Hosting::where([
                            "id" => $requestServiceID,
                            "userid" => $auth ? $auth->id : 0,
                            "domainstatus" => "Active"
                        ]);

                        $serviceid = $data->value("id");
                        $pid = $data->value("packageid");

                        if (!$serviceid) {
                            return redirect()->route('cart', ['gid' => 'addons'])->with('errormessage', 'tidak ada serviceID');
                        }

                        $data = \App\Models\Addon::where(["id" => $requestAddonID]);
                        $aid = $data->value("id");
                        $packages = $data->value("packages");

                        if (!$aid) {
                            return redirect()->route('cart', ['gid' => 'addons'])->with('errormessage', 'Tidak ada aID');
                        }

                        // Convert $packages to an array for easier comparison
                        $packages = explode(",", $packages);
                        if (!in_array($pid, $packages)) {
                            return redirect()->route('cart', ['gid' => 'addons']);
                        }

                        $request->session()->push("cart.addons", [
                            "id" => $aid,
                            "productid" => $serviceid
                        ]);

                        if ($ajax) {
                            return "";
                        }

                        return redirect()->route('cart', ['a' => 'view']);
                    } else {
                        if ($domain = $request->get("domain")) {
                            $allowRegistration = Cfg::getValue("AllowRegister");
                            $allowTransfers = Cfg::getValue("AllowTransfer");
                            $allowRenewalOrders = Cfg::getValue("EnableDomainRenewalOrders");

                            $smartyvalues["domainRegistrationEnabled"] = (bool) $allowRegistration;
                            $smartyvalues["registerdomainenabled"] = $smartyvalues["domainRegistrationEnabled"];
                            $smartyvalues["domainTransferEnabled"] = (bool) $allowTransfers;
                            $smartyvalues["transferdomainenabled"] = $smartyvalues["domainTransferEnabled"];
                            $smartyvalues["renewalsenabled"] = (bool) $allowRenewalOrders;

                            if (!in_array($domain, ["register", "transfer"])) {
                                $domain = "register";
                            }

                            if ($domain == "register" && !$allowRegistration) {
                                return redirect()->route('cart')->with('errormessage', 'Domain tidak diizinkan untuk melakukan registrasi');
                            }

                            if ($domain == "transfer" && !$allowTransfers) {
                                return redirect()->route('cart')->with('errormessage', 'Domain tidak diizinkan untuk melakukan transfer');
                            }

                            $pricing = \App\Helpers\HelperApi::post("GetTLDPricing", [
                                // "clientid" => $auth ? $auth->id : 0,
                                // "currencyid" => $currency["id"]
                            ]);

                            if ($pricing["result"] == "error") {
                                return abort(404, $pricing["message"]);
                            }

                            $smartyvalues["pricing"] = $pricing["result"] == "error" ? ["pricing" => []] : $pricing;
                            $prc = $smartyvalues["pricing"]["pricing"];

                            foreach ($prc as $tld => &$priceData) {
                                foreach (["register", "transfer", "renew"] as $action) {
                                    foreach ($priceData[$action] as $term => &$price) {
                                        $price = new \App\Helpers\FormatterPrice($price, (array) $smartyvalues["pricing"]["currency"]);
                                    }
                                }
                            }
                            unset($price);
                            unset($priceData);

                            $extensions = array_keys($prc) ?: [];
                            $featuredTlds = [];
                            $spotlights = \App\Helpers\DomainFunctions::getSpotlightTldsWithPricing();

                            foreach ($spotlights as $spotlight) {
                                // Placeholder for future logic related to TLD logos
                            }

                            $smartyvalues["featuredTlds"] = $featuredTlds;

                            try {
                                $tldCategories = \App\Models\Domain\TopLevel\Category::whereHas("topLevelDomains", function ($query) use ($extensions) {
                                    $query->whereIn("tld", $extensions);
                                })->with("topLevelDomains")->tldsIn($extensions)->orderBy("is_primary", "desc")
                                    ->orderBy("display_order")->orderBy("category")->get();
                            } catch (\Exception $e) {
                                $tldCategories = [];
                            }

                            // $categoryCounts = [];
                            // foreach ($pricing["pricing"] as $extension => $price) {
                            //     foreach ($price["categories"] as $category) {
                            //         $categoryCounts[$category]++;
                            //     }
                            // }

                            $categoryCounts = [];
                            foreach ($pricing["pricing"] as $extension => $price) {
                                if (isset($price["categories"]) && is_array($price["categories"])) {
                                    foreach ($price["categories"] as $category) {
                                        if (!isset($categoryCounts[$category])) {
                                            $categoryCounts[$category] = 0;
                                        }
                                        $categoryCounts[$category]++;
                                    }
                                }
                            }

                            $categoriesWithCounts = [];
                            foreach ($tldCategories->pluck("category") as $category) {
                                $categoriesWithCounts[$category] = $categoryCounts[$category];
                            }

                            if (array_key_exists("Other", $categoryCounts)) {
                                $categoriesWithCounts["Other"] = $categoryCounts["Other"];
                            }

                            $smartyvalues["categoriesWithCounts"] = $categoriesWithCounts;
                            $smartyvalues["availabilityresults"] = [];

                            if ($domains) {
                                $passedvariables = $request->session()->get("cart.passedvariables");
                                $request->session()->forget("cart.passedvariables");

                                foreach ($domains as $domainname) {
                                    \App\Helpers\Cart::cartPreventDuplicateDomain($domainname);
                                    $regperiod = $domainsregperiod[$domainname] ?? 1;
                                    $domainparts = explode(".", $domainname, 2);
                                    $temppricelist = \App\Helpers\Domain::getTLDPriceList("." . $domainparts[1]);

                                    if (!isset($temppricelist[$regperiod][$domain])) {
                                        if (isset($regperiods) && is_array($regperiods)) {
                                            foreach ($regperiods as $period) {
                                                if (substr($period, 0, strlen($domainname)) == $domainname) {
                                                    $regperiod = substr($period, strlen($domainname));
                                                }
                                            }
                                        }
                                        if (!$regperiod) {
                                            $tldyears = array_keys($temppricelist);
                                            $regperiod = $tldyears[0];
                                        }
                                    }

                                    $domainArray = [
                                        "type" => $domain,
                                        "domain" => $domainname,
                                        "regperiod" => $regperiod,
                                        "eppcode" => $eppcode ?? "",
                                        "isPremium" => false
                                    ];

                                    if (isset($passedvariables["addons"])) {
                                        foreach ($passedvariables["addons"] as $domaddon) {
                                            $domainArray[$domaddon] = true;
                                        }
                                    }

                                    if (isset($passedvariables["bnum"])) {
                                        $domainArray["bnum"] = $passedvariables["bnum"];
                                    }

                                    if (isset($passedvariables["bitem"])) {
                                        $domainArray["bitem"] = $passedvariables["bitem"];
                                    }

                                    $premiumData = Session::get("PremiumDomains");
                                    if ((bool) (int) Cfg::getValue("PremiumDomains") && array_key_exists($domainname, $premiumData)) {
                                        $premiumPrice = $premiumData[$domainname];
                                        if (array_key_exists("transfer", $premiumPrice["cost"])) {
                                            $domainArray["isPremium"] = true;
                                            $domainArray["domainpriceoverride"] = $premiumPrice["markupPrice"][1]["transfer"];
                                            $domainArray["registrarCostPrice"] = $premiumPrice["cost"]["transfer"];
                                            $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                                            $domainArray["domainpriceoverride"] = $domainArray["domainpriceoverride"]->toNumeric();
                                        }
                                        if (array_key_exists("renew", $premiumPrice["cost"])) {
                                            $domainArray["domainrenewoverride"] = $premiumPrice["markupPrice"][1]["renew"];
                                            $domainArray["registrarRenewalCostPrice"] = $premiumPrice["cost"]["renew"];
                                            $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                                            $domainArray["domainrenewoverride"] = $domainArray["domainrenewoverride"]->toNumeric();
                                        } else {
                                            $domainArray["isPremium"] = false;
                                        }
                                    }

                                    $request->session()->push("cart.domains", $domainArray);
                                }

                                if ($ajax) {
                                    $ajax = "1";
                                }

                                $newdomnum = count($request->session()->get("cart.domains")) - 1;
                                $request->session()->put("cart.lastconfigured", ["type" => "domain", "i" => $newdomnum]);

                                if (!$ajax && is_array($orderconf["denynonajaxaccess"]) && in_array("confdomains", $orderconf["denynonajaxaccess"])) {
                                    $smartyvalues["selecteddomains"] = $request->session()->get("cart.domains");
                                    $smartyvalues["skipselect"] = true;
                                } else {
                                    return redirect()->route('cart', ['a' => 'confdomains', 'ajax' => $ajax]);
                                }
                            }

                            $check = new \App\Helpers\Domain\Checker();

                            if ($domain == "transfer") {
                                //HARDCODE
                                $orderFormTemplate = (object) null;
                                //HARDCODE
                                // if ($orderFormTemplate->hasTemplate("domaintransfer")) {
                                //     $smartyvalues['captcha'] = "";
                                //     $smartyvalues['captchaForm'] = "";
                                //     $templatefile = "domaintransfer";
                                // } else {
                                //     $templatefile = "adddomain";
                                // }
                                $smartyvalues['captcha'] = "";
                                $smartyvalues['captchaForm'] = "";
                                $templatefile = "adddomain";
                            } else {
                                // //HARDCODE
                                // $orderFormTemplate = new \App\Helpers\OrderForm;
                                // $orderFormTemplate = (object) null;
                                // //HARDCODE
                                // if ($orderFormTemplate->hasTemplate("domainregister")) {
                                //     $showSuggestions = true;
                                //     if ($check->getLookupProvider() instanceof \App\Helpers\Domains\DomainLookup\Provider\BasicWhois && !Cfg::getValue("BulkCheckTLDs") || $check->getLookupProvider() instanceof \App\Helpers\Domains\DomainLookup\Provider\WhmcsWhois && !Cfg::getValue("domainLookup_WhmcsWhois_suggestTlds")) {
                                //         $showSuggestions = false;
                                //     }
                                //     // $smarty->assign("showSuggestionsContainer", $showSuggestions);
                                //     // $smarty->assign("captcha", $captcha);
                                //     // $smarty->assign("captchaForm", WHMCS\Utility\Captcha::FORM_DOMAIN_CHECKER);
                                //     $smartyvalues["showSuggestionsContainer"] = $showSuggestions;
                                //     $smartyvalues["captcha"] = "";
                                //     $smartyvalues["captchaForm"] = "";
                                //     // $captchaData = WHMCSSession::getAndDelete("captchaData");
                                //     // if ($captchaData) {
                                //     //     if (!$captchaData["invalidCaptchaError"]) {
                                //     //         $captcha->setEnabled(false);
                                //     //         $smarty->assign("captcha", $captcha);
                                //     //     } else {
                                //     //         $smarty->assign("captchaError", $captchaData["invalidCaptchaError"]);
                                //     //     }
                                //     // } else {
                                //     //     WHMCSSession::set("CaptchaComplete", false);
                                //     // }
                                //     $templatefile = "domainregister";
                                // } else {
                                //     $templatefile = "adddomain";
                                // }

                                //HARDCODE
                                // $orderFormTemplate = new \App\Helpers\OrderForm;
                                // $orderFormTemplate = (object) null;
                                //HARDCODE
                                $showSuggestions = true;
                                if ($check->getLookupProvider() instanceof \App\Helpers\Domains\DomainLookup\Provider\BasicWhois && !Cfg::getValue("BulkCheckTLDs") || $check->getLookupProvider() instanceof \App\Helpers\Domains\DomainLookup\Provider\WhmcsWhois && !Cfg::getValue("domainLookup_WhmcsWhois_suggestTlds")) {
                                    $showSuggestions = false;
                                }
                                $smartyvalues["showSuggestionsContainer"] = $showSuggestions;
                                $smartyvalues["captcha"] = "";
                                $smartyvalues["captchaForm"] = "";
                                $templatefile = "adddomain";
                            }
                            $registerTlds = \App\Helpers\DomainFunctions::getTLDList();
                            $transferTlds = \App\Helpers\DomainFunctions::getTLDList("transfer");
                            // $smarty->assign("registertlds", $registerTlds);
                            $smartyvalues["registertlds"] = $registerTlds;
                            // $smarty->assign("transfertlds", $transferTlds);
                            $smartyvalues["transfertlds"] = $transferTlds;
                            $tldslist = $domain == "register" ? $registerTlds : $transferTlds;
                            // $smarty->assign("tlds", $tldslist);
                            $smartyvalues["tlds"] = $tldslist;
                            // $smarty->assign("spotlightTlds", getSpotlightTldsWithPricing());
                            $smartyvalues["spotlightTlds"] = \App\Helpers\DomainFunctions::getSpotlightTldsWithPricing();
                            $smartyvalues["domain"] = $domain;
                            $sld = $request->get("sld");
                            $tld = $request->get("tld");
                            if ($domain == "transfer" && $request->get("sld_transfer")) {
                                $sld = $request->get("sld_transfer");
                            }
                            if ($domain == "transfer" && $request->get("tld_transfer")) {
                                $tld = $request->get("tld_transfer");
                            }
                            $lookupTerm = $request->get("query");
                            if (!$lookupTerm && $sld) {
                                if ($tld && ltrim($tld, ".") == $tld) {
                                    $tld = "." . $tld;
                                }
                                $lookupTerm = $sld . $tld;
                            }
                            if ($lookupTerm) {
                                $passedDomain = new \App\Helpers\Domain\Domain($lookupTerm);
                                $sld = $passedDomain->getSecondLevel();
                                $tld = $passedDomain->getDotTopLevel();
                            }
                            $smartyvalues["lookupTerm"] = $lookupTerm;
                            $smartyvalues["sld"] = $sld;
                            $smartyvalues["tld"] = $tld;
                            if ($sld && $tld && !$errormessage && $templatefile == "adddomain") {
                                $check->cartDomainCheck(new \App\Helpers\Domain\Domain($sld), array($tld));
                                $check->populateCartWithDomainSmartyVariables($domain, $smartyvalues);
                            }
                        } else {
                            $renewals = false;
                            $renewalid = false;
                            $renewalperiod = "";
                            $renewalids = [];
                            if ($renewals) {
                                if ($renewalid) {
                                    $request->session()->put("cart.renewals.$renewalid", $renewalperiod);
                                } else {
                                    if (!count($renewalids)) {
                                        return redirect()->route("cart", ['gid' => 'renewals']);
                                    } else {
                                        foreach ($renewalids as $domainid) {
                                            $request->session()->put("cart.renewals.$domainid", $renewalperiod[$domainid]);
                                        }
                                    }
                                }
                                if ($ajax) {
                                    return "";
                                }
                                return redirect()->route('cart', ['a' => 'view']);
                            } else {
                                $bid = false;
                                if ($bid) {
                                    $data = \App\Models\Bundle::where(array("id" => $bid));
                                    $bid = $data->value("id");
                                    $validfrom = $data->value("validfrom") ?? "0000-00-00";
                                    $validuntil = $data->value("validuntil") ?? "0000-00-00";
                                    $uses = $data->value("uses") ?? 0;
                                    $maxuses = $data->value("maxuses") ?? 0;
                                    $itemdata = $data->value("itemdata");
                                    $itemdata = (new \App\Helpers\Client)->safe_unserialize($itemdata);
                                    $vals = $itemdata[0];

                                    if ($validfrom != "0000-00-00" && date("Ymd") < str_replace("-", "", $validfrom) || $validuntil != "0000-00-00" && str_replace("-", "", $validuntil) < date("Ymd")) {
                                        $templatefile = "error";
                                        $smartyvalues["errortitle"] = $_LANG["bundlevaliddateserror"];
                                        $smartyvalues["errormsg"] = $_LANG["bundlevaliddateserrordesc"];
                                        $this->setTheme($orderFormTemplateName);
                                        return view($templatefile, $smartyvalues);
                                    }

                                    if ($maxuses && $maxuses <= $uses) {
                                        $templatefile = "error";
                                        $smartyvalues["errortitle"] = $_LANG["bundlemaxusesreached"];
                                        $smartyvalues["errormsg"] = $_LANG["bundlemaxusesreacheddesc"];
                                        $this->setTheme($orderFormTemplateName);
                                        return view($templatefile, $smartyvalues);
                                    }

                                    $request->session()->push("cart.bundle", array("bid" => $bid, "step" => "0", "complete" => "0"));
                                    $totalnum = count($request->session()->get("cart.bundle"));
                                    $vals["bnum"] = $totalnum - 1;
                                    $vals["bitem"] = "0";
                                    $vals["billingcycle"] = str_replace(array("-", " "), "", strtolower($vals["billingcycle"]));
                                    $request->session()->put("cart.passedvariables", $vals);

                                    if ($vals["type"] == "product") {
                                        $extraVars = ['pid' => $vals['pid']];
                                    } else if ($vals["type"] == "domain") {
                                        $extraVars = ['domain' => 'register'];
                                    } else {
                                        return redirect()->route('cart')->with('errormessage', 'Tipe product yang didukung tidak sesuai');
                                    }
                                    $extraVars = array_merge(['a' => 'add'], $extraVars);
                                    return redirect()->route('cart', $extraVars);
                                } else {
                                    return redirect()->route('cart')->with('errormessage', 'Terjadi kesalahan bid');
                                }
                            }
                        }
                    }
                }
            break;

            case 'confproduct':
                $billingcycle = $request->input('billingcycle');
                $templatefile = "configureproduct";

                if (is_null($productInfoKey) || !$request->session()->get("cart.products.$productInfoKey") || !is_array($request->session()->get("cart.products.$productInfoKey"))) {
                    if ($ajax) {
                        return $_LANG["invoiceserror"];
                    }
                    return redirect()->route("cart")->with('errormessage', 'Tidak ditemukan productInfoKey dari session');
                }

                // Retrieve the current product configuration
                $productInfoKey = (int) $request->input('i');
                $currentProduct = $request->session()->get("cart.products.$productInfoKey");

                // Check if the custom field has changed
                $newCustomField = $request->input('customfield.1215');
                if ($newCustomField && $newCustomField !== $currentProduct['customfields'][1215]) {
                    // Update the custom field in the session
                    $request->session()->put("cart.products.$productInfoKey.customfields.1215", $newCustomField);

                    // Recalculate the backorder fee based on the new custom field
                    $backorderFee = $this->hook_domainFeeBackorder([
                        'products' => [
                            [
                                'customfields' => ['1215' => $newCustomField],
                                'domain' => $currentProduct['domain']
                            ]
                        ]
                    ]);

                    $currentAdjustments = $request->session()->get('cart.adjustments', []);
                    if (is_null($currentAdjustments)) {
                        $backorderFee = $this->hook_domainFeeBackorder([
                            'products' => [
                                [
                                    'customfields' => ['1215' => $newCustomField],
                                    'domain' => $currentProduct['domain']
                                ]
                            ]
                        ]);
                        $currentAdjustments = [
                            [
                                'description' => $backorderFee['description'],
                                'amount' => $backorderFee['amount'],
                                'taxed' => $backorderFee['taxed'],
                                'pid' => $currentProduct['pid']
                            ]
                        ];
                    }

                    foreach ($currentAdjustments as &$adjustment) {
                        if ($adjustment['pid'] == $currentProduct['pid']) {
                            $adjustment['amount'] = $backorderFee['amount'];
                            $adjustment['description'] = $backorderFee['description'];
                            $adjustment['taxed'] = $backorderFee['taxed'];
                        }
                    }

                    $request->session()->put('cart.adjustments', $currentAdjustments);
                }

                $skipConfig = $request->session()->get("cart.products.$productInfoKey.skipConfig");
                if ($skipConfig) {
                    $request->session()->put("cart.products.$productInfoKey.skipConfig", false);
                    return redirect()->route("cart", ['a' => 'view']);
                }

                $newproduct = $request->session()->get("cart.newproduct") ? $request->session()->get("cart.newproduct") : "";
                $request->session()->forget("cart.newproduct");

                $pid = $request->session()->get("cart.products.$productInfoKey.pid");
                $productinfo = $orderfrm->setPid($pid);
                if (!$productinfo) {
                    return redirect()->route("cart")->with('errormessage', 'Produk dengan id : ' . $pid . ' tidak ditemukan');
                }

                $orderFormTemplateName = $productinfo["orderfrmtpl"] == "" ? $orderFormTemplateName : $productinfo["orderfrmtpl"];
                $request->session()->put("cart.cartsummarypid", $productinfo["pid"]);

                $validate = new \App\Helpers\Validate();
                $configure = $request->input("configure");
                if ($configure) {
                    global $errormessage;
                    $errormessage = "";
                    $serverarray = [];

                    if ($productinfo["type"] == "server") {
                        $hostname = $request->input("hostname");
                        $ns1prefix = $request->input("ns1prefix");
                        $ns2prefix = $request->input("ns2prefix");
                        $rootpw = $request->input("rootpw");

                        $validate->validate("required", "hostname", "client.ordererrorservernohostname");
                        if ($validate->validated("hostname")) {
                            $validate->validate("unique_service_domain", "hostname", "client.ordererrorserverhostnameinuse");
                            if ($validate->validated("hostname")) {
                                $validate->validate("hostname", "hostname", "client.orderErrorServerHostnameInvalid");
                                if ($validate->validated("hostname")) {
                                    $validate->reverseValidate("numeric", "hostname", "client.orderErrorServerHostnameInvalid");
                                }
                            }
                        }

                        $validate->validate("required", "ns1prefix", "client.ordererrorservernonameservers");
                        if ($validate->validated("ns1prefix")) {
                            $validate->validate("alphanumeric", "ns1prefix", "client.orderErrorServerNameserversInvalid");
                            if ($validate->validated("ns1prefix")) {
                                $validate->reverseValidate("numeric", "ns1prefix", "client.orderErrorServerNameserversInvalid");
                            }
                        }

                        $validate->validate("required", "ns2prefix", "client.ordererrorservernonameservers");
                        if ($validate->validated("ns2prefix")) {
                            $validate->validate("alphanumeric", "ns2prefix", "client.orderErrorServerNameserversInvalid");
                            if ($validate->validated("ns2prefix")) {
                                $validate->reverseValidate("numeric", "ns2prefix", "client.orderErrorServerNameserversInvalid");
                            }
                        }

                        $validate->validate("required", "rootpw", "client.ordererrorservernorootpw");
                        $serverarray = ["hostname" => $hostname, "ns1prefix" => $ns1prefix, "ns2prefix" => $ns2prefix, "rootpw" => $rootpw];
                    }

                    $configoptionsarray = [];
                    $configoption = $request->input("configoption");
                    if ($configoption) {
                        $configOpsReturn = \App\Helpers\ConfigOptions::validateAndSanitizeQuantityConfigOptions($configoption);
                        $configoptionsarray = $configOpsReturn["validOptions"];
                        $errormessage .= $configOpsReturn["errorMessage"];
                    }

                    $addons = $request->input("addons");
                    $addonsarray = is_array($addons) ? array_keys($addons) : [];
                    foreach ($request->input("addons_radio") ?? [] as $addonId) {
                        if (is_numeric($addonId)) {
                            $addonsarray[] = $addonId;
                        }
                    }

                    $customFieldData = $request->session()->get("cart.products.$productInfoKey.customfields") ?? [];
                    $newCustomFieldData = (array) $request->input("customfield");
                    foreach ($newCustomFieldData as $key => $value) {
                        $customFieldData[$key] = $value;
                    }

                    // Custom fields for addons
                    $customFieldDataAddon = $request->session()->get("cart.products.$productInfoKey.acustomfields") ?? [];
                    $newCustomFieldDataAddon = (array) $request->input("acustomfield");
                    foreach ($newCustomFieldDataAddon as $key => $value) {
                        $customFieldDataAddon[$key] = $value;
                    }

                    $errormessage .= \App\Helpers\Cart::bundlesValidateProductConfig($productInfoKey, $billingcycle ?? "", $configoptionsarray, $addonsarray);

                    $request->session()->put("cart.products.$productInfoKey.billingcycle", $billingcycle ?? "");
                    $request->session()->put("cart.products.$productInfoKey.server", $serverarray);
                    $request->session()->put("cart.products.$productInfoKey.configoptions", $configoptionsarray);
                    $request->session()->put("cart.products.$productInfoKey.customfields", $customFieldData);
                    $request->session()->put("cart.products.$productInfoKey.acustomfields", $customFieldDataAddon);
                    $request->session()->put("cart.products.$productInfoKey.addons", $addonsarray);
                    
                    $calctotal = $request->input("calctotal");
                    if ($calctotal) {
                        $productinfo = $orderfrm->setPid($request->session()->get("cart.products.$productInfoKey.pid"));
                        $orderFormTemplateName = $productinfo["orderfrmtpl"] == "" ? $orderFormTemplateName : $productinfo["orderfrmtpl"];
                        try {
                            $orderSummaryTemplate = "ordersummary";
                            $cartTotals = \App\Helpers\Orders::calcCartTotals(false, true);
                            $templateVariables = ["producttotals" => $cartTotals["products"][$productInfoKey], "carttotals" => $cartTotals];
                            $this->setTheme($orderFormTemplateName);

                            return view($orderSummaryTemplate, $templateVariables);
                            } catch (\Exception $e) {
                        }
                        return "";
                    }

                    $previousbillingcycle = "";
                    if (!$ajax && !$request->input("nocyclerefresh") && $previousbillingcycle != ($billingcycle ?? "")) {
                        return redirect()->route("cart", ['a' => 'confproduct', 'i' => $productInfoKey]);
                    }

                    $validate->validateCustomFields("product", $pid, true);

                    // Custom field validation for addons
                    foreach ($addonsarray as $addonId) {
                        $validate->validateCustomFields("addon", $addonId, true, [], "acustomfield");
                    }

                    Hooks::run_validate_hook($validate, "ShoppingCartValidateProductUpdate", $request->all());

                    if ($validate->hasErrors()) {
                        $errormessage .= $validate->getHTMLErrorOutput();
                    }

                    if ($errormessage) {
                        if ($ajax) {
                            return $errormessage;
                        }
                        $smartyvalues["errormessage"] = $errormessage;
                    } else {
                        $request->session()->forget("cart.products.$productInfoKey.noconfig");
                        $request->session()->put("cart.lastconfigured", ["type" => "product", "i" => $productInfoKey]);
                        if ($ajax) {
                            return "";
                        }
                        return redirect()->route('cart', ['a' => 'confdomains']);
                    }
                }

                $billingcycle = $request->session()->get("cart.products.$productInfoKey.billingcycle");
                $server = $request->session()->get("cart.products.$productInfoKey.server");
                $customfields = $request->session()->get("cart.products.$productInfoKey.customfields");
                $acustomfields = $request->session()->get("cart.products.$productInfoKey.acustomfields");
                $configoptions = $request->session()->get("cart.products.$productInfoKey.configoptions");
                $addons = $request->session()->get("cart.products.$productInfoKey.addons") ?? [];
                $domain = $request->session()->get("cart.products.$productInfoKey.domain");
                $noconfig = $request->session()->get("cart.products.$productInfoKey.noconfig");

                $billingcycle = $orderfrm->validateBillingCycle($billingcycle);
                $pricing = \App\Helpers\Orders::getPricingInfo($pid);
                $configurableoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($pid, $configoptions, $billingcycle, "", true);
                $customfields = \App\Helpers\Customfield::getCustomFields("product", $pid, "", "", "on", $customfields);
                $addonsarray = \App\Helpers\Orders::getAddons($pid, $addons);

                $addonsPromoOutput = [];
                $hookResponses = Hooks::run_hook("ShoppingCartConfigureProductAddonsOutput", ["billingCycle" => $billingcycle, "selectedAddons" => $addonsarray]);
                foreach ($hookResponses as $response) {
                    if ($response) {
                        $addonsPromoOutput[] = $response;
                    }
                }
                $smartyvalues["addonsPromoOutput"] = $addonsPromoOutput;

                $recurringcycles = 0;
                if (isset($pricing["type"]) && $pricing["type"] == "recurring") {
                    foreach (["monthly", "quarterly", "semiannually", "annually", "biennially"] as $cycle) {
                        if ($pricing["rawpricing"][$cycle] >= 0) {
                            $recurringcycles++;
                        }
                    }
                }

                if ($newproduct && $productinfo["type"] != "server" && ($pricing["type"] != "recurring" || $recurringcycles <= 1) && !count($configurableoptions) && !count($customfields) && !count($addonsarray) && !$addonsPromoOutput) {
                    $request->session()->put("cart.products.$productInfoKey.noconfig", false);
                    $request->session()->put("cart.lastconfigured", ["type" => "product", "i" => $productInfoKey]);
                    if ($ajax) {
                        return "";
                    }
                    return redirect()->route("cart", ['a' => 'confdomains']);
                }

                $serverarray = [
                    "hostname" => $server["hostname"] ?? "",
                    "ns1prefix" => $server["ns1prefix"] ?? "",
                    "ns2prefix" => $server["ns2prefix"] ?? "",
                    "rootpw" => $server["rootpw"] ?? ""
                ];

                $smartyvalues["editconfig"] = true;
                $smartyvalues["firstconfig"] = $noconfig ? true : false;
                $smartyvalues["i"] = $productInfoKey;
                $smartyvalues["productinfo"] = $productinfo;
                $smartyvalues["pricing"] = $pricing;
                $smartyvalues["billingcycle"] = $billingcycle;
                $smartyvalues["server"] = $serverarray;
                $smartyvalues["configurableoptions"] = $configurableoptions;
                $smartyvalues["addons"] = $addonsarray;
                $smartyvalues["customfields"] = $customfields;
                $smartyvalues["domain"] = $domain;
            break;

            case 'confdomains':
                $templatefile = "configuredomains";
                $skipstep = true;
                $request->session()->put("cartdomain", "");

                $update = $request->input("update");
                $validate = $request->input("validate");

                if ($update || $validate) {
                    $validateHookParams = $request->all();
                    $domains = $request->session()->get("cart.domains");
                    $domainfield = [];

                    foreach ($domains as $key => $domainname) {
                        if ($validate) {
                            $domainfield[$key] = $request->session()->get("cart.domains.$key.fields");
                        } else {
                            $request->session()->put("cart.domains.$key.dnsmanagement", $request->input("dnsmanagement.$key"));
                            $request->session()->put("cart.domains.$key.emailforwarding", $request->input("emailforwarding.$key"));
                            $request->session()->put("cart.domains.$key.idprotection", $request->input("idprotection.$key"));
                            $request->session()->put("cart.domains.$key.eppcode", $request->input("epp.$key"));
                        }

                        $domainparts = explode(".", $domainname["domain"], 2);
                        $additflds = new \App\Helpers\Domain\AdditionalFields();
                        $additflds->setTLD($domainparts[1]);
                        $additflds->setFieldValues($domainfield[$key] ?? "");
                        $missingfields = $additflds->getMissingRequiredFields();

                        foreach ($missingfields as $missingfield) {
                            $errormessage .= "<li>" . $missingfield . " " . $_LANG["clientareaerrorisrequired"] . " (" . $domainname["domain"] . ")";
                        }

                        $request->session()->put("cart.domains.$key.fields", $domainfield[$key] ?? "");
                        $validateHookParams["domainfield"][$key] = $additflds->getAsNameValueArray();

                        if ($domainname["type"] !== "register") {
                            $result = \App\Models\Domainpricing::where(["extension" => "." . $domainparts[1]]);
                            $data = $result;
                            if ($data->value("eppcode") && !$request->input("epp.$key")) {
                                $errormessage .= "<li>" . $_LANG["domaineppcoderequired"] . " " . $domainname["domain"];
                            }
                        }
                    }

                    for ($i = 1; $i <= 5; $i++) {
                        $ns = $request->input("domainns" . $i);
                        if (preg_match($nameserverRegexPattern, $ns)) {
                            $request->session()->put("cart.ns$i", $ns);
                        }
                        if ($ns == "" && session("cart.ns$i")) {
                            $request->session()->forget("cart.ns$i");
                        }
                    }

                    $validate = new \App\Helpers\Validate();
                    Hooks::run_validate_hook($validate, "ShoppingCartValidateDomainsConfig", $validateHookParams);

                    if ($validate->hasErrors()) {
                        $errormessage .= $validate->getHTMLErrorOutput();
                    }

                    if ($ajax) {
                        return $errormessage;
                    }

                    if ($errormessage) {
                        $smartyvalues["errormessage"] = $errormessage;
                    } else {
                        return redirect()->route('cart', ['a' => 'view']);
                    }
                }

                $domains = $request->session()->get("cart.domains");
                if ($domains) {
                    foreach ($domains as $key => $domainname) {
                        $regperiod = $domainname["regperiod"];
                        $domainparts = explode(".", $domainname["domain"], 2);
                        list($sld, $tld) = $domainparts;
                        $result = \App\Models\Domainpricing::where(["extension" => "." . $tld]);
                        $data = $result;

                        $domainconfigsshowing = $eppenabled = false;
                        if ($data->value("dnsmanagement")) {
                            $domainconfigsshowing = true;
                        }
                        if ($data->value("emailforwarding")) {
                            $domainconfigsshowing = true;
                        }
                        if ($data->value("idprotection")) {
                            $domainconfigsshowing = true;
                        }

                        $result = \App\Models\Pricing::where([
                            "type" => "domainaddons",
                            "currency" => $currency["id"],
                            "relid" => 0
                        ]);

                        $data2 = $result;
                        $domaindnsmanagementprice = ($data2->value("msetupfee") ?? 0) * $regperiod;
                        $domainemailforwardingprice = ($data2->value("qsetupfee") ?? 0) * $regperiod;
                        $domainidprotectionprice = ($data2->value("ssetupfee") ?? 0) * $regperiod;

                        $domaindnsmanagementprice = $domaindnsmanagementprice == "0.00" || $domaindnsmanagementprice == "0" ? $_LANG["orderfree"] : new \App\Helpers\FormatterPrice($domaindnsmanagementprice, $currency);
                        $domainemailforwardingprice = $domainemailforwardingprice == "0.00" || $domainemailforwardingprice == "0" ? $_LANG["orderfree"] : new \App\Helpers\FormatterPrice($domainemailforwardingprice, $currency);
                        $domainidprotectionprice = $domainidprotectionprice == "0.00" || $domainidprotectionprice == "0" ? $_LANG["orderfree"] : new \App\Helpers\FormatterPrice($domainidprotectionprice, $currency);

                        if ($data->value("eppcode") && $domainname["type"] == "transfer") {
                            $eppenabled = true;
                            $domainconfigsshowing = true;
                        }

                        $additflds = new \App\Helpers\Domain\AdditionalFields();
                        $additflds->setTLD($tld);
                        $fieldValues = isset($domainname["fields"]) ? $domainname["fields"] : [];
                        $additflds->setFieldValues($fieldValues);
                        $domainfields = $additflds->getFieldsForOutput($key);

                        if (count($domainfields)) {
                            $domainconfigsshowing = true;
                        }

                        $products = $request->session()->get("cart.products");
                        $hashosting = false;

                        if ($products) {
                            foreach ($products as $product) {
                                if ($product["domain"] == $domainname["domain"]) {
                                    $hashosting = true;
                                }
                            }
                        }

                        if (!$hashosting) {
                            $atleastonenohosting = true;
                        }

                        if (isset($atleastonenohosting) && $atleastonenohosting) {
                            $skipstep = false;
                        }

                        $domainAddonsCount = 0;
                        if ($data->value("dnsmanagement")) {
                            $domainAddonsCount++;
                        }
                        if ($data->value("emailforwarding")) {
                            $domainAddonsCount++;
                        }
                        if ($data->value("idprotection")) {
                            $domainAddonsCount++;
                        }

                        $domainsarray[$key] = [
                            "domain" => $domainname["domain"],
                            "regperiod" => $domainname["regperiod"],
                            "dnsmanagement" => $data->value("dnsmanagement"),
                            "emailforwarding" => $data->value("emailforwarding"),
                            "idprotection" => $data->value("idprotection"),
                            "addonsCount" => $domainAddonsCount,
                            "dnsmanagementprice" => $domaindnsmanagementprice,
                            "emailforwardingprice" => $domainemailforwardingprice,
                            "idprotectionprice" => $domainidprotectionprice,
                            "dnsmanagementselected" => isset($domainname["dnsmanagement"]) ? $domainname["dnsmanagement"] : false,
                            "emailforwardingselected" => isset($domainname["emailforwarding"]) ? $domainname["emailforwarding"] : false,
                            "idprotectionselected" => isset($domainname["idprotection"]) ? $domainname["idprotection"] : false,
                            "eppenabled" => $eppenabled,
                            "eppvalue" => isset($domainname["eppcode"]) ? $domainname["eppcode"] : "",
                            "fields" => $domainfields,
                            "configtoshow" => $domainconfigsshowing,
                            "hosting" => $hashosting
                        ];

                        if ($domainconfigsshowing || $eppenabled || $domainfields || $data->value("dnsmanagement") || $data->value("emailforwarding") || $data->value("idprotection")) {
                            $skipstep = false;
                        }
                    }
                }

                $smartyvalues["domains"] = $domainsarray ?? [];
                $smartyvalues["atleastonenohosting"] = $atleastonenohosting ?? false;

                if (!$skipstep && !$request->session()->get("cart.ns1") && !$request->session()->get("cart.ns2")) {
                    for ($i = 1; $i <= 5; $i++) {
                        $request->session()->put("cart.ns$i", isset($CONFIG["DefaultNameserver" . $i]) ? $CONFIG["DefaultNameserver" . $i] : null);
                    }
                }

                for ($i = 1; $i <= 5; $i++) {
                    $ns = $request->session()->get("cart.ns$i") ?? "";
                    $smartyvalues["domainns" . $i] = $ns;
                }

                if ($skipstep) {
                    if ($ajax) {
                        return "";
                    }
                    return redirect()->route('cart', ['a' => 'view']);
                }
            break;

            case 'view':
                if (session()->has('removepromomessage')) {
                    $smartyvalues["removepromomessage"] = session('removepromomessage');
                    session()->forget('removepromomessage');
                }
                $errormessage = "";
                if (session()->has('errormessage')) {
                    $errormessage = session('errormessage');
                    $smartyvalues["errormessage"] = $errormessage;
                    session()->forget('errormessage');
                }
                $templatefile = "viewcart";
                $gateways = new \App\Helpers\Gateways();
                $availablegateways = \App\Helpers\Orders::getAvailableOrderPaymentGateways(true);
                $securityquestions = (new \App\Helpers\Client)->getSecurityQuestions();
                
                $submit = $request->input("submit");
                $checkout = $request->input("checkout");
                $validatelogin = $request->input("validatelogin");
                $validatepromo = $request->input("validatepromo");
                
                $ccinfo = $request->input("ccinfo");
                $cctype = $request->input("cctype");
                $ccDescription = $request->input("ccdescription");
                $ccnumber = $request->input("ccnumber");
                $ccexpirymonth = $request->input("ccexpirymonth");
                $ccexpiryyear = $request->input("ccexpiryyear");
                $ccstartmonth = $request->input("ccstartmonth");
                $ccstartyear = $request->input("ccstartyear");
                $ccissuenum = $request->input("ccissuenum");
                $cccvvexisting = $cccvv = $request->input("cccvv");
                $nostore = $request->input("nostore");
                
                $password = $request->input("password");
                $password2 = $request->input("password2");
                $customfields = $request->input("customfields");
                $notes = $request->input("notes");
                $contact = $request->input("contact");
                $addcontact = $request->input("addcontact");
                
                $domaincontactfirstname = $request->input("domaincontactfirstname");
                $domaincontactlastname = $request->input("domaincontactlastname");
                $domaincontactcompanyname = $request->input("domaincontactcompanyname");
                $domaincontactemail = $request->input("domaincontactemail");
                $domaincontactaddress1 = $request->input("domaincontactaddress1");
                $domaincontactaddress2 = $request->input("domaincontactaddress2");
                $domaincontactcity = $request->input("domaincontactcity");
                $domaincontactstate = $request->input("domaincontactstate");
                $domaincontactpostcode = $request->input("domaincontactpostcode");
                $domaincontactcountry = $request->input("domaincontactcountry");
                $domaincontactphonenumber = $request->input("domaincontactphonenumber");
                
                if ($domaincontactphonenumber) {
                    $number = PhoneNumber::parse($domaincontactphonenumber, 'ID');
                    $domaincontactphonenumber = $number->format(PhoneNumberFormat::E164);
                }
                
                $domainContactTaxId = $request->input("domaincontacttax_id");
                $loginfailed = $request->input("loginfailed");
                $insufficientstock = $request->input("insufficientstock");
                
                if ($insufficientstock) {
                    $errormessage .= "<li>" . $_LANG["insufficientstockmessage"] . "</li>";
                }
                
                $ccExpiryDate = $request->input("ccexpirydate");
                if ($ccExpiryDate) {
                    $ccExpirySplit = explode("/", $ccExpiryDate);
                    $ccexpirymonth = !empty($ccExpirySplit[0]) ? $ccExpirySplit[0] : "";
                    $ccexpiryyear = !empty($ccExpirySplit[1]) ? $ccExpirySplit[1] : "";
                }
                $ccexpirymonth = trim($ccexpirymonth);
                $ccexpiryyear = trim($ccexpiryyear);
                if (strlen($ccexpiryyear) > 2) {
                    $ccexpiryyear = substr($ccexpiryyear, -2);
                }
                
                $ccStartDate = $request->input("ccstartdate");
                if ($ccStartDate) {
                    $ccStartSplit = explode("/", $ccStartDate);
                    $ccstartmonth = !empty($ccStartSplit[0]) ? $ccStartSplit[0] : "";
                    $ccstartyear = !empty($ccStartSplit[1]) ? $ccStartSplit[1] : "";
                }
                $ccstartmonth = trim($ccstartmonth);
                $ccstartyear = trim($ccstartyear);
                if (strlen($ccstartmonth) > 2) {
                    $ccstartmonth = substr($ccstartmonth, -2);
                }
                if (!$cccvv && $cccvvexisting) {
                    $cccvv = $cccvvexisting;
                }
                
                $encryptedVarNames = ["cctype", "ccnumber", "ccexpirymonth", "ccexpiryyear", "ccstartmonth", "ccstartyear", "ccissuenum", "cccvv", "nostore"];
                foreach ($encryptedVarNames as $varName) {
                    if (strlen(${$varName}) > 32) {
                        ${$varName} = substr(${$varName}, 0, 32);
                    }
                }
                
                $firstname = $request->input("firstname") ?? "";
                $lastname = $request->input("lastname") ?? "";
                $companyname = $request->input("companyname") ?? "";
                $email = $request->input("email") ?? "";
                $address1 = $request->input("address1") ?? "";
                $address2 = $request->input("address2") ?? "";
                $city = $request->input("city") ?? "";
                $state = $request->input("state") ?? "";
                $postcode = $request->input("postcode") ?? "";
                $country = $request->input("country") ?? "";
                $phonenumber = $request->input("phonenumber") ?? "";
                
                if (($submit || $checkout || $validatelogin) && !$validatepromo) {
                    $paymentmethod = $request->input("paymentmethod");
                
                    if ($orderfrm->getNumItemsInCart() <= 0) {
                        return redirect()->route('cart', ['a' => 'view'])->with('errormessage', 'Item produk dalam keranjang kosong');
                    }
                    
                    $request->session()->put("cart.paymentmethod", $paymentmethod);
                    $request->session()->put("cart.notes", $notes);
                
                    if (!$auth) {
                        if ((isset($custtype) && $custtype == "existing") || $validatelogin) {
                            $loginemail = $request->input("loginemail");
                            $loginpw = \App\Helpers\Sanitize::decode($request->input("loginpw") ?: $request->input("loginpassword"));
                
                            if (\App\Helpers\ClientHelper::validateClientLogin($loginemail, $loginpw)) {
                                $values = \App\Helpers\ClientHelper::initialiseLoggedInClient($loginemail);
                                foreach ($values as $key => $value) {
                                    $smartyvalues[$key] = $value;
                                }
                                $auth = Auth::guard('web')->user();
                            } else {
                                if ($validatelogin) {
                                    return redirect()->route("cart", ['a' => 'checkout', 'loginfailed' => '1']);
                                }
                                $errormessage .= "<li>" . $_LANG["loginincorrect"];
                            }
                
                            if (session("2faverifyc")) {
                                session()->put("2fafromcart", true);
                                return redirect()->route("home");
                            }
                            if ($validatelogin) {
                                return redirect()->route("cart", ['a' => 'checkout']);
                            }
                        } else {
                            $cartUser = [
                                "firstname" => $firstname ?? "",
                                "lastname" => $lastname ?? "",
                                "companyname" => $companyname ?? "",
                                "email" => $email ?? "",
                                "address1" => $address1 ?? "",
                                "address2" => $address2 ?? "",
                                "city" => $city ?? "",
                                "state" => $state ?? "",
                                "postcode" => $postcode ?? "",
                                "country" => $country ?? "",
                                "phonenumber" => $phonenumber ?? ""
                            ];
                            
                            $request->session()->put("cart.user", $cartUser);
                            $errormessage .= (new \App\Helpers\Client)->checkDetailsareValid("", true, true, false);
                        }
                    }
                    
                    if ($validatelogin) {
                        return redirect()->route('cart', ['a' => 'checkout']);
                    }
                    
                    if ($contact == "new") {
                        return redirect()->route('cart', ['a' => 'addcontact']);
                    }
                    
                    if ($contact == "addingnew") {
                        $errormessage .= (new \App\Helpers\Client)->checkContactDetails("", false, "domaincontact");
                    }
                    
                    if (!empty($availablegateways) && $availablegateways[$paymentmethod]["type"] == "CC" && $ccinfo) {
                        $gateway = new \App\Module\Gateway();
                        $gateway->load($paymentmethod);
                        
                        if ($gateway->functionExists("cc_validation")) {
                            $params = [
                                "cardtype" => $cctype,
                                "cardnum" => \App\Helpers\Cc::ccFormatNumbers($ccnumber),
                                "cardexp" => \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccexpirymonth . $ccexpiryyear)),
                                "cardstart" => \App\Helpers\Cc::ccFormatDate(\App\Helpers\Cc::ccFormatNumbers($ccstartmonth . $ccstartyear)),
                                "cardissuenum" => \App\Helpers\Cc::ccFormatNumbers($ccissuenum)
                            ];
                            $errormessage .= $gateway->call("cc_validation", $params);
                        }
                    }
                
                    $validate = new \App\Helpers\Validate();
                    $cartCheckoutHookData = $request->all();
                    $cartCheckoutHookData["promocode"] = $orderfrm->getCartDataByKey("promo");
                    $cartCheckoutHookData["userid"] = $auth ? $auth->id : 0;
                    
                    \App\Helpers\Hooks::run_validate_hook($validate, "ShoppingCartValidateCheckout", $cartCheckoutHookData);
                    if ($auth && \App\Helpers\Cfg::get("EnableTOSAccept")) {
                        $validate->validate("required", "accepttos", "client.ordererroraccepttos");
                    }
                    
                    if ($validate->hasErrors()) {
                        $errormessage .= $validate->getHTMLErrorOutput();
                    }
                    
                    $currency = \App\Helpers\Format::getCurrency($auth ? $auth->id : 0, Session::get("currency"));
                    if ($request->input("updateonly")) {
                        $errormessage = "";
                    }
                    
                    if ($ajax && $errormessage) {
                        return $errormessage;
                    }
                    
                    if (!$errormessage && !$request->input("updateonly")) {
                        if (!$auth) {
                            $marketingoptin = empty($marketingoptin) ? 0 : 1;
                            $userid = \App\Helpers\ClientHelper::addClient(
                                $firstname, $lastname, $companyname, $email, $address1, $address2, $city,
                                $state, $postcode, $country, $phonenumber, $password, $securityqid,
                                $securityqans, true, ["tax_id" => $request->input("tax_id")], "", false, $marketingoptin
                            );
                        }
                
                        if ($contact == "addingnew") {
                            $contact = \App\Helpers\ClientHelper::addContact(
                                $auth ? $auth->id : 0, $domaincontactfirstname, $domaincontactlastname,
                                $domaincontactcompanyname, $domaincontactemail, $domaincontactaddress1,
                                $domaincontactaddress2, $domaincontactcity, $domaincontactstate,
                                $domaincontactpostcode, $domaincontactcountry, $domaincontactphonenumber, "",
                                [], "", "", "", "", "", "", $domainContactTaxId
                            );
                        }
                        
                        $request->session()->put("cart.contact", $contact);
                        define("INORDERFORM", true);
                        
                        $carttotals = \App\Helpers\Orders::calcCartTotals(true, false, $currency);
                        $request->session()->put("orderdetails.ccinfo", $ccinfo);
                        if ($ccinfo == "new" && !$nostore) {
                            $newPayMethod = NULL;
                            \App\Helpers\Cc::updateCCDetails($auth ? $auth->id : 0, $cctype, $ccnumber, $cccvv, $ccexpirymonth . $ccexpiryyear, $ccstartmonth . $ccstartyear, $ccissuenum, "", "", $paymentmethod, $newPayMethod, $ccDescription);
                            if ($newPayMethod) {
                                $invoiceModel = \App\Models\Invoice::find($request->session()->get("orderdetails.InvoiceID"));
                                if ($invoiceModel) {
                                    $invoiceModel->payMethod()->associate($newPayMethod);
                                    $invoiceModel->save();
                                }
                            }
                        }
                        
                        $orderid = $request->session()->get("orderdetails.OrderID");
                        $order = new \App\Helpers\OrderClass();
                        $order->setID($orderid);
                        $fraudModule = $order->getActiveFraudModule();
                
                        if ($fraudModule && $order->shouldFraudCheckBeSkipped()) {
                            $fraudModule = "";
                        }
                
                        if (!$fraudModule) {
                            if ($ajax) {
                                return "";
                            }
                            return redirect()->route('cart', ['a' => 'complete']);
                        }
                        
                        $fraud = new \App\Module\Fraud();
                        \App\Helpers\LogActivity::Save("Order ID " . $orderid . " Fraud Check Initiated");
                        \App\Models\Order::where(["id" => $orderid])->update(["status" => "Fraud"]);
                        
                        if ($request->session()->get("orderdetails.Products")) {
                            foreach ($request->session()->get("orderdetails.Products") as $productid) {
                                \App\Models\Hosting::where(["id" => $productid, "domainstatus" => "Pending"])->update(["domainstatus" => "Fraud"]);
                            }
                        }
                        
                        if ($request->session()->get("orderdetails.Addons")) {
                            foreach ($request->session()->get("orderdetails.Addons") as $addonid) {
                                \App\Models\Hostingaddon::where(["id" => $addonid, "status" => "Pending"])->update(["status" => "Fraud"]);
                            }
                        }
                        
                        if ($request->session()->get("orderdetails.Domains")) {
                            foreach ($request->session()->get("orderdetails.Domains") as $domainid) {
                                \App\Models\Domain::where(["id" => $domainid, "status" => "Pending"])->update(["status" => "Fraud"]);
                            }
                        }
                        
                        \App\Models\Invoice::where(["id" => $request->session()->get("orderdetails.InvoiceID"), "status" => "Unpaid"])->update(["status" => "Cancelled"]);
                
                        if ($fraud->load($fraudModule)) {
                            $results = $fraud->doFraudCheck($orderid);
                            $request->session()->put("orderdetails.fraudcheckresults", $results);
                        }
                
                        if ($ajax) {
                            return "";
                        }
                        
                        return redirect()->route('cart', ['a' => 'fraudcheck']);
                    }
                
                    if (!$paymentmethod) {
                        $errormessage .= "<li>No payment gateways available so order cannot proceed";
                    }
                }
                
                $smartyvalues["errormessage"] = $errormessage;
                
                if ($allowcheckout) {
                    $smartyvalues["captcha"] = null;
                    $smartyvalues["captchaForm"] = "";
                    $hookResponses = Hooks::run_hook("ShoppingCartCheckoutOutput", array("cart" => Session::get("cart")));
                    $smartyvalues["hookOutput"] = $hookResponses;
                } else {
                    $hookResponses = Hooks::run_hook("ShoppingCartViewCartOutput", array("cart" => Session::get("cart")));
                    $smartyvalues["hookOutput"] = $hookResponses;
                }

                if ($request->input('qty') && is_array($request->input('qty'))) {
                    $didQtyChangeRemoveProducts = false;
                    $temporderfrm = new \App\Helpers\OrderForm();
                    $insufficientstock = false;
                    foreach ($request->input('qty') as $i => $qty) {
                        $i = (int) $i;
                        $qty = (int) $qty;
                        if (is_array($request->session()->get("cart.products.$i"))) {
                            if (0 < $qty) {
                                $productinfo = $temporderfrm->setPid($request->session()->get("cart.products.$i.pid"));
                                if (!empty($productinfo) && $productinfo["stockcontrol"]) {
                                    if (!isset($productinfo["qty"])) {
                                        $productinfo["qty"] = 0;
                                    }
                                    if ($productinfo["qty"] < $qty) {
                                        $qty = $productinfo["qty"];
                                        $insufficientstock = true;
                                    }
                                }
                                $request->session()->put("cart.products.$i.qty", $qty);
                            } else {
                                if ($qty == 0) {
                                    $request->session()->forget("cart.products.$i");
                                    $didQtyChangeRemoveProducts = true;
                                }
                            }
                        }
                    }
                    if ($didQtyChangeRemoveProducts) {
                        $request->session()->put("cart.products", array_values($request->session()->get("cart.products")));
                    }
                    return redirect()->route("cart", ['a' => 'view', 'insufficientstock' => $insufficientstock ? '1' : '']);
                }

                $smartyvalues["promoaddedsuccess"] = false;

                if ($promocode) {
                    $paymentmethod = $request->input("paymentmethod");
                    $firstname = $request->input("firstname");
                    $promoerrormessage = \App\Helpers\Orders::SetPromoCode($promocode);
                    if ($promoerrormessage) {
                        $smartyvalues["promoerrormessage"] = $promoerrormessage;
                        $smartyvalues["errormessage"] = "<li>" . $promoerrormessage;
                    } else {
                        $smartyvalues["promoaddedsuccess"] = true;
                    }
                    if ($paymentmethod) {
                        $request->session()->put("cart.paymentmethod", $paymentmethod);
                    }
                    if ($ccinfo) {
                        $request->session()->put("cart.ccinfo", $ccinfo);
                    }
                    if ($notes) {
                        $request->session()->put("cart.notes", $notes);
                    }
                    if ($firstname) {
                        $phonenumber = $request->input('phonenumber');
                        $request->session()->put("cart.user", array("firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber));
                    }
                }

                $smartyvalues["promotioncode"] = $orderfrm->getCartDataByKey("promo");
                $cartsummary = $request->input("cartsummary");
                $ignorenoconfig = $cartsummary ? true : false;
                $carttotals = \App\Helpers\Orders::calcCartTotals(false, $ignorenoconfig, $currency);
                // dump($smartyvalues);
                // dump($ignorenoconfig);
                // dump($request->session()->all());
                // dump('$carttotals : ');
                // dump($carttotals);
                $promotype = $carttotals["promotype"];
                $promovalue = $carttotals["promovalue"];
                $promorecurring = $carttotals["promorecurring"];

                if (isset($carttotals["productRemovedFromCart"]) && $carttotals["productRemovedFromCart"]) {
                    $smartyvalues["errormessage"] .= "<li>" . Lang::get("client.outOfStockProductRemoved") . "</li>";
                }

                $promodescription = $promotype == "Percentage" ? $promovalue . "%" : $promovalue;
                if ($promotype == "Price Override") {
                    $promodescription .= " " . Lang::get("client.orderpromopriceoverride");
                } else {
                    if ($promotype == "Free Setup") {
                        $promodescription = Lang::get("client.orderpromofreesetup");
                    }
                }

                $promoCycles = DB::table("tblpromotions")->where("code", $smartyvalues["promotioncode"])->pluck("recurfor");
                $message = "orderForm.promoCycles";
                $replace["cycles"] = $promoCycles[0] ?? "";
                $forCycles = Lang::get($message, $replace);

                if (isset($promoCycles[0]) && $promoCycles[0] == 0) {
                    $promodescription .= " " . $promorecurring . " " . Lang::get("client.orderdiscount");
                } else {
                    $promodescription .= " " . $promorecurring . " " . Lang::get("client.orderdiscount") . " <br /> " . $forCycles;
                }

                $smartyvalues["promotiondescription"] = $promodescription;

                $amountOfCredit = 0;
                $canUseCreditOnCheckout = false;
                if ($auth) {
                    $amountOfCredit = $auth->credit;
                    if (0 < $amountOfCredit) {
                        $canUseCreditOnCheckout = true;
                    }
                }

                $smartyvalues["canUseCreditOnCheckout"] = $canUseCreditOnCheckout;
                $smartyvalues["creditBalance"] = new \App\Helpers\FormatterPrice($amountOfCredit, $currency);
                $smartyvalues["applyCredit"] = $request->input("applycredit") ? (bool) $request->input("applycredit") : true;
                $smartyvalues["client"] = \App\User\Client::loggedIn()->first();

                foreach ($carttotals as $k => $v) {
                    $smartyvalues[$k] = $v;
                }
                
                $hasProductQuantities = false;
                foreach ($carttotals["products"] as $product) {
                    if ($product["allowqty"]) {
                        $hasProductQuantities = true;
                    }
                }

                $smartyvalues["showqtyoptions"] = $hasProductQuantities;
                $smartyvalues["taxenabled"] = $CONFIG["TaxEnabled"];
                $paymentmethod = $request->session()->get("cart.paymentmethod");

                if (!$paymentmethod || !isset($availablegateways[$paymentmethod])) {
                    $paymentmethod = null;
                    foreach ($availablegateways as $k => $v) {
                        $paymentmethod = $k;
                        break;
                    }
                }

                $smartyvalues["selectedgateway"] = $paymentmethod;
                $smartyvalues["selectedgatewaytype"] = !empty($availablegateways) ? $availablegateways[$paymentmethod]["type"] : "";

                if (empty($request->session()->get("paypalexpress.payerid"))) {
                    $smartyvalues["gateways"] = array_filter($availablegateways, function ($item) {
                        return $item["sysname"] != "paypalexpress";
                    });
                } else {
                    $smartyvalues["gateways"] = array_filter($availablegateways, function ($item) {
                        return $item["sysname"] == "paypalexpress";
                    });
                    $smartyvalues["selectedgateway"] = "paypalexpress";
                }

                $smartyvalues["ccinfo"] = $ccinfo;
                $smartyvalues["cctype"] = $cctype;
                $smartyvalues["ccdescription"] = $ccDescription;
                $smartyvalues["ccnumber"] = $ccnumber;
                $smartyvalues["ccexpirymonth"] = $ccexpirymonth;
                $smartyvalues["ccexpiryyear"] = $ccexpiryyear;
                $smartyvalues["ccstartmonth"] = $ccstartmonth;
                $smartyvalues["ccstartyear"] = $ccstartyear;
                $smartyvalues["ccissuenum"] = $ccissuenum;
                $smartyvalues["cccvv"] = $cccvv;
                $smartyvalues["showccissuestart"] = $CONFIG["ShowCCIssueStart"];
                $smartyvalues["shownostore"] = $CONFIG["CCAllowCustomerDelete"];
                $smartyvalues["allowClientsToRemoveCards"] = $CONFIG["CCAllowCustomerDelete"];
                $smartyvalues["months"] = $gateways->getCCDateMonths();
                $smartyvalues["startyears"] = $gateways->getCCStartDateYears();
                $smartyvalues["years"] = $gateways->getCCExpiryDateYears();
                $smartyvalues["expiryyears"] = $smartyvalues["years"];
                $cartitems = $orderfrm->getNumItemsInCart();

                if (!$cartitems) {
                    $allowcheckout = false;
                }

                $smartyvalues["cartitems"] = $cartitems;
                $smartyvalues["checkout"] = $allowcheckout;

                if ($auth) {
                    $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails();
                    $clientsdetails["country"] = $clientsdetails["countryname"];
                    $custtype = "existing";
                    $smartyvalues["loggedin"] = true;
                } else {
                    $clientsdetails = $request->session()->get("cart.user");
                    $customfields = \App\Helpers\Customfield::getCustomFields("client", "", "", "", "on", isset($customfield) ? $customfield : "");
                    $request->session()->put("loginurlredirect", route('cart', ['a' => 'login']));
                    if (isset($custtype) && !$custtype) {
                        $custtype = "new";
                    }
                }

                $smartyvalues["custtype"] = isset($custtype) ? $custtype : "";
                $smartyvalues["clientsdetails"] = $clientsdetails;
                $smartyvalues["loginfailed"] = $loginfailed;

                $countries = new \App\Helpers\Country();
                if (!$countries) {
                    return redirect()->route('cart')->with('errormessage', 'Tidak ditemukan countries');
                }
                $smartyvalues["countries"] = $countries->getCountryNameArray();
                $smartyvalues["defaultcountry"] = Cfg::getValue("DefaultCountry");

                if (!isset($country)) {
                    $country = isset($clientsdetails["countrycode"]) ? $clientsdetails["countrycode"] : $clientsdetails["country"];
                }

                $smartyvalues["clientcountrydropdown"] = \App\Helpers\ClientHelper::getCountriesDropDown($country);
                $smartyvalues["country"] = $country;
                $smartyvalues["password"] = $password;
                $smartyvalues["password2"] = $password2;
                $smartyvalues["securityqans"] = $securityqans;
                $smartyvalues["securityqid"] = $securityqid;
                $smartyvalues["customfields"] = $customfields;
                $smartyvalues["securityquestions"] = $securityquestions;
                $smartyvalues["shownotesfield"] = $CONFIG["ShowNotesFieldonCheckout"] ?? "";
                $smartyvalues["orderNotes"] = $notes;
                $smartyvalues["notes"] = 0 < strlen($notes) ? $notes : Lang::get("client.ordernotesdescription");
                $smartyvalues["showMarketingEmailOptIn"] = !$auth && Cfg::getValue("AllowClientsEmailOptOut");
                $smartyvalues["marketingEmailOptInMessage"] = Lang::get("client.emailMarketing.optInMessage") != "client.emailMarketing.optInMessage" ? Lang::get("client.emailMarketing.optInMessage") : Cfg::getValue("EmailMarketingOptInMessage");
                $smartyvalues["marketingEmailOptIn"] = $request->has("marketingoptin") ? (bool) $request->input("marketingoptin") : (bool) (!Cfg::getValue("EmailMarketingRequireOptIn"));
                $smartyvalues["accepttos"] = $CONFIG["EnableTOSAccept"];
                $smartyvalues["tosurl"] = $CONFIG["TermsOfService"];
                $smartyvalues["domainsinorder"] = 0 < count($orderfrm->getCartDataByKey("domains", array()));

                $domaincontacts = [];
                $result = \App\Models\Contact::where(array("userid" => $auth ? $auth->id : 0, "address1" => ""))->orderBy("firstname", "ASC")->orderBy("lastname", "ASC")->get();
                if (!$result) {
                    return redirect()->route('cart')->with('errormessage', 'Tidak ditemukan contacts');
                }
                foreach ($result->toArray() as $data) {
                    $domaincontacts[] = array("id" => $data["id"], "name" => $data["firstname"] . " " . $data["lastname"]);
                }

                $smartyvalues["domaincontacts"] = $domaincontacts;
                $smartyvalues["contact"] = $contact;

                if ($contact == "addingnew") {
                    $addcontact = true;
                }

                $smartyvalues["addcontact"] = $addcontact;
                $smartyvalues["domaincontact"] = array("firstname" => $domaincontactfirstname, "lastname" => $domaincontactlastname, "companyname" => $domaincontactcompanyname, "email" => $domaincontactemail, "address1" => $domaincontactaddress1, "address2" => $domaincontactaddress2, "city" => $domaincontactcity, "state" => $domaincontactstate, "postcode" => $domaincontactpostcode, "country" => $domaincontactcountry, "phonenumber" => $domaincontactphonenumber);
                $smartyvalues["domaincontactcountrydropdown"] = \App\Helpers\ClientHelper::getCountriesDropDown($domaincontactcountry, "domaincontactcountry");
                
                $gatewaysoutput = $checkoutOutput = array();
                
                foreach ($availablegateways as $module => $vals) {
                    $gatewayModule = new \App\Module\Gateway();
                    $gatewayModule->load($module);
                    $params = $gatewayModule->loadSettings();
                    $params["amount"] = $carttotals["rawtotal"];
                    $params["currency"] = $currency["code"];

                    if (isset($params["convertto"]) && $params["convertto"]) {
                        $currencyCode = DB::table("tblcurrencies")->where("id", "=", (int) $params["convertto"])->value("code");
                        $convertToAmount = \App\Helpers\Format::convertCurrency($carttotals["rawtotal"], $currency["id"], $params["convertto"]);
                        $params["amount"] = \App\Helpers\Functions::format_as_currency($convertToAmount);
                        $params["currency"] = $currencyCode;
                        $params["currencyId"] = (int) $params["convertto"];
                        $params["basecurrencyamount"] = \App\Helpers\Functions::format_as_currency($carttotals["rawtotal"]);
                        $params["basecurrency"] = $currency["code"];
                        $params["baseCurrencyId"] = $currency["id"];
                    }

                    if (!isset($params["currency"]) || !$params["currency"]) {
                        $params["amount"] = \App\Helpers\Functions::format_as_currency($carttotals["rawtotal"]);
                        $params["currency"] = $currency["code"];
                        $params["currencyId"] = $currency["id"];
                    }

                    if ($userid) {
                        $payMethod = \App\User\Client::find($userid)->payMethods()->where("gateway_name", $module)->first();
                        $gatewayId = "";
                        if ($payMethod) {
                            $payment = $payMethod->payment;
                            if ($payment instanceof \App\Payment\Contracts\RemoteTokenDetailsInterface) {
                                $gatewayId = $payment->getRemoteToken();
                            }
                        }
                        $params["gatewayid"] = $gatewayId;
                    }

                    $params["isCheckout"] = (bool) $allowcheckout;
                    if ($gatewayModule->functionExists("orderformoutput")) {
                        $output = $gatewayModule->call("orderformoutput", $params);
                        if ($output) {
                            $gatewaysoutput[] = $output;
                        }
                    }
                }

                $smartyvalues["gatewaysoutput"] = $gatewaysoutput;
                $smartyvalues["checkoutOutput"] = $checkoutOutput;
                
                $profileFields = Cfg::getValue("ClientsProfileOptionalFields");
                if (is_string($profileFields)) {
                } else {
                    // dump('$profileFields :');
                    // dump($profileFields);
                }

                $smartyvalues["clientsProfileOptionalFields"] = explode(",", $profileFields ?? "" );
                $smartyvalues["showTaxIdField"] = \App\Helpers\Vat::isUsingNativeField();
                
                // dump('$smartyvalues : ');
                // dump($smartyvalues);

                $cartsummary = $request->input("cartsummary");
                if ($cartsummary) {
                    $ajax = "1";
                    $templatefile = "cartsummary";
                    $productinfo = $orderfrm->setPid($request->session()->get("cart.cartsummarypid"));
                    $orderFormTemplateName = $productinfo["orderfrmtpl"] == "" ? $orderFormTemplateName : $productinfo["orderfrmtpl"];
                }
            break;

            case 'checkDomain':
                (new \App\Helpers\Domain\Checker())->ajaxCheck();
                \App\Helpers\Terminus::getInstance()->doExit();
            break;

            case 'addToCart':
                $domain = $request->input("domain");
                $domain = new \App\Helpers\Domain\Domain($domain);
                $whoisCheck = (bool) (int) $request->input("whois");
                $response = new \Illuminate\Http\JsonResponse();

                if ($whoisCheck) {
                    $check = new \App\Helpers\Domain\Checker();
                    $check->cartDomainCheck($domain, [$domain->getDotTopLevel()]);
                    $searchResult = $check->getSearchResult();
                }
                //Edited
                if (!$whoisCheck || (isset($searchResult) && in_array($searchResult = (object) $check->getSearchResult(), [
                    \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_NOT_REGISTERED,
                    \App\Helpers\Domains\DomainLookup\SearchResult::STATUS_UNKNOWN
                ]))) {

                    \App\Helpers\Cart::cartPreventDuplicateDomain($domain->getDomain(false));
                    $tldPrice = \App\Helpers\Domain::getTLDPriceList($domain->getDotTopLevel());

                    $domainArray = [
                        "type" => "register",
                        "domain" => $domain->getDomain(false),
                        "regperiod" => key($tldPrice),
                        "isPremium" => false
                    ];

                    if (!$request->input("sideorder")) {
                        $passedVariables = $request->session()->get("cart.passedvariables");
                        $request->session()->forget("cart.passedvariables");

                        if (isset($passedVariables["bitem"])) {
                            $domainArray["bitem"] = $passedVariables["bitem"];
                        }
                        if (isset($passedVariables["bnum"])) {
                            $domainArray["bnum"] = $passedVariables["bnum"];
                        }
                    }

                    $premiumData = Session::get("PremiumDomains");
                    if ((bool) (int) Cfg::getValue("PremiumDomains") && array_key_exists($domain->getDomain(), $premiumData)) {
                        $premiumPrice = $premiumData[$domain->getDomain()];

                        if (array_key_exists("register", $premiumPrice["cost"])) {
                            $domainArray["isPremium"] = true;
                            $domainArray["domainpriceoverride"] = $premiumPrice["markupPrice"][1]["register"]->toNumeric();
                            $domainArray["registrarCostPrice"] = $premiumPrice["cost"]["register"];
                            $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                        }

                        if (array_key_exists("renew", $premiumPrice["cost"])) {
                            $domainArray["domainrenewoverride"] = $premiumPrice["markupPrice"][1]["renew"]->toNumeric();
                            $domainArray["registrarRenewalCostPrice"] = $premiumPrice["cost"]["renew"];
                            $domainArray["registrarCurrency"] = $premiumPrice["markupPrice"][1]["currency"];
                        } else {
                            $domainArray["isPremium"] = false;
                        }
                    }

                    $request->session()->push("cart.domains", $domainArray);

                    if (isset($domainArray["bnum"])) {
                        $request->session()->put("cart.lastconfigured", [
                            "type" => "domain",
                            "i" => count($request->session()->get("cart.domains")) - 1
                        ]);
                    }

                    $cart = new \App\Helpers\OrderForm();
                    $response->setData([
                        "result" => "added",
                        "period" => key($tldPrice),
                        "cartCount" => $cart->getNumItemsInCart()
                    ]);
                } else {
                    $response->setData([
                        "result" => isset($searchResult) ? $searchResult->getStatus() : "unavailable"
                    ]);
                }

                $response->send();
                // \App\Helpers\Terminus::getInstance()->doExit();
            break;

            case 'removepromo':
                $request->session()->put("cart.promo", "");
                if ($ajax) {
                    return "";
                }
                return redirect()->route("cart", ['a' => 'view'])->with('removepromomessage', 'Promo berhasil dihapus');
            break;

            case 'setstateandcountry':
                $request->session()->put("cart.user.state", $request->input('state'));
                $request->session()->put("cart.user.country", $request->input('country'));
                return redirect()->route("cart", ['a' => 'view']);
            break;
            
            case 'updateDomainPeriod':
                $domain = $request->input("domain");
                $period = $request->input("period");
                foreach ($request->session()->get("cart.domains") ?? [] as $key => $domainItem) {
                    if ($domainItem["domain"] == $domain) {
                        $request->session()->put("cart.domains.$key.regperiod", $period);
                        break;
                    }
                }
                $response = new \Illuminate\Http\JsonResponse();
                $response->setData(\App\Helpers\Orders::calcCartTotals(false, false, $currency));
                $response->send();
                \App\Helpers\Terminus::getInstance()->doExit();
            break;

            case 'complete':
                // Remote Authentication handling
                $remoteAuth = "";
                $remoteAuthData = [];
                foreach ($remoteAuthData as $key => $value) {
                    $smartyvalues[$key] = $value;
                }

                // Order details validation
                if (!is_array($request->session()->get("orderdetails"))) {
                    return redirect()->route('cart')->with('errormessage', 'Gagal memproses pesanan');
                }

                $orderid = $request->session()->get("orderdetails.OrderID");
                $invoiceid = $request->session()->get("orderdetails.InvoiceID");
                $paymentmethod = $request->session()->get("orderdetails.PaymentMethod");

                if (Session::get("InOrderButNeedProcessPaidInvoiceAction") && $invoiceid > 0) {
                    \App\Helpers\Invoice::processPaidInvoice($invoiceid);
                }

                $total = 0;
                if ($invoiceid) {
                    $data = \App\Models\Invoice::where([
                        "userid" => $auth ? $auth->id : 0,
                        "id" => $invoiceid
                    ])->first();

                    $invoiceid = $data->id;
                    $total = $data->total;
                    $paymentmethod = $data->paymentmethod;
                    $status = $data->status;

                    if (!$invoiceid) {
                        return "Invalid Invoice ID";
                    }

                    $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($auth ? $auth->id : 0);
                }

                $paymentmethod = \App\Helpers\Gateways::makeSafeName($paymentmethod);
                if (!$paymentmethod) {
                    return "Unexpected payment method value. Exiting.";
                }

                // Hosting services provisioning
                $result = \App\Models\Hosting::selectRaw("tblhosting.id, tblproducts.servertype")
                    ->where([
                        "tblhosting.orderid" => $orderid,
                        "tblhosting.domainstatus" => "Pending",
                        "tblproducts.autosetup" => "order"
                    ])
                    ->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
                    ->get();

                foreach ($result as $data) {
                    $id = $data->id;
                    $servertype = $data->servertype;

                    if (\App\Helpers\Invoice::getNewClientAutoProvisionStatus($auth ? $auth->id : 0)) {
                        \App\Helpers\LogActivity::Save("Running Module Create on Order");

                        if (!\Module::find($servertype)) {
                            return "Invalid Server Module Name";
                        }

                        $moduleresult = (new \App\Module\Server)->ServerCreateAccount($id);
                        if ($moduleresult == "success" && $servertype != "marketconnect") {
                            \App\Helpers\Functions::sendMessage("defaultnewacc", $id);
                        }
                    } else {
                        \App\Helpers\LogActivity::Save("Module Create on Order Suppressed for New Client");
                    }
                }

                // Addon provisioning
                $addons = \App\Models\Hostingaddon::whereHas("productAddon", function ($query) {
                    $query->where("autoactivate", "order");
                })
                    ->with("productAddon.welcomeEmailTemplate", "productAddon")
                    ->where([
                        "orderid" => $orderid,
                        "status" => "Pending",
                        ["addonid", ">", 0]
                    ])
                    ->get();

                foreach ($addons as $addon) {
                    if (!$addon->productAddon) {
                        continue;
                    }

                    $noModule = true;
                    $automationResult = false;

                    if ($addon->productAddon->module) {
                        $noModule = false;

                        if (\App\Helpers\Invoice::getNewClientAutoProvisionStatus($auth ? $auth->id : 0)) {
                            $automationResult = \App\Helpers\AddonAutomation::factory($addon)->runAction("CreateAccount");
                        } else {
                            \App\Helpers\LogActivity::Save("Module Create on Order Suppressed for New Client");
                        }
                    }

                    if ($noModule || $automationResult) {
                        if ($addon->productAddon->welcomeEmailTemplateId) {
                            \App\Helpers\Functions::sendMessage($addon->productAddon->welcomeEmailTemplate, $id);
                        }
                        if ($noModule) {
                            $addon->status = "Active";
                            $addon->save();
                            $params = [
                                "id" => $addon->id,
                                "userid" => $auth ? $auth->id : 0,
                                "serviceid" => $id,
                                "addonid" => $addon->addonId
                            ];
                            Hooks::run_hook("AddonActivation", $params);
                        }
                    }
                }

                // Payment gateway handling
                $gateway = new \App\Module\Gateway();
                $gateway->load($paymentmethod);

                if ($invoiceid && $status == "Unpaid" && $gateway->functionExists("orderformcheckout")) {
                    $payMethodId = (int) $request->session()->get("orderdetails.ccinfo");
                    $payMethod = $payMethodId ? \App\Payment\PayMethod\Model::findForClient($payMethodId, $auth ? $auth->id : 0) : null;

                    if ($payMethod) {
                        DB::table("tblinvoices")->where("id", $invoiceid)->update(["paymethodid" => $payMethod->id]);
                    }

                    $invoice = new \App\Helpers\InvoiceClass($invoiceid);
                    try {
                        $params = $invoice->initialiseGatewayAndParams();
                    } catch (\Exception $e) {
                        \App\Helpers\LogActivity::Save("Failed to initialise payment gateway module: " . $e->getMessage());
                        throw new \App\Exceptions\Fatal("Could not initialise payment gateway. Please contact support.");
                    }

                    $params = array_merge($params, $invoice->getGatewayInvoiceParams());
                    $params["gatewayid"] = $params["clientdetails"]["gatewayid"];
                    $captureresult = $gateway->call("orderformcheckout", $params);

                    if (is_array($captureresult)) {
                        \App\Helpers\Gateway::logTransaction($paymentmethod, $captureresult["rawdata"], ucfirst($captureresult["status"]));
                        if ($captureresult["status"] == "success") {
                            if (isset($captureresult["newRemoteCreditCard"]) && $captureresult["newRemoteCreditCard"]) {
                                $newCardPayMethod = \App\Helpers\Cc::saveNewRemoteCardDetails($captureresult["newRemoteCreditCard"], $gateway, $params["clientdetails"]["userid"]);
                                \App\Models\Invoice::where("id", $invoiceid)->first()->payMethod()->associate($newCardPayMethod)->save();
                            }

                            \App\Helpers\Invoice::addInvoicePayment($invoiceid, $captureresult["transid"], $captureresult["amount"] ?? "", $captureresult["fee"], $paymentmethod);
                            $request->session()->put("orderdetails.paymentcomplete", true);
                            $status = "Paid";
                        }
                    }
                }

                // Redirects and final template rendering
                if ($invoiceid && $status == "Unpaid") {
                    $gatewaytype = \App\Models\Paymentgateway::where(["gateway" => $paymentmethod, "setting" => "type"])->value("value");

                    if (!\Module::find($paymentmethod)) {
                        return "Invalid Payment Gateway Name";
                    }

                    if (($gatewaytype == "CC" || $gatewaytype == "OfflineCC") && in_array($CONFIG["AutoRedirectoInvoice"], ["on", "gateway"]) && !$gateway->functionExists("nolocalcc")) {
                        return redirect()->to("creditcard.php?invoiceid=$invoiceid");
                    }

                    if ($CONFIG["AutoRedirectoInvoice"] == "on") {
                        return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceid);
                    }

                    if ($CONFIG["AutoRedirectoInvoice"] == "gateway" && in_array($paymentmethod, ["mailin", "banktransfer"])) {
                        return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceid);
                    }

                    $invoice = new \App\Helpers\InvoiceClass($invoiceid);
                    $paymentbutton = $invoice->getPaymentLink();
                    $templatefile = "forwardpage";
                    $smartyvalues = [
                        "message" => $_LANG["forwardingtogateway"],
                        "code" => $paymentbutton,
                        "invoiceid" => $invoiceid
                    ];

                    return view($templatefile, $smartyvalues);
                }

                $amount = \App\Models\Order::where(["userid" => $auth ? $auth->id : 0, "id" => $orderid])->value("amount") ?? 0;
                $ispaid = ($invoiceid && \App\Models\Invoice::where("id", $invoiceid)->value("status") == "Paid");

                if ($ispaid) {
                    $request->session()->put("orderdetails.paymentcomplete", true);
                }

                $templatefile = "complete";
                $smartyvalues = array_merge($smartyvalues, [
                    "orderid" => $orderid,
                    "ordernumber" => $request->session()->get("orderdetails.OrderNumber"),
                    "invoiceid" => $invoiceid,
                    "ispaid" => $ispaid,
                    "amount" => $amount,
                    "paymentmethod" => $paymentmethod,
                    "clientdetails" => \App\Helpers\ClientHelper::getClientsDetails($auth ? $auth->id : 0)
                ]);

                $addons_html = Hooks::run_hook("ShoppingCartCheckoutCompletePage", $smartyvalues);
                $smartyvalues["addons_html"] = $addons_html;
            break;

            default:
                # code...
            break;
        }
        $this->setTheme($orderFormTemplateName);
        // return view("$templatefile", $smartyvalues);
        // outputClientArea($templatefile, $nowrapper, array("ClientAreaPageCart"));
        return \App\Helpers\ClientareaFunctions::outputClientArea($templatefile, true, array("ClientAreaPageCart"), $smartyvalues);
    }

    public function hook_domainFeeBackorder($vars) {
        $cartAdjustments = [];
        $price = 0;

        foreach ($vars['products'] as $domain) {
            if (!empty($domain['customfields']['1215'])) {
                $domainName = $domain['customfields']['1215'];
                $explode = explode('.', $domainName);

                if (isset($explode[2])) {
                    $jointTld = "." . $explode[1] . "." . $explode[2];

                    $result = DB::select("
                        SELECT tblpricing.msetupfee 
                        FROM tblpricing 
                        JOIN tbldomainpricing ON tblpricing.relid = tbldomainpricing.id 
                        WHERE tbldomainpricing.extension = ? 
                        AND tblpricing.type = 'domainrenew' 
                        AND tblpricing.currency = '1'
                    ", [$jointTld]);

                    $price = $result[0]->msetupfee ?? 0;

                    $count = strlen($explode[0]);
                    switch ($count) {
                        case 2:
                            $price += 17000000;
                            break;
                        default:
                            $price += 0;
                            break;
                    }
                } else {
                    $jointTld = "." . $explode[1];

                    $result = DB::select("
                        SELECT tblpricing.msetupfee 
                        FROM tblpricing 
                        JOIN tbldomainpricing ON tblpricing.relid = tbldomainpricing.id 
                        WHERE tbldomainpricing.extension = ? 
                        AND tblpricing.type = 'domainrenew' 
                        AND tblpricing.currency = '1'
                    ", [$jointTld]);

                    $price = $result[0]->msetupfee ?? 0;

                    if ($jointTld == '.id') {
                        $count = strlen($explode[0]);
                        switch ($count) {
                            case 2:
                                $price += 500000000;
                                break;
                            case 3:
                                $price += 15000000;
                                break;
                            case 4:
                                $price += 2250000;
                                break;
                            default:
                                $price += 0;
                                break;
                        }
                    }
                }
            }
        }

        if ($price != 0) {
            $cartAdjustments = [
                "description" => "Biaya Pendaftaran Domain Backorder [$domainName]",
                "amount" => $price,
                "taxed" => true,
            ];
        }

        return $cartAdjustments;
    }

    public function plugin_backorderDomain($params = [])
    {
        $price = 0;

        if (!empty($params['product'])) {
            $products = $params['product'];
            $domainName = $products;
            $explode = explode('.', $products);

            if (isset($explode[2])) {
                $jointld = "." . $explode[1] . "." . $explode[2];

                $result = DB::table('tblpricing')
                    ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                    ->where('tbldomainpricing.extension', $jointld)
                    ->where('tblpricing.type', 'domainrenew')
                    ->where('tblpricing.currency', 1)
                    ->select('tblpricing.msetupfee')
                    ->first();

                $price = $result->msetupfee ?? 0;
            } else {
                $jointld = "." . $explode[1];

                $result = DB::table('tblpricing')
                    ->join('tbldomainpricing', 'tblpricing.relid', '=', 'tbldomainpricing.id')
                    ->where('tbldomainpricing.extension', $jointld)
                    ->where('tblpricing.type', 'domainrenew')
                    ->where('tblpricing.currency', 1)
                    ->select('tblpricing.msetupfee')
                    ->first();

                $price = $result->msetupfee ?? 0;
            }
        }

        if (!empty($params['check']) && $params['check'] === "true") {
            if ($price > 0) {
                return [
                    'productsbackorder' => true,
                    'domainbackorder' => $domainName,
                ];
            } else {
                return [
                    'productsbackorder' => false,
                ];
            }
        } else {
            if ($price > 0) {
                $priceFormatted = "RP" . number_format($price, 2, ',', '.');
                return $priceFormatted;
            } else {
                return [
                    'productsbackorder' => false,
                ];
            }
        }
    }
    private function parseDomainName($domainName) {
        $parts = explode('.', $domainName, 2);
        if (count($parts) < 2) {
            throw new \Exception("Invalid domain name format");
        }
        return [
            'sld' => $parts[0],
            'tld' => '.' . $parts[1]
        ];
    }

    private function checkWhoisBackorder($domainName)
    {
        $whois = new \App\Helpers\WHOIS();
        $domainParts = $this->parseDomainName($domainName);
        $whoisData = $whois->lookup($domainParts);
        $eppStatus = $whois->getEppStatus($whoisData);
        $statusToCheck = ['redemptionPeriod', 'pendingDelete', 'expired'];
    
        if (array_intersect($eppStatus, $statusToCheck)) {
            $auctionController = new \Modules\Addons\Auction\Http\Controllers\_IrsfaAuctionController();
            if ($auctionController->getDomainBackorder($domainName)) {
                return 'Domain sudah ada dalam daftar backorder.';
            } else {
                return 'Domain tersedia untuk backorder.';
            }
        }
    
        return 'Status Domain ' . $domainName . ' tidak memungkinkan untuk melakukan backorder (silahkan coba domain berstatus redemption, pending deletion, atau expired).';
    }
}
