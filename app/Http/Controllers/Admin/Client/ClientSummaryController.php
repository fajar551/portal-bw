<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AddonAutomation;
use App\Helpers\AdminFunctions;
use App\Helpers\Affiliate as HelpersAffiliate;
use App\Helpers\Cfg;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Country;
use App\Helpers\FileUploader;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\Invoice;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;
use App\Helpers\Sanitize;

// Models
use App\Models\Addon;
use App\Models\Affiliate;
use App\Models\AffiliateAccount;
use App\Models\AffiliateHistory;
use App\Models\AffiliateWithdrawal;
use App\Models\Client;
use App\Models\Clientgroup;
use App\Models\Clientsfile;
use App\Models\Contact;
use App\Models\Credit;
use App\Models\Domain;
use App\Models\Email;
use App\Models\Emailtemplate;
use App\Models\Hosting;
use App\Models\Hostingaddon;
use App\Models\Invoiceitem;
use App\Models\Paymentgateway;
use App\Models\Paymethod;
use App\Models\Quote;
use App\Module\Server;
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientSummaryController extends Controller
{
    
    use DatatableFilter;
    
    private $fileUploadPath = 'uploads/clientfiles';
    
    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.index")
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> ') .__('admin.clientsinvalidclientid'));
        }

        // Init vars
        // $userid = $request->userid;

         $userid = $request->get('userid', 0);

        // Get client notes
        $clientNotes = Note::where('userid', $userid)
            // ->where('sticky', 1)  // Only get sticky/important notes
            ->orderBy('id', 'desc')
            ->get();

        // Log the client notes
        \Log::info('Client Notes:', [
            'userid' => $userid,
            'notes_count' => $clientNotes->count(),
            'notes' => $clientNotes->toArray()
        ]);

        $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($userid);
        $client = $clientsdetails["model"];
        $clientHelper = new HelpersClient();
        $countries = new Country();
        $dateFormat = $clientHelper->getAdminDateFormat();

        // Add clientNotes to templatevars
        $templatevars["clientNotes"] = $clientNotes;
        
        // Get clientsdetails vars
        $clientstats = (new ClientHelper())->getClientsStats($userid, $client);

        $clientsdetails["status"] = __('admin.status'.strtolower($clientsdetails["status"]));
        $clientsdetails["autocc"] = $clientsdetails["disableautocc"] ? __("admin.no") : __("admin.yes");
        $clientsdetails["taxstatus"] = $clientsdetails["taxexempt"] ? __("admin.yes") : __("admin.no");
        $clientsdetails["overduenotices"] = $clientsdetails["overideduenotices"] ? __("admin.no") : __("admin.yes");
        $clientsdetails["latefees"] = $clientsdetails["latefeeoveride"] ? __("admin.no") : __("admin.yes");
        $clientsdetails["splitinvoices"] = $clientsdetails["separateinvoices"] ? __("admin.yes") : __("admin.no");    
        $clientsdetails["phonenumber"] = $clientsdetails["telephoneNumber"];

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["clientsdetails"]["countrylong"] = $countries->getName($clientsdetails["country"]);

        // Get email vars
        $verifyEmailAddressEnabled = Cfg::get("EnableEmailVerification");
        $isEmailAddressVerified = (bool) $client->email_verified;
        $emailVerificationPending = $verifyEmailAddressEnabled && !$isEmailAddressVerified ? true : false;

        $templatevars["emailVerificationEnabled"] = $verifyEmailAddressEnabled;
        $templatevars["emailVerificationPending"] = $emailVerificationPending;
        $templatevars["emailVerified"] = $isEmailAddressVerified;
        $templatevars["showTaxIdField"] = \App\Helpers\Vat::isUsingNativeField();

        // Get contact vars
        $dataContacts = Contact::where("userid", $userid)->get()->toArray();
        $contacts = [];
        foreach ($dataContacts as $data) {
            $contacts[] = [
                "id" => $data["id"], 
                "firstname" => $data["firstname"], 
                "lastname" => $data["lastname"], 
                "fullname" => "{$data["firstname"]} {$data["lastname"]}", 
                "email" => $data["email"],
            ];
        }

        $templatevars["contacts"] = $contacts;

        // Get client group vars
        $groupname = $groupcolour = "";
        if ($clientsdetails["groupid"]) {
            $data = Clientgroup::find($clientsdetails["groupid"])->toArray();
            $groupname = $data["groupname"];
            $groupcolour = $data["groupcolour"];
        }

        if (!$groupname) $groupname = __("admin.none");

        $templatevars["clientgroup"] = [
            "name" => $groupname,
            "colour" => $groupcolour
        ];

        // Get date vars
        $datecreated = $client->datecreated;        
        $templatevars["signupdate"] = $clientHelper->fromMySQLDate($datecreated);
        
        if ($datecreated == "0000-00-00") {
            $clientfor = "Unknown";
        } else {
            $todaysdate = date("Ymd");
            $datecreated = strtotime($datecreated);
            $todaysdate = strtotime($todaysdate);
            $days = round(($datecreated - $todaysdate) / 86400);
            $clientfor = ceil($days / 30 * -1);

            if ($clientfor <= 0) $clientfor = 0;

            $clientfor .= " " .__("admin.billableitemsmonths");
        }

        $templatevars["clientfor"] = $clientfor;

        // Get lastlogin vars
        if ($clientsdetails["lastlogin"]) {
            $templatevars["lastlogin"] = $clientsdetails["lastlogin"];
        } else {
            $templatevars["lastlogin"] = __("admin.none");
        }
        $templatevars["stats"] = $clientstats;

        // Get lastfivemail vars
        // TODO: $templatevars 
        // $templatevars["paymethodsSummary"] = (new WHMCS\Admin\Client\PayMethod\ViewHelper($aInt))->clientProfileSummaryHtml($client);
        $dataEmails = Email::where("userid", $userid)->orderBy("id", "DESC")->skip(0)->take(5)->get()->toArray();
        $lastfivemail = [];
        foreach ($dataEmails as $data) {
            $date = $clientHelper->fromMySQLDate($data["date"], "time");
            $safeDate = Sanitize::makeSafeForOutput($date);
            $subject = $data["subject"] ? Sanitize::makeSafeForOutput($data["subject"]) : __("admin.emailsnosubject");
            
            $lastfivemail[] = [
                "id" => (int) $data["id"], 
                "date" => $safeDate, 
                "subject" => $subject,
            ];
        }

        $templatevars["lastfivemail"] = $lastfivemail;

        // Get affiliates vars
        $dataAffilitate = Affiliate::where("clientid", $userid)->first();
        $templatevars["affiliateid"] = $dataAffilitate ? $dataAffilitate->toArray()["id"] : null;
        /*
        if ($affid) {
            $route = route('admin.pages.clients.manageaffiliates.edit', ['id' => $affid]);

            $templatevars["afflink"] = "<a href=\"{$route}\" title=\"Edit\">" .__("admin.clientsummaryviewaffiliate") ."</a> <br/><br/>";
        } else {
            $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid, "activateaffiliate" => true]);

            $templatevars["afflink"] = "<a href=\"{$route}\">" .__("admin.clientsummaryactivateaffiliate") ."</a> <br/><br/>";
        }
        */

        // Get tamplate message vars
        $mailTemplates = Emailtemplate::where("type", "general")->where("disabled", 0)->where("language", "")->where("name", "!=", "Password Reset Validation")->orderBy("name", "ASC")->get();
        /*
        $templatevars["messages"] = "<select name=\"messageID\" class=\"form-control select-inline\"><option value=\"0\">" . __("admin.newmessage") ."</option>";
        foreach ($mailTemplates as $template) {
            $templatevars["messages"] .= "<option value=\"" . $template->id . "\"";
            if ($template->custom) {
                $templatevars["messages"] .= " style=\"background-color:#efefef\"";
            }
            $templatevars["messages"] .= ">" .$template->name ."</option>";
        }

        $templatevars["messages"] .= "</select>";
        */
        // TODO: Alternatively loop on the blade view
        $templatevars["messageslist"] = $mailTemplates;

        // Get statuses filters vars
        $itemStatuses = [
            "Pending" => __("admin.statuspending"), 
            "Pending Registration" => __("admin.statuspendingregistration"), 
            "Pending Transfer" => __("admin.statuspendingtransfer"), 
            "Active" => __("admin.statusactive"), 
            "Completed" => __("admin.statuscompleted"), 
            "Suspended" => __("admin.statussuspended"), 
            "Terminated" => __("admin.statusterminated"), 
            "Cancelled" => __("admin.statuscancelled"), 
            "Grace" => __("admin.statusgrace"), 
            "Redemption" => __("admin.statusredemption"), 
            "Expired" => __("admin.statusexpired"), 
            "Transferred Away" => __("admin.statustransferredaway"), 
            "Fraud" => __("admin.statusfraud")
        ];
        
        $templatevars["itemstatuses"] = $itemStatuses;

        // Get client file vars
        $dataClientFiles = Clientsfile::where("userid", $userid)->orderBy("title", "ASC")->get()->toArray();
        $files = [];
        foreach ($dataClientFiles as $data) {
            $files[] = [
                "id" => $data["id"], 
                "title" => $data["title"], 
                "adminonly" => $data["adminonly"], 
                "date" => $clientHelper->fromMySQLDate($data["dateadded"]),
            ];
        }

        $templatevars["files"] = $files;

        // Get client file vars
        $paymentmethoddropdown = (new Gateway($request))->paymentMethodsSelection("- " . __("admin.clientsummarysetPaymentMethod") . " -");
        $templatevars["paymentmethoddropdown"] = $paymentmethoddropdown;
        // TODO: Alternatively loop on the blade view
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();

        // Get addons_html vars
        $addons_html = Hooks::run_hook("AdminAreaClientSummaryPage", ["userid" => $userid]);
        $templatevars["addons_html"] = $addons_html;

        // Get tmplinks vars
        $tmplinks = Hooks::run_hook("AdminAreaClientSummaryActionLinks", ["userid" => $userid]);
        $actionlinks = [];
        foreach ($tmplinks as $tmplinks2) {
            foreach ($tmplinks2 as $tmplinks3) {
                $actionlinks[] = $tmplinks3;
            }
        }

        $templatevars["customactionlinks"] = $actionlinks;
        // $templatevars["tokenvar"] = generate_token("link");
        // $templatevars["csrfToken"] = generate_token("plain");

        // Get Client Merge Name
        $templatevars["firstclientmerge_name"] = $clientsdetails["fullname"] ." ({$clientsdetails["userid"]})";
        $templatevars["userid"] = $userid;

        // Add notes count
        $templatevars["notesCount"] = \App\Models\Note::where('userid', $userid)->count();
        
        // AddFundsMinimum 
        global $CONFIG;
        $templatevars["addFundsMinimum"] = $CONFIG["AddFundsMinimum"];

        // dd($templatevars);
        return view('pages.clients.viewclients.clientsummary.index', $templatevars);
    }

    public function massAction(Request $request)
    {
        // dd($request->all());
        $userid = $request->userid;

        $validator = Validator::make(request()->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'selproducts' => "required|string",
            'seladdons' => "required|string",
            'seldomains' => "required|string",
            'set_status' => "nullable|string",
            'paymentmethod' => "nullable|string",
            'inv' => "nullable|numeric",
            'del' => "nullable|numeric",
            'masscreate' => "nullable|numeric",
            'masssuspend' => "nullable|numeric",
            'massunsuspend' => "nullable|numeric",
            'massterminate' => "nullable|numeric",
            'masschangepackage' => "nullable|numeric",
            'masschangepw' => "nullable|numeric",
            'overideautosuspend' => "nullable",
            'overidesuspenduntil' => "nullable|date_format:d/m/Y",
            'firstpaymentamount' => "nullable|numeric",
            'recurringamount' => "nullable|numeric",
            'nextduedate' => "nullable|date_format:d/m/Y",
            'proratabill' => "nullable",
            'billingcycle' => "nullable|string",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientsummary.index", ["userid" => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }
        
        $client = Client::findOrFail($userid);
        $userId = $client->id;

        $selproducts = json_decode($request->selproducts, JSON_NUMERIC_CHECK);
        $seladdons = json_decode($request->seladdons, JSON_NUMERIC_CHECK);
        $seldomains = json_decode($request->seldomains, JSON_NUMERIC_CHECK);

        $serviceDetails = ["userid" => $userid, "serviceid" => ""];
        $addonDetails = ["userid" => $userid, "id" => "", "serviceid" => "", "addonid" => ""];
        $domainDetails = ["userid" => $userid, "domainid" => ""];
        $queryStr = [];

        $inv = $request->inv;
        if ($inv) {
            if (AdminFunctions::checkPermission("Generate Due Invoices")) {
                // Note: $invoicecount is from global in @createInvoices 
                $invoicecount = 0;
                $specificitems = ["products" => $selproducts, "addons" => $seladdons, "domains" => $seldomains];
                $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userid, "", "", $specificitems);

                // createInvoices($userid, "", "", $specificitems);
                // $queryStr .= "&invoicecount=" . $invoicecount;
                $queryStr["invoicecount"] = $invoicecount;
            } else {
                // TODO: Perform action without permission
            }
        }

        $del = $request->del;
        if ($del) {
            if ($selproducts) {
                if (AdminFunctions::checkPermission("Delete Clients Products/Services")) {
                    foreach ($selproducts as $pid) {
                        $hosting = $client->services()->find((int) $pid);
                        
                        if ($hosting) {
                            $serviceDetails["serviceid"] = $hosting->id;
                            Hooks::run_hook("ServiceDelete", $serviceDetails);
                            $hosting->delete();
    
                            $activityMessage = "Deleted Product/Service - User ID: " . $userId ." - Service ID: " . $hosting->id;
                            LogActivity::Save($activityMessage, $userId);
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($seladdons) {
                if (AdminFunctions::checkPermission("Delete Clients Products/Services")) {
                    foreach ($seladdons as $aid) {
                        $addon = Hostingaddon::find((int) $aid);
                        $addonUserId = $addon->service()->first()->clientId;

                        if ($addonUserId == $userId) {
                            Hooks::run_hook("AddonDeleted", array("id" => $addon->id));
                            $addon->delete();
                            LogActivity::Save("Deleted Addon ID: " . $addon->id . " - User ID: " . $userId, $userId);
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($seldomains) {
                if (AdminFunctions::checkPermission("Delete Clients Domains")) {
                    foreach ($seldomains as $did) {
                        $domain = $client->domains()->find((int) $did);

                        if ($domain) {
                            $domainDetails["domainid"] = $domain->id;
                            Hooks::run_hook("DomainDelete", $domainDetails);
                            $domain->delete();
                            LogActivity::Save("Deleted Domain ID: " . $did . " - User ID: " . $userId, $userId);
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            $queryStr["deletesuccess"] = true;
        }

        $massupdate = $request->massupdate; 
        $masscreate = $request->masscreate; 
        $masssuspend = $request->masssuspend; 
        $massunsuspend = $request->massunsuspend; 
        $massterminate = $request->massterminate; 
        $masschangepackage = $request->masschangepackage; 
        $masschangepw = $request->masschangepw;
        
        if ($massupdate || $masscreate || $masssuspend || $massunsuspend || $massterminate || $masschangepackage || $masschangepw) {
            $paymentmethod = $request->paymentmethod; 
            if ($paymentmethod) {
                $paymentmethod = Paymentgateway::where("gateway", $paymentmethod)->first()->gateway;
            }

            $proratabill = $request->proratabill; 
            if ($proratabill) {
                if (AdminFunctions::checkPermission("Edit Clients Products/Services")) {
                    $nextduedate = $request->nextduedate;
                    $targetnextduedate = (new \App\Helpers\SystemHelper())->toMySQLDate($nextduedate);
                    foreach ($selproducts as $serviceid) {
                        // $data = get_query_vals("tblhosting", "packageid,domain,nextduedate,billingcycle,amount,paymentmethod", array("id" => $serviceid));
                        $data = Hosting::select("packageid", "domain", "nextduedate", "billingcycle", "amount", "paymentmethod")->find($serviceid)->toArray();
                        $existingpid = $data["packageid"];
                        $domain = $data["domain"];
                        $existingnextduedate = $data["nextduedate"];
                        $billingcycle = $data["billingcycle"];
                        $price = $data["amount"];
                        
                        if (!$paymentmethod) {
                            $paymentmethod = $data["paymentmethod"];
                        }
                        
                        $recurringamount = $request->recurringamount;
                        if ($recurringamount) {
                            $price = $recurringamount;
                        }
                        
                        $totaldays = Invoice::getBillingCycleDays($billingcycle);
                        $timediff = Carbon::createFromFormat("Y-m-d", $targetnextduedate)->diffInDays(Carbon::createFromFormat("Y-m-d", $existingnextduedate));
                        $percent = $timediff / $totaldays;
                        
                        $amountdue = Functions::format_as_currency($price * $percent);
                        $invdata = \App\Helpers\ProcessInvoices::getInvoiceProductDetails($serviceid, $existingpid, "", "", $billingcycle, $domain, $userid);
                        $description = $invdata["description"] . " (" . (new HelpersClient())->fromMySQLDate($existingnextduedate) . " - " . $nextduedate . ")";
                        $tax = $invdata["tax"];
                        
                        // insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "ProrataProduct" . $targetnextduedate, "relid" => $serviceid, "description" => $description, "amount" => $amountdue, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
                        $item = new Invoiceitem();
                        $item->userid = $userid;
                        $item->type = "ProrataProduct" . $targetnextduedate;
                        $item->relid = $serviceid;
                        $item->description = $description;
                        $item->amount = $amountdue;
                        $item->taxed = $tax;
                        $item->duedate = now();
                        $item->paymentmethod = $paymentmethod;
                        $item->save();
                    }
                    
                    foreach ($seladdons as $aid) {
                        // $data = get_query_vals("tblhostingaddons", "hostingid,addonid,name,nextduedate,billingcycle,recurring,paymentmethod", array("id" => $aid));
                        $data = Hostingaddon::select("hostingid", "addonid", "name", "nextduedate", "billingcycle", "recurring", "paymentmethod")->find($aid)->toArray();
                        $serviceid = $data["hostingid"];
                        $addonid = $data["addonid"];
                        $name = $data["name"];
                        $existingnextduedate = $data["nextduedate"];
                        $billingcycle = $data["billingcycle"];
                        $price = $data["recurring"];

                        if (!$paymentmethod) {
                            $paymentmethod = $data["paymentmethod"];
                        }
                        
                        // $domain = get_query_val("tblhosting", "domain", array("id" => $serviceid));
                        $domain = Hosting::find($serviceid)->domain;
                        $recurringamount = $request->recurringamount;
                        if ($recurringamount) {
                            $price = $recurringamount;
                        }

                        $totaldays = Invoice::getBillingCycleDays($billingcycle);
                        $timediff = Carbon::createFromFormat("Y-m-d", $targetnextduedate)->diffInDays(Carbon::createFromFormat("Y-m-d", $existingnextduedate));
                        $percent = $timediff / $totaldays;
                        $amountdue = Functions::format_as_currency($price * $percent);
                        
                        if ($domain) {
                            $domain = "(" . $domain . ") ";
                        }

                        $description = __("client.orderaddon") . " " . $domain . "- ";
                        
                        if ($name) {
                            $description .= $name;
                        } else {
                            // get_query_val("tbladdons", "name", array("id" => $addonid));
                            $description .= Addon::find($addonid)->name;
                        }
                        
                        $nextduedate = $request->nextduedate;
                        $description .= " (" . (new HelpersClient())->fromMySQLDate($existingnextduedate) . " - " . $nextduedate . ")";
                        $tax = $invdata["tax"];

                        // insert_query("tblinvoiceitems", array("userid" => $userid, "type" => "ProrataAddon" . $targetnextduedate, "relid" => $aid, "description" => $description, "amount" => $amountdue, "taxed" => $tax, "duedate" => "now()", "paymentmethod" => $paymentmethod));
                        $item = new Invoiceitem();
                        $item->userid = $userid; 
                        $item->type = "ProrataAddon" . $targetnextduedate; 
                        $item->relid = $aid; 
                        $item->description = $description; 
                        $item->amount = $amountdue;
                        $item->taxed = $tax; 
                        $item->duedate = now();
                        $item->paymentmethod = $paymentmethod;
                        $item->save();
                    }

                    // TODO: Check for helper createInvoices still onprogress
                    \App\Helpers\ProcessInvoices::createInvoices($userid);
                } else {
                    // TODO: Perform action without permission
                }
            }

            $updateqry = [];

            $firstpaymentamount = $request->firstpaymentamount;
            if ($firstpaymentamount) {
                $updateqry["firstpaymentamount"] = $firstpaymentamount;
            }

            $recurringamount = $request->recurringamount;
            if ($recurringamount) {
                $updateqry["amount"] = $recurringamount;
            }

            $nextduedate = $request->nextduedate;
            $proratabill = $request->proratabill;
            if ($nextduedate && !$proratabill) {
                $updateqry["nextinvoicedate"] = (new \App\Helpers\SystemHelper())->toMySQLDate($nextduedate);
                $updateqry["nextduedate"] = $updateqry["nextinvoicedate"];
            }

            $billingcycle = $request->billingcycle;
            if ($billingcycle) {
                $updateqry["billingcycle"] = $billingcycle;
            }

            // $paymentmethod = $request->paymentmethod;
            if ($paymentmethod) {
                $updateqry["paymentmethod"] = $paymentmethod;
            }

            $status = $request->set_status;
            if ($status) {
                $updateqry["domainstatus"] = $status;
            }

            $overideautosuspend = $request->overideautosuspend;
            $overidesuspenduntil = $request->overidesuspenduntil;
            if ($overideautosuspend) {
                $updateqry["overideautosuspend"] = "1";
                $updateqry["overidesuspenduntil"] = (new \App\Helpers\SystemHelper())->toMySQLDate($overidesuspenduntil);
            }
            
            if ($selproducts && count($updateqry)) {
                if (AdminFunctions::checkPermission("Edit Clients Products/Services")) {
                    foreach ($selproducts as $pid) {
                        Hooks::run_hook("PreServiceEdit", array("serviceid" => $pid));
                        Hosting::where("id", $pid)->update($updateqry);
                        $serviceDetails["serviceid"] = $pid;

                        Hooks::run_hook("ServiceEdit", $serviceDetails);
                        Hooks::run_hook("AdminServiceEdit", $serviceDetails);
                    }

                    LogActivity::Save("Mass Updated Products IDs: " . implode(",", $selproducts) . " - User ID: " . $userid, $userid);
                } else {
                    // TODO: Perform action without permission
                }
            }

            unset($updateqry["amount"]);
            unset($updateqry["domainstatus"]);
            unset($updateqry["overideautosuspend"]);
            unset($updateqry["overidesuspenduntil"]);

            if ($status) {
                $updateqry["status"] = $status;
            }

            if ($seladdons) {
                $addonHook = "AddonEdit";
                unset($updateqry["firstpaymentamount"]);

                $recurringamount = $request->recurringamount;
                if ($recurringamount) {
                    $updateqry["recurring"] = $recurringamount;
                }

                if (count($updateqry)) {
                    if (AdminFunctions::checkPermission("Edit Clients Products/Services")) {
                        foreach ($seladdons as $aid) {
                            // $addonData = get_query_vals("tblhostingaddons", "addonid, hostingid, status", array("id" => $aid));
                            $addonData = Hostingaddon::select("addonid", "hostingid", "status")->find($aid)->toArray();

                            $currentStatus = $addonData["status"];
                            if ($status && $currentStatus != $status) {
                                if ($currentStatus == "Suspended" && $status == "Active") {
                                    $addonHook = "AddonUnsuspended";
                                } else {
                                    if ($currentStatus != "Active" && $status == "Active") {
                                        $addonHook = "AddonActivated";
                                    } else {
                                        if ($currentStatus != "Suspended" && $status == "Suspended") {
                                            $addonHook = "AddonSuspended";
                                        } else {
                                            if ($currentStatus != "Terminated" && $status == "Terminated") {
                                                $addonHook = "AddonTerminated";
                                            } else {
                                                if ($currentStatus != "Cancelled" && $status == "Cancelled") {
                                                    $addonHook = "AddonCancelled";
                                                } else {
                                                    if ($currentStatus != "Fraud" && $status == "Fraud") {
                                                        $addonHook = "AddonFraud";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $definedAddonID = $addonData["addonid"];
                            $addonServiceID = $addonData["hostingid"];
                            $addonDetails["addonid"] = $definedAddonID;
                            $addonDetails["id"] = $aid;
                            $addonDetails["serviceid"] = $addonServiceID;
                            
                            // update_query("tblhostingaddons", $updateqry, array("id" => $aid));
                            Hostingaddon::where("id", $aid)->update($updateqry);
                            Hooks::run_hook($addonHook, $addonDetails);
                        }

                        LogActivity::Save("Mass Updated Addons IDs: " . implode(",", $seladdons) . " - User ID: " . $userid, $userid);
                    } else {
                        // TODO: Perform action without permission
                    }
                }
            }

            if ($seldomains) {
                unset($updateqry["recurring"]);
                unset($updateqry["billingcycle"]);

                $firstpaymentamount = $request->firstpaymentamount;
                if ($firstpaymentamount) {
                    $updateqry["firstpaymentamount"] = $firstpaymentamount;
                }

                $recurringamount = $request->recurringamount;
                if ($recurringamount) {
                    $updateqry["recurringamount"] = $recurringamount;
                }

                $billingcycle = $request->billingcycle;
                if ($billingcycle == "Annually") {
                    $updateqry["registrationperiod"] = "1";
                }

                if ($billingcycle == "Biennially") {
                    $updateqry["registrationperiod"] = "2";
                }

                if ($billingcycle == "Triennially") {
                    $updateqry["registrationperiod"] = "3";
                }

                $status = $request->set_status;
                if (in_array($status, ["Suspended", "Terminated", "Completed"])) {
                    $updateqry["status"] = "Expired";
                }
                
                if (count($updateqry)) {
                    if (AdminFunctions::checkPermission("Edit Clients Domains")) {
                        foreach ($seldomains as $did) {
                            $domainDetails["domainid"] = $did;
                            Hooks::run_hook("DomainEdit", $domainDetails);

                            // update_query("tbldomains", $updateqry, array("id" => $did));
                            Domain::where("id", $did)->update($updateqry);
                        }

                        LogActivity::Save("Mass Updated Domains IDs: " . implode(",", $seldomains) . " - User ID: " . $userid, $userid);
                    } else {
                        // TODO: Perform action without permission
                    }
                }
            }

            $moduleresults = [];

            if ($masscreate) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    $createSuccess = __("admin.servicescreatesuccess");

                    foreach ($selproducts as $serviceid) {
                        try {
                            // $modresult = WHMCS\Service\Service::findOrFail($serviceid)->legacyProvision();
                            $modresult = Hosting::findOrFail($serviceid)->legacyProvision();
                        } catch (\Exception $e) {
                            $modresult = $e->getMessage();
                        }

                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $createSuccess;
                        }
                    }
                    
                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        
                        if (!$moduleAutomation->runAction("CreateAccount")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $createSuccess;
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($masssuspend) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    foreach ($selproducts as $serviceid) {
                        $modresult = (new Server())->ServerSuspendAccount($serviceid);
                        
                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . __("admin.servicessuspendsuccess");
                        }
                    }

                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        
                        if (!$moduleAutomation->runAction("SuspendAccount")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . __("admin.servicessuspendsuccess");
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($massunsuspend) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    foreach ($selproducts as $serviceid) {
                        $modresult = (new Server())->ServerUnsuspendAccount($serviceid);
                        
                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . __("admin.servicesunsuspendsuccess");
                        }
                    }

                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        
                        if (!$moduleAutomation->runAction("UnsuspendAccount")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . __("admin.servicesunsuspendsuccess");
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($massterminate) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    foreach ($selproducts as $serviceid) {
                        $modresult = (new Server())->ServerTerminateAccount($serviceid);
                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . __("admin.servicesterminatesuccess");
                        }
                    }

                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        if (!$moduleAutomation->runAction("TerminateAccount")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . __("admin.servicesterminatesuccess");
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($masschangepackage) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    foreach ($selproducts as $serviceid) {
                        $modresult = (new Server())->ServerChangePackage($serviceid);
                        
                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . __("admin.servicesupdownsuccess");
                        }
                    }

                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        
                        if (!$moduleAutomation->runAction("ChangePackage")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . __("admin.servicesupdownsuccess");
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            if ($masschangepw) {
                if (AdminFunctions::checkPermission("Perform Server Operations")) {
                    foreach ($selproducts as $serviceid) {
                        $modresult = (new Server())->ServerChangePassword($serviceid);
                        
                        if ($modresult != "success") {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . $modresult;
                        } else {
                            $moduleresults[] = "Service ID " . $serviceid . ": " . __("admin.servicespwchangesuccess");
                        }
                    }

                    foreach ($seladdons as $addonUniqueId) {
                        $moduleAutomation = AddonAutomation::factory($addonUniqueId);
                        
                        if (!$moduleAutomation->runAction("ChangePassword")) {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . $moduleAutomation->getError();
                        } else {
                            $moduleresults[] = "Addon ID: " . $addonUniqueId . ": " . __("admin.servicespwchangesuccess");
                        }
                    }
                } else {
                    // TODO: Perform action without permission
                }
            }

            $queryStr["massupdatecomplete"] = true;
        }

        $message = "";
        $deletesuccess = $queryStr["deletesuccess"] ?? false;
        $invoicecount = $queryStr["invoicecount"] ?? 0;
        $massupdatecomplete = $queryStr["massupdatecomplete"] ?? null;

        if ($deletesuccess) {
            $message .= " " .AdminFunctions::infoBoxMessage(__("admin.success"), __("admin.clientsummarydeletesuccess")) ."<br/>";
        }

        if ($invoicecount && 0 < strlen(trim($invoicecount))) {
            $message .= " " .AdminFunctions::infoBoxMessage(__("admin.invoicesgencomplete"), $invoicecount ." Invoices Created") ."<br/>";
        }

        if ($massupdatecomplete) {
            $message .= " " .AdminFunctions::infoBoxMessage(__("admin.clientsummarymassupdcomplete"), __("admin.clientsummarymodifysuccess") . ($moduleresults ? "<br />- Module action result: " .implode("<br />", $moduleresults) : "") ) ."<br/>";
        }

        // TODO: Add message for action no permission
        # code
        
        return redirect()
                ->route("admin.pages.clients.viewclients.clientsummary.index", ["userid" => $userid])
                ->with('type', 'success')
                ->with('message', $message);
    }

    public function clientEmailsIndex(Request $request)
    {    
        if ($request->displaymessage) {
            $email = Email::findOrFail($request->id);

            $title = __("admin.emailsviewemail");
            $to = is_null($email->to) ? __("admin.emailsregisteredemail") : $email->to;
            $cc = $email->cc;
            $bcc = $email->bcc;
            $subject = $email->subject;
            $message = $email->message;

            $content = "<p><b>" .__("admin.emailsto") .":</b> " .Sanitize::makeSafeForOutput($to) ."<br/>";
            if ($cc) $content .= "<b>" .__("admin.emailscc") .":</b> " .Sanitize::makeSafeForOutput($cc) ."<br/>";
            if ($bcc) $content .= "<b>" .__("admin.emailsbcc") .":</b> " .Sanitize::makeSafeForOutput($bcc) ."<br/>";
            $content .= "<b>" .__("admin.emailssubject") .":</b> <span id=\"subject\">" .Sanitize::makeSafeForOutput($subject) ."</span></p>\n";
            $content .= $message;

            return view('pages.clients.viewclients.clientemails.display-message', compact('title', 'content', 'message'));
        }

        return view('pages.clients.viewclients.clientemails.index');
    }

    public function saveNotes() {
        $validator = Validator::make(request()->all(), [
            'id'   => "required|integer|exists:App\Models\Client,id",
            'admin_notes'   => "nullable|string",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.index")
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $userid = request()->id;

        if (!auth()->user()->checkPermissionTo("Edit Clients Details")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $client = Client::findOrFail($userid);
        $client->notes = request()->admin_notes;
        $client->save();

        LogActivity::Save("Client Summary Notes Updated - User ID: $userid", $userid);
    
        return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The data has been successfully saved.'));
    }

    public function uploadFile(Request $request)
    {
        $userid = $request->clientid;

        $validator = Validator::make($request->all(), [
            'clientid'   => "required|integer|exists:App\Models\Client,id",
            'title'   => "required|string",
            'adminonly'   => "nullable|numeric",
            'upload_file'   => "required|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2000",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        if (!auth()->user()->checkPermissionTo("Manage Clients Files")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $title = $request->title;
        $file = $request->file('upload_file');
        $adminonly = (bool) $request->adminonly;

        $data = FileUploader::singleUpload($file, [
            'file_name_prefix' => '',
            'file_attachment_type' => 'clientfiles',
            'file_upload_path' => "clientfiles/$userid",
            'old_file_path' => null,
        ]);

        $params = [
            "userid" => $userid,
            "title" => $title ?? $data["origfilename"],
            "filename" => $data["filename"],
            "adminonly" => $adminonly,
            "dateadded" => now(),
            "origfilename" => $data["origfilename"],
        ];

        $clientFile = new Clientsfile();
        $clientFile->userid = $params["userid"];
        $clientFile->title = $params["title"];
        $clientFile->filename = $params["filename"];
        $clientFile->adminonly = $params["adminonly"];
        $clientFile->dateadded = $params["dateadded"];
        $clientFile->save();

        Hooks::run_hook("AdminClientFileUpload", $params);
        LogActivity::Save("Added Client File - Title: $title - User ID: $userid", $userid);

        return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The data has been successfully saved.'));

    }

    public function deleteFile(Request $request)
    {
        $userid = $request->clientid;

        $validator = Validator::make($request->all(), [
            'id'   => "required|integer|exists:App\Models\Clientsfile,id",
            'clientid'   => "required|integer|exists:App\Models\Client,id",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        if (!auth()->user()->checkPermissionTo("Manage Clients Files")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $clientFile = Clientsfile::findOrFail($request->id);
        
        $userid = $clientFile->userid;
        $title = $clientFile->title;
        $filename = $clientFile->filename;

        $clientFile->delete();

        FileUploader::delete("clientfiles/$userid/$filename");
        LogActivity::Save("Deleted Client File - Title: $title - User ID: $userid", $userid);

        return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The data has been successfully deleted.'));
    }

    public function downloadFile(Request $request)
    {
        $clientFile = Clientsfile::findOrFail($request->id);
        $userid = $clientFile->userid;
        $filename = $clientFile->filename;
        $adminonly = $clientFile->adminonly;

        // Permission denied if not admin and the file is adminonly
        if (!auth()->guard("admin")->check() && $adminonly) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        return FileUploader::download("clientfiles/$userid/$filename");
    }

    public function closeClient(Request $request)
    {
        $userid = $request->clientid;

        $validator = Validator::make($request->all(), [
            'clientid' => "required|integer|exists:App\Models\Client,id",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        if (!auth()->user()->checkPermissionTo('Edit Clients Details') ||
            !auth()->user()->checkPermissionTo('Edit Clients Products/Services') ||
            !auth()->user()->checkPermissionTo('Edit Clients Domains') ||
            !auth()->user()->checkPermissionTo('Manage Invoice')) {

            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $response = (new HelpersClient())->CloseClient($userid);
        if ($response["result"] == "error") {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' .$response["message"]));
        }

        return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The client has been successfully closed.'));

    }

    public function deleteClient(Request $request)
    {
        $userid = $request->clientid;
        
        $validator = Validator::make($request->all(), [
            'clientid' => "required|integer|exists:App\Models\Client,id",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        if (!auth()->user()->checkPermissionTo('Delete Client')) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $response = (new HelpersClient())->DeleteClient($userid);
        if ($response["result"] == "error") {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' .$response["message"]));
        }

        Hooks::run_hook("ClientDelete", ["userid" => $userid]);

        return redirect()
                    ->route('admin.pages.clients.viewclients.index')
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> The client has been successfully deleted.'));

    }

    public function affiliateActivate(Request $request)
    {
        $userid = $request->clientid;
        
        $validator = Validator::make($request->all(), [
            'clientid' => "required|integer|exists:App\Models\Client,id",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        HelpersAffiliate::Activate($userid);

        return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'success')
                    ->with('message', __('<b>Well Done!</b> ') .__("admin.clientsummaryaffiliateactivatesuccess"));
    }

    public function mergeClient(Request $request)
    {
        try {
            \DB::beginTransaction();

            $pfx = $this->prefix;
            $userid = $request->clientid;
            $newuserid = trim($request->newuserid);

            $data = Client::select("id")->find($newuserid);
            if (!$data) {
                return redirect()
                        ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                        ->with('type', 'danger')
                        ->with('message', __('<b>Oh No!</b> Can\'t find the newuserid (Second Client ID).'));
            }

            if ($request->mergemethod == "to1") {
                $resultinguserid = $userid;
                $deleteuser = $newuserid;
            } else {
                $resultinguserid = $newuserid;
                $deleteuser = $userid;
            }

            $tables_array = [
                    "{$pfx}accounts", 
                    "{$pfx}contacts", 
                    "{$pfx}domains", 
                    "{$pfx}emails", 
                    "{$pfx}hosting", 
                    "{$pfx}hostingaddons", 
                    "{$pfx}invoiceitems", 
                    "{$pfx}invoices", 
                    "{$pfx}notes", 
                    "{$pfx}orders", 
                    "{$pfx}quotes", 
                    "{$pfx}ticketreplies", 
                    "{$pfx}tickets", 
                    "{$pfx}activitylog", 
                    "{$pfx}sslorders", 
                    "{$pfx}clientsfiles", 
                    "{$pfx}billableitems"
            ];
            
            foreach ($tables_array as $table) {
                \DB::table($table)->where("userid", $deleteuser)->update(['userid' => $resultinguserid]);
            }

            Credit::where("clientid", $deleteuser)->update(["clientid" => $resultinguserid]);

            $userid = $newuserid;

            $credit = 0;
            $clientCredit = Client::select("credit")->where("id", $deleteuser)->first();
            if ($clientCredit) {
                $credit = $clientCredit->credit;
            }

            $newClientCredit = Client::select("credit")->where("id", (int) $resultinguserid)->first();
            if ($newClientCredit) {
                $newClientCredit->credit += $credit;
                $newClientCredit->save();
            }

            Paymethod::where("userid", $deleteuser)->update(["userid" =>  $resultinguserid]);

            // Added from whmcs decode version 7.10.2
            Paymethod::where("contact_type", "Client")->where("contact_id", $deleteuser)->update(["contact_id" => $resultinguserid]);

            $affiliate = Affiliate::where("clientid", $deleteuser)->first();
            if ($affiliate) {
                $data = $affiliate->toArray();
                $affid = $data["id"];
        
                if ($affid) {
                    $visitors = $data["visitors"];
                    $balance = $data["balance"];
                    $withdrawn = $data["withdrawn"];

                    $newAffiliate = Affiliate::where("clientid", $resultinguserid)->first();
                    
                    if (!$newAffiliate) {
                        $newaff = new Affiliate();
                        $newaff->date = now();
                        $newaff->clientid = $resultinguserid;
                        $newaff->save();

                        $newaffid = $newaff->id; 
                    } else {
                        $newaffid = $newAffiliate->id;
                    }

                    Affiliate::where("id", (int) $newaffid)->update([
                        "visitors" => \DB::raw("visitors + $visitors"),
                        "balance" => \DB::raw("balance + $balance"),
                        "withdrawn" => \DB::raw("withdrawn + $withdrawn"),
                    ]);

                    AffiliateAccount::where("affiliateid", $affid)->update(["affiliateid" => $newaffid]);
                    AffiliateHistory::where("affiliateid", $affid)->update(["affiliateid" => $newaffid]);
                    AffiliateWithdrawal::where("affiliateid", $affid)->update(["affiliateid" => $newaffid]);
                    Affiliate::where("clientid", $deleteuser)->delete();
                }
            }

            LogActivity::save("Merged User ID: $deleteuser with User ID: $resultinguserid", $resultinguserid);
            Hooks::run_hook("AfterClientMerge", ["toUserID" => $resultinguserid, "fromUserID" => $deleteuser]);
            
            if ($resultinguserid != $deleteuser) {
                $response = (new HelpersClient())->DeleteClient($deleteuser);
                if ($response["result"] == "error") {
                    return redirect()
                            ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $resultinguserid])
                            ->with('type', 'danger')
                            ->with('message', __('<b>Oh No!</b> Something went wrong, please try again later.<br>Message: ' .$response["message"]));
                }
            }

            \DB::commit();
            
            return redirect()
                        ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $resultinguserid])
                        ->with('type', 'success')
                        ->with('message', __("<b>Well Done!</b> Successfully Merged User ID: $deleteuser with User ID: $resultinguserid"));

        } catch (\Throwable $th) {
            \DB::rollBack();
            throw $th;
        }
    }

    public function searchClient(Request $request)
    {
        $value = $request->search;
        $userid = $request->clientid;
        $data = [];

        if (!$value) {
            return response()->json([
                "results" => $data
            ], 200);
        }

        $clients = Client::select("id", "firstname", "lastname", "companyname", "email")
                            ->where("id", "!=", $userid)
                            ->whereRaw("concat(firstname,' ',lastname) LIKE '%" . $value . "%' OR companyname LIKE '%" . $value . "%' OR email LIKE '%" . $value . "%'")
                            ->skip(0)
                            ->take(10)
                            ->get();
        
        foreach ($clients as $client) {
            
            if ($client->companyame) {
                $client->companyname = " ({$client->companyname})";
            }

            $text = "{$client->firstname} {$client->lastname} - {$client->companyname} #{$client->id}\n{$client->email}";
            $data[] = [
                "id" => $client->id,
                "text" => $text,
                "data" => [
                    "id" => $client->id,
                    "firstname" => $client->firstname,
                    "lastname" => $client->lastname,
                    "companyname" => $client->companyname ? "({$client->companyname})" : "",
                    "email" => $client->email,
                ]
            ];
        }

        return response()->json([
            "results" => $data
        ], 200);
    }

    public function addfunds(Request $request)
    {
        $userid = $request->clientid;
        
        if (!auth()->user()->checkPermissionTo('Create Invoice')) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $validator = Validator::make($request->all(), [
            'clientid' => "required|integer|exists:App\Models\Client,id",
            "addfundsamt" => "required|numeric|min:0|gt:0",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $addfundsamt = round($request->addfundsamt, 2);

        if ($addfundsamt < 0) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> The Amount must be greater than zero.'));
        }

        global $_LANG;
        $invoiceid = 0;
        // TODO: Need merge feature createInvoices
        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userid);
        $paymentmethod = Gateway::getClientsPaymentMethod($userid);
        
        $item = new Invoiceitem();
        $item->userid = $userid; 
        // Note: The invoiceid not assigned in decode version
        $item->invoiceid = $invoiceid;  
        $item->type = "AddFunds"; 
        $item->relid = "";
        $item->description = $_LANG["addfunds"] ?? ""; 
        $item->amount = $addfundsamt;
        $item->taxed = "0";
        $item->duedate = now(); 
        $item->paymentmethod = $paymentmethod;
        $item->save();
    
        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userid, "", true);
        $route = route("admin.pages.billing.invoices.edit", ["id" => (int) $invoiceid]);
        $invoiceRedirect =  " - <a href=\"$route\">" . __("admin.fieldsinvoicenum") .$invoiceid . "</a>";

        return redirect()
                ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                ->with('type', 'success')
                ->with('message', __("<b>Well Done!</b> ") .__("admin.clientsummarycreateaddfundssuccess") .$invoiceRedirect);
    }

    public function generateDueInvoices(Request $request)
    {
        $userid = $request->clientid;
        
        if (!auth()->user()->checkPermissionTo('Generate Due Invoices')) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> You don\'t have permission to access the action.'));
        }

        $validator = Validator::make($request->all(), [
            'clientid' => "required|integer|exists:App\Models\Client,id",
            "noemails" => "required|string|in:true,false",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }
        
        $noemails = $request->noemails;
        // $invoicecount = 0;
        // TODO: Need merge feature createInvoices
        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userid, $noemails);
        $invoicecount = $GLOBALS['invoicecount'];
        // $_SESSION["adminclientgeninvoicescount"] = $invoicecount;

        return redirect()
                ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                ->with('type', 'success')
                ->with('message', __("<b>Well Done!</b> ") .__("admin.invoicesgencomplete") ." $invoicecount Invoices Created");
    }

    public function dtClientSummaryProductServices()
    {
        $pfx = $this->prefix;
        $dataFiltered = request()->dataFiltered;
        $userid = $dataFiltered["userid"];

        $query = Hosting::select(\DB::raw("{$pfx}hosting.*, {$pfx}products.name as product_name"))
                                ->where("userid", (int) $userid)
                                ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                                ;// ->orderBy("{$pfx}hosting.id", "DESC");

        $filters = $dataFiltered["status_filters"] ?? [];        
        if ($filters) {
            $query->whereIn("domainstatus", $filters);
        }

        return datatables()->of($query)->addColumn('raw_id', function($row) use($userid) {
                    $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid, 'id' => $row->id]);

                    return "<a href=\"{$route}\">{$row->id}</a>";
                })
                ->addColumn('product_services', function($row) {
                    return "{$row->product_name} - <a href=\"http://{$row->domain}\" target=\"_blank\">{$row->domain}</a>";
                 })
                ->editColumn('amount', function($row) {
                    if ($row->billingcycle == "One Time" || $row->billingcycle == "Free Account") {
                        return Format::formatCurrency($row->firstpaymentamount);
                    }

                    return Format::formatCurrency($row->amount);
                })
                ->editColumn('regdate', function($row) {
                    return (new HelpersClient())->fromMySQLDate($row->regdate);
                })
                ->editColumn('nextduedate', function($row) {
                    if ($row->billingcycle == "One Time" || $row->billingcycle == "Free Account") {
                        return "-";
                    }

                    return (new HelpersClient())->fromMySQLDate($row->nextduedate);
                })
                ->editColumn('domain', function($row) {
                    return $row->domain == "" ? "(" .__("admin.addonsnodomain") .")" : $row->domain;
                })
                ->editColumn('billingcycle', function($row) {
                    return __("admin.billingcycles" .str_replace(["-", "account", " "], "", strtolower($row->billingcycle)));
                })
                ->editColumn('domainstatus', function($row) {
                    return $row->domainstatus ? __("admin.status" .strtolower($row->domainstatus)) : null;
                })
                ->addColumn('domainoriginalstatus', function($row) {
                    return $row->domainstatus;
                })
                ->addColumn('actions', function($row) use($userid) {
                    $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $userid, 'id' => $row->id]);
                    $action = "";

                    $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";

                    return $action;
                })
                ->orderColumn('raw_id', function($query, $order) {
                    $query->orderBy("id", $order);
                })
                ->orderColumn('product_services', function($query, $order) {
                    $query->orderBy("product_name", $order);
                })
                ->orderColumn('domainstatus', function($query, $order) {
                    $query->orderBy("domainstatus", $order);
                })
                ->rawColumns(['raw_id', 'product_services', 'actions'])
                ->addIndexColumn()
                ->toJson();
    }

    public function dtClientSummaryAddons()
    {
        $pfx = $this->prefix;
        $dataFiltered = request()->dataFiltered;
        $userid = $dataFiltered["userid"];

        // Predefined addons
        $predefinedaddons = [];
        $addonsData = Addon::get()->toArray();
        foreach ($addonsData as $data) {
            $addonId = $data["id"];
            $addonName = $data["name"];

            $predefinedaddons[$addonId] = $addonName;
        }

        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
            "predefinedaddons" => $predefinedaddons,
        ];

        $query =  Hostingaddon::withoutAppends()->select(
                                    "{$pfx}hostingaddons.*", 
                                    "{$pfx}hostingaddons.id AS aid", 
                                    "{$pfx}hostingaddons.name AS addonname", 
                                    "{$pfx}hosting.id AS hostingid", 
                                    "{$pfx}hosting.domain",
                                    "{$pfx}products.name"
                                )
                                ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}hostingaddons.hostingid")
                                ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                                ->where("{$pfx}hosting.userid", $userid);
                                // ->orderBy("{$pfx}hosting.id", "DESC");

        $filters = $dataFiltered["status_filters"] ?? [];
        if ($filters) {
            $query->whereIn("status", $filters);
        }

        return datatables()->of($query)
            ->addColumn('raw_id', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientservices.editAddon', [
                            'userid' => $params["userid"], 
                            'id' => $row->hostingid, 
                            'aid' => $row->aid
                        ]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->editColumn('addonname', function($row) use($params) {
                if (!$row->addonname) {
                    $predefinedaddons = $params["predefinedaddons"];
                    $row->addonname = $predefinedaddons[$row->addonid];
                }

                return "{$row->addonname}<br>{$row->name} - <a href=\"http://{$row->domain}\" target=\"_blank\">{$row->domain}</a>";
            })
            ->editColumn('recurring', function($row) {
                return Format::formatCurrency($row->recurring);
            })
            ->editColumn('billingcycle', function($row) {
                return __("admin.billingcycles" .str_replace(["-", "account", " "], "", strtolower($row->billingcycle)));
            })
            ->editColumn('regdate', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->regdate);
            })
            ->editColumn('nextduedate', function($row) {
                if ($row->billingcycle == "One Time" || $row->billingcycle == "Free Account") {
                    return "-";
                }

                return (new HelpersClient())->fromMySQLDate($row->nextduedate);
            })
            ->addColumn('translated_status', function($row) {
                return __("admin.status" .strtolower($row->status));
            })
            ->editColumn('domain', function($row) {
                return !$row->domain ? "(" .__("admin.addonsnodomain") .")" : $row->domain;
            })
            ->addColumn('amount', function($row) {
                return Format::formatCurrency($row->recurring);
            })
            ->addColumn('serviceid', function($row) {
                return $row->hostingid;
            })
            ->addColumn('actions', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientservices.editAddon', [
                            'userid' => $params["userid"], 
                            'id' => $row->hostingid, 
                            'aid' => $row->aid
                        ]);
                
                $action = "";
                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy("id", $order);
            })
            ->orderColumn('amount', function($query, $order) {
                $query->orderBy("recurring", $order);
            })
            ->orderColumn('addonname', function($query, $order) {
                $query->orderBy("addonname", $order);
            })
            ->orderColumn('translated_status', function($query, $order) {
                $query->orderBy("status", $order);
            })
            ->orderColumn('serviceid', function($query, $order) {
                $query->orderBy("hostingid", $order);
            })
            ->rawColumns(['raw_id', 'addonname', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

    public function dtClientSummaryDomain()
    {
        $pfx = $this->prefix;
        $dataFiltered = request()->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        // $query = Domain::where("userid", $userid)->orderBy("id", "DESC");
        $query = \DB::table("{$pfx}domains")->where("userid", $userid)->orderBy("id", "DESC");

        $filters = $dataFiltered["status_filters"] ?? [];
        if ($filters) {
            $query->whereIn("status", $filters); 
        }

        return datatables()->of($query)->addColumn('raw_id', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientdomain.index', [
                            'userid' => $params["userid"], 
                            'domainid' => $row->id, 
                        ]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->editColumn('domain', function($row) {
                return "<a href=\"http://{$row->domain}\" target=\"_blank\">{$row->domain}</a>";
            })
            ->editColumn('registrar', function($row) {
                return ucfirst($row->registrar);
            })
            ->editColumn('registrationdate', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->registrationdate);
            })
            ->editColumn('nextduedate', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->nextduedate);
            })
            ->editColumn('expirydate', function($row) {                
                return (new HelpersClient())->fromMySQLDate($row->expirydate);
            })
            ->addColumn('translated_status', function($row) {
                return __("admin.status" .strtolower(str_replace(" ", "", $row->status)));
            })
            ->addColumn('actions', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientdomain.index', [
                            'userid' => $params["userid"], 
                            'domainid' => $row->id, 
                        ]);
                
                $action = "";
                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy("id", $order);
            })
            ->orderColumn('translated_status', function($query, $order) {
                $query->orderBy("status", $order);
            })
            ->rawColumns(['raw_id', 'domain', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

    public function dtClientSummaryQuotes()
    {
        $pfx = $this->prefix;
        $dataFiltered = request()->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = Quote::where("userid", $userid)->where("validuntil", ">", date("Ymd"));

        // Notes: No status column in whmcs decode, but already exist in proto.qwords 
        // $filters = $dataFiltered["status_filters"] ?? [];
        // if ($filters) $query->whereIn("status", $filters);

        return datatables()->of($query)->addColumn('raw_id', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientquotes.index', [
                    'action' => 'manage',
                    'userid' => $params["userid"], 
                    'id' => $row->id, 
                ]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->editColumn('datecreated', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->datecreated);
            })
            ->editColumn('validuntil', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->validuntil);
            })
            ->editColumn('total', function($row) {
                return Format::formatCurrency($row->total);
            })
            ->addColumn('actions', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientquotes.index', [
                            'action' => 'manage',
                            'userid' => $params["userid"], 
                            'id' => $row->id, 
                        ]);
                
                $action = "";
                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy("id", $order);
            })
            ->rawColumns(['raw_id', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

    public function loginAsClient(Request $request)
    {
        $client = Client::find($request->userid);
        if (!$client || !\Auth::guard("web")->loginUsingId($client->id)) {
            return redirect()->route("login");
        }

        return redirect()->route("home");
    }

    public function csajaxtoggle(Request $request)
    {
        if (!AdminFunctions::checkPermission("Edit Clients Details")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $userid = $request->get("userid");
        $toggle = $request->get("csajaxtoggle");
        $fieldName = "";

        switch ($toggle) {
            case "autocc":
                $fieldName = "disableautocc";
                break;
            case "taxstatus":
                $fieldName = "taxexempt";
                break;
            case "overduenotices":
                $fieldName = "overideduenotices";
                break;
            case "latefees":
                $fieldName = "latefeeoveride";
                break;
            case "splitinvoices":
                $fieldName = "separateinvoices";
                break;
            default:
                return ResponseAPI::Error([
                    'message' => "Invalid Toggle Value!",
                ]);
        }

        $client = Client::find($userid);
        $csajaxtoggleval = $client->{$fieldName};
        $data = [];
        $data["element"] = $toggle;

        if ($csajaxtoggleval == "1") {
            $client->{$fieldName} = 0;
            if ($fieldName == "taxexempt") {
                $data["body"] = "<strong class=\"text-danger \"><u>" . __("admin.no") . "</u></strong>";
            } else {
                $data["body"] = "<strong class=\"text-success\"><u>" . __("admin.yes") . "</u></strong>";
            }
        } else {
            $client->{$fieldName} = 1;
            if ($fieldName == "taxexempt") {
                $data["body"] = "<strong class=\"text-success\"><u>" . __("admin.yes") . "</u></strong>";
            } else {
                $data["body"] = "<strong class=\"text-danger \"><u>" . __("admin.no") . "</u></strong>";
            }
        }

        $client->save();

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => $data,
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        $userid = $request->get("userid");
        $client = \App\Models\Client::find($userid);

        if (!$client) {
            return ResponseAPI::Error([
                'message' => "Invalid Client ID!",
            ]);
        }

        // $client->sendEmailAddressVerification();
        $client->sendEmailVerificationNotification();
        $data["body"] = __("admin.emailSent");

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => $data,
        ]);
    }

}
