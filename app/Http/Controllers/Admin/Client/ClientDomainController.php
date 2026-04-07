<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\AdditionalFields;
use App\Helpers\ClientClass;
use App\Helpers\Country;
use App\Helpers\Domains as HelpersDomain;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Orders;
use App\Helpers\ResponseAPI;
use App\Helpers\Sanitize;
use App\Helpers\SystemHelper;
use Illuminate\Support\Facades\Http; 

// Models
use App\Models\Contact;
use App\Models\Order;
use App\Models\Orderstatus;
use App\Models\Domain;
use App\Models\DomainpricingPremium;
use App\Models\DomainsExtra;
use App\Models\Emailtemplate;
use App\Models\Pricing;
use App\Models\Promotion;
use App\Models\Server;
use App\Models\Sslstatus;
use App\Models\Note;

// Module
use App\Module\Registrar;

// Traits
use App\Traits\DatatableFilter;

class ClientDomainController extends Controller
{
    use DatatableFilter;

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
            $templatevars['invalidClientId'] = true;
            
            return view('pages.clients.viewclients.clientdomain.index', $templatevars);
        }
        
        $userid = $request->userid;     // Client ID
        $id = $request->domainid;       // Domain ID

        if ($userid && !$id) {
            // $aInt->valUserID($userid);
            // $id = get_query_val("tbldomains", "id", array("userid" => $userid), "domain", "ASC", "0,1");
            $domain = Domain::where("userid", $userid)->orderby("domain", "ASC")->first();
            $id = $domain->id ?? null;
        }

        if (!$id) {
            // $aInt->gracefulExit(AdminLang::trans("domains.domainidnotfound"));
            $templatevars['invalidDomainId'] = true;
                
            $templatevars["notesCount"] = Note::where('userid', $userid)->count();
            return view('pages.clients.viewclients.clientdomain.index', $templatevars);
        }

        $domains = new HelpersDomain();
        $domain_data = $domains->getDomainsDatabyID($id);
        
        // dd($domain_data);
        $domainregistraractions = AdminFunctions::checkPermission("Perform Registrar Operations") && $domains->getModule() ? true : false;
        $nsvalues = $domains->getNameservers();
        $obtainEmailReminders = $domains->obtainEmailReminders();

        // dd($domain_data);

        if (!$domain_data || @$domain_data["userid"] != $userid) {
            $templatevars['invalidDomainId'] = true;
            $templatevars['userid'] = $userid;
            $templatevars["notesCount"] = Note::where('userid', $userid)->count();
            return view('pages.clients.viewclients.clientdomain.index', $templatevars);
        }

        $id = $did = $domainid = $domain_data["id"];

        if ($userid != $domain_data["userid"]) {
            $userid = $domain_data["userid"];
            // $aInt->valUserID($userid);
        }

        $currency = (new AdminFunctions())->getCurrency($userid);
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $paymentmethodlist = (new Gateway($request))->paymentMethodsList();
        $addonsPricing = Pricing::where("type", "domainaddons")
                                    ->where("currency", $currency["id"])
                                    ->where("relid", 0)
                                    ->first(["msetupfee", "qsetupfee", "ssetupfee"]);
        $domainList = Domain::where("userid", $userid)->orderBy("domain", "ASC")->get();
        $promotionList = Promotion::orderBy("code", "ASC")->get()->toArray();
        $domainMailTemplates = Emailtemplate::where("type", "domain")->where("language", "")->orderBy("name")->get();

        $domaindnsmanagementprice = $addonsPricing->msetupfee * $domain_data["registrationperiod"];
        $domainemailforwardingprice = $addonsPricing->qsetupfee * $domain_data["registrationperiod"];
        $domainidprotectionprice = $addonsPricing->ssetupfee * $domain_data["registrationperiod"];

        $did = $domain_data["id"];
        $orderid = $domain_data["orderid"];
        $ordertype = $domain_data["type"];
        
        $domain = $domain_data["domain"];
        $domainid = $domain_data["id"];
        // dd($domainid);
        // $domainparts = explode('.', $domain, 2); // Pecah domain menjadi SLD dan TLD
        $domainparts = explode('.', $domain_data["domain"], 2); // Pecah domain menjadi SLD dan TLD
        
        

        $paymentmethod = $domain_data["paymentmethod"];

        $gateways = new \App\Module\Gateway();
        if (!$paymentmethod || !$gateways->isActiveGateway($paymentmethod)) {
            $paymentmethod = Functions::ensurePaymentMethodIsSet($userid, $id, "tbldomains");
        }

        $firstpaymentamount = $domain_data["firstpaymentamount"];
        $recurringamount = $domain_data["recurringamount"];
        $registrar = $domain_data["registrar"];
        $regtype = $domain_data["type"];
        $expirydate = $domain_data["expirydate"];
        $nextduedate = $domain_data["nextduedate"];
        $nextinvoicedate = $domain_data["nextinvoicedate"];
        $subscriptionid = $domain_data["subscriptionid"];
        $promoid = $domain_data["promoid"];
        $registrationdate = $domain_data["registrationdate"];
        $registrationperiod = $domain_data["registrationperiod"];
        $domainstatus = $domain_data["status"];
        $additionalnotes = $domain_data["additionalnotes"];
        $dnsmanagement = $domain_data["dnsmanagement"];
        $emailforwarding = $domain_data["emailforwarding"];
        $idprotection = $domain_data["idprotection"];
        $donotrenew = $domain_data["donotrenew"];
        $isPremium = $domain_data["is_premium"];
        $expirydate = (new Functions())->fromMySQLDate(date("Y-m-d", strtotime($expirydate)));
        $nextduedate = (new Functions())->fromMySQLDate(date("Y-m-d", strtotime($nextduedate)));
        $nextinvoicedate = (new Functions())->fromMySQLDate(date("Y-m-d", strtotime($nextinvoicedate)));
        $regdate = (new Functions())->fromMySQLDate(date("Y-m-d", strtotime($registrationdate)));
        $reminderEmails = ["", "first", "second", "third", "fourth", "fifth"];

        $additflds = new AdditionalFields();
        $additflds->setDomain($domain)->setDomainType($ordertype)->getFieldValuesFromDatabase($id);
        $additflds = $additflds->getFieldsForOutput();

        // SSL Status Toggle
        $sslStatus = Sslstatus::factory($userid, $domain);
        $html = "<img src=\"%s\" class=\"%s\" data-toggle=\"tooltip\" title=\"%s\" data-domain=\"%s\" data-user-id=\"%s\" style=\"width:25px;\">";
        $sslStatusToggle = sprintf($html, $sslStatus->getImagePath(), $sslStatus->getClass(), $sslStatus->getTooltipContent(), $domain, $userid);

        // NS
        // dd(session('domainsavetemp'));
        $ns1 = "";
        $ns2 = "";
        $ns3 = "";
        $ns4 = "";
        $ns5 = "";
        $oldns1 = "";
        $oldns2 = "";
        $oldns3 = "";
        $oldns4 = "";
        $oldns5 = "";
        $defaultns = "";
        $newlockstatus = "";
        $oldlockstatus = "";
        $oldidprotect = "";
        $idprotect = "";

        $domainsavetemp = session('domainsavetemp');
        $conf = session('conf');
        if ($conf && $domainsavetemp) {
            $ns1 = $domainsavetemp["ns1"];
            $ns2 = $domainsavetemp["ns2"];
            $ns3 = $domainsavetemp["ns3"];
            $ns4 = $domainsavetemp["ns4"];
            $ns5 = $domainsavetemp["ns5"];
            $oldns1 = $domainsavetemp["oldns1"];
            $oldns2 = $domainsavetemp["oldns2"];
            $oldns3 = $domainsavetemp["oldns3"];
            $oldns4 = $domainsavetemp["oldns4"];
            $oldns5 = $domainsavetemp["oldns5"];
            $defaultns = $domainsavetemp["defaultns"];
            $newlockstatus = $domainsavetemp["newlockstatus"];
            $oldlockstatus = $domainsavetemp["oldlockstatus"];
            $oldidprotect = $domainsavetemp["oldidprotection"];
            $idprotect = $domainsavetemp["idprotection"];
        }

        // Template Vars
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $id;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["paymentmethodlist"] = $paymentmethodlist;
        $templatevars["gateway"] = $paymentmethod;
        $templatevars["domainList"] = $domainList;
        $templatevars["isPremium"] = $isPremium;
        $templatevars["orderid"] = $orderid;
        $templatevars["registrationperiod"] = $registrationperiod;
        $templatevars["regdate"] = $regdate;
        $templatevars["domain"] = $domain;
        $templatevars["ordertype"] = $ordertype;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();
        // $templatevars["registrars"] = (new RegistrarX())->getRegistrarsDropdownMenu($registrar);
        $templatevars["registrars"] = (new Registrar())->getList();
        $templatevars["current_registrar"] = $registrar;
        $templatevars["expirydate"] = $expirydate;
        $templatevars["nextduedate"] = $nextduedate;
        $templatevars["nextinvoicedate"] = $nextinvoicedate;
        $templatevars["firstpaymentamount"] = $firstpaymentamount;
        $templatevars["recurringamount"] = $recurringamount;
        $templatevars["statuses"] = (new HelpersDomain())->translatedDropdownOptions([$domainstatus]);
        $templatevars["promotionList"] = $promotionList;
        $templatevars["promoid"] = $promoid;
        $templatevars["subscriptionid"] = $subscriptionid;
        $templatevars["domainsHelper"] = $domains;
        $templatevars["domainregistraractions"] = $domainregistraractions;
        $templatevars["nsvalues"] = $nsvalues;
        $templatevars["dnsmanagement"] = $dnsmanagement;
        $templatevars["emailforwarding"] = $emailforwarding;
        $templatevars["idprotection"] = $idprotection;
        $templatevars["donotrenew"] = $donotrenew;
        $templatevars["registrar"] = $registrar;
        $templatevars["reminderEmails"] = $reminderEmails;
        $templatevars["obtainEmailReminders"] = $obtainEmailReminders;
        $templatevars["additflds"] = $additflds;
        $templatevars["additionalnotes"] = $additionalnotes;
        $templatevars["domainMailTemplates"] = $domainMailTemplates;
        $templatevars["sslStatusToggle"] = $sslStatusToggle;
        $templatevars["defaultns"] = $defaultns;
        $templatevars["domainparts"] = $domainparts;
        $templatevars["domainid"] = $domainid;
        $templatevars['sld'] = $domainparts[0];
        $templatevars['tld'] = $domainparts[1];

        // dd($templatevars["registrars"]);

        return view('pages.clients.viewclients.clientdomain.index', $templatevars);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'domainid' => "required|integer|exists:App\Models\Domain,id",
            'action' => "required|string|in:register,transfer",
        ]);

        $userid = $request->userid;
        $id = $request->domainid;
        $action = $request->action;
        $transfersecret = $request->transfersecret;
        
        if ($validator->fails()) {
            $templatevars['invalidDomainId'] = true;
            $templatevars['userid'] = $userid;
            
            return view('pages.clients.viewclients.clientdomain.register', $templatevars);
        }

        global $CONFIG;
        $domains = new HelpersDomain();
        $data = $domains->getDomainsDatabyID($id);
        $domainregistraractions = AdminFunctions::checkPermission("Perform Registrar Operations") && $domains->getModule() ? true : false;
        $nsvalues = $domains->getNameservers();
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        
        if (!$domainregistraractions) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", ["userid" => $userid, "domainid" => $id])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()));
        }

        if ($data && $data["userid"] != $userid) {
            $templatevars['invalidDomainId'] = true;
            $templatevars['userid'] = $userid;
            
            return view('pages.clients.viewclients.clientdomain.register', $templatevars);
        }

        $domainid = $data["id"];
        $userid = $data["userid"];
        $domain = $data["domain"];
        $orderid = $data["orderid"];
        $registrar = $data["registrar"];
        $registrationperiod = $data["registrationperiod"];
        
        // $params = [];
        // $domainparts = explode(".", $domain, 2);
        // list($params["sld"], $params["tld"]) = $domainparts;
        // $params["domainid"] = $domainid;
        // $params["regperiod"] = $registrationperiod;
        // $params["registrar"] = $registrar;
        $nsvals = [];
        $autonsdesc = "";

        if (!isset($ns1) && !isset($ns2)) {
            $hostingAccount = \DB::table("{$this->prefix}hosting")->where("domain", "=", $domain)->whereIn("domainstatus", ["Active", "Pending"])->first(["server"]);
            $server = (int) @$hostingAccount->server;
            
            if ($server) {
                $result = Server::find($server);
                $data = $result->toArray();
                
                for ($i = 1; $i <= 5; $i++) {
                    $nsvals[$i] = $data["nameserver" . $i];
                }
                $autonsdesc = "(" . __("admin.domainsautonsdesc1") . ")";
            } else {
                for ($i = 1; $i <= 5; $i++) {
                    $nsvals[$i] = $CONFIG["DefaultNameserver" . $i];
                }
    
                $autonsdesc = "(" . __("admin.domainsautonsdesc2") . ")";
            }
        }

        $result = Order::find($orderid);
        $data = $result->toArray();
        $nameservers = $data["nameservers"];

        if ($nameservers && $nameservers != "," /*&& !$_POST*/) {
            $nameservers = explode(",", $nameservers);
            for ($i = 1; $i <= 5; $i++) {
                $nsvals[$i] = $nameservers[$i - 1] ?? "";
            }

            $autonsdesc = "(" . __("admin.domainsautonsdesc3") . ")";
        }

        if (isset($transfersecret) && !$transfersecret) {
            $transfersecret = $data["transfersecret"];
            $transfersecret = $transfersecret ? (new \App\Helpers\Client())->safe_unserialize($transfersecret) : [];
            $transfersecret = $transfersecret[$domain] ?? "";
        }

        // if (is_array($_POST)) {
        //     for ($i = 1; $i <= 5; $i++) {
        //         if (isset($_POST["ns" . $i])) {
        //             $nsvals[$i] = $_POST["ns" . $i];
        //         }
        //     }
        // }

        // Template Vars
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $id;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["action"] = $action;
        $templatevars["registrar"] = ucfirst($registrar);
        $templatevars["domain"] = $domain;
        $templatevars["registrationperiod"] = $registrationperiod;
        $templatevars["nsvals"] = $nsvals;
        $templatevars["autonsdesc"] = $autonsdesc;
        $templatevars["transfersecret"] =  \App\Helpers\Sanitize::makeSafeForOutput($transfersecret);

        // dd($templatevars["autonsdesc"]);

        return view('pages.clients.viewclients.clientdomain.register', $templatevars);
    }

    public function clientdomaincontact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'domainid' => "required|integer|exists:App\Models\Domain,id",
        ]);

        $userid = $request->userid;
        $id = $request->domainid;
        
        if ($validator->fails()) {
            $templatevars['invalidDomainId'] = true;
            $templatevars['userid'] = $userid;
            
            return view('pages.clients.viewclients.clientdomain.clientdomaincontact', $templatevars);
        }

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $domains = new HelpersDomain();
        $domain_data = $domains->getDomainsDatabyID($id);
        if ($domain_data && $domain_data["userid"] != $userid) {
            $templatevars['invalidDomainId'] = true;
            $templatevars['userid'] = $userid;
            
            return view('pages.clients.viewclients.clientdomain.clientdomaincontact', $templatevars);
        }

        $domainid = $domain_data["id"];
        $userid = $domain_data["userid"];
        $domain = $domain_data["domain"];
        $registrar = $domain_data["registrar"];
        $contactdetails = [];

        $success = $domains->moduleCall2("GetContactDetails");
        $alert = "";
        $additionalData = NULL;
        $domainInformation = NULL;
        $regError = "";

        if ($success) {
            $contactdetails = $domains->getModuleReturn();
            try {
                $domainInformation = $domains->getDomainInformation();
            } catch (\Exception $e) {
            }

            if ($domainInformation instanceof \App\Helpers\Domain\Registrar\Domain && !$request->get("pending") && $domainInformation->isIrtpEnabled() && $domainInformation->isContactChangePending()) {
                $title = "admin.domainscontactChangePending";
                $description = "admin.domainscontactsChanged";
                $type = "info";
                if ($domainInformation->getPendingSuspension()) {
                    $title = "admin.domainsverificationRequired";
                    $description = "admin.domainsnewRegistration";
                    $type = "warning";
                }

                $title = __($title);
                $description = __($description);
                $alert = "<strong>" . $title . "</strong><br>" . $description;
            }
        } else {
            // infoBox($aInt->lang("domains", "registrarerror"), $domains->getLastError());
            $regError = AdminFunctions::infoBoxMessage(__("admin.domainsregistrarerror"), $domains->getLastError());
        }

        $pendingMsg = "";
        if ($request->get("pending") == 1 && $domainInformation instanceof \App\Helpers\Domain\Registrar\Domain) {
            $message = "admin.domainschangePending";
            $replacement = array("email" => $domainInformation->getRegistrantEmailAddress());
            if ($domainInformation->getDomainContactChangeExpiryDate()) {
                $message = "admin.domainschangePendingDate";
                $replacement["days"] = $domainInformation->getDomainContactChangeExpiryDate()->diffInDays();
            }

            $pendingMsg = AdminFunctions::infoBoxMessage(__("admin.domainsmodifyPending"), __($message, $replacement));
        }
        
        $contactsarray = array();
        $irtpFields = array();
        if ($success) {
            $result = Contact::selectRaw("id,firstname,lastname")
                            ->where("userid", $userid)
                            ->where("address1", "!=", "''")
                            ->orderBy("firstname", "ASC")
                            ->orderBy("lastname", "ASC")
                            ->get();

            if ($result) {
                $result = $result->toArray();
                foreach ($result as $key => $data) {
                    $contactsarray[] = array("id" => $data["id"], "name" => $data["firstname"] . " " . $data["lastname"]);
                }
            }

            if ($domainInformation && $domainInformation->isIrtpEnabled()) {
                $irtpFields = $domainInformation->getIrtpVerificationTriggerFields();
            }
        }

        // Template Vars
        $templatevars["userid"] = $userid;
        $templatevars["id"] = $domainid;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["domain"] = $domain;
        $templatevars["registrar"] = ucfirst($registrar);
        $templatevars["regError"] = $regError;
        $templatevars["alert"] = $alert;
        $templatevars["pendingMsg"] = $pendingMsg;
        $templatevars["regsuccess"] = $success;
        $templatevars["contactdetails"] = $contactdetails;
        $templatevars["contactsarray"] = $contactsarray;
        $templatevars["country"] = new Country();
        $templatevars["irtpFields"] = $irtpFields;
        $templatevars["domainInformation"] = $domainInformation;

        // dd($templatevars);

        return view('pages.clients.viewclients.clientdomain.clientdomaincontact', $templatevars);
    }

    public function savedomaincontact(Request $request)
    {
        $id = $request->domainid;
        $userid = $request->userid;

        $domains = new HelpersDomain();
        $domain_data = $domains->getDomainsDatabyID($id);
        $domainid = $domain_data["id"];

        $reDirVars = array();
        $reDirVars["domainid"] = $domainid;

        $result = $domains->saveContactDetails(new ClientClass($userid), $request->get("contactdetails") ?: array(), $request->get("wc"), $request->get("sel"));

        return $result;
        try {
            $result = $domains->saveContactDetails(new ClientClass($userid), $request->get("contactdetails") ?: array(), $request->get("wc"), $request->get("sel"));
            $reDirVars["success"] = true;
            $reDirVars["pending"] = false;
            if (isset($result["status"]) && $result["status"] == "pending") {
                $reDirVars["pending"] = true;
                $reDirVars["success"] = false;
            }
        } catch (\Exception $e) {
            $reDirVars["error"] = true;
            // WHMCS\Cookie::set("contactEditError", $e->getMessage());
            $editError = Sanitize::makeSafeForOutput($e->getMessage());
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.clientdomaincontact", ['userid' => $userid, 'domainid' => $id])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('admin.domainsregistrarerror', $editError));
        }

        $qparams = ['userid' => $userid, 'domainid' => $id];
        if (isset($reDirVars["pending"]) && $reDirVars["pending"]) {
            $qparams["pending"] = $reDirVars["pending"];
        }

        return redirect()
                ->route("admin.pages.clients.viewclients.clientdomain.clientdomaincontact", $qparams)
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage(__("admin.domainsmodifySuccess"), __("admin.domainschangesuccess")));
    }

    public function updateDomainContact (Request $request) {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email_address' => 'required|email|max:255',
            'address_1' => 'required|string|max:255',
            'address_number' => 'nullable|string|max:10',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'required|string|max:10',
            'country' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ];

        $id = $request->domainid;
        $domains = new HelpersDomain();
        $data = $domains->getDomainsDatabyID($id);
        $module = new \App\Module\Registrar();
        $userid = $data["userid"];
        $registrar = $data["registrar"];

        $qparams = ['userid' => $userid, 'domainid' => $id];

        // Validate input
        $validator = Validator::make($request->all(), $rules);
        
        // Validate fails return
        if ($validator->fails()) {
            $errors = implode('<br>', $validator->errors()->all());

            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.clientdomaincontact", $qparams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.<br>' . $errors));
        };

        $params = [
            'registrar' => $registrar,
            'domainid' => $id,
            'contact_id' => $request->input('contact_id'),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'company_name' => $request->input('company_name'),
            'email_address' => $request->input('email_address'),
            'address_1' => $request->input('address_1'),
            'address_number' => $request->input('address_number'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'postcode' => $request->input('postcode'),
            'country' => $request->input('country'),
            'phone_number' => $request->input('phone_number'),
            'fax_number' => $request->input('fax_number'),
        ];

        $module = new \App\Module\Registrar();
        $response = $module->RegUpdateContactDetails($params);    
        $result = json_decode($response->getContent(), true); // Convert JsonResponse to array

        if ($result["code"] == 200) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", $qparams)
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage("admin.success", __("admin.contactupdateregsuccess")));
        } else {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.clientdomaincontact", $qparams)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('admin.erroroccurred', $result["error"]));
        }
    }

    public function updatenameservers (Request $request) {
        $rules = [
            'ns1' => 'required|string|max:255',
            'ns2' => 'required|string|max:255',
            'ns3' => 'nullable|string|max:255',
            'ns4' => 'nullable|string|max:255',
            'ns5' => 'nullable|string|max:255',
        ];

        $customMessages = [
            'ns1.required' => 'Nameserver 1 field is required.',
            'ns2.required' => 'Nameserver 2 field is required.',
        ];

        $id = $request->input('id');
        $domains = new HelpersDomain();
        $data = $domains->getDomainsDatabyID($id);
        $module = new \App\Module\Registrar();
        $userid = $data["userid"];
        $registrar = $data["registrar"];

        $qparams = ['userid' => $userid, 'domainid' => $id];

        // Validate input
        $validator = Validator::make($request->all(), $rules, $customMessages);
        
        // Validate fails return
        if ($validator->fails()) {
            $errors = $validator->errors()->all();

            return response()->json([
                'errors' => $errors
            ], 422);
        };

        $params = [
            'registrar' => $registrar,
            'domainid' => $id,
            'ns1' => $request->input('ns1'),
            'ns2' => $request->input('ns2'),
            'ns3' => $request->input('ns3'),
            'ns4' => $request->input('ns4'),
            'ns5' => $request->input('ns5'),
        ];

        $module = new \App\Module\Registrar();
        $result = $module->RegUpdateNameservers($params);    
        // $result = json_decode($response->getContent(), true);

        return $result;
    }

    public function saveRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'domainid' => "required|integer|exists:App\Models\Domain,id",
            'action' => "required|string|in:register,transfer",
        ]);

        $userid = $request->userid;
        $id = $request->domainid;
        $action = $request->action;
        $qparams = ['userid' => $userid, 'domainid' => $id, "action" => $action];

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.register", $qparams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        define("NO_QUEUE", true);
        $emptyNameservers = true;
        $result = [];
        $domains = new HelpersDomain();
        $data = $domains->getDomainsDatabyID($id);
        $module = new \App\Module\Registrar();

        $domainid = $data["id"];    
        $userid = $data["userid"];
        $domain = $data["domain"];
        $orderid = $data["orderid"];
        $registrar = $data["registrar"];
        $registrationperiod = $data["registrationperiod"];
        
        $params = [];
        $domainparts = explode(".", $domain, 2);
        list($params["sld"], $params["tld"]) = $domainparts;
        
        $params["domainid"] = $domainid;
        $params["regperiod"] = $registrationperiod;
        $params["registrar"] = $registrar;
        $nsvals = [];
        $autonsdesc = "";

        for ($i = 1; $i <= 5; $i++) {
            $params["ns$i"] = $request->get("ns$i");
            if ($emptyNameservers && $params["ns$i"]) {
                $emptyNameservers = false;
            }
        }

        $params["transfersecret"] = $request->get("transfersecret");
        if ($emptyNameservers) {
            $result["error"] = __("admin.domainsnoNameservers");
        } else {
            if ($action == 'register') {
                $result = $module->RegRegisterDomain($params);
            } else {
                $result = $module->RegTransferDomain($params);
            }
        }

        if (isset($result["error"])) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.register", $qparams)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('admin.erroroccurred', $result["error"]));
        } 

        $qparams = ['userid' => $userid, 'domainid' => $id];
        if (isset($result["pending"])) {
            return redirect()
            ->route("admin.pages.clients.viewclients.clientdomain.index", $qparams)
            ->with('type', 'info')
            ->with('message', AdminFunctions::infoBoxMessage("admin.statuspending", $result["pendingMessage"]));
        } 
        
        $sendregisterconfirm = $request->get("sendregisterconfirm");

        if ($action == "register") {
            if ($sendregisterconfirm) {
                \App\Helpers\Functions::sendMessage("Domain Registration Confirmation", $domainid);
            }

            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", $qparams)
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage("admin.success", __("admin.domainsregsuccess")));
        } 

        if ($sendregisterconfirm) {
            \App\Helpers\Functions::sendMessage("Domain Transfer Initiated", $domainid);
        }

        return redirect()
                ->route("admin.pages.clients.viewclients.clientdomain.index", $qparams)
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage("admin.success", __("admin.domainstransuccess")));
    }

    public function filterDomain(Request $request)
    {
        $userid = $request->userid;
        $id = $request->domainid;

        return redirect()->route("admin.pages.clients.viewclients.clientdomain.index", [
            "userid" => $userid,
            "domainid" => $id,
        ]);
    }

    public function savedomain(Request $request)
    {
        $userid = $request->userid;
        $id = $request->domainid;
        $domain = trim($request->domain);
        $queryParams = ["userid" => $userid, "domainid" => $id];

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'domainid' => "required|integer|exists:App\Models\Domain,id",
        ]);
        
        if (!AdminFunctions::checkPermission("Edit Clients Domains")) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", $queryParams)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()));
        }
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", $queryParams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $domains = new HelpersDomain($request);
        $domain_data = $domains->getDomainsDatabyID($id);

        if (!$domain_data) { 
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientdomain.index", $queryParams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "admin.domainsdomainidnotfound"));
        }

        $currency = (new AdminFunctions())->getCurrency($userid);
        $addonsPricing = Pricing::where("type", "domainaddons")
                                ->where("currency", $currency["id"])
                                ->where("relid", 0)
                                ->first(["msetupfee", "qsetupfee", "ssetupfee"]);

        $conf = "success";
        $regperiod = (int) $request->get("regperiod");
        $recurringamount = $request->get("recurringamount");

        if ($domain_data["is_premium"]) {
            $regperiod = $domain_data["registrationperiod"];
        }

        $domaindnsmanagementprice = $addonsPricing->msetupfee * $regperiod;
        $domainemailforwardingprice = $addonsPricing->qsetupfee * $regperiod;
        $domainidprotectionprice = $addonsPricing->ssetupfee * $regperiod;
        $olddnsmanagement = $domain_data["dnsmanagement"];
        $oldemailforwarding = $domain_data["emailforwarding"];
        $oldidprotection = $domain_data["idprotection"];
        $olddonotrenew = $domain_data["donotrenew"];
        $dnsmanagement = (int) (bool) $request->get("dnsmanagement");
        $emailforwarding = (int) (bool) $request->get("emailforwarding");
        $idprotection = (int) (bool) $request->get("idprotection");
        $idProtectionInRequest = $request->has("idprotection");
        $donotrenew = (int) (bool) $request->get("donotrenew");
        $promoid = (int) $request->get("promoid");
        $oldlockstatus = $request->get("oldlockstatus");
        $lockstatus = $request->get("lockstatus");
        $newlockstatus = $lockstatus ? "locked" : "unlocked";
        $autorecalc = $request->get("autorecalc");
        $regdate = $request->get("regdate");
        $expirydate = $request->get("expirydate");
        $nextduedate = $request->get("nextduedate");
        $registrar = $request->get("registrar");
        $firstpaymentamount = $request->get("firstpaymentamount");
        $subscriptionid = $request->get("subscriptionid");
        $additionalnotes = $request->get("additionalnotes");
        $status = $request->get("domainstatus");
        $paymentmethod = $request->get("paymentmethod");
        
        $changelog = [];
        $logChangeFields = [
            "registrationdate" => "Registration Date", 
            "domain" => "Domain Name", 
            "firstpaymentamount" => "First Payment Amount", 
            "recurringamount" => "Recurring Amount", 
            "registrar" => "Registrar", 
            "registrationperiod" => "Registration Period", 
            "expirydate" => "Expiry Date", 
            "subscriptionid" => "Subscription Id", 
            "status" => "Status", 
            "nextduedate" => "Next Due Date", 
            "additionalnotes" => "Notes", 
            "paymentmethod" => "Payment Method", 
            "dnsmanagement" => "DNS Management", 
            "emailforwarding" => "Email Forwarding", 
            "idprotection" => "ID Protection", 
            "donotrenew" => "Do Not Renew", 
            "promoid" => "Promotion Code"
        ];

        if ($olddnsmanagement) {
            if (!$dnsmanagement) {
                $recurringamount -= $domaindnsmanagementprice;
                $conf = "removeddns";
            }
        } else {
            if ($dnsmanagement) {
                $recurringamount += $domaindnsmanagementprice;
                $conf = "addeddns";
            }
        }

        if ($oldemailforwarding) {
            if (!$emailforwarding) {
                $recurringamount -= $domainemailforwardingprice;
                $conf = "removedemailforward";
            }
        } else {
            if ($emailforwarding) {
                $recurringamount += $domainemailforwardingprice;
                $conf = "addedemailforward";
            }
        }

        if ($idProtectionInRequest) {
            if ($oldidprotection) {
                if (!$idprotection) {
                    $recurringamount -= $domainidprotectionprice;
                    $conf = "removedidprotect";
                }
            } else {
                if ($idprotection) {
                    $recurringamount += $domainidprotectionprice;
                    $conf = "addedidprotect";
                }
            }
        }

        if ($autorecalc) {
            $domainparts = explode(".", $domain, 2);
            if ($domain_data["is_premium"]) {
                // NOTE: Something weird with $recurringamount variable
                $recurringamount = (double) DomainsExtra::where("id", $domain_data["id"])->where("name", "registrarRenewalCostPrice")->value("value");
                // $recurringamount = Format::ConvertCurrency($recurringamount["price"], $recurringamount["currency"], $currency["id"]);
                $hookReturns = Hooks::run_hook("PremiumPriceRecalculationOverride", [
                    "domainName" => $domain, 
                    "tld" => $domainparts[1], 
                    "sld" => $domainparts[0], 
                    "renew" => $recurringamount
                ]);
                $skipMarkup = false;

                foreach ($hookReturns as $hookReturn) {
                    if (array_key_exists("renew", $hookReturn)) {
                        $recurringamount = $hookReturn["renew"];
                    }

                    if (array_key_exists("skipMarkup", $hookReturn) && $hookReturn["skipMarkup"] === true) {
                        $skipMarkup = true;
                    }
                }

                if (!$skipMarkup) {
                    $recurringamount *= 1 + DomainpricingPremium::markupForCost($recurringamount) / 100;
                }
            } else {
                $temppricelist = (new HelpersDomain())->GetTLDPriceList("." . $domainparts[1], "", true, $userid);
                $recurringamount = $temppricelist[$regperiod]["renew"];
            }

            if ($dnsmanagement) {
                $recurringamount += $domaindnsmanagementprice;
            }

            if ($emailforwarding) {
                $recurringamount += $domainemailforwardingprice;
            }

            if ($idProtectionInRequest && $idprotection || !$idProtectionInRequest && $oldidprotection) {
                $recurringamount += $domainidprotectionprice;
            }

            if ($promoid) {
                $recurringamount -= (new HelpersDomain())->recalcPromoAmount("D." . $domainparts[1], $userid, $id, $regperiod . "Years", $recurringamount, $promoid);
            }
        }

        $changes = array();
        foreach ($logChangeFields as $fieldName => $displayName) {
            // $newValue = ${$fieldName};
            $newValue = isset(${$fieldName}) ? ${$fieldName} : "";

            if ($fieldName == "registrationdate") {
                $newValue = $regdate;
            }

            if ($fieldName == "registrationperiod") {
                $newValue = $regperiod;
            }
        
            $oldValue = $domain_data[$fieldName];
            if (in_array($fieldName, ["dnsmanagement", "emailforwarding", "idprotection", "donotrenew"]) && $newValue != $oldValue) {
                if ($newValue && !$oldValue) {
                    $changelog[] = (string) $displayName . " Enabled";
                    
                    if ($fieldName == "donotrenew") {
                        // TODO
                        // disableAutoRenew($id);
                    }
                } else {
                    
                    if (!$newValue && $oldValue) {
                        $changelog[] = (string) $displayName . " Disabled";
                    }
                }

                $changes[$fieldName] = $newValue;
                continue;
            }

            if (in_array($fieldName, ["promoid", "additionalnotes"]) && $newValue != $oldValue) {
                $changelog[] = (string) $displayName . " Changed";
                $changes[$fieldName] = $newValue;
            }

            if (in_array($fieldName, ["registrationdate", "expirydate", "nextduedate"])) {
                $newValue = (new SystemHelper())->toMySQLDate($newValue);
            }

            if ($newValue != $oldValue) {
                $changelog[] = (string) $displayName . " changed from '" . $oldValue . "' to '" . $newValue . "'";
                $changes[$fieldName] = $newValue;
                
                if ($fieldName == "nextduedate") {
                    $changes["nextinvoicedate"] = $newValue;
                }

                if ($fieldName == "expirydate") {
                    $changes["reminders"] = "";
                }
            }
        }

        if (0 < count($changes)) {
            // dd($changes);
            Domain::where("id", $id)->update($changes);
            LogActivity::Save("Modified Domain - " . implode(", ", $changelog) . " - User ID: $userid - Domain ID: $id", $userid);
        }

        if (isset($domainfield) && is_array($domainfield)) {
            $additflds = new AdditionalFields();
            $additflds->setDomain($domain)->setDomainType($domain_data["type"])->setFieldValues($domainfield)->saveToDatabase($id, false);
        }

        // loadRegistrarModule($registrar);
        // if (function_exists($registrar . "_AdminDomainsTabFieldsSave")) {
        //     $domainparts = explode(".", $domain, 2);
        //     $params = array();
        //     $params["domainid"] = $id;
        //     list($params["sld"], $params["tld"]) = $domainparts;
        //     $params["regperiod"] = $regperiod;
        //     $params["registrar"] = $registrar;
        //     $fieldsarray = call_user_func($registrar . "_AdminDomainsTabFieldsSave", $params);
        // }

        $module = new \App\Module\Registrar();
        $domainparts = explode(".", $domain, 2);
        $params = array();
        $params["domainid"] = $id;
        list($params["sld"], $params["tld"]) = $domainparts;
        $params["regperiod"] = $regperiod;
        $params["registrar"] = $registrar;
        $fieldsarray = $module->loadModule($registrar, "AdminDomainsTabFieldsSave", $params);

        Hooks::run_hook("AdminClientDomainsTabFieldsSave", $_REQUEST);
        Hooks::run_hook("DomainEdit", array("userid" => $userid, "domainid" => $id));
        $domainsavetemp = array(
            "ns1" => $request->ns1, 
            "ns2" => $request->ns2, 
            "ns3" => $request->ns3, 
            "ns4" => $request->ns4, 
            "ns5" => $request->ns5, 
            "oldns1" => $request->oldns1, 
            "oldns2" => $request->oldns2, 
            "oldns3" => $request->oldns3, 
            "oldns4" => $request->oldns4, 
            "oldns5" => $request->oldns5, 
            "defaultns" => $request->defaultns, 
            "newlockstatus" => $newlockstatus, 
            "oldlockstatus" => $oldlockstatus, 
            "oldidprotection" => $oldidprotection, 
            "idprotection" => $idProtectionInRequest ? $idprotection : $oldidprotection
        );

        $nsChangeInfo = "";
        if ($request->oldns1 != $request->ns1 || $request->oldns2 != $request->ns2 || $request->oldns3 != $request->ns3 || $request->oldns4 != $request->ns4 || $request->oldns5 != $request->ns5 || $request->defaultns) {
            $nameservers = $request->defaultns ? $domains->getDefaultNameservers() 
                                        : $domainsavetemp;
            $params = array_merge($params, $nameservers);
            $success = $domains->moduleCall2("SaveNameservers", $params);
            
            if (!$success) {
                $nsChangeInfo = AdminFunctions::infoBoxMessage(__("admin.domainsnschangefail"), $domains->getLastError());
            } else {
                $nsChangeInfo = AdminFunctions::infoBoxMessage(__("admin.domainsnschangesuccess"), __("admin.domainsnschangeinfo"));
            }
        }

        // WHMCS\Session::set("domainsavetemp", $domainsavetemp);
        // redir("userid=" . $userid . "&id=" . $id . "&conf=" . $conf);
        // TODO: return response
        $conf = $this->confMssage($conf);
        return redirect()
                ->route("admin.pages.clients.viewclients.clientdomain.index", $queryParams)
                ->with('type', 'success')
                ->with('domainsavetemp', $domainsavetemp)
                ->with('conf', $conf)
                ->with('message', __("<b>Well Done!</b> The data updated successfully.<br>Info: $conf<br>$nsChangeInfo"));
    }

    private function confMssage($conf)
    {
        $message = "";

        switch ($conf) {
            case "success":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.changesuccessdesc"));
                break;
            case "addeddns":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsdnsmanagementadded"));
                break;
            case "addedemailforward":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsemailforwardingadded"));
                break;
            case "addedidprotect":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsidprotectionadded"));
                break;
            case "removeddns":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsdnsmanagementremoved"));
                break;
            case "removedemailforward":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsemailforwardingremoved"));
                break;
            case "removedidprotect":
                $message = AdminFunctions::infoBoxMessage(__("admin.changesuccess"), __("admin.domainsidprotectionremoved"));
                break;
            case "domainreleasedanddeleted":
                // $successMessage = WHMCS\Session::getAndDelete("DomainReleaseInfo");
                $successMessage = "";
                $message = AdminFunctions::infoBoxMessage(__("admin.domainsreleasesuccess"), $successMessage);
                break;
        }

        return $message;
    }

    public function delete(Request $request)
    {
        if (!AdminFunctions::checkPermission("Delete Clients Domains")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $userid = $request->userid;
        $id = $request->id;

        $domain = Domain::find($id);
        if (!$domain) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh Noooo!</b>', 'Invalid ID.'),
            ]);
        }
        
        $domain->delete();
        Hooks::run_hook("DomainDelete", array("userid" => $userid, "domainid" => $id));
        LogActivity::Save("Deleted Domain - User ID: $userid - Domain ID: $id", $userid);

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
        ]);
    }

    public function showClientDomain($id)
   {
       // Ambil data domain berdasarkan ID
       $domain = Domain::find($id);
       if (!$domain) {
           abort(404, 'Domain not found');
       }

       // Pecah nama domain menjadi SLD dan TLD
       $domainparts = explode('.', $domain->name, 2);

       // Kirim data ke view
       return view('pages.clients.viewclients.clientdomain.index', compact('domainparts', 'domain'));
   }

    public function regCommand(Request $request)
    {
        $regaction = $request->get("regaction");    // Action
        $id = $request->get("id");                  // Domain ID
        $userid = $request->get("userid");          // User ID
        $transtag = $request->get("transtag");      // Transtag
        
        $domains = new HelpersDomain($request);
        $domain_data = $domains->getDomainsDatabyID($id);
        if (!$domain_data) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        $domainregistraractions = AdminFunctions::checkPermission("Perform Registrar Operations") && $domains->getModule() ? true : false;
        if ($regaction == "delete") {
            $domainregistraractions = true;
        }

        if (!$domainregistraractions) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $domain = $domain_data["domain"]; 
        $registrationperiod = $domain_data["registrationperiod"];
        $registrar = $domain_data["registrar"];
        $regtype = $domain_data["type"];

        $module = new \App\Module\Registrar();
        $domainparts = explode(".", $domain, 2);

        $params = [];
        list($params["sld"], $params["tld"]) = $domainparts;
        $params["domainid"] = $id;
        $params["regperiod"] = $registrationperiod;
        $params["registrar"] = $registrar;
        $params["regtype"] = $regtype;

        if ($regaction == "lockopenprovider") {
            $response = Http::post(route('pages.clients.viewclients.clientdomain.lockDomainOpenprovider'), $params);
            return $this->handleResponse($response);
        }
    
        if ($regaction == "unlockopenprovider") {
            $response = Http::post(route('pages.clients.viewclients.clientdomain.unlockDomainOpenprovider'), $params);
            return $this->handleResponse($response);
        }

        // Action Renew
        if ($regaction == "renew") {
            
            $values = $module->RegRenewDomain($params);
        
            return $values;
        }

        if ($regaction == "lock") {
            $response = $module->RegLockDomain($params);
            $result = json_decode($response->getContent(), true);

            if ($result["code"] == 200) {
                if ($result["data"] == 'Domain already locked!') {
                    return ResponseAPI::Error([
                        'message' => AdminFunctions::infoBoxMessage(__("admin.domainslockfailed"), $result["data"]),
                    ]);
                } else {
                    return ResponseAPI::Success([
                        'message' => AdminFunctions::infoBoxMessage(__("admin.domainslocksuccess"), $result["data"]),
                    ]);
                }
            } else {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainslockfailed"), $result["error"]),
                ]);
            
            }
        }

        if ($regaction == "unlock") {
            $response = $module->RegUnlockDomain($params);
            $result = json_decode($response->getContent(), true);

            if ($result["code"] == 200) {
                if ($result["data"] == 'Domain already unlocked!') {
                    return ResponseAPI::Error([
                        'message' => AdminFunctions::infoBoxMessage(__("admin.domainsunlockfailed"), $result["data"]),
                    ]);
                } else {
                    return ResponseAPI::Success([
                        'message' => AdminFunctions::infoBoxMessage(__("admin.domainsunlocksuccess"), $result["data"]),
                    ]);
                }
            } else {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsunlockfailed"), $result["error"]),
                ]);
            
            }
        }

        // Action GetEPP
        if ($regaction == "eppcode") {
            $values = $module->RegGetEPPCode($params);
            
            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainseppfailed"), $values["error"]),
                ]);
            } 

            $successmessage = AdminFunctions::infoBoxMessage(__("admin.domainsepprequest"), __("client.domaingeteppcodeemailconfirmation"));
            if (isset($values["eppcode"])) {
                $successmessage = AdminFunctions::infoBoxMessage(__("admin.domainsepprequest"), __("client.domaingeteppcodeis") . " " .$values["eppcode"]);
            }

            return ResponseAPI::Success([
                'message' => $successmessage
            ]);   
        }

        if ($regaction == "eppcodePopup") {
            $values = $module->RegGetEPPCode($params);
        
           return $values;
        }

        if ($regaction == "getDomainDetail") {
            $values = $module->RegGetDomainDetail($params);
        
           return $values;
        }

        // Action RequestDelete
        if ($regaction == "reqdelete") {
            $values = $module->RegRequestDelete($params);
            
            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsdeletefailed"), $values["error"]),
                ]);
            }

            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage(__("admin.domainsdeletesuccess"), __("admin.domainsdeleteinfo")),
            ]);
        }
        
        // Action ReleaseDomain
        if ($regaction == "release") {
            $params["transfertag"] = $transtag;
            $values = $module->RegReleaseDomain($params);

            if (array_key_exists("deleted", $values) && isset($values["deleted"]) && $values["deleted"]) {
                $successmessage = __("admin.domainsreleasedAndDeleted", ["domain" => $domain, "tag" => $transtag]);
                return ResponseAPI::Success([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsreleasesuccess"), $successmessage),
                ]);
            }

            $successmessage = str_replace("%s", $transtag, __("admin.domainsreleaseinfo"));
            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsreleasefailed"), $values["error"]),
                ]);
            } 

            Domain::where("id", $id)->update(["status" => HelpersDomain::TRANSFERRED_AWAY]);
            // $domainstatus = HelpersDomain::TRANSFERRED_AWAY;
            // $domain_data["status"] = HelpersDomain::TRANSFERRED_AWAY;

            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage(__("admin.domainsreleasesuccess"), $successmessage),
            ]);
        }

        // Action IDProtectToggle
        if ($regaction == "idtoggle") {
            $currency = (new AdminFunctions())->getCurrency($userid);
            $addonsPricing = Pricing::where("type", "domainaddons")
                                ->where("currency", $currency["id"])
                                ->where("relid", 0)
                                ->first(["msetupfee", "qsetupfee", "ssetupfee"]);

            $domaindnsmanagementprice = $addonsPricing->msetupfee * $domain_data["registrationperiod"];
            $domainemailforwardingprice = $addonsPricing->qsetupfee * $domain_data["registrationperiod"];
            $domainidprotectionprice = $addonsPricing->ssetupfee * $domain_data["registrationperiod"];

            $params["protectenable"] = !(bool) (int) $domain_data["idprotection"];
            $values = $module->RegIDProtectToggle($params);
            
            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsidprotectfailed"), $values["error"]),
                ]);
            } 

            $idprotection = !(bool) (int) $domain_data["idprotection"];
            $recurringamount = $domain_data["recurringamount"] - $domainidprotectionprice;
            if ($idprotection) {
                $recurringamount = $domain_data["recurringamount"] + $domainidprotectionprice;
            }

            $updateArray = ["idprotection" => $idprotection, "recurringamount" => $recurringamount];
            Domain::where("id", $domain_data["id"])->update($updateArray);
            
            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage(__("admin.domainsidprotectsuccess"), __("admin.domainsidprotectinfo")),
            ]);
        }

        // Action ResendIRTPVerificationEmail
        if ($regaction == "resendirtpemail" && $domains->hasFunction("ResendIRTPVerificationEmail")) {
            $values = $domains->moduleCall("ResendIRTPVerificationEmail");
            
            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsresendNotification"), $values["error"]),
                ]);
            }

            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage(__("admin.domainsresendNotification"), __("admin.domainsresendNotificationSuccess")),
            ]);
        }

        // Action Custom
        if ($regaction == "custom") {
            // TODO: $ac is From admin action button field
            $ac = "AdminCustomButtonArray";
            $values = $domains->getRegistrarModule()->Regcallfunction($params, $ac);

            if (isset($values["error"])) {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage(__("admin.domainsregistrarerror"), $values["error"]),
                ]);
            } 

            if (isset($values["message"]) && !$values["message"]) {
                $values["message"] = __("admin.domainschangesuccess");
            }

            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage(__("admin.domainschangesuccess"), $values["message"]),
            ]);
        }

        // Action Delete
        if ($regaction == "delete") {
            return $this->delete($request);
        }

        return ResponseAPI::Error([
            'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Action undefined!"),
        ]);
    }

    private function handleResponse($response)
    {
        \Log::info('API Response:', $response->json());

        $result = $response->json();

        if ($response->successful()) {
            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage('Success', $result['message']),
            ]);
        } else {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('Error', $result['message']),
            ]);
        }
    }

    public function sslCheckAdminArea(Request $request)
    {
        $domain = trim($request->get("domain"));
        $userId = $request->get("userid");
        $sslStatus = Sslstatus::factory($userId, $domain)->syncAndSave();
        $response = [
            "image" => $sslStatus->getImagePath(), 
            "tooltip" => $sslStatus->getTooltipContent(), 
            "class" => $sslStatus->getClass()
        ];

        if ($request->get("details")) {
            $issuerName = "";
            if ($sslStatus->issuer_name) {
                $issuerName = $sslStatus->issuer_org;
                if (!$issuerName) {
                    $issuerName = $sslStatus->issuer_name;
                }
            }

            $response["issuerName"] = $issuerName;
            $expiryDate = $sslStatus->expiry_date;
            if ($expiryDate) {
                $expiryDate = \App\Helpers\Carbon::parse($expiryDate)->endOfDay()->toAdminDateTimeFormat();
            } else {
                $expiryDate = "-";
            }

            $response["expiryDate"] = $expiryDate;
        }

        return ResponseAPI::Success([
            'data' => $response,
        ]);
    }

    public function sslCheckClientArea(Request $request)
    {
        $domain = trim($request->get("domain"));
        $userId = auth()->user()->id;
        $type = $request->get("type", "service");

        if (!in_array($type, array("domain", "service"))) {
            $type = "service";
        }

        $table = "{$this->prefix}hosting";
        $statusField = "domainstatus";
        if ($type == "domain") {
            $table = "{$this->prefix}domains";
            $statusField = "status";
        }
        
        $activeDomain = \DB::table($table)
                            ->where("domain", $domain)
                            ->where("userid", $userId)
                            ->whereIn($statusField, ["Active", "Completed", "Grace"])
                            ->pluck("id");

        if ($activeDomain) {
            $sslStatus = Sslstatus::factory($userId, $domain)->syncAndSave();
            $response = [
                "image" => $sslStatus->getImagePath(), 
                "tooltip" => $sslStatus->getTooltipContent(), 
                "class" => $sslStatus->getClass()
            ];
        } else {
            $response = ["invalid" => true];

            return ResponseAPI::Error([
                'message' => "No Domain or Invalid Product/Service",
                'data' => $response,
            ]);
        }

        return ResponseAPI::Success([
            'message' => "success",
            'data' => $response,
        ]);
    }

}