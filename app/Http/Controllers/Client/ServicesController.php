<?php

namespace App\Http\Controllers\Client;

use App\Helpers\ConfigOptions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Hosting;
use App\Models\Productgroup;
use App\Models\Currency;
use App\Models\Domainpricing;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Pricing;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Order;
use App\Models\Tax as ModelsTax;

use App\Helpers\CoreDomains;
use App\Helpers\Customfield;
use App\Helpers\Cycles;
use App\Helpers\Domain\Domain;
use App\Helpers\Format;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Orders;
use App\Helpers\Product as HelpersProduct;
use App\Helpers\ResponseAPI;
use App\Helpers\WHOIS;
use Illuminate\Support\Facades\Log;

use Darryldecode\Cart\CartCondition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use API;
use App\Helpers\Functions;
use App\Helpers\OrderForm;
use App\Helpers\Pricing as HelpersPricing;
use App\Helpers\ProductPricing;
use App\Models\Domain as ModelsDomain;
use Hexadog\ThemesManager\Facades\ThemesManager;

class ServicesController extends Controller
{
   public function __construct()
   {
      // $this->middleware(['auth:web']);
      $this->prefix = \Database::prefix();
   }
   public function Services_myservices()
   {
      $auth = Auth::user();
      $userid = $auth->id;
      $queryService = Hosting::selectRaw("tblhosting.id, tblhosting.domain, tblproducts.name, tblproducts.description, tblhosting.domainstatus, tblhosting.firstpaymentamount, tblhosting.amount, tblhosting.nextduedate")->where("userid", $userid)->orderBy("domain", "ASC")->join("tblproducts", "tblhosting.packageid", "tblproducts.id")->get();
      $packages = Product::where('is_featured', 1)->get();
      $addons = Addon::where('hidden', 0)->get();

      // $prodsStatsPending = $queryService->where('domainstatus', 'Pending')->count();
      return view('pages.services.myservices.index', ['user' => $auth, 'serviceProd' => $queryService, 'packages' => $packages, 'addons' => $addons]);
   }
   public function dt_myServices()
   {
      $auth = Auth::user();
      $userid = $auth->id;

      $queryService = Hosting::selectRaw("tblhosting.id, tblhosting.domain, tblhosting.dedicatedip, tblproducts.name, tblhosting.domainstatus, tblhosting.firstpaymentamount, tblhosting.amount, tblhosting.nextduedate")->where("userid", $userid)->orderBy("domain", "ASC")->join("tblproducts", "tblhosting.packageid", "tblproducts.id")->get();

      return datatables()->of($queryService)->editColumn('name', function ($row) {
         return $row->name;
      })->editColumn('dedicatedip', function ($row) {
         return $row->dedicatedip;
      })->editColumn('amount', function ($row) {
         $amount = Format::Price($row->amount);
         return $amount;
      })->editColumn('nextduedate', function ($row) {
         return $row->nextduedate;
      })->editColumn('status', function ($row) {
         switch ($row->status) {
            case 'Active':
               return "<div class=\"badge badge-success\">{$row->status}</div>";
               break;
            case 'Pending':
               return "<div class=\"badge badge-warning\">{$row->status}</div>";
               break;
            case 'Suspended':
               return "<div class=\"badge badge-danger\">{$row->status}</div>";
               break;
            case 'Terminated':
               return "<div class=\"badge badge-secondary\">{$row->status}</div>";
               break;
            case 'Cancelled':
               return "<div class=\"badge badge-info\">{$row->status}</div>";
               break;

            default:
               return "<div class=\"badge badge-dark\">Unknown</div>";
               break;
         }
      })->editColumn('actions', function ($row) {
         $actionRoute = route('pages.services.myservices.servicedetails', ['id' => $row->id]);
         $action = "";

         $action .= "<a href=\"{$actionRoute}\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs btn-success p-1 \" data-id=\"{$row->id}\" title=\"Details\">Details</a>";

         return $action;
      })
         ->rawColumns(['actions', 'status'])
         ->addIndexColumn()
         ->toJson();
   }
   public function Services_DetailServicesOLD($id)
   {
      $auth = Auth::user();
      $userid = $auth->id;
      $queryService = Hosting::selectRaw("tblhosting.id, tblhosting.domain, tblproducts.name, tblproducts.type, tblhosting.domainstatus, tblhosting.firstpaymentamount, tblhosting.billingcycle, tblhosting.amount, tblhosting.paymentmethod, tblhosting.nextduedate, tblhosting.regdate")->where("userid", $userid)->orderBy("domain", "ASC")->join("tblproducts", "tblhosting.packageid", "tblproducts.id")->get();
      // dd($queryService);
      return view('pages.services.myservices.servicedetails', ['services' => $queryService]);
   }
  public function Services_DetailServices(Request $request, $id)
  {
      // $id = $request->query()["id"];
      $auth = Auth::guard('web')->user();
      $legacyClient = new \App\Helpers\ClientClass($auth);
      $clientInformation = $legacyClient->getClientModel();
      $clientInformationAvailable = is_null($clientInformation) ? false : true;
      $emailVerificationPending = false;
      $emailVerificationRecentlyCleared = false;
      $verificationIdNotValid = false;
      $today = \App\Helpers\Carbon::today();
      $service = new \App\Helpers\Service($id, $legacyClient->getID());
      if ($service->isNotValid()) {
         return abort(404);
      }
      $serviceModel = \App\Models\Hosting::find($service->getID());
      $customfields = $service->getCustomFields();
      $domainIds = \App\Models\Domain::where("userid", $legacyClient->getID())->where("domain", $service->getData("domain"))->where("status", "Active")->pluck("id")->all();
      if (count($domainIds) < 1) {
         $domainIds = \App\Models\Domain::where("userid", $legacyClient->getID())->where("domain", $service->getData("domain"))->where("status", "!=", "Fraud")->pluck("id")->all();
      }
      if (count($domainIds) < 1) {
         $domainIds = \App\Models\Domain::where("userid", $legacyClient->getID())->where("domain", $service->getData("domain"))->where("status", "Fraud")->pluck("id")->all();
      }
      if (count($domainIds) < 1) {
         $domainId = "";
      } else {
         $domainId = array_shift($domainIds);
      }
      $params["id"] = $service->getData("id");
      $params["domainId"] = $domainId;
      $params["serviceid"] = $service->getData("id");
      $params["pid"] = $service->getData("packageid");
      $params["producttype"] = $service->getData("type");
      $params["type"] = $service->getData("type");
      $params["regdate"] = (new \App\Helpers\Functions)->fromMySQLDate($service->getData("regdate"), 0, 1, "-");
      $params["modulename"] = $service->getModule();
      $params["module"] = $service->getModule();
      $params["serverdata"] = $service->getServerInfo();
      $params["domain"] = $service->getData("domain");
      $params["domainValid"] = str_replace(".", "", $service->getData("domain")) != $service->getData("domain");
      $params["groupname"] = $service->getData("groupname");
      $params["product"] = $service->getData("productname");
      $params["paymentmethod"] = $service->getPaymentMethod();
      $params["firstpaymentamount"] = Format::formatCurrency($service->getData("firstpaymentamount"));
      $params["recurringamount"] = Format::formatCurrency($service->getData("amount"));
      $params["billingcycle"] = $service->getBillingCycleDisplay();
      $params["nextduedate"] = (new \App\Helpers\Functions)->fromMySQLDate($service->getData("nextduedate"), 0, 1, "-");
      $params["systemStatus"] = $service->getData("status");
      $params["status"] = $service->getStatusDisplay();
      $params["rawstatus"] = strtolower($service->getData("status"));
      $params["dedicatedip"] = $service->getData("dedicatedip");
      $params["assignedips"] = $service->getData("assignedips");
      $params["ns1"] = $service->getData("ns1");
      $params["ns2"] = $service->getData("ns2");
      $params["packagesupgrade"] = $service->getAllowProductUpgrades();
      $params["configoptionsupgrade"] = $service->getAllowConfigOptionsUpgrade();
      $params["customfields"] = $customfields;
      $params["productcustomfields"] = $customfields;
      $params["suspendreason"] = $service->getSuspensionReason();
      $params["subscriptionid"] = $service->getData("subscriptionid");
      $isDomain = str_replace(".", "", $service->getData("domain")) != $service->getData("domain");
      if ($service->getData("type") == "other") {
         $isDomain = false;
      }
      $sslStatus = NULL;
      if ($isDomain) {
         $sslStatus = \App\Models\Sslstatus::factory($legacyClient->getID(), $service->getData("domain"))->syncAndSave();
      }
      $params["sslStatus"] = $sslStatus;
      $diskstats = $service->getDiskUsageStats();
      foreach ($diskstats as $k => $v) {
         $params[$k] = $v;
      }
      $availableAddonIds = array();
      $availableAddonProducts = array();
      if ($service->getData("status") == "Active") {
         $predefinedAddonProducts = $service->getPredefinedAddonsOnce();
         $availableAddonIds = $service->hasProductGotAddons();
         foreach ($availableAddonIds as $addonId) {
            $availableAddonProducts[$addonId] = $predefinedAddonProducts[$addonId];
         }
      }
      $params["showcancelbutton"] = $service->getAllowCancellation();
      $params["configurableoptions"] = $service->getConfigurableOptions();
      $params["addons"] = $service->getAddons();
      $params["addonsavailable"] = $availableAddonIds;
      $params["availableAddonProducts"] = $availableAddonProducts;
      $params["downloads"] = $service->getAssociatedDownloads();
      $params["pendingcancellation"] = $service->hasCancellationRequest();
      $params["username"] = $service->getData("username");
      $params["password"] = $service->getData("password");
      $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaProductDetailsOutput", array("service" => $serviceModel));
      $params["hookOutput"] = $hookResponses;
      $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaProductDetailsPreModuleTemplate", $params);
      foreach ($hookResponses as $hookTemplateVariables) {
         foreach ($hookTemplateVariables as $k => $v) {
            $params[$k] = $v;
         }
      }
      $tplOverviewTabOutput = "";
      $moduleClientAreaOutput = "";
      $clientAreaCustomButtons = array();
      $params["modulecustombuttonresult"] = "";
      if ($request->has("addonId") && 0 < (int) $request->input("addonId") && $request->input("modop") == "custom") {
         $service = new \App\Helpers\Addon();
         $service->setAddonId($request->input("addonId"));
      }
      // dd($service->getModule());
      if ($service->getModule()) {
         $moduleInterface = new \App\Module\Server();
         if ($service instanceof \App\Helpers\Addon) {
            $moduleInterface->loadByAddonId($service->getID());
         } else {
            $moduleInterface->loadByServiceID($service->getID());
         }
         // HOTFIX: if ($request->get("dosinglesignon") && checkContactPermission("productsso", true)) {
         if ($request->input("dosinglesignon")) {
            if ($service->getData("status") == "Active") {
              try {
                  $redirectUrl = $moduleInterface->getSingleSignOnUrlForService();
                  return redirect()->away($redirectUrl);
              } catch (\App\Exceptions\Module\SingleSignOnError $e) {
                  $params["modulecustombuttonresult"] = \Lang::get("client.ssounabletologin");
              } catch (\Exception $e) {
                  LogActivity::Save("Single Sign-On Request Failed with a Fatal Error: " . $e->getMessage());
                  $params["modulecustombuttonresult"] = \Lang::get("client.ssofatalerror");
              }
            } else {
              $params["modulecustombuttonresult"] = \Lang::get("client.productMustBeActiveForModuleCmds");
            }
         } else {
            if ($request->input("dosinglesignon")) {
              $params["modulecustombuttonresult"] = \Lang::get("client.subaccountSsoDenied");
            }
         }
         // $moduleFolderPath = $moduleInterface->getBaseModuleDir() . DIRECTORY_SEPARATOR . $service->getModule();
         // $moduleFolderPath = substr($moduleFolderPath, strlen(ROOTDIR));
         $allowedModuleFunctions = array();
         $success = $service->moduleCall("ClientAreaAllowedFunctions");
         if ($success) {
            $clientAreaAllowedFunctions = $service->getModuleReturn("data");
            if (is_array($clientAreaAllowedFunctions)) {
              foreach ($clientAreaAllowedFunctions as $functionName) {
                  if (is_string($functionName)) {
                     $allowedModuleFunctions[] = $functionName;
                  }
              }
            }
         }
         $success = $service->moduleCall("ClientAreaCustomButtonArray");
         if ($success) {
            $clientAreaCustomButtons = $service->getModuleReturn("data");
            if (is_array($clientAreaCustomButtons)) {
              foreach ($clientAreaCustomButtons as $buttonLabel => $functionName) {
                  if (is_string($functionName)) {
                     $allowedModuleFunctions[] = $functionName;
                  }
              }
            }
         }
         $moduleOperation = $request->input("modop");
         $moduleAction = $request->input("a");
         if ($serverAction = $request->input("serveraction")) {
            $moduleOperation = $serverAction;
         }
         if ($moduleOperation == "custom" && in_array($moduleAction, $allowedModuleFunctions)) {
            if ($service->getData("status") == "Active") {
              // HOTFIX: checkContactPermission("manageproducts");

              $success = $service->moduleCall($moduleAction);
              if ($success) {
                  $data = $service->getModuleReturn("data");
                  // dd($data);
                  if (is_array($data)) {
                     if (isset($data["jsonResponse"])) {
                        // TODO: $response = new WHMCS\Http\JsonResponse();
                        // $response->setData($data["jsonResponse"]);
                        // $response->send();
                        // exit;
                     }
                     if (isset($data["overrideDisplayTitle"])) {
                        // $ca->setDisplayTitle($data["overrideDisplayTitle"]);
                     }
                     if (isset($data["overrideBreadcrumb"]) && is_array($data["overrideBreadcrumb"])) {
                        // $ca->resetBreadCrumb()->addToBreadCrumb("index.php", \Lang::get("globalsystemname"))->addToBreadCrumb("clientarea.php", \Lang::get("clientareatitle"));
                        // foreach ($data["overrideBreadcrumb"] as $breadcrumb) {
                        //     $ca->addToBreadCrumb($breadcrumb[0], $breadcrumb[1]);
                        // }
                     }
                     if (isset($data["appendToBreadcrumb"]) && is_array($data["appendToBreadcrumb"])) {
                        // foreach ($data["appendToBreadcrumb"] as $breadcrumb) {
                        //     $ca->addToBreadCrumb($breadcrumb[0], $breadcrumb[1]);
                        // }
                     }
                     if (isset($data["outputTemplateFile"])) {
                        // $ca->setTemplate($moduleInterface->findTemplate($data["outputTemplateFile"]));
                     } else {
                        if (isset($data["templatefile"])) {
                          // $ca->setTemplate($moduleInterface->findTemplate($data["templatefile"] . ".tpl"));
                        }
                     }
                     if (isset($data["breadcrumb"]) && is_array($data["breadcrumb"])) {
                        // foreach ($data["breadcrumb"] as $href => $label) {
                        //     $ca->addToBreadCrumb($href, $label);
                        // }
                     }
                     if (is_array($data["templateVariables"]) || is_array($data["vars"])) {
                        $templateVars = isset($data["templateVariables"]) ? $data["templateVariables"] : $data["vars"];
                        foreach ($templateVars as $key => $value) {
                          $params[$key] = $value;
                        }
                     }
                  } else {
                     $params["modulecustombuttonresult"] = "success";
                  }
              } else {
                  $params["modulecustombuttonresult"] = $service->getLastError();
              }
            } else {
              $params["modulecustombuttonresult"] = \Lang::get("client.productMustBeActiveForModuleCmds");
            }
         }
         $params["modulechangepwresult"] = "";
         if ($service->getData("status") == "Active" && $service->hasFunction("ChangePassword") && $service->getAllowChangePassword()) {
            $params["serverchangepassword"] = true;
            $params["modulechangepassword"] = true;
            $modulechangepasswordmessage = "";
            $modulechangepassword = $request->input("modulechangepassword");
            if ($request->input("serverchangepassword")) {
              $modulechangepassword = true;
            }
            if ($modulechangepassword) {
              // check_token();
              // checkContactPermission("manageproducts");
              $newpwfield = "newpw";
              $newpassword1 = $request->input("newpw");
              $newpassword2 = $request->input("confirmpw");
              foreach (array("newpassword1", "newserverpassword1") as $key) {
                  if (!$newpassword1 && $request->input($key)) {
                     $newpwfield = $key;
                     $newpassword1 = $request->input($key);
                  }
              }
              foreach (array("newpassword2", "newserverpassword2") as $key) {
                  if ($request->input($key)) {
                     $newpassword2 = $request->input($key);
                  }
              }
              $validate = new \App\Helpers\Validate();
              if ($validate->validate("match_value", "newpw", "client.clientareaerrorpasswordnotmatch", array($newpassword1, $newpassword2))) {
                  $validate->validate("pwstrength", $newpwfield, "client.pwstrengthfail");
              }
              if ($validate->hasErrors()) {
                  $modulechangepwresult = "error";
                  $modulechangepasswordmessage = $validate->getHTMLErrorOutput();
              } else {
                  \App\Models\Hosting::where(array("id" => $id))->update(array("password" => (new \App\Helpers\Pwd)->encrypt($newpassword1)));
                  $updatearr = array("password" => \App\Helpers\Sanitize::decode($newpassword1));
                  $success = $service->moduleCall("ChangePassword", $updatearr);
                  if ($success) {
                     LogActivity::Save("Module Change Password Successful - Service ID: " . $id);
                     Hooks::run_hook("AfterModuleChangePassword", array("serviceid" => $id, "oldpassword" => $service->getData("password"), "newpassword" => $updatearr["password"]));
                     $modulechangepwresult = "success";
                     $modulechangepasswordmessage = \Lang::get("client.serverchangepasswordsuccessful");
                     $params["password"] = $newpassword1;
                  } else {
                     $modulechangepwresult = "error";
                     $modulechangepasswordmessage = \Lang::get("client.serverchangepasswordfailed");
                     \App\Models\Hosting::where(array("id" => $id))->update(array("password" => (new \App\Helpers\Pwd)->encrypt($service->getData("password"))));
                  }
              }
              $params["modulechangepwresult"] = $modulechangepwresult;
              $params["modulechangepasswordmessage"] = $modulechangepasswordmessage;
            }
         }
         $customTemplateVariables = $params;
         $customTemplateVariables["moduleParams"] = $moduleInterface->buildParams();
         $moduleTemplateVariables = array();
         $tabOverviewModuleDirectOutputContent = "";
         $tabOverviewModuleOutputTemplate = "";
         $tabOverviewReplacementTemplate = "";
         if ($service->hasFunction("ClientArea")) {
            $inputParams = array("clientareatemplate" => $request->route()->getName(), "templatevars" => $customTemplateVariables, "whmcsVersion" => "");
            $success = $service->moduleCall("ClientArea", $inputParams);
            $data = $service->getModuleReturn("data");
            if (is_array($data)) {
              if (isset($data["overrideDisplayTitle"])) {
                  // $ca->setDisplayTitle($data["overrideDisplayTitle"]);
              }
              if (isset($data["overrideBreadcrumb"]) && is_array($data["overrideBreadcrumb"])) {
                  // $ca->resetBreadCrumb()->addToBreadCrumb("index.php", \Lang::get("globalsystemname"))->addToBreadCrumb("clientarea.php", \Lang::get("clientareatitle"));
                  // foreach ($data["overrideBreadcrumb"] as $breadcrumb) {
                  //     $ca->addToBreadCrumb($breadcrumb[0], $breadcrumb[1]);
                  // }
              }
              if (isset($data["appendToBreadcrumb"]) && is_array($data["appendToBreadcrumb"])) {
                  // foreach ($data["appendToBreadcrumb"] as $breadcrumb) {
                  //     $ca->addToBreadCrumb($breadcrumb[0], $breadcrumb[1]);
                  // }
              }
              if (isset($data["tabOverviewModuleOutputTemplate"])) {
                  $tabOverviewModuleOutputTemplate = $moduleInterface->findTemplate($data["tabOverviewModuleOutputTemplate"]);
              } else {
                  if (isset($data["templatefile"])) {
                     $tabOverviewModuleOutputTemplate = $moduleInterface->findTemplate($data["templatefile"]);
                  }
              }
              if (isset($data["tabOverviewReplacementTemplate"])) {
                  $tabOverviewReplacementTemplate = $moduleInterface->findTemplate($data["tabOverviewReplacementTemplate"]);
              }
              if (isset($data["templateVariables"]) && is_array($data["templateVariables"])) {
                  $moduleTemplateVariables = $data["templateVariables"];
              } else {
                  if (isset($data["vars"]) && is_array($data["vars"])) {
                     $moduleTemplateVariables = $data["vars"];
                  }
              }
            } else {
              $tabOverviewModuleDirectOutputContent = $data != \App\Module\Server::FUNCTIONDOESNTEXIST ? $data : "";
            }
         }
         // HOTFIX: if ($service->getData("status") == "Active" && checkContactPermission("manageproducts", true)) {
         if ($service->getData("status") == "Active") {
            if ($tabOverviewModuleOutputTemplate) {
              if (\View::exists($tabOverviewModuleOutputTemplate)) {
                  $moduleClientAreaOutput = view($tabOverviewModuleOutputTemplate, $moduleInterface->prepareParams(array_merge($customTemplateVariables, $customTemplateVariables["moduleParams"], $moduleTemplateVariables)))->render();
              } else {
                  $moduleClientAreaOutput = "Template File \"" . \App\Helpers\Sanitize::makeSafeForOutput($tabOverviewModuleOutputTemplate) . "\" Not Found";
              }
            } else {
              if ($tabOverviewModuleDirectOutputContent) {
                  $tabOverviewModuleOutputTemplate = "";
                  $moduleClientAreaOutput = $tabOverviewModuleDirectOutputContent;
              } else {
                  $clientareaBlade = $moduleInterface->findTemplate("clientarea");
                  if (\View::exists($clientareaBlade)) {
                     $moduleClientAreaOutput = view($clientareaBlade, $moduleInterface->prepareParams(array_merge($customTemplateVariables, $customTemplateVariables["moduleParams"], $moduleTemplateVariables)))->render();
                  }
              }
            }
         }
         if ($tabOverviewReplacementTemplate) {
            if (\View::exists($tabOverviewReplacementTemplate)) {
              $tplOverviewTabOutput = view($tabOverviewReplacementTemplate, $moduleInterface->prepareParams(array_merge($customTemplateVariables, $moduleTemplateVariables)))->render();
            } else {
              $tplOverviewTabOutput = "Template File \"" . \App\Helpers\Sanitize::makeSafeForOutput($tabOverviewReplacementTemplate) . "\" Not Found";
            }
         }
      }
      $params["tplOverviewTabOutput"] = $tplOverviewTabOutput;
      $params["modulecustombuttons"] = $clientAreaCustomButtons;
      $params["servercustombuttons"] = $clientAreaCustomButtons;
      $params["moduleclientarea"] = $moduleClientAreaOutput;
      $params["serverclientarea"] = $moduleClientAreaOutput;
      $invoice = DB::table("tblinvoices")->join("tblinvoiceitems", function (\Illuminate\Database\Query\JoinClause $join) use ($service) {
         $join->on("tblinvoices.id", "=", "tblinvoiceitems.invoiceid")->where("tblinvoiceitems.type", "=", "Hosting")->where("tblinvoiceitems.relid", "=", $service->getData("id"));
      })->where("tblinvoices.status", "Unpaid")->orderBy("tblinvoices.duedate", "asc")->first(array("tblinvoices.id", "tblinvoices.duedate"));
      $invoiceId = NULL;
      $overdue = false;
      $params["unpaidInvoiceMessage"] = "";
      if ($invoice) {
         $invoiceId = $invoice->id;
         $dueDate = \App\Helpers\Carbon::createFromFormat("Y-m-d", $invoice->duedate);
         $overdue = $today->gt($dueDate);
         $languageString = "client.unpaidInvoiceAlert";
         if ($overdue) {
            $languageString = "client.overdueInvoiceAlert";
         }
         $params["unpaidInvoiceMessage"] = \Lang::get($languageString);
      }
      $params["unpaidInvoice"] = $invoiceId;
      $params["unpaidInvoiceOverdue"] = $overdue;
      $hookResponses = \App\Helpers\Hooks::run_hook("ClientAreaProductDetails", array("service" => $serviceModel));
      foreach ($hookResponses as $hookTemplateVariables) {
         foreach ($hookTemplateVariables as $k => $v) {
            $params[$k] = $v;
         }
      }
      // dd($params);

      if ($params['modulename'] == 'Virtualizor') {
         // dd('gg');
         return view('pages.services.myservices.servicedetails', ['id' => $service->getData("id"), 'give' => 'index.html', 'act' => 'vpsmanage'], $params);
      } else {
         return view('pages.services.myservices.servicedetails', $params);
      }
  }
   public function Services_cancelservice()
   {
      return view('pages.services.cancelservice.index');
   }
   public function Services_CartServices()
   {
      $catProducts = Productgroup::where('hidden', 0)->get();
      return view('pages.services.cartservices.index', ['catProducts' => $catProducts]);
   }
   public function Services_ProductList(Request $request)
   {
      $request->session()->forget('clientcart');
      $currencyId = $request->currencyId;
      $groupId = $request->groupId;
      $pfx = $this->prefix;

      $currency = Currency::where('id', $currencyId)->first();
      $prodDetails = DB::table("{$pfx}products")
         ->join("{$pfx}pricing", "${pfx}products.id", "=", "{$pfx}pricing.relid")
         ->join("{$pfx}currencies", "{$pfx}pricing.currency", "=", "{$pfx}currencies.id")
         ->select("{$pfx}products.*", "{$pfx}pricing.msetupfee", "{$pfx}pricing.monthly", "{$pfx}currencies.code", "{$pfx}currencies.prefix")
         ->where("{$pfx}products.gid", "=", $groupId)
         ->where("{$pfx}products.hidden", "=", 0)
         ->where("{$pfx}pricing.currency", "=", $currencyId)
         ->where("{$pfx}pricing.type", "=", "product")
         ->get();

      return response()->json($prodDetails);
   }

   public function Services_ProductList_Configure(Request $request, $pid)
   {
      $auth = Auth::guard('web')->user();
      $currencyId = $auth->currency ?? 1;
      $pfx = $this->prefix;
      // pages.clients.domainregistrations.whois
      $tldList = Domainpricing::all();

      $prodConf = DB::table("{$pfx}products")
         ->join("{$pfx}pricing", "${pfx}products.id", "=", "{$pfx}pricing.relid")
         ->join("{$pfx}currencies", "{$pfx}pricing.currency", "=", "{$pfx}currencies.id")
         ->select("{$pfx}products.*", "{$pfx}pricing.msetupfee", "{$pfx}pricing.monthly", "{$pfx}pricing.qsetupfee", "{$pfx}pricing.quarterly", "{$pfx}pricing.ssetupfee", "{$pfx}pricing.semiannually", "{$pfx}pricing.asetupfee", "{$pfx}pricing.annually", "{$pfx}pricing.bsetupfee", "{$pfx}pricing.biennially", "{$pfx}pricing.tsetupfee", "{$pfx}pricing.triennially", "{$pfx}currencies.code", "{$pfx}currencies.prefix")
         ->where("{$pfx}products.id", "=", (int)$pid)->where("{$pfx}pricing.currency", "=", $currencyId)->where("{$pfx}pricing.type", "=", "product")
         ->first();

      if ($prodConf->showdomainoptions == 1 && $prodConf->stockcontrol == 1 && !$prodConf->qty <= 0) {
         return view('pages.services.cartservices.cart', ['prods' => $prodConf, 'tlds' => $tldList]);
      } else if ($prodConf->stockcontrol == 1 && $prodConf->qty <= 0) {
         return redirect()->route('outofstock', $prodConf->id);
      } else if ($prodConf->showdomainoptions == 1) {
         return view('pages.services.cartservices.cart', ['prods' => $prodConf, 'tlds' => $tldList]);
      } else {
         $userId = $auth->id ?? 0;
         // $userCart = \Cart::session($userId);
         $request->session()->push('clientcart', $request->all());
         return response()->json($request->all());
      }
   }
   public function Services_OutOfStock(Request $request, $id)
   {
      $prodsInfo = Orders::getProductInfo($id);
      return view('pages.services.cartservices.outstock', $prodsInfo);
   }

   public function Service_ProductList_DomainChecker(Request $request)
   {
      $domain = $request->domain;
      $domainId = $request->domainId;
      $domainReg = $request->domainReg;
      $action = $request->action;
      $templatevars = [];
      // dd($domainReg);

      $resultArr = [];
      if ($domain) {
         $tldPrice = \App\Models\Pricing::where('type', 'domainregister')->where('currency', 1)->where('relid', $domainId)->first();

         switch ($domainReg) {
            case '1':
               $periodPrice = $tldPrice->msetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->msetupfee);
               break;
            case '2':
               $periodPrice = $tldPrice->qsetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->qsetupfee);
               break;
            case '3':
               $periodPrice = $tldPrice->ssetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->ssetupfee);
               break;
            case '4':
               $periodPrice = $tldPrice->asetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->asetupfee);
               break;
            case '5':
               $periodPrice = $tldPrice->bsetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->bsetupfee);
               break;
            case '6':
               $periodPrice = $tldPrice->monthly;
               $tldFormatted = Format::formatCurrency($tldPrice->monthly);
               break;
            case '7':
               $periodPrice = $tldPrice->quarterly;
               $tldFormatted = Format::formatCurrency($tldPrice->quarterly);
               break;
            case '8':
               $periodPrice = $tldPrice->semiannually;
               $tldFormatted = Format::formatCurrency($tldPrice->semiannually);
               break;
            case '9':
               $periodPrice = $tldPrice->annually;
               $tldFormatted = Format::formatCurrency($tldPrice->annually);
               break;
            case '10':
               $periodPrice = $tldPrice->biennially;
               $tldFormatted = Format::formatCurrency($tldPrice->biennially);
               break;
            default:
               $periodPrice = $tldPrice->msetupfee;
               $tldFormatted = Format::formatCurrency($tldPrice->msetupfee);
               break;
         }

         $domains = new CoreDomains();
         $domainparts = $domains->splitAndCleanDomainInput($domain);
         $isValid = $domains->checkDomainisValid($domainparts);

         if ($isValid) {
            $whois = new WHOIS();
            if ($whois->canLookup($domainparts["tld"])) {
               $result = $whois->lookup($domainparts);
               if ($result["result"] == "available") {
                  $resultArr = [
                     "type" => "success",
                     "message" => sprintf(__("admin.whoisavailable"), $domain),
                     "availability" => "available",
                     "price" => $periodPrice,
                     "priceformatted" => $tldFormatted,
                     "extension" => $domainparts['tld'],
                  ];
               } else {
                  if ($result["result"] == "unavailable") {
                     $resultArr = [
                        "type" => "danger",
                        "message" => sprintf(__("admin.whoisunavailable"), $domain),
                        "availability" => "unavailable",
                        "price" => $periodPrice,
                        "priceformatted" => $tldFormatted
                     ];
                  } else {
                     $resultArr = [
                        "type" => "danger",
                        "message" => __("admin.whoiserror") . "<br>" . $result["errordetail"],
                     ];
                  }
               }
            } else {
               $resultArr = [
                  "type" => "danger",
                  "message" => sprintf(__("admin.whoisinvalidtld"), $domainparts["tld"])
               ];
            }
         } else {
            $resultArr = [
               "type" => "danger",
               "message" => __("admin.whoisinvaliddomain")
            ];
         }
      }
      $templatevars["domain"] = $domain;
      $templatevars["result"] = $resultArr;

      return response()->json($templatevars);
   }

   public function Service_ProductList_DomainStatus(Request $request)
   {
      $check = new Domain($request->domain);
      $tld = $request->tld;
      $sld = $request->sld;
      $domain = $request->domain;

      $resultArr = [];

      if ($check->isValidDomainName($sld, $tld) == true) {
         $resultArr = [
            'status' => 'available',
            'message' => __('client.orderFormtransferEligible')
         ];
      } else {
         $resultArr = [
            'status' => 'unavailable',
            'message' => __('client.orderFormtransferNotEligible')
         ];
      }

      return response()->json($resultArr);
   }

   public function Service_Order_Post(Request $request)
   {
      $auth = Auth::guard('web')->user();
      if ($request->type) {
         $request->session()->forget('clientcart');
         $getProdsById = Product::where('id', $request->pid)->first();
         $pricing = Pricing::where('type', $request->type)->where('relid', $request->pid)->where('currency', $auth->currency)->first();

         $request->session()->push('clientcart', $request->all());
         return response()->json($request->all());
      } else {
         $request->session()->forget('clientcart');
         $request->session()->push('clientcart', $request->all());
         return response()->json($request->all());
      }
   }

   public function Service_OrderSummary(Request $request, $id)
   {
      $pfx = $this->prefix;
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      $catProducts = Productgroup::where('hidden', 0)->get();
      // $userCart = \Cart::session();
      \Cart::clear();


      if ($request->session()->exists('clientcart')) { //
         $cartArray = $request->session()->get('clientcart');
         foreach ($cartArray as $key => $prods) {
            $selectedServices = Product::findOrFail($prods['prodId'] ?? $id);
            $pid =  $selectedServices->id ?? 0;
            $domainArr = $prods['domain'] ?? "";
            $domainPrice = $domainArr['price'] ?? "";
         }
      } else {
         $request->session()->forget('clientcart');
         $request->session()->push('clientcart', $id);
         $pid = $id ?? 0;
         $domainArr = "";
         $domainPrice = "";
      }

      \Cart::clearItemConditions($pid);

      $prodConf = DB::table("{$pfx}products")
         ->join("{$pfx}pricing", "${pfx}products.id", "=", "{$pfx}pricing.relid")
         ->join("{$pfx}currencies", "{$pfx}pricing.currency", "=", "{$pfx}currencies.id")
         ->select("{$pfx}products.*", "{$pfx}pricing.msetupfee", "{$pfx}pricing.monthly", "{$pfx}pricing.qsetupfee", "{$pfx}pricing.quarterly", "{$pfx}pricing.ssetupfee", "{$pfx}pricing.semiannually", "{$pfx}pricing.asetupfee", "{$pfx}pricing.annually", "{$pfx}pricing.bsetupfee", "{$pfx}pricing.biennially", "{$pfx}pricing.tsetupfee", "{$pfx}pricing.triennially", "{$pfx}currencies.code", "{$pfx}currencies.prefix")
         ->where("{$pfx}products.id", "=", (int)$pid)->where("{$pfx}pricing.currency", "=", $auth->currency ?? 1)->where("{$pfx}pricing.type", "=", "product")
         ->first();

      if ($prodConf->stockcontrol == 1 && $prodConf->qty <= 0) {
         return redirect()->route('outofstock', $prodConf->id);
      } else {
         $pricingInfo = Orders::getPricingInfo($prodConf->id);
         if ($pricingInfo['type'] == 'recurring') {
            if (!isset($billingcycle)) {
               $billingcycle = "Monthly";
            }
            $cycles = Cycles::cyclesDropDown($billingcycle, "", "", "billingcycle[]", "updatesummary();loadproductoptions(jQuery('#pid' + this.id.substring(12))[0]);return false;", "billingcycle0");
         } else {
            $cycles = $pricingInfo['type'] == "onetime" ? 'One Time' : ucfirst($pricingInfo['type']);
         };

         //Product Price
         $productBasePrice = floatval($prodConf->monthly + $prodConf->msetupfee);

         //Covert If Product has dollar Currency
         if (isset($auth->currency) && $auth->currency != 1) {
            $initProdsPrice = Format::ConvertCurrency($productBasePrice, 2, 1);
         } else {
            $initProdsPrice = $productBasePrice;
         }

         //Check Product Status
         $taxStatus = $prodConf->tax;
         $taxCon = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Free Tax',
            'type' => 'tax',
            'value' => '0%',
            'target' => 'total',
         ));

         $taxRule = ModelsTax::where('name', 'PPN 10%')->first();
         $taxRate = $taxRule->taxrate ?? 10.0;
         $taxName = $taxRule->name ?? 'PPN 10%';

         if ($taxStatus) {
            $taxCon = new \Darryldecode\Cart\CartCondition(array(
               'name' => $taxName,
               'type' => 'tax',
               'target' => 'total',
               'value' => $taxRate . '%',
            ));
         }

         $baseProduct = array(
            'id' => (int)$prodConf->id,
            'name' => $prodConf->name,
            'price' => $initProdsPrice,
            'quantity' => 1,
            'attributes' => [
               'description' => $prodConf->description,
               'priceformatted' => Format::formatCurrency($initProdsPrice),
               'pricewsetupfee' => $pricingInfo['cycles']['onetime'] ?? $pricingInfo['cycles']['monthly'],
            ],
         );

         if (\Cart::getTotalQuantity() < 2 && $domainArr) {
            \Cart::add(array(
               $baseProduct,
               array(
                  'id' =>  $domainArr['type'],
                  'name' => $domainArr['type'],
                  'price' => $domainPrice,
                  'quantity' => 1,
                  'attributes' => [
                     'description' => $domainArr['domainName'],
                     'extensionTld' => $domainArr['extensionTld'],
                     'registrationPeriod' => $domainArr['period'],
                     'priceformatted' => Format::formatCurrency($domainPrice),
                     'ns1' => '',
                     'ns2' => '',
                  ],
               )
            ));
         } else if (\Cart::getTotalQuantity() < 2 && !$domainArr) {
            \Cart::add(array(
               $baseProduct
            ));
         }

         //Apply condition
         \Cart::condition($taxCon);

         //Apply Tax
         $subTotal = \Cart::getSubTotal();
         $taxGet = \Cart::getCondition($taxName);

         if ($taxStatus) {
            $totalTax = $taxGet->getCalculatedValue($subTotal);
            $totalPay = \Cart::getTotal();
         } else {
            $totalPay = $subTotal;
         }

         $cartCollection = \Cart::getContent();
         // \Cart::clear();
         // dd($cartCollection);


         if ($prodConf->type == 'server') {
            $addons = Orders::getAddons($prodConf->id);

            $getProds = API::post('GetProducts', ['pid' => (string)$prodConf->id]);
            $prodsAPI = $getProds['products']['product'];

            foreach ($prodsAPI as $key => $prod) {
               $cf = $prod['customfields']['customfield'];
               $co = $prod['configoptions']['configoption'];
            }

            $checkCycle = Orders::getPricingInfo($prodConf->id);
            $availCycles = $checkCycle['cycles'];
            return view('pages.services.order.configserver', [
               'prods' => $prodConf,
               'cycle' => $cycles,
               'addons' => $addons,
               'cfieldArray' => $cf,
               'cOptionArray' => $co,
               'detailCycles' => $availCycles,
               'catProducts' => $catProducts,
            ]);
         } else {
            return view('pages.services.order.config', [
               'domainData' => $cartArray ?? [],
               'prods' => $prodConf,
               'cycle' => $cycles ?? [],
               'pricingInfo' => $pricingInfo,
               'taxStatus' => $taxStatus,
               'totalPrice' => $initTotalPrice ?? 0,
               'totalTaxPrice' => $totalTaxPrice ?? 0,
               'cartCollection' => $cartCollection,
               'subtotal' => $subTotal,
               'totalTax' => $totalTax ?? 0,
               'totalPay' => $totalPay,
               'catProducts' => $catProducts,
            ]);
         }
      }
   }

   public function Services_ViewCart(Request $request, $pid)
   {
      $pfx = $this->prefix;
      $prods = HelpersProduct::getProducts($pid);
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      // $userCart = \Cart::session($userId);
      // dd(\Cart::getContent());

      $gateways =  \App\Helpers\Gateway::GetGatewaysArray();

      $params['fullname'] = $auth ? "$auth->firstname" . " $auth->lastname" : "";
      $params['email'] = $auth->email ?? "";
      $params['companyname'] = $auth->companyname ?? "";
      $params['address1'] = $auth->address1 ?? "";
      $params['city'] = $auth->city ?? "";
      $params['postcode'] = $auth->postcode ?? "";

      $prodConf = DB::table("{$pfx}products")
         ->join("{$pfx}pricing", "${pfx}products.id", "=", "{$pfx}pricing.relid")
         ->join("{$pfx}currencies", "{$pfx}pricing.currency", "=", "{$pfx}currencies.id")
         ->select("{$pfx}products.*", "{$pfx}pricing.msetupfee", "{$pfx}pricing.monthly", "{$pfx}pricing.qsetupfee", "{$pfx}pricing.quarterly", "{$pfx}pricing.ssetupfee", "{$pfx}pricing.semiannually", "{$pfx}pricing.asetupfee", "{$pfx}pricing.annually", "{$pfx}pricing.bsetupfee", "{$pfx}pricing.biennially", "{$pfx}pricing.tsetupfee", "{$pfx}pricing.triennially", "{$pfx}currencies.code", "{$pfx}currencies.prefix")
         ->where("{$pfx}products.id", "=", (int)$pid)->where("{$pfx}pricing.currency", "=", $auth->currency ?? 1)->where("{$pfx}pricing.type", "=", "product")
         ->first();


      //Check Product Status
      $taxStatus = $prodConf->tax;
      // dd($taxStatus);

      //Product Price
      $productBasePrice = floatval($prodConf->msetupfee);
      //Covert If Product has dollar Currency
      if (isset($auth->currency) && $auth->currency != 1) {
         $initProdsPrice = Format::ConvertCurrency($productBasePrice, 2, 1);  //Service price monthly
      } else {
         $initProdsPrice = Format::formatCurrency($productBasePrice);
      }

      $condition = \Cart::getCondition('PPN 10%');
      $rawSubtotal = \Cart::getSubTotal();
      //Get Cart List
      $subTotal = \Cart::getSubTotal();
      // dd($subTotal);
      if ($taxStatus) {
         $totalTax = $condition->getCalculatedValue($rawSubtotal);
         $totalPay = \Cart::getTotal();
      } else {
         $totalPay = $subTotal;
      }

      $cartCollection = \Cart::getContent();
      // $cartCollectionArr = $cartCollection->toArray();
      $condition = $cartCollection->toArray();
      $conditionContent = $condition[$pid]['conditions'];
      $registerType = "";

      foreach ($condition as $key => $n) {
         if ($key == "Register Domain") {
            $selectType = explode(" ", $key);
            $registerType .= strtolower($selectType[0]);
         }
      }

      $totalPriceWithAdditional = \Cart::get($pid)->getPriceWithConditions();
      $baseItemPrice = \Cart::get($pid)->getPriceSum();

      // dd(HelpersProduct::getTaxStatus($prodConf->id));
      return view('pages.services.order.viewchart', [
         'gateways' => $gateways,
         'prods' => $prodConf,
         'totalPrice' => $initTotalPrice ?? 0,
         'totalTaxPrice' => $totalTaxPrice ?? 0,
         'cartCollection' => $cartCollection,
         'subtotal' => $subTotal,
         'registerType' => $registerType,
         'totalTax' => $totalTax ?? 0,
         'totalPay' => $totalPay,
         'conditions' => $conditionContent,
         'totalPriceWithAddon' =>  $totalPriceWithAdditional ?? $baseItemPrice
      ], $params);
   }

   public function Services_Checkout(Request $request, $id)
   {
      try {
         \DB::beginTransaction();

         $auth = Auth::guard('web')->user();
         $userId = $auth->id;
         $userCart = \Cart::session($userId);

         $cartCollection = $userCart->getContent();
         $cartArray = $cartCollection->toArray();
         // dd($cartArray);

         $userData = $request->all();

         $invoiceData = array_merge($userData, $cartArray);
         $invoiceData['Product Name'] = $invoiceData['0'];
         unset($invoiceData['0']);

         $forceSubmit = true;

         if ($invoiceData['regaction']) {
            $regdomain = [];
            $getValue = $invoiceData['regaction'];
            $getValueRegister = implode("", $getValue);
            $registerDomainID = ucfirst($getValueRegister);

            $getDataDomainRegister = $userCart->get($registerDomainID . " Domain");
            $domainData = $getDataDomainRegister->attributes['description'];
            $regdomain[] = $domainData;
         } else {
            $regdomain = [];
         }
         //  --> get current active session
         // dd($request->session());
         // dd($invoiceData);
         session()->forget("uid");

         $sessionOrigin = null;
         if (session()->get('uid')) {
            $sessionOrigin = session()->get('uid');
         }

         session()->put('uid', $userId);

         $userLang = Functions::getUsersLang($userId);
         $currency = Format::GetCurrency($userId);
         global $CONFIG;

         //prep for new session array
         $sessionArray["cart"] = array();
         $sessionArray["cart"]["paymentmethod"] = $invoiceData['paymentmethod'] ?? "BankTransfer";

         $pid = array($id) ?? [];

         //get active addons
         $getCondition = $userCart->get($id);
         $conditionItem = $getCondition->conditions;
         $addonsId = [];
         foreach ($conditionItem as $condition) {
            $getAddonsId = $condition->getAttributes();
            $addonsId[] = $getAddonsId['id'];
         }

         //create product array for cart session
         foreach ($pid as $key => $prodid) {
            if ($prodid) {
               $productArray = [
                  'pid' => $prodid,
                  'domain' => $regdomain[$key],
                  'billingcycle' => 'monthly',
                  'server' => '',
                  'configoptions' => [],
                  'customfields' => [],
                  'addons' => $addonsId ?? [],
               ];

               $sessionArray["cart"]["products"][] = $productArray;
            }
         }

         //get tld exension
         $validtlds = [];
         $result = Domainpricing::select('extension')->get();
         foreach ($result as $data) {
            $validtlds[] = $data->extension;
         }

         $orderContainValidTld = false;
         $domains = new CoreDomains();
         $regaction = $invoiceData['regaction'] ?? [];

         foreach ($regaction as $key => $regact) {
            if ($regact) {
               $domainparts = explode('.', $domains->clean($regdomain[$key]), 2);
               if (isset($domainparts[1]) && in_array("." . $domainparts[1], $validtlds)) {
                  $domainArray = [
                     'type' => $regact,
                     'domain' => trim($regdomain[$key]),
                     'regperiod' => '1',
                     'dnsmanagement' => 'off',
                     'emailforwarding' => 'off',
                     'idprotection' => 'off',
                     'eppcode' => null,
                     'fields' => $regdomain[$key]
                  ];
               }

               $sessionArray['cart']['domains'][] = $domainArray;
            }
         }

         $adminorderconf = false;
         $admingenerateinvoice = true;
         $sessionArray["cart"]["orderconfdisabled"] = $adminorderconf ? false : true;
         $sessionArray["cart"]["geninvoicedisabled"] = $admingenerateinvoice ? false : true;

         $adminsendinvoice = true;
         if (!$adminsendinvoice) {
            $CONFIG["NoInvoiceEmailOnOrder"] = true;
         }

         //put to cart
         session()->put("cart", $sessionArray["cart"]);

         $sessionArray["cart"] = session()->get("cart");
         $cartitems = count($sessionArray["cart"]["products"] ?? []) + count($sessionArray["cart"]["addons"] ?? []) + count($sessionArray["cart"]["domains"] ?? []) + count($sessionArray["cart"]["renewals"] ?? []);
         if (!$cartitems) {
            return redirect()->back()->with(['error' => 'Cannot continue this transaction']);
         }

         Orders::calcCartTotals(true, false, $currency);
         unset($sessionArray["uid"]);
         session()->forget("uid");

         $sessionArrayOrderDetails = $request->session()->get("orderdetails");
         // dd($sessionArrayOrderDetails);
         $orderstatus = "Pending";
         if ($orderstatus == "Active") {
            Order::where("id", $sessionArrayOrderDetails["OrderID"])->update(["status" => "Active"]);
            if (isset($sessionArrayOrderDetails["Products"]) && is_array($sessionArrayOrderDetails["Products"])) {
               foreach ($sessionArrayOrderDetails["Products"] as $productid) {
                  Hosting::where("id", $productid)->update(["domainstatus" => "Active"]);
               }
            }

            if (isset($sessionArrayOrderDetails["Domains"]) && is_array($sessionArrayOrderDetails["Domains"])) {
               foreach ($sessionArrayOrderDetails["Domains"] as $domainid) {
                  ModelsDomain::where("id", $domainid)->update(["status" => "Active"]);
               }
            }
         }
         // dd();
         $userLang = Functions::getUsersLang(0);
         if ($sessionOrigin) {
            session()->put("uid", $sessionOrigin);
         } else {
            session()->forget("uid");
         }
         // dd($request->session());
         \DB::commit();
         $userCart->clear();
         $userCart->clearCartConditions();
         return redirect()->route('pages.services.mydomains.viewinvoiceweb', $sessionArrayOrderDetails['InvoiceID']);
      } catch (\Throwable $th) {
         \DB::rollBack();
         throw $th;
      }
   }

   public function Checkout_API(Request $request, $pid)
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      // $userCart = \Cart::session($userId);

      $productArray = [];
      $getProductsFromSession = \Cart::get($pid);

      $getDomainRegisterFromSession = \Cart::get('Register Domain');
      $getAddonId = $getProductsFromSession->conditions;
      // dd($getProductsFromSession);

      $productArray['clientid'] = $auth->id ?? 0;
      $productArray['pid'][] = $getProductsFromSession->id;
      $productArray['paymentmethod'] = $request->paymentmethod;

      $addonIdData = [];
      if (!empty($getAddonId)) {
         foreach ($getAddonId as $key => $v) {
            $addonIdData[] = $v->getAttributes()['id'];
         }
         $productArray['addons'] = array(implode(",", $addonIdData) ?? '');
      }

      if ($getDomainRegisterFromSession) {
         $productArray['regperiod'] = array($getDomainRegisterFromSession->attributes->registrationPeriod) ?? [1];;
         $productArray['domain'][] = $getDomainRegisterFromSession->attributes->description ?? "";
         $productArray['domaintype'] = $request->regaction ?? '';
      };

      if ($getProductsFromSession->cycle) {
         $billingcycle = $getProductsFromSession->cycle;
         $productArray['billingcycle'] = array($billingcycle) ?? ['onetime'];
      } else {
         $productArray['billingcycle'] =  ['monthly'] ?? ['onetime'];
      };
      // $productArray['promocode'] = 'bucketlist';
      // dd($productArray);
      $orderAPI = API::post('AddOrder', $productArray);

      if ($orderAPI['result'] == 'error') {
         return  redirect()->back()->with(['error' => $orderAPI['message']]);
      } else {
         return redirect()->route('pages.services.mydomains.viewinvoiceweb', $orderAPI['invoiceid']);
      }
   }

   public function commandFunction(Request $request)
   {
      $action = $request->action;

      switch ($action) {
         case 'getconfigoption':
            return $this->getConfigOptions($request);
         case 'updatecycle':
            return $this->updatePriceByCycle($request);
         case 'refreshsummary':
            return $this->refreshSummary($request);
         case 'updatetotalprice':
            return $this->updateTotalPrice($request);
         case 'removeitem':
            return $this->removeItem($request);
         case 'addmultiaddons':
            return $this->multipleAddonSelect($request);
         case 'updateaddons':
            return $this->updatePriceByAddons($request);
         case 'removeaddons':
            return $this->removeAddonsCondition($request);
         case 'removeaddonsmulti':
            return $this->removeMultiAddonsCondition($request);
         case 'vpsConfig':
            return $this->Services_VPSConfig($request);
         default:
            break;
      }
      return abort(404, "Action not found!");
   }

   // public function Services_VPSconfig(Request $request)
   // {
   //     dd($request->all());
   // }

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
      $cycle = $request->get("cycle") ?? 'monthly';
      $cycles = new Cycles();
      $cycle = $cycles->getNormalisedBillingCycle($cycle);
      $configoptions = ConfigOptions::getCartConfigOptions($pid, "", $cycle);
      if (count($configoptions)) {
         $options .= "<div id=\"configoptions\">";
         $options .= "<h4 class=\"card-title mb-3\"><strong>" . __("admin.setupconfigoptions") . "</strong></h4>";
         $options .= "<hr>";
         foreach ($configoptions as $configoption) {
            $optionid = $configoption["id"];
            $optionhidden = $configoption["hidden"];
            $optionname = $optionhidden ? $configoption["optionname"] . " <i>(" . __("admin.hidden") . ")</i>" : $configoption["optionname"];
            $optiontype = $configoption["optiontype"];
            $selectedvalue = $configoption["selectedvalue"];
            $selectedqty = $configoption["selectedqty"];
            // dd($configoption);
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
                                    <input type=\"checkbox\" name=\"configoption[" . $orderid . "][" . $optionid . "]\" onclick=\"updatePayment($optionid)\" class=\"form-check-input\" data-optionid=\"$optionid\" id=\"configoption{$optionid}\" value=\"1\" " . ($selectedqty ? "checked" : "") . ">
                                    <label class=\"form-check-label\" for=\"configoption{$optionid}\">" . ($configoption["options"][0]["name"]) . "</label>
                                </div>";
            } else if ($optiontype == "4") {
               $inputcode = "<input type=\"text\" name=\"configoption[" . $orderid . "][" . $optionid . "]\" onchange=\"updatesummary()\"  value=\"" . $selectedqty . "\" class=\"form-control \"> x " . $configoption["options"][0]["name"];
            }

            $options .= '<div class="form-group row">
                                <div for="#" class="col-sm-2 col-form-label">' . $optionname . '</div>
                                <div class="col-sm-10">' . $inputcode . '</div>
                            </div>
                            ';
         }
      }

      $customfields = $request->customfields;
      $customfields = Customfield::getCustomFields("product", $pid, "", true, "", $customfields);
      if (count($customfields)) {
         $options .= "<h4 class=\"card-title mt-2 my-3\"><strong>" . __("admin.setupcustomfields") . "</strong></h4>";
         $options .= "<hr>";
         foreach ($customfields as $customfield) {
            $inputfield = str_replace("name=\"customfield", "name=\"customfield[" . $orderid . "]", $customfield["input"]);
            $options .= "<div class=\"form-group row\">
                                <div for=\"#\" class=\"col-sm-2 col-form-label\">{$customfield["name"]}</div>
                                <div class=\"col-sm-10\">
                                    $inputfield
                                </div>
                            </div>
                        </div>";
         }
      }
      $addonshtml = "";
      $addonsarray = Orders::getAddons($pid);
      $orderItemId = $request->get("orderid");

      if (count($addonsarray)) {
         $addonCb = "";
         foreach ($addonsarray as $addon) {
            $description = "";
            if (isset($addon["description"])) {
               $description .= " - " . $addon["description"];
            }
            $addonCb .= "<div class=\"form-check mt-2\">" . str_replace($addon["checkbox"], "<input type=\"checkbox\" class=\"form-check-input\" onchange=\"multipleAddonsSelect(this)\" name=\"addons[]\" id=\"a" . $addon["id"] . "\" value=\"" .  $addon["id"] . "\">", $addon["checkbox"])
               // . "<input type=\"hidden\" name=\"addons[" . $addon["id"] . "]\" id=\"a" . $addon["id"] . "Hidden\" value=\"false\" onchange=\"updateAddons(this.id)\">"
               . "<label class=\"form-check-label\" for=\"a" . $addon["id"] . "\" id=\"label" . $addon["id"] . "\">" . $addon["name"] . "</label>
                    </div>";
         }
         // <span id=\"span\">" . $addon["minPrice"]["price"] . "</span>
         $addonshtml = "<div class=\"form-group row\">
                            <div class=\"col-sm-2 align-self-center\">Addons</div>
                            <div class=\"col-sm-10\">
                                $addonCb
                            </div>
                        </div>";
      }
      return ResponseAPI::Success([
         'message' => "OK!",
         'data' => [
            "options" => $options,
            "addons" => $addonshtml
         ],
      ]);
   }


   private function updatePriceByCycle(Request $request)
   {
      $auth = Auth::guard('web')->user();
      $currency = $auth->currency;
      $relid = $request->get('prodsId');
      $billingcycle = $request->get('cycle');
      $result = \App\Models\Pricing::where('type', 'Product')->where('currency', (int) $currency)->where('relid', (int) $relid)->get();
      $configId = $request->get('configId');

      $keyOfCycle = substr($billingcycle, 0, 1);
      $valueOfCycle = strtolower($billingcycle);
      $getsetupfee = $valueOfCycle;

      foreach ($result as $key => $value) {
         $updatedFee = $value->$getsetupfee;
      }

      //Covert If Product has dollar Currency
      if ($auth->currency != 1) {
         $updatedFee = Format::ConvertCurrency($updatedFee, 2, 1);  //Service price monthly
      }

      $userCart = \Cart::session($auth->id);


      $getOptions = ConfigOptions::getCartConfigOptions($relid, "", $valueOfCycle);
      // dd($getOptions);
      foreach ($getOptions as $keyOption => $option) {
         // dd($option);
         $configOption[$option['id']]['name'] = $option['optionname'];
         foreach ($option['options'] as $addPrice) {
            $configOption[$option['id']]['price'] = $addPrice['recurring'];
            $configOption[$option['id']]['priceformatted'] = Format::formatCurrency($addPrice['recurring']);
         }
      }
      // dd($configOption);


      $userCart->update($relid, ([
         'price' => ($updatedFee),
         'priceformatted' => Format::formatCurrency($updatedFee),
         'cycle' => strtolower($billingcycle) ?? '-',
         'quantity' => [
            'relative' => false,
            'value' => 1
         ],
         'configoptions' => []
      ]));

      $updatedCart = $userCart->get($relid);
      return response()->json($updatedCart);
   }
   private function updateTotalPrice(Request $request)
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      $prodId = $request->get('prodId');
      // $userCart = \Cart::session($userId);

      //tax
      $prodsTaxStatus = HelpersProduct::getTaxStatus($prodId);
      $rawSubtotal = \Cart::getSubTotal();

      if ($prodsTaxStatus) {
         $condition = \Cart::getCondition('PPN 10%');
         $rawTaxValue = $condition->getCalculatedValue($rawSubtotal) ?? 0;
         $rawTotal = \Cart::getTotal();
      } else {
         $rawTaxValue = 0;
         $rawTotal = $rawSubtotal;
      }

      $taxValue = Format::formatCurrency($rawTaxValue);
      $subTotal = Format::formatCurrency($rawSubtotal);
      $total = Format::formatCurrency($rawTotal);

      $updatedPrice = [];

      $updatedPrice['subtotal'] = $subTotal;
      $updatedPrice['tax'] = $taxValue;
      $updatedPrice['total'] = $total;

      return response()->json($updatedPrice);
   }


   private function multipleAddonSelect(Request $request)
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      $arrAddons = $request->addonId;
      $notAddons = $request->notCheckedId;
      $pid = $request->pid;
      $cycle = strtolower($request->cycle);
      if ($cycle == 'onetime') {
         $cycle = 'monthly';
      }

      if ($notAddons && !empty($arrAddons)) {
         \Cart::clearItemConditions($pid);
      }

      if ($arrAddons) {
         $addonsItem = "";
         foreach ($arrAddons as $key => $addonId) {
            $addonsData = Addon::where('id', $addonId)->first();
            $pricing = Pricing::where("type", "=", "addon")->where("currency", "=", $auth->currency ?? 1)->where("relid", "=", $addonId)->first();

            if (isset($auth->currency) && $auth->currency !== 1) {
               $getPriceFormatted = Format::formatCurrency(Format::ConvertCurrency($pricing->$cycle, $auth->currency, 1));
               $getPrice = Format::ConvertCurrency($pricing->$cycle, $auth->currency, 1);
            } else {
               $getPriceFormatted = Format::formatCurrency($pricing->$cycle);
               $getPrice = $pricing->$cycle;
            }

            $addonsItem .= "<div class=\"col-lg-12 mb-3 text-right\" id=\"parent$addonsData->id\">
                    <h6 class=\"text-qw mb-0\" id=\"addons$addonsData->id\">$addonsData->name</h6>
                    <div class=\"text-qw\"><p class=\"mb-0 text-success font-weight-bold\" id=\"addons-price\">+ $getPriceFormatted</p></div>
                    </div>";
         }

         if ($arrAddons) {
            $addons = new CartCondition(array(
               'name' => $addonsData->name ?? "",
               'type' => 'misc',
               'description' => $addonsData->description ?? "",
               'value' => $getPrice ?? 0,
               'attributes' => [
                  'id' => $addonId
               ]
            ));

            \Cart::addItemCondition($pid, $addons);

            $currenctProdPrice = \Cart::get($pid)->getPriceSum();
            $pricewithAddon = \Cart::get($pid)->getPriceWithConditions();

            \Cart::update($pid, [
               'addons' => [
                  'id' => $addonsData->id,
                  'addonName' => $addonsData->name,
                  'price' => $getPrice,
               ],
               'price' => $currenctProdPrice,
               'priceformatted' => Format::formatCurrency($pricewithAddon),
               'quantity' => [
                  'relative' => false,
                  'value' => 1
               ]
            ]);
         }
      } else {
         \Cart::clearItemConditions($pid);
         $currenctProdPrice = \Cart::get($pid)->getPriceSum();
         $pricewithAddon = \Cart::get($pid)->getPriceWithConditions();
         // dd($currenctProdPrice);
         \Cart::update($pid, [
            'price' => $currenctProdPrice,
            'priceformatted' => Format::formatCurrency($pricewithAddon),
            'quantity' => [
               'relative' => false,
               'value' => 1
            ]
         ]);
      }

      return ResponseAPI::success([
         'message' => 'OK',
         'dataAddon' => $addonsItem ?? '',
         'prodId' => $pid,
      ]);
   }

   private function removeMultiAddonsCondition(Request $request)
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      $pid = $request->pid;
      $notAddons = $request->addonId;
      $cycle = strtolower($request->cycle);
      // $userCart = \Cart::session($userId);

      if ($notAddons) {
         foreach ($notAddons as $key => $addonId) {
            $addonsData = Addon::where('id', $addonId)->first();
            $pricing = Pricing::where("type", "=", "addon")->where("currency", "=", $auth->currency ?? 1)->where("relid", "=", $addonId)->first();


            // if ($auth->currency != 1) {
            //     $getPriceFormatted = Format::formatCurrency(Format::ConvertCurrency($pricing->$cycle, $auth->currency, 1));
            //     $getPrice = Format::ConvertCurrency($pricing->$cycle, $auth->currency, 1);
            // }

            $currentProdPrice = \Cart::get($pid)->getPriceSum();

            \Cart::removeItemCondition($pid, $addonsData->name);
         }
      }

      $currentProdPrice = \Cart::get($pid)->getPriceSum();
      $getItem = \Cart::get($pid);

      return response()->json(
         [
            'message' => 'Addon Removed!',
            'addonsData' => [
               'prodId' => $pid,
               'addonId' => $addonsData->id,
               'name' => $addonsData->name,
               'price' => $currentProdPrice
            ]
         ]
      );
   }


   private function refreshSummary()
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id ?? 0;
      // $userCart = \Cart::session($userId);

      $latestUserCart = \Cart::getContent();
      $cartArray = $latestUserCart->toArray();

      // dd($cartArray);
      return response()->json($cartArray);
   }


   private function removeItem(Request $request)
   {
      $auth = Auth::guard('web')->user();
      $userId = $auth->id;
      // $userCart = \Cart::session($userId);
      $cartId = $request->get('sessionCartId');

      // $userCart->clearItemConditions($cartId);
      $removedItem = \Cart::remove($cartId);

      return ResponseAPI::Success([
         'message' => "OK!",
         'data' => [
            'removed' => $removedItem
         ],
      ]);
   }

   public function Services_Upgrade(Request $request)
   {
      global $CONFIG;
      global $_LANG;
      define("CLIENTAREA", true);
      $auth = Auth::guard('web')->user();
      $route = "pages.services.upgrade";
      $id = $request->input("id");
      $type = $request->input("type");
      // initialiseClientArea($pagetitle, $displayTitle, $tagline, $pageicon, $breadcrumbnav);
      if (!$auth) {
         $goto = "clientarea";
         // include "login.php";
         return view("auth.login");
      }
      // checkContactPermission("orders");
      $currency = \App\Helpers\Format::getCurrency($auth->id);
      $templatefile = "upgrade";
      $step = $request->input("step");
      if ($step == "4") {
         foreach (session("upgradeorder") ?? [] as $k => $v) {
            ${$k} = $v;
         }
      }
      $result = \App\Models\Hosting::selectRaw("tblhosting.id,tblhosting.domain,tblhosting.nextduedate,tblhosting.billingcycle,tblhosting.packageid," . "tblproducts.name as product_name, tblproductgroups.id AS group_id,tblproductgroups.name as group_name")
         ->where(array("userid" => $auth->id, "tblhosting.id" => $id, "tblhosting.domainstatus" => "Active"))
         ->join("tblproducts", "tblproducts.id", "=", "tblhosting.packageid")
         ->join("tblproductgroups", "tblproductgroups.id", "=", "tblproducts.gid");
      $data = $result;
      $id = $data->value("id");
      if (!$id) {
         // redir("", "clientarea.php");
         return redirect()->route('home');
      }
      $domain = $data->value("domain");
      $productname = \App\Models\Product::getProductName($data->value("packageid"), $data->value("product_name"));
      $groupname = \App\Models\Productgroup::getGroupName($data->value("group_id"), $data->value("group_name"));
      $packageid = $data->value("packageid");
      $nextduedate = $data->value("nextduedate");
      $billingcycle = $data->value("billingcycle");
      $smartyvalues["id"] = $id;
      $smartyvalues["type"] = $type;
      $smartyvalues["groupname"] = $groupname;
      $smartyvalues["productname"] = $productname;
      $smartyvalues["domain"] = $domain;
      $smartyvalues["overdueinvoice"] = false;
      $smartyvalues["existingupgradeinvoice"] = false;
      $smartyvalues["upgradenotavailable"] = false;
      $smartyvalues["overdueinvoice"] = false;
      $smartyvalues["existingupgradeinvoice"] = false;
      $result = \App\Models\Invoiceitem::where(array("type" => "Hosting", "relid" => $id, "status" => "Unpaid", "tblinvoices.userid" => $auth->id))
         ->join("tblinvoices", "tblinvoices.id", "=", "tblinvoiceitems.invoiceid");
      $data = $result;
      if ($data->value("invoiceid")) {
         $smartyvalues["overdueinvoice"] = true;
         return view($templatefile, $smartyvalues);
      }
      $errormessage = "";
      if ($step == "2" && $type == "configoptions") {
         $configOpsReturn = \App\Helpers\ConfigOption::validateAndSanitizeQuantityConfigOptions($request->input("configoption"));
         if ($configOpsReturn["errorMessage"]) {
            $errormessage = $configOpsReturn["errorMessage"];
            $step = "";
         }
      }
      $checkUpgradeAlreadyInProgress = \App\Helpers\Upgrade::upgradeAlreadyInProgress($id);
      if (!$step) {
         if (\App\Helpers\Upgrade::upgradeAlreadyInProgress($id)) {
            $smartyvalues["existingupgradeinvoice"] = true;
            return view($templatefile, $smartyvalues);
         }
         $service = new \App\Helpers\Service($id, $auth->id);
         if ($type == "package" && !$service->getAllowProductUpgrades() || $type == "configoptions" && !$service->getAllowConfigOptionsUpgrade()) {
            $redirect = "cart.php";
            $vars = "";
            if (0 < count($service->hasProductGotAddons())) {
               $vars = "gid=addons";
               return redirect()->route("cart", ['gid' => 'addons']);
            }
            return redirect()->route("cart");
         }
         if ($type == "package") {
            $upgradepackages = \App\Models\Product::find($packageid)->getUpgradeProductIds();
            $result = \App\Models\Product::whereRaw("id IN (" . \App\Helpers\Database::db_build_in_array($upgradepackages) . ")")->orderBy("order", "ASC")->orderBy("name", "ASC")->get();
            foreach ($result->toArray() as $data) {
               $upgradepackageid = $data["id"];
               $stockControlEnabled = $data["stockcontrol"];
               $stockQty = $data["qty"];
               if (!$stockControlEnabled || 0 < $stockQty) {
                  $upgradepackagesarray[$upgradepackageid] = \App\Helpers\Orders::getProductInfo($upgradepackageid);
                  $upgradepackagesarray[$upgradepackageid]["pricing"] = \App\Helpers\Orders::getPricingInfo($upgradepackageid, "", true);
               }
            }
            $smartyvalues["upgradepackages"] = $upgradepackagesarray;
         } else {
            if ($type == "configoptions") {
               $result = \App\Models\Hosting::where(array("userid" => $auth->id, "id" => $id));
               $data = $result;
               $billingcycle = $data->value("billingcycle");
               $newproductbillingcycle = strtolower($billingcycle);
               $newproductbillingcycle = str_replace("-", "", $newproductbillingcycle);
               $newproductbillingcycle = str_replace("lly", "l", $newproductbillingcycle);
               if ($newproductbillingcycle == "onetime") {
                  $newproductbillingcycle = "monthly";
               }
               $configoptions = array();
               $configoptions = \App\Helpers\ConfigOptions::getCartConfigOptions($packageid, "", $billingcycle, $id);
               foreach ($configoptions as $configkey => $configoption) {
                  $selectedoption = $configoption["selectedoption"];
                  $selectedName = $configoption["selectedname"];
                  $selectedprice = $configoption["selectedrecurring"];
                  $options = $configoption["options"];
                  foreach ($options as $optionkey => $option) {
                     $optionname = $option["name"];
                     $optionNameOnly = $option["nameonly"];
                     $optionprice = $option["recurring"];
                     $optionprice = $optionprice - $selectedprice;
                     $configoptions[$configkey]["options"][$optionkey]["price"] = \App\Helpers\Format::formatCurrency($optionprice);
                     if ($optionname == $selectedoption || $optionNameOnly == $selectedName && 0 < $configoption["selectedsetup"]) {
                        $configoptions[$configkey]["options"][$optionkey]["selected"] = true;
                     }
                  }
               }
               $smartyvalues["configoptions"] = $configoptions;
               $smartyvalues["errormessage"] = $errormessage;
            }
         }
      }
      if ($step == "2") {
         $templatefile = "upgradesummary";
         $upgrades = array();
         $applytax = false;
         $serviceid = $request->input("id");
         $configoption = $request->input("configoption");
         $promocode = $request->input("promocode") ?? "";
         $smartyvalues["promoerror"] = "";
         $smartyvalues["promorecurring"] = "";
         $smartyvalues["promodesc"] = "";
         $smartyvalues["promocode"] = "";
         if ($promocode && empty($request->input("removepromo"))) {
            $promodata = \App\Helpers\Upgrade::validateUpgradePromo($promocode);
            if (!is_array($promodata)) {
               $promocode = "";
               $smartyvalues["promoerror"] = $promodata;
            } else {
               $smartyvalues["promocode"] = $promocode;
               if ($promodata["type"] == "configoptions" && count($promodata["configoptions"])) {
                  $promodata["desc"] .= " " . $_LANG["upgradeonselectedoptions"];
               }
               $smartyvalues["promodesc"] = $promodata["desc"];
               $smartyvalues["promorecurring"] = $promodata["recurringdesc"];
            }
         } else {
            $promodata = \App\Models\Promotion::where(array("lifetimepromo" => 1, "recurring" => 1, "id" => \App\Models\Hosting::where(array("id" => $serviceid))->value("promoid")))->first();
            if ($promodata) {
               $promodata = $promodata->toArray();
               $smartyvalues["promocode"] = $promocode = $promodata["code"];
               $smartyvalues["promodesc"] = $promodata["type"] == "Percentage" ? $promodata["value"] . "%" : \App\Helpers\Format::formatCurrency($promodata["value"]);
               $smartyvalues["promorecurring"] = $smartyvalues["promodesc"];
               $smartyvalues["promodesc"] .= " " . $_LANG["orderdiscount"];
            }
         }
         if ($request->input("removepromo")) {
            $promocode = "";
            unset($smartyvalues["promoerror"]);
            unset($smartyvalues["promocode"]);
            unset($smartyvalues["promodesc"]);
            unset($smartyvalues["promorecurring"]);
            $GLOBALS["discount"] = 0;
            $GLOBALS["qualifies"] = false;
         }
         if ($type == "package") {
            $newproductid = $request->input("pid");
            $newproductbillingcycle = $request->input("billingcycle");
            $upgrades = \App\Helpers\Upgrade::SumUpPackageUpgradeOrder($serviceid, $newproductid, $newproductbillingcycle, $promocode);
         } else {
            if ($type == "configoptions") {
               $configoptions = $request->input("configoption");
               $upgrades = \App\Helpers\Upgrade::SumUpConfigOptionsOrder($serviceid, $configoptions, $promocode);
            }
         }
         $subtotal = $GLOBALS["subtotal"];
         $qualifies = $GLOBALS["qualifies"];
         $discount = $GLOBALS["discount"];
         if ($promocode && !$qualifies) {
            $smartyvalues["promoerror"] = $_LANG["promoappliedbutnodiscount"];
         }
         $smartyvalues["configoptions"] = $configoption ?? [];
         $smartyvalues["upgrades"] = $upgrades;
         $gatewayslist = \App\Helpers\Gateway::showPaymentGatewaysList(array(), $auth->id);
         $paymentmethod = key($gatewayslist);
         $smartyvalues["gateways"] = $gatewayslist;
         $smartyvalues["allowgatewayselection"] = (bool) \App\Helpers\Cfg::getValue("AllowCustomerChangeInvoiceGateway");
         $smartyvalues["selectedgateway"] = $paymentmethod;
         if ($CONFIG["TaxEnabled"]) {
            $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($auth->id);
            $state = $clientsdetails["state"];
            $country = $clientsdetails["country"];
            $taxexempt = $clientsdetails["taxexempt"];
            if (!$taxexempt) {
               $smartyvalues["taxenabled"] = true;
               $taxdata = \App\Helpers\Invoice::getTaxRate(1, $state, $country);
               $taxrate = $taxdata["rate"];
               $taxname = $taxdata["name"];
               $taxdata2 = \App\Helpers\Invoice::getTaxRate(2, $state, $country);
               $taxrate2 = $taxdata2["rate"];
               $taxname2 = $taxdata2["name"];
            }
         }
         $smartyvalues["subtotal"] = \App\Helpers\Format::formatCurrency($subtotal);
         $smartyvalues["discount"] = \App\Helpers\Format::formatCurrency($discount);
         $subtotal = $subtotal - $GLOBALS["discount"];
         $tax = $tax2 = 0;
         if ($applytax) {
            if ($taxrate) {
               if ($CONFIG["TaxType"] == "Inclusive") {
                  $inctaxrate = 1 + $taxrate / 100;
                  $tempsubtotal = $subtotal;
                  $subtotal = $subtotal / $inctaxrate;
                  $tax = $tempsubtotal - $subtotal;
               } else {
                  $tax = $subtotal * $taxrate / 100;
               }
            }
            if ($taxrate2) {
               $tempsubtotal = $subtotal;
               if ($CONFIG["TaxL2Compound"]) {
                  $tempsubtotal += $tax;
               }
               if ($CONFIG["TaxType"] == "Inclusive") {
                  $inctaxrate = 1 + $taxrate / 100;
                  $subtotal = $tempsubtotal / $inctaxrate;
                  $tax2 = $tempsubtotal - $subtotal;
               } else {
                  $tax2 = $tempsubtotal * $taxrate2 / 100;
               }
            }
            $tax = round($tax, 2);
            $tax2 = round($tax2, 2);
         }
         $tax = \App\Helpers\Functions::format_as_currency($tax);
         $tax2 = \App\Helpers\Functions::format_as_currency($tax2);
         $smartyvalues["taxenabled"] = $CONFIG["TaxEnabled"];
         $smartyvalues["taxname"] = $taxname;
         $smartyvalues["taxrate"] = $taxrate;
         $smartyvalues["tax"] = \App\Helpers\Format::formatCurrency($tax);
         $smartyvalues["taxname2"] = $taxname2;
         $smartyvalues["taxrate2"] = $taxrate2;
         $smartyvalues["tax2"] = \App\Helpers\Format::formatCurrency($tax2);
         $total = $subtotal + $tax + $tax2;
         $total = \App\Helpers\Format::formatCurrency($total);
         $smartyvalues["total"] = $total;
      }
      if ($step == "3") {
         $orderdescription = "";
         $serviceid = $request->input("id");
         $paymentmethod = $request->input("paymentmethod") ?? "";
         $promocode = $request->input("promocode") ?? "";
         $notes = $request->input("notes") ?? "";
         if ($type == "package") {
            $newproductid = $request->input("pid");
            $newproductbillingcycle = $request->input("billingcycle");
            $upgrades = \App\Helpers\Upgrade::SumUpPackageUpgradeOrder($serviceid, $newproductid, $newproductbillingcycle, $promocode, $paymentmethod, true);
         } else {
            if ($type == "configoptions") {
               $configoptions = $request->input("configoption");
               $upgrades = \App\Helpers\Upgrade::SumUpConfigOptionsOrder($serviceid, $configoptions, $promocode, $paymentmethod, true);
            }
         }
         $ordernotes = "";
         if ($notes && $notes != $_LANG["ordernotesdescription"]) {
            $ordernotes = $notes;
         }
         // $_SESSION["upgradeorder"] = createUpgradeOrder($serviceid, $ordernotes, $promocode, $paymentmethod);
         session()->put("upgradeorder", \App\Helpers\Upgrade::createUpgradeOrder($serviceid, $ordernotes, $promocode, $paymentmethod));
         // redir("step=4");
         return redirect()->route($route, ['step' => '4']);
      }
      if ($step == "4") {
         $invoiceid = $request->input('invoiceid');
         $orderfrm = new \App\Helpers\OrderForm();
         $invoiceid = (int) $invoiceid;
         if ($invoiceid) {
            $result = \App\Models\Invoice::where(array("userid" => $auth->id, "id" => $invoiceid));
            $data = $result;
            $invoiceid = $data->value("id");
            $total = $data->value("total") ?? 0;
            $paymentmethod = $data->value("paymentmethod");
            if ($invoiceid && 0 < $total) {
               $paymentmethod = \App\Helpers\Gateways::makeSafeName($paymentmethod);
               if (!$paymentmethod) {
                  exit("Unexpected payment method value. Exiting.");
               }
               $result = \App\Models\Paymentgatewy::where(array("gateway" => $paymentmethod, "setting" => "type"));
               $data = $result;
               $gatewaytype = $data->value("value");
               if (($gatewaytype == "CC" || $gatewaytype == "OfflineCC") && ($CONFIG["AutoRedirectoInvoice"] == "on" || $CONFIG["AutoRedirectoInvoice"] == "gateway")) {
                  if (!\Module::find($paymentmethod)) {
                     exit("Invalid Payment Gateway Name");
                  }
                  $pg = new \App\Module\Gateway();
                  $pg->load($paymentmethod);

                  if (!$pg->functionExists("link")) {
                     // $whmcs->redirect("creditcard.php", "invoiceid=" . (int) $invoiceid);
                     return redirect()->url("creditcard");
                  }
               }
               if ($CONFIG["AutoRedirectoInvoice"] == "on") {
                  // $whmcs->redirect("viewinvoice.php", "id=" . (int) $invoiceid);
                  return redirect()->route("pages.services.mydomains.viewinvoiceweb", $invoiceid);
               }
               if ($CONFIG["AutoRedirectoInvoice"] == "gateway") {
                  $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($_SESSION["uid"]);
                  $params = \App\Helpers\Gateway::getGatewayVariables($paymentmethod, $invoiceid, $total);
                  $pg = new \App\Module\Gateway();
                  $pg->load($paymentmethod);
                  $paymentbutton = $pg->call("link", $params);
                  $templatefile = "forwardpage";
                  $smartyvalues["message"] = $_LANG["forwardingtogateway"];
                  $smartyvalues["code"] = $paymentbutton;
                  $smartyvalues["invoiceid"] = $invoiceid;
                  // outputClientArea($templatefile);
                  // exit;
                  return view($templatefile, $smartyvalues);
               }
            } else {
               $smartyvalues["ispaid"] = true;
            }
         }
         $templatefile = "complete";
         $smartyvalues["orderid"] = (int) $orderid;
         $smartyvalues["ordernumber"] = $order_number;
         $smartyvalues["invoiceid"] = $invoiceid;
         // $smartyvalues["carttpl"] = WHMCS\View\Template\OrderForm::factory($templatefile . ".tpl")->getName();
         $orderform = "true";
         ThemesManager::set(\App\Helpers\ThemeManager::orderformThemeVendor() . "/" . \App\Helpers\ThemeManager::orderformThemeDefault());
         return view($templatefile, $smartyvalues);
      }
      // outputClientArea($templatefile, false, array("ClientAreaPageUpgrade"));
      return view($templatefile, $smartyvalues);
   }

   public function Services_ViewAddons()
   {
      $data = array();
      $getAddons = Addon::all();
      $data['addons'] = $getAddons;
      return view('pages.services.viewaddons.index', $data);
   }

    /**
     * Module : Cpanel
     * Author: Fajar
     * Date: 06-12-2024
     */
    public function Services_DetailJalanPintasCpanel(Request $request, $id)
    {
        try {

            // Tambahkan logging di awal function
            Log::info('Accessing JalanPintasCpanel:', [
                'id' => $id,
                'request_method' => $request->method()
            ]);

            $auth = Auth::guard('web')->user();
            $legacyClient = new \App\Helpers\ClientClass($auth);
            $service = new \App\Helpers\Service($id, $legacyClient->getID());
            $customfields = $service->getCustomFields();

            // Log status dan informasi service
            Log::info('Service Info:', [
                'service_id' => $id,
                'status' => $service->getData('status'),
                'domain' => $service->getData('domain'),
                'username' => $service->getData('username')
            ]);
            
            $cpanel = $this->getCpanelUrl($id);
            // Validasi username dan password
            if (empty($cpanel['username']) || empty($cpanel['password'])) {
                return redirect()
                    ->route('pages.services.myservices.index')
                    ->with('error', 'Username atau password cPanel belum diatur. Silakan hubungi support untuk mengatur kredensial cPanel Anda. Dihalaman admin CLient Profile > Product/Services');
            }
            
            // Periksa apakah data cPanel tersedia
            if (!$cpanel) {
                throw new \Exception('Data cPanel tidak tersedia untuk layanan ini. Silakan hubungi support.');
            }
            
            $serviceModel2 = \App\Models\Hosting::findOrFail($id);

            $domainIds = \App\Models\Domain::where("userid", $legacyClient->getID())
                ->where("domain", $service->getData("domain"))
                ->where("status", "Fraud")
                ->pluck("id")
                ->all();

            $domainId = count($domainIds) < 1 ? "" : array_shift($domainIds);

            // Log informasi cPanel yang berhasil didapat
            Log::info('cPanel Info:', [
                'cpanel' => [
                    'baseUrl' => $cpanel['baseUrl'] ?? null,
                    'username' => $cpanel['username'] ?? null,
                    'server_exists' => !empty($cpanel)
                ]
            ]);

            $params = [
                "id" => $service->getData("id"),
                "domainId" => $domainId,
                "serviceid" => $service->getData("id"),
                "pid" => $service->getData("packageid"),
                "producttype" => $service->getData("type"),
                "type" => $service->getData("type"),
                "regdate" => (new \App\Helpers\Functions)->fromMySQLDate($service->getData("regdate"), 0, 1, "-"),
                "modulename" => $service->getModule(),
                "module" => $service->getModule(),
                "serverdata" => $service->getServerInfo(),
                "domain" => $service->getData("domain"),
                "domainValid" => str_replace(".", "", $service->getData("domain")) != $service->getData("domain"),
                "groupname" => $service->getData("groupname"),
                "product" => $service->getData("productname"),
                "paymentmethod" => $service->getPaymentMethod(),
                "firstpaymentamount" => Format::formatCurrency($service->getData("firstpaymentamount")),
                "recurringamount" => Format::formatCurrency($service->getData("amount")),
                "billingcycle" => $service->getBillingCycleDisplay(),
                "nextduedate" => (new \App\Helpers\Functions)->fromMySQLDate($service->getData("nextduedate"), 0, 1, "-"),
                "systemStatus" => $service->getData("status"),
                "status" => $service->getStatusDisplay(),
                "rawstatus" => strtolower($service->getData("status")),
                "dedicatedip" => $service->getData("dedicatedip"),
                "assignedips" => $service->getData("assignedips"),
                "ns1" => $service->getData("ns1"),
                "ns2" => $service->getData("ns2"),
                "packagesupgrade" => $service->getAllowProductUpgrades(),
                "configoptionsupgrade" => $service->getAllowConfigOptionsUpgrade(),
                "customfields" => $customfields,
                "productcustomfields" => $customfields,
                "suspendreason" => $service->getSuspensionReason(),
                "subscriptionid" => $service->getData("subscriptionid"),
                "showcancelbutton" => $service->getAllowCancellation(),
                "configurableoptions" => $service->getConfigurableOptions(),
                "addons" => $service->getAddons(),
                "addonsavailable" => [],
                "availableAddonProducts" => [],
                "downloads" => $service->getAssociatedDownloads(),
                "pendingcancellation" => $service->hasCancellationRequest(),
                "username" => $service->getData("username"),
                "password" => $service->getData("password"),
                "hookOutput" => [],
                "modulecustombuttonresult" => "",
                "modulechangepwresult" => "",
                "modulechangepasswordmessage" => "",
                "tplOverviewTabOutput" => "",
                "modulecustombuttons" => [],
                "servercustombuttons" => [],
                "moduleclientarea" => "",
                "serverclientarea" => "",
                "unpaidInvoiceMessage" => "",
                "unpaidInvoice" => null,
                "unpaidInvoiceOverdue" => false,
                'serverdata2' => [
                    'ipaddress' => $serviceModel2->dedicatedip,
                    'hostname' => $serviceModel2->domain,
                    'cpanel_url' => $cpanel['baseUrl'],
                    'cpanel_username' => $cpanel['username'],
                    'cpanel_password' => $cpanel['password'],
                    // 'cpanel_token' => $cpanel['cpanel_token'],
                    'accesshash' => $cpanel['accesshash'] ?? null,
                    'debug' => $cpanel['debug'],
                    'paths' => $cpanel['paths']
                ],
            ];

            $cpanelSession = $request->cookie('cpanel_session_' . $id);
            if ($cpanelSession) {
                $params['serverdata']['cpanel_url'] .= '/cpsess' . $cpanelSession;
            }

            return view('pages.services.myservices.jalanPintasCpanel', $params);
        } catch (\Exception $e) {
            // Log::error('Error in JalanPintasCpanel:', [
            //     'error' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            Log::error('Error in JalanPintasCpanel:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'service_id' => $id,
                'user_id' => $auth->id ?? null
            ]);


            return redirect()
                ->route('pages.services.myservices.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function Services_CreateEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            $cpanelService = app(CpanelService::class);

            $result = $cpanelService->createEmail([
                'email' => $validated['email'],
                'password' => $validated['password'],
                'domain' => $request->domain,
                'quota' => '1024'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil dibuat'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function Services_ChangePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'new_password' => 'required|min:8',
                'confirm_password' => 'required|same:new_password'
            ]);

            $cpanelService = app(CpanelService::class);

            $result = $cpanelService->changePassword($validated['new_password']);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    protected function getCpanelPaths()
    {
        return [
            'email' => '/frontend/jupiter/email_accounts/index.html#/create',
            'password' => '/frontend/jupiter/passwd/index.html',

            // Email Management
            'emailAccounts' => '/frontend/jupiter/email_accounts/index.html#/list',
            'forwarders' => '/frontend/jupiter/mail/fwds.html',
            'autoresponders' => '/frontend/jupiter/mail/autores.html',
            'default_address' => '/frontend/jupiter/mail/def.html',
            'mailing_lists' => '/frontend/jupiter/mail/lists.html',
            'track_delivery' => '/frontend/jupiter/mail/track_delivery.html',
            'authentication' => '/frontend/jupiter/mail/auth.html',
            'email_filters' => '/frontend/jupiter/mail/filters.html',
            'encryption' => '/frontend/jupiter/mail/encryption.html',

            // Files
            'filemanager' => '/frontend/jupiter/filemanager/index.html',
            'directory_privacy' => '/frontend/jupiter/sec_dir/index.html',
            'disk_usage' => '/frontend/jupiter/diskusage/index.html',
            'ftp_accounts' => '/frontend/jupiter/ftp/accounts.html',
            'ftp_connections' => '/frontend/jupiter/ftp/sessions.html',
            'backup' => '/frontend/jupiter/backup/index.html',
            'git' => '/frontend/jupiter/version_control/index.html',

            // Databases
            'mysql' => '/frontend/jupiter/sql/index.html',
            'mysql_databases' => '/frontend/jupiter/sql/databases.html',
            'mysql_users' => '/frontend/jupiter/sql/users.html',
            'phpmyadmin' => '/frontend/jupiter/sql/PhpMyAdmin.html',
            'remote_mysql' => '/frontend/jupiter/sql/remote.html',

            // Domains
            'domains' => '/frontend/jupiter/domains/index.html',
            'subdomain' => '/frontend/jupiter/domains/index.html',
            'addondomain' => '/frontend/jupiter/addon/index.html',
            'redirects' => '/frontend/jupiter/redirects/index.html',
            'zone_editor' => '/frontend/jupiter/zone_editor/index.html',

            // Metrics
            'awstats' => '/frontend/jupiter/stats/awstats_landing.html',
            'bandwidth' => '/frontend/jupiter/stats/bandwidth.html',
            'errors' => '/frontend/jupiter/stats/errlog.html',
            'visitors' => '/frontend/jupiter/stats/lastvisitors.html',
            'raw_access' => '/frontend/jupiter/stats/raw.html',
            'webalizer' => '/frontend/jupiter/stats/webalizer.html',

            // Security
            'ssl' => '/frontend/jupiter/ssl/index.html',
            'ssh_access' => '/frontend/jupiter/ssh/index.html',
            'ip_blocker' => '/frontend/jupiter/ip/index.html',
            'ssl_tls' => '/frontend/jupiter/ssl_tls_status/index.html',
            'hotlink_protection' => '/frontend/jupiter/htaccess/index.html',
            'leech_protection' => '/frontend/jupiter/leech/index.html',

            // Software
            'php_version' => '/frontend/jupiter/php/index.html',
            'php_pear' => '/frontend/jupiter/php_pear/index.html',
            'php_settings' => '/frontend/jupiter/php_settings/index.html',
            'multiphp_ini' => '/frontend/jupiter/php_ini/index.html',
            'site_software' => '/frontend/jupiter/softaculous/index.html',
            'optimize_website' => '/frontend/jupiter/optimize/index.html',

            // Advanced
            'cron' => '/frontend/jupiter/cron/index.html',
            'track_dns' => '/frontend/jupiter/track_dns/index.html',
            'indexes' => '/frontend/jupiter/indexmanager/index.html',
            'error_pages' => '/frontend/jupiter/err_pages/index.html',
            'apache_handlers' => '/frontend/jupiter/handlers/index.html',
            'mime_types' => '/frontend/jupiter/mime/index.html',

            // Preferences
            'password' => '/frontend/jupiter/passwd/index.html',
            'change_language' => '/frontend/jupiter/setlang/index.html',
            'change_style' => '/frontend/jupiter/styleswitcher/index.html',
            'contact_info' => '/frontend/jupiter/contact/index.html',
            'user_manager' => '/frontend/jupiter/user_manager/index.html',
            'optimize_website' => '/frontend/jupiter/optimize/index.html',
        ];
    } 
    
    // protected function getCpanelUrl($id)
    // {
    //     $serviceModel = \App\Models\Hosting::findOrFail($id);

    //     // Ambil informasi server dari tblservers
    //     $server = DB::table('tblservers')->where('id', $serviceModel->server_id)->first();

    //     if (!$server) {
    //         \Log::error('Server not found for service ID: ' . $id);
    //         return null;
    //     }

    //     $protocol = $server->secure ? 'https://' : 'http://';
    //     $hostname = $server->hostname;
    //     $userCpanelPort = '2083';

    //     $baseUrl = $protocol . $hostname . ':' . $userCpanelPort;

    //     \Log::info('Debug cPanel Info:', [
    //         'url' => $baseUrl,
    //         'username' => $serviceModel->username,
    //         'debug' => env('CPANEL_DEBUG', true)
    //     ]);

    //     return [
    //         'baseUrl' => $baseUrl,
    //         'username' => $serviceModel->username,
    //         'password' => $serviceModel->password,
    //         'domain' => $serviceModel->domain,
    //         'accesshash' => $server->accesshash ?? null,
    //         'debug' => 'off',
    //         'paths' => $this->getCpanelPaths() // Tambahkan ini
    //     ];
    // }

    protected function getCpanelUrl($id)
    {
        try {
            $serviceModel = \App\Models\Hosting::findOrFail($id);

            // Log informasi service
            \Log::info('Service Info:', [
                'id' => $id,
                'server_id' => $serviceModel->server_id,
                'domain' => $serviceModel->domain
            ]);

            // Cek jika server_id adalah 0 atau null
            if (!$serviceModel->server_id || $serviceModel->server_id == 0) {
                // Coba ambil server default yang aktif
                $server = DB::table('tblservers')
                    ->where('active', 'yes')
                    ->first();

                if ($server) {
                    // Update server_id di hosting
                    DB::table('tblhosting')
                        ->where('id', $id)
                        ->update(['server' => $server->id]);
                    
                    \Log::info('Updated service with default server:', [
                        'service_id' => $id,
                        'new_server_id' => $server->id
                    ]);
                } else {
                    \Log::error('No active server found for service ID: ' . $id);
                    return null;
                }
            } else {
                // Ambil informasi server dari tblservers
                $server = DB::table('tblservers')
                    ->where('id', $serviceModel->server_id)
                    ->first();
            }

            if (!$server) {
                \Log::error('Server not found for service ID: ' . $id);
                return null;
            }

            $protocol = $server->secure ? 'https://' : 'http://';
            $hostname = $server->hostname;
            $userCpanelPort = '2083';

            $baseUrl = $protocol . $hostname . ':' . $userCpanelPort;

            \Log::info('cPanel Info:', [
                'url' => $baseUrl,
                'username' => $serviceModel->username,
                'server_hostname' => $hostname
            ]);

            return [
                'baseUrl' => $baseUrl,
                'username' => $serviceModel->username,
                'password' => $serviceModel->password,
                'domain' => $serviceModel->domain,
                'accesshash' => $server->accesshash ?? null,
                'debug' => "off",
                'paths' => $this->getCpanelPaths()
            ];

        } catch (\Exception $e) {
            \Log::error('Error in getCpanelUrl:', [
                'error' => $e->getMessage(),
                'service_id' => $id
            ]);
            return null;
        }
    }

    protected function generateOrGetSecurityToken($serviceModel)
    {
        $existingToken = Cache::get('cpanel_token_' . $serviceModel->id);
        if ($existingToken) {
            return $existingToken;
        }

        try {
            $token = $this->generateCpanelToken($serviceModel);

            Cache::put('cpanel_token_' . $serviceModel->id, $token, now()->addHours(24));

            return $token;
        } catch (\Exception $e) {
            Log::error('Error generating cPanel token:', [
                'error' => $e->getMessage(),
                'service_id' => $serviceModel->id
            ]);

            return env('CPANEL_TOKEN');
        }
    }

    protected function generateCpanelToken($username)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getCpanelApiUrl() . '/create_user_session',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'user' => $username,
                'service' => 'cpaneld',
                'expires' => 86400, // 24 hours
                'api.version' => 2
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: whm ' . env('CPANEL_USERNAME') . ':' . env('CPANEL_TOKEN'),
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            \Log::error('cPanel Token Generation Error:', ['error' => $error]);
            throw new \Exception('Failed to generate cPanel token: ' . $error);
        }

        $result = json_decode($response, true);
        
        \Log::info('cPanel Token Response:', ['response' => $result]);

        if (isset($result['data']['security_token'])) {
            return $result['data']['security_token'];
        }

        throw new \Exception('Failed to generate cPanel token: Invalid response format');
    }

    protected function getCpanelApiUrl()
    {
        $hostname = env('CPANEL_HOSTNAME', 'garuda6.fastcloud.id');
        $whmPort = env('CPANEL_PORT', '2087');
        return 'https://' . $hostname . ':' . $whmPort . '/json-api/api2';
    }

    public function Services_LoginCpanel(Request $request, $id)
    {
        try {
            // Langsung ambil kredensial dari WHM API
            $whmCredentials = $this->getWhmUserCredentials($id);
            
            if (!$whmCredentials) {
                \Log::error('Failed to get WHM credentials');
                return redirect()->away('https://garuda6.fastcloud.id:2083/');
            }
            
            $loginUrl = 'https://garuda6.fastcloud.id:2083'
                . '/login/?user=' . urlencode($whmCredentials['username'])
                . '&pass=' . urlencode($whmCredentials['password'])
                . '&skiptoken=1'
                . '&goto_uri=/frontend/jupiter/index.html';

            \Log::info('Debug cPanel Login URL:', [
                'url' => $loginUrl,
                'username' => $whmCredentials['username'],
                'debug' => env('CPANEL_DEBUG', true)
            ]);

            return redirect()->away($loginUrl);
            
        } catch (\Exception $e) {
            Log::error('Error in cPanel login:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->away('https://garuda6.fastcloud.id:2083/');
        }
    }

    protected function getWhmUserCredentials($serviceId) 
    {
        try {
            $serviceData = DB::table('tblhosting')
                ->where('id', $serviceId)
                ->first();
                
            \Log::info('Service query:', [
                'service_id' => $serviceId,
                'service_data' => $serviceData
            ]);
            
            if (!$serviceData || empty($serviceData->domain)) {
                \Log::warning('Service/Domain not found in tblhosting', [
                    'service_id' => $serviceId
                ]);
                return null;
            }

            $domainName = $serviceData->domain;
            
            \Log::info('Found domain from service:', [
                'service_id' => $serviceId,
                'domain' => $domainName
            ]);
            
            // Jika username dan password sudah ada di tblhosting, gunakan itu
            if (!empty($serviceData->username) && !empty($serviceData->password)) {
                \Log::info('Using credentials from tblhosting');
                return [
                    'username' => $serviceData->username,
                    'password' => $serviceData->password
                ];
            }
            
            // Jika tidak ada di tblhosting, coba ambil dari WHM
            $whmUsername = env('CPANEL_USERNAME');
            $whmToken = env('CPANEL_TOKEN');
            $whmHost = env('CPANEL_HOSTNAME', 'garuda6.fastcloud.id');
            
            $apiUrl = "https://{$whmHost}:2087/json-api/listaccts?api.version=1";
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization: WHM ' . $whmUsername . ':' . $whmToken
            ]);
            curl_setopt($curl, CURLOPT_URL, $apiUrl);
            
            $result = curl_exec($curl);
            
            if ($result === false) {
                \Log::error('WHM API call failed:', [
                    'error' => curl_error($curl),
                    'url' => $apiUrl
                ]);
                return null;
            }
            
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            \Log::info('WHM API Response:', [
                'http_code' => $httpCode,
                'response' => $result
            ]);
            
            $data = json_decode($result, true);

            if (!isset($data['data']['data']['acct'])) {
                \Log::warning('Invalid WHM API response format', [
                    'response' => $data
                ]);
                return null;
            }

            foreach ($data['data']['data']['acct'] as $account) {
                if (strtolower($account['domain']) === strtolower($domainName)) {
                    return [
                        'username' => $account['user'],
                        'password' => $serviceData->password // gunakan password dari tblhosting
                    ];
                }
            }

            \Log::warning('Account not found in WHM', [
                'service_id' => $serviceId,
                'domain_name' => $domainName,
                'available_domains' => array_column($data['data']['data']['acct'] ?? [], 'domain')
            ]);
            
            return null;

        } catch (\Exception $e) {
            \Log::error('Failed to get WHM credentials:', [
                'error' => $e->getMessage(),
                'service_id' => $serviceId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    // Fungsi untuk mendapatkan password user
    protected function getWhmUserPassword($username)
    {
        try {
            $whmUsername = env('CPANEL_USERNAME');
            $whmToken = env('CPANEL_TOKEN');
            $whmHost = env('CPANEL_HOSTNAME', 'garuda6.fastcloud.id');
            
            $apiUrl = "https://{$whmHost}:2087/json-api/passwd?api.version=1&user={$username}&password=" . urlencode($this->generateRandomPassword());
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: WHM ' . $whmUsername . ':' . $whmToken
                ],
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            ]);
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            $data = json_decode($response, true);
            if (isset($data['passwd']) && $data['passwd'][0]['status'] === 1) {
                return $data['passwd'][0]['newpassword'];
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to set WHM user password:', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function getWhmSession()
    {
        try {
            $whmUsername = env('CPANEL_USERNAME');
            $whmPassword = env('CPANEL_PASSWORD');
            $whmHost = env('CPANEL_HOSTNAME', 'garuda6.fastcloud.id');
            
            $loginUrl = "https://{$whmHost}:2087/login";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $loginUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'user' => $whmUsername,
                    'pass' => $whmPassword
                ]),
                CURLOPT_HEADER => true,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            ]);
            
            $response = curl_exec($curl);
            curl_close($curl);
            
            // Extract session ID from cookies
            if (preg_match('/cpsess\d+/', $response, $matches)) {
                return $matches[0];
            }
            
            return null;
        } catch (\Exception $e) {
            \Log::error('Error getting WHM session:', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function matchAccount($account, $domainId)
    {
        try {
            // Implementasi logika untuk mencocokkan akun
            // Bisa berdasarkan domain, IP, atau identifier lainnya
            $domain = \App\Models\Domain::find($domainId);
            if ($domain) {
                return $account['domain'] === $domain->domain ||
                       $account['user'] === $domain->username;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error matching account:', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function generateRandomPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    public function Services_RedirectToAutoresponder(Request $request, $id)
    {
        try {
            $cpanel = $this->getCpanelUrl($id);

            $autoLoginUrl = $cpanel['baseUrl'] . '/login/?user=' . $cpanel['username']
                . '&pass=' . urlencode($cpanel['password'])
                . '&skiptoken=1'
                . '&goto_uri=' . urlencode('/frontend/jupiter/mail/autores.html');

            \Log::info('Debug AutoLogin URL:', [
                'url' => $autoLoginUrl,
                'username' => $cpanel['username'],
                'debug' => env('CPANEL_DEBUG', true)
            ]);

            return redirect()->away($autoLoginUrl);
        } catch (\Exception $e) {
            Log::error('Error redirecting to cPanel:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat mengakses cPanel');
        }
    }

    protected function getCpanelDirectUrl($baseUrl, $username, $password, $path)
    {
        return $baseUrl . '/login/?user=' . urlencode($username)
            . '&pass=' . urlencode($password)
            . '&skiptoken=1'
            . '&goto_uri=' . urlencode($path);
    }
}