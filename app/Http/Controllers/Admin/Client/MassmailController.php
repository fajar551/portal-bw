<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Customfield;
use App\Helpers\Database;
use App\Helpers\Domains as HelpersDomain;
use App\Helpers\Emailer;
use App\Helpers\EmailSubscription;
use App\Helpers\FileUploader;
use App\Helpers\Functions;
use App\Helpers\Hooks;
use App\Helpers\Message;
use App\Helpers\ResponseAPI;
use App\Helpers\Sanitize;
use App\Helpers\ViewHelper;

// Models
use App\Models\Client;
use App\Models\Server;
use App\Models\Affiliate;
use App\Models\Domain;
use App\Models\Email;
use App\Models\Emailtemplate;
use App\Models\Hosting;
use App\Models\Order;

// Traits
use App\Traits\DatatableFilter;

class MassmailController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $templatevars = [];
        
        $clientgroups = ClientHelper::getClientGroups();
        $customfields = Customfield::getCustomFields("client", "", "", true);
        $countryHelper = new \App\Helpers\Country();
        $clientCountries = Client::distinct("country")->orderBy("country")->pluck("country");        
        $clientLanguages = Client::distinct("language")->orderBy("language")->pluck("language");        
        $productsList = \App\Helpers\Product::getProducts();
        $serverList = Server::orderBy("name")->get()->toArray();
        $domainStatuses = (new HelpersDomain())->translatedDropdownOptions();

        $countries = [];
        foreach ($clientCountries as $countryCode) {
            if ($countryHelper->isValidCountryCode($countryCode)) {
                $countries[$countryCode] = $countryHelper->getName($countryCode);
            }
        }

        $languages = [];
        foreach ($clientLanguages as $language) {
            if ($language) {
                $languages[$language] = ucfirst($language);
            }
        }

        $templatevars = [
            "clientgroups" => $clientgroups,
            "customfields" => $customfields,
            "countries" => $countries,
            "languages" => $languages,
            "productsList" => $productsList,
            "serverList" => $serverList,
            "domainStatuses" => $domainStatuses,
        ];

        return view('pages.clients.massmail.index', $templatevars);
    }

    public function sendmessage(Request $request)
    {
        $pfx = $this->prefix;
        $type = $request->get("type");
        $clientstatus = $request->get("clientstatus");
        $clientgroup =  $request->get("clientgroup");
        $clientcountry =  $request->get("clientcountry");
        $clientlanguage =  $request->get("clientlanguage");
        $productids =  $request->get("productids");
        $productstatus =  $request->get("productstatus");
        $server =  $request->get("server");
        $addonids =  $request->get("addonids");
        $addonstatus =  $request->get("addonstatus");
        $domainstatus =  $request->get("domainstatus");
        $emailtype = $request->get("emailtype");
        $customfield = $request->get("customfield");
        $sendforeach = $request->get("sendforeach");
        $userInput_massmailquery = $request->get("massmailquery");
        $multiple = $request->get("multiple");
        $resend = $request->get("resend");
        $massmailquery = $query = $safeStoredQuery = $queryMadeFromEmailType = $token = $message = NULL;

        global $CONFIG;
        $todata = [];
        $query = "";
        if (!$type) { $type = "general"; }

        $queryMadeFromEmailType = "";
        if ($type == "massmail") {
            $clientstatus = Database::db_build_in_array($clientstatus);
            $clientgroup =  Database::db_build_in_array($clientgroup);
            $clientcountry =  Database::db_build_in_array($clientcountry, true);
            $clientlanguage =  Database::db_build_in_array($clientlanguage, true);
            $productids =  Database::db_build_in_array($productids);
            $productstatus =  Database::db_build_in_array($productstatus);
            $server =  Database::db_build_in_array($server);
            $addonids =  Database::db_build_in_array($addonids);
            $addonstatus =  Database::db_build_in_array($addonstatus);
            $domainstatus =  Database::db_build_in_array($domainstatus);
        
            if ($emailtype == "General") {
                $type = "general";
                $query = "SELECT id,id AS userid,{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email FROM {$pfx}clients WHERE id!=''";

                if ($clientstatus) { $query .= " AND {$pfx}clients.status IN (" . $clientstatus . ")"; } 
                if ($clientgroup) { $query .= " AND {$pfx}clients.groupid IN (" . $clientgroup . ")"; } 
                if ($clientcountry) { $query .= " AND {$pfx}clients.country IN (" . $clientcountry . ")"; } 
                if ($clientlanguage) { $query .= " AND {$pfx}clients.language IN (" . $clientlanguage . ")"; } 
                if (is_array($customfield)) {
                    foreach ($customfield as $k => $v) {
                        if ($v) {
                            if ($v == "cfon") { $v = "on"; } 
                            if ($v == "cfoff") {
                                $query .= " AND ((SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1)='' OR (SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1) IS NULL)";
                            } else {
                                $query .= " AND (SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1)='" . Database::db_escape_string($v) . "'";
                            }
                        }
                    }
                }
            } else if ($emailtype == "Product/Service") {
                $type = "product";
                $query = "SELECT {$pfx}hosting.id,{$pfx}hosting.userid,{$pfx}hosting.domain,{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email FROM {$pfx}hosting INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}hosting.userid INNER JOIN {$pfx}products ON {$pfx}products.id={$pfx}hosting.packageid WHERE {$pfx}hosting.id!=''";

                if ($productids) { $query .= " AND {$pfx}products.id IN (" . $productids . ")"; }
                if ($productstatus) { $query .= " AND {$pfx}hosting.domainstatus IN (" . $productstatus . ")"; }
                if ($server) { $query .= " AND {$pfx}hosting.server IN (" . $server . ")"; }
                if ($clientstatus) { $query .= " AND {$pfx}clients.status IN (" . $clientstatus . ")"; }
                if ($clientgroup) { $query .= " AND {$pfx}clients.groupid IN (" . $clientgroup . ")"; }
                if ($clientcountry) { $query .= " AND {$pfx}clients.country IN (" . $clientcountry . ")"; }
                if ($clientlanguage) { $query .= " AND {$pfx}clients.language IN (" . $clientlanguage . ")"; }
                if (is_array($customfield)) {
                    foreach ($customfield as $k => $v) {
                        if ($v) { $query .= " AND (SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1)='" . Database::db_escape_string($v) . "'"; }
                    }
                }
            } else if ($emailtype == "Addon") {
                $type = "addon";
                $query = "SELECT {$pfx}hosting.id, {$pfx}hosting.userid, {$pfx}hosting.domain, {$pfx}clients.firstname, {$pfx}clients.lastname, {$pfx}clients.email, {$pfx}hostingaddons.id as aid FROM {$pfx}hosting INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}hosting.userid INNER JOIN {$pfx}hostingaddons ON {$pfx}hostingaddons.hostingid = {$pfx}hosting.id WHERE {$pfx}hostingaddons.id!=''";

                if ($addonids) { $query .= " AND {$pfx}hostingaddons.addonid IN (" . $addonids . ")"; }
                if ($addonstatus) { $query .= " AND {$pfx}hostingaddons.status IN (" . $addonstatus . ")"; }
                if ($clientstatus) { $query .= " AND {$pfx}clients.status IN (" . $clientstatus . ")"; }
                if ($clientgroup) { $query .= " AND {$pfx}clients.groupid IN (" . $clientgroup . ")"; }
                if ($clientcountry) { $query .= " AND {$pfx}clients.country IN (" . $clientcountry . ")"; }
                if ($clientlanguage) { $query .= " AND {$pfx}clients.language IN (" . $clientlanguage . ")"; }
                if (is_array($customfield)) {
                    foreach ($customfield as $k => $v) {
                        if ($v) {
                            $query .= " AND (SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1)='" . Database::db_escape_string($v) . "'";
                        }
                    }
                }
            } else if ($emailtype == "Domain") {
                $type = "domain";
                $query = "SELECT {$pfx}domains.id,{$pfx}domains.userid,{$pfx}domains.domain,{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email FROM {$pfx}domains INNER JOIN {$pfx}clients ON {$pfx}clients.id={$pfx}domains.userid WHERE {$pfx}domains.id!=''";

                if ($domainstatus) { $query .= " AND {$pfx}domains.status IN (" . $domainstatus . ")"; }
                if ($clientstatus) { $query .= " AND {$pfx}clients.status IN (" . $clientstatus . ")"; }
                if ($clientgroup) { $query .= " AND {$pfx}clients.groupid IN (" . $clientgroup . ")"; }
                if ($clientcountry) { $query .= " AND {$pfx}clients.country IN (" . $clientcountry . ")"; }
                if ($clientlanguage) { $query .= " AND {$pfx}clients.language IN (" . $clientlanguage . ")"; }
                if (is_array($customfield)) {
                    foreach ($customfield as $k => $v) {
                        if ($v) {
                            $query .= " AND (SELECT value FROM {$pfx}customfieldsvalues WHERE fieldid='" . Database::db_escape_string($k) . "' AND relid={$pfx}clients.id LIMIT 1)='" . Database::db_escape_string($v) . "'";
                        }
                    }
                }
            }

            $queryMadeFromEmailType = $query;
        }
        
        if ($queryMadeFromEmailType || $userInput_massmailquery) {
            if ($queryMadeFromEmailType) {
                $massmailquery = $queryMadeFromEmailType;
            } else if (!$queryMadeFromEmailType && $userInput_massmailquery) {
                try {
                    $massmailquery = session()->get("massmailquery");
                    $massmailquery = Crypt::decryptString($massmailquery);
                } catch (\Throwable $th) {
                    $massmailquery = "";
                }
            } else {
                $massmailquery = "";
            }

            /// Process if $massmailquery not empty 
            if ($massmailquery) {
                $useridsdone = array();
                $result = \DB::select($massmailquery);
                foreach ($result as $data) {
                    if ($sendforeach || !$sendforeach && !in_array($data->userid, $useridsdone)) {
                        $temptodata = (string) $data->firstname . " " . $data->lastname;

                        if (isset($data->domain) && $data->domain) {
                            $temptodata .= " - " . $data->domain;
                        }

                        $temptodata .= " &lt;" . $data->email . "&gt;";
                        $todata[] = $temptodata;
                        $useridsdone[] = $data->userid;
                    }
                }

                // Save $massmailquery to session for future usage and encrypt to make it more secure
                $massmailquery = Crypt::encryptString($massmailquery);
                session()->put("massmailquery", $massmailquery);
            }
        } else if ($multiple) {
            $selectedclients = $request->get("selectedclients");

            if ($type == "order") {
                $clientslist = Order::distinct("userid")->whereIn("id", $selectedclients)->pluck("userid")->toArray() ?? [];
                $selectedclients = array_unique($clientslist);

                $type = "general";
            }

            foreach ($selectedclients as $id) {
                $todata[] = $this->getToData($type, $id, "string"); 
            }

            /*
            TODO: Use this script if @getToData() return wrong result
            if ($type == "general") {
                foreach ($selectedclients as $id) {
                    $result = Client::find($id);
                    if ($result) {
                        $data = $result->toArray();
                        $todata[] = (string)$data["firstname"] . " " . $data["lastname"] . " &lt;" . $data["email"] . "&gt;";
                    }
                }
            } else if ($type == "product") {
                foreach ($selectedclients as $id) {
                   $result = Hosting::selectRaw("{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email,{$pfx}hosting.domain")
                                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid")
                                    ->where("{$pfx}hosting.id", $id)
                                    ->first();

                    if ($result) {
                        $data = $result->toArray();
                        $todata[] = (string)$data["firstname"] . " " . $data["lastname"] . " - " . $data["domain"] . " &lt;" . $data["email"] . "&gt;";
                    }
                }
            } else if ($type == "domain") {
                foreach ($selectedclients as $id) {
                    $result = Domain::selectRaw("{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email,{$pfx}domains.domain")
                                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}domains.userid")
                                    ->where("{$pfx}domains.id", $id)
                                    ->first();
                    
                    if ($result) {
                        $data = $result->toArray();
                        $todata[] = (string)$data["firstname"] . " " . $data["lastname"] . " - " . $data["domain"] . " &lt;" . $data["email"] . "&gt;";
                    }
                }
            } else if ($type == "affiliate") {
                foreach ($selectedclients as $id) {
                    $result = Affiliate::selectRaw("{$pfx}clients.firstname,{$pfx}clients.lastname,{$pfx}clients.email")
                                    ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}affiliates.clientid")
                                    ->where("{$pfx}affiliates.id", $id)
                                    ->first();

                    if ($result) {
                        $data = $result->toArray();
                        $todata[] = (string)$data["firstname"] . " " . $data["lastname"] . " - " . $data["domain"] . " &lt;" . $data["email"] . "&gt;";
                    }
                }
            }
            */
        } else {
            $id = (int) $request->get("id");
            if ($resend) {
                $emailid = $request->emailid;
                $email = Email::find($emailid);

                if (!$email) {
                    return redirect()
                            ->back()
                            ->with('type', 'danger')
                            ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'));
                }

                $data = $email->toArray();
                $id = $data["userid"];
                $subject = $data["subject"];
                $message = $data["message"];
                
                $message = str_replace("<p><a href=\"" . $CONFIG["Domain"] . "\" target=\"_blank\"><img src=\"" ."#" . "\" alt=\"" . $CONFIG["CompanyName"] . "\" border=\"0\"></a></p>", "", $message);
                $message = str_replace("<p><a href=\"" . $CONFIG["Domain"] . "\" target=\"_blank\"><img src=\"" ."#" . "\" alt=\"" . $CONFIG["CompanyName"] . "\" border=\"0\" /></a></p>", "", $message);
                $message = str_replace(Sanitize::decode($CONFIG["EmailGlobalHeader"]), "", $message);
                $message = str_replace(Sanitize::decode($CONFIG["EmailGlobalFooter"]), "", $message);
        
                $headerMarkerPos = strpos($message, Message::HEADER_MARKER);
                if ($headerMarkerPos !== false) {
                    $message = substr($message, $headerMarkerPos + strlen(Message::HEADER_MARKER));
                }
        
                $footerMarkerPos = strpos($message, Message::FOOTER_MARKER);
                if ($footerMarkerPos !== false) {
                    $message = substr($message, 0, $footerMarkerPos);
                }
        
                $styleend = strpos($message, "</style>");
                if ($styleend !== false) {
                    $message = trim(substr($message, $styleend + 8));
                }
                
                $type = "general";
            }

            $todata = $this->getToData($type, $id);
        }

        $numRecipients = count($todata);
        $noRecipent = false;
        if (!$numRecipients) {
           $noRecipent = AdminFunctions::infoBoxMessage(__("admin.sendmessagenoreceiptients"), __("admin.sendmessagenoreceiptientsdesc"));
           $request->session()->flash('type', 'danger');
           $request->session()->flash('message', $noRecipent);
        }

        $templates = Emailtemplate::where("type", ($type != "general" ? $type : "general"))->where("language", "")
                                    ->orderBy("custom")
                                    ->orderby("name")
                                    ->get();

        $customfields = Hooks::run_hook("EmailTplMergeFields", ["type" => $type]);
 
        // Blade template vars
        $templatevars["CONFIG"] = $CONFIG;
        $templatevars["subject"] = $subject ?? "";
        $templatevars["mailmessage"] = $message ?? "";
        $templatevars["type"] = $type;
        $templatevars["noRecipent"] = $noRecipent;
        $templatevars["numRecipients"] = $numRecipients;
        $templatevars["todata"] = $todata;
        $templatevars["fromname"] = $fromname ?? $CONFIG["CompanyName"];
        $templatevars["fromemail"] = $fromemail ?? $CONFIG["Email"];
        $templatevars["multiple"] = $multiple;
        $templatevars["sendforeach"] = $sendforeach;
        $templatevars["massmailquery"] = $massmailquery ? true : false;
        $templatevars["selectedclients"] = $selectedclients ?? [];
        $templatevars["id"] = $id ?? 0;
        $templatevars["templates"] = $templates;
        $templatevars["customfields"] = $customfields;

        return view('pages.clients.massmail.sendmessage.index', $templatevars);
    }

    public function getToData($type, $id, $returnAs = "array")
    {
        $todata = [];
        $pfx = $this->prefix;

        if ($type == "general") {
            $result = Client::find($id);
            if ($result) {
                $data = $result->toArray();
                if ($data["email"]) {
                    $todata[] = (string) $data["firstname"] . " " . $data["lastname"] . " &lt;" . $data["email"] . "&gt;";
                }
            }
        } else if ($type == "product") {
            $result = Client::select("{$pfx}clients.id", "{$pfx}clients.firstname", "{$pfx}clients.lastname", "{$pfx}clients.email", "{$pfx}hosting.domain")
                            ->where("{$pfx}hosting.id", $id)
                            ->join("{$pfx}hosting", "{$pfx}clients.id", "{$pfx}hosting.userid")
                            ->first();

            if ($result) {
                $data = $result->toArray();
                if ($data["email"]) {
                    $todata[] = (string) $data["firstname"] . " " . $data["lastname"] . " - " . ($data["domain"] ?? __("admin.domainsnodomains")) . " &lt;" . $data["email"] . "&gt;";
                }
            }
        } else if ($type == "domain") {
            $result = Client::select("{$pfx}clients.id", "{$pfx}clients.firstname", "{$pfx}clients.lastname", "{$pfx}clients.email", "{$pfx}domains.domain")
                            ->where("{$pfx}domains.id", $id)
                            ->join("{$pfx}domains", "{$pfx}clients.id", "{$pfx}domains.userid")
                            ->first();
            
            if ($result) {
                $data = $result->toArray();
                if ($data["email"]) {
                    $todata[] = (string) $data["firstname"] . " " . $data["lastname"] . " - " . ($data["domain"] ?? __("admin.domainsnodomains")) . " &lt;" . $data["email"] . "&gt;";
                }
            }
        } else if ($type == "affiliate") {
            $result = Affiliate::select("{$pfx}clients.firstname", "{$pfx}clients.lastname", "{$pfx}clients.email")
                            ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}affiliates.clientid")
                            ->where("{$pfx}affiliates.id", $id)
                            ->first();

            if ($result) {
                $data = $result->toArray();
                if ($data["email"]) {
                    $todata[] = (string)$data["firstname"] . " " . $data["lastname"] . " - " . ($data["domain"] ?? __("admin.domainsnodomains")) . " &lt;" . $data["email"] . "&gt;";
                }
            }
        }

        return $returnAs == "array" ? $todata : ($todata[0] ?? "");
    }

    public function loadmessage(Request $request)
    {
        $massmailquery = $request->get("massmailquery");
        $multiple = $request->get("multiple");
        $id = $request->get("id");
        $language = (!$massmailquery && !$multiple && (int) $id) ? Client::find($id)->value("language") : "";

        $messageName = $request->get("messagename");
        $template = Emailtemplate::where("name", $messageName)->where("language", $language)->get()->first();    

        if (is_null($template)) {
            $template = Emailtemplate::where("name", $messageName)->get()->first();
        }

        $subject = $template->subject;
        $message = $template->message;
        $fromname = $template->fromName;
        $fromemail = $template->fromEmail;
        $plaintext = $template->plaintext;
        
        if ($plaintext) {
            $message = nl2br($message);
        }

        return ResponseAPI::Success([
            'message' => "Message loaded successfully!",
            'data' => [
                "fromname" => $fromname,
                "fromemail" => $fromemail,
                "subject" => $subject,
                "message" => $message,
            ],
        ]);
    }
    
    public function preview(Request $request)
    {
        $action = "";
        $type = $request->type;
        $subject = $request->subject;
        $message = $request->message;
        $massmail = $request->massmail;
        $massmailquery = $request->massmailquery;
        $id = $request->id;

        // For multiple emails
        $multiple = $request->multiple;
        $selectedclients = $request->selectedclients;

        Emailtemplate::where("name", "Mass Mail Template")->delete();
        
        if ($type == "addon") {
            $type = "product";
        }

        $template = new Emailtemplate();
        $template->type = $type;
        $template->name = "Mass Mail Template";
        $template->subject = Sanitize::decode($subject);
        $template->message = Sanitize::decode($message);
        $template->fromName = "";
        $template->fromEmail = "";
        $template->copyTo = array();
        $template->blindCopyTo = array();
        $template->disabled = false;
        $template->custom = false;
        $template->plaintext = false;
        $safeStoredQuery = null;
        try {
            $safeStoredQuery = session()->get("massmailquery");
            $safeStoredQuery = Crypt::decryptString($safeStoredQuery);
        } catch (\Throwable $th) {
            $safeStoredQuery = null;
        }
        $relatedId = null;

        if ($massmail && $safeStoredQuery) {
            $massmailquery = $safeStoredQuery;
            $result = \DB::select($massmailquery . " LIMIT 0,1");
            $data = $result ? $result[0] : null;
            $relatedId = $data ? $data->id : 0;
        } else if ($multiple) {
            $relatedId = isset($selectedclients[0]) ? $selectedclients[0] : 0;
        } else {
            $relatedId = isset($id) ? $id : 0;
        }

        $preview = "";
        if ($relatedId) {
            try {
                $emailer = Emailer::factoryByTemplate($template, $relatedId);
                $preview = $emailer->preview()->getBodyWithoutCSS();

                $mergeData = $emailer->getMergeData();
                $preview = ViewHelper::render($preview, $mergeData);
            } catch(\Exception $e) {
                $preview = $e->getMessage();
                return ResponseAPI::Error([
                    'message' => "Something went wrong!",
                    'data' => ["html" => $preview],
                ]);
            }
        } else {
            $preview = "No related entities found to preview message. Unable to preview.";
        }

        return ResponseAPI::Success([
            'message' => "OK!",
            'data' => ["html" => $preview],
        ]);
    }

    public function send(Request $request)
    {
        $pfx = $this->prefix;
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'subject' => 'required|string',
            'fromname' => 'required|string',
            'fromemail' => 'required|string|email',
            'recipients' => 'required|numeric|gt:0',
            'save' => $request->savename && !$request->step ? 'required_with:savename|numeric|in:0,1' : "nullable|numeric|in:0,1",
            'savename' => $request->save && !$request->step ? 'required_with:save|string|unique:App\Models\Emailtemplate,name,'.$request->savename : "nullable|string",
            'attachment' => 'nullable|array',
            'attachment.*' => !empty($request->attachment) ? 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2000' : '',
        ]);

        if ($validator->fails()) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'),
                'data' => ['errors' => $validator->errors()],
            ]);
        }

        $step = $request->get("step");
        $type = $request->get("type");
        $save = $request->get("save");
        $savename = $request->get("savename");
        $message = $request->get("message");
        $subject = $request->get("subject");
        $fromemail = $request->get("fromemail");
        $massmailamount = $request->get("massmailamount");
        $massmailinterval = $request->get("massmailinterval");
        $attachment = $request->file("attachment");
        $cc = explode(",", trim($request->get("cc")));
        $bcc = explode(",", trim($request->get("bcc")));

        $done = false;
        $additionalMergeFields = [];
        if ($type == "addon") {
            $type = "product";
            $additionalMergeFields["addonemail"] = true;
        }

        if ($save && $savename && !$step) {
            $this->saveMailTemplate($request);
        }

        if (!$step) {
            // NOTE: Should we need to add this code?
            Emailtemplate::where("name", "Mass Mail Template")->delete();
            $template = new Emailtemplate();
            $template->type = $request->type;
            $template->name = "Mass Mail Template";
            $template->subject = Sanitize::decode($subject);
            $template->message = Sanitize::decode($message);
            $template->fromName = $request->fromname;
            $template->fromEmail = $request->fromemail;
            $template->copyTo = $cc;
            $template->blindCopyTo = $bcc;
            $template->save();

            $_SESSION["massmail"]["massmailamount"] = $massmailamount;
            $_SESSION["massmail"]["massmailinterval"] = $massmailinterval;
    
            $attachments = [];
            if ($request->hasFile('attachment')) {
                $attachments = FileUploader::setDiskName("attachments")->multipleUpload($attachment, [
                    'file_name_prefix' => 'attch',
                    'file_attachment_type' => 'attachment',
                    'file_upload_path' => "/",
                ]);
            }

            $_SESSION["massmail"]["attachments"] = $attachments;
            $step = 0;

            session()->put("massmail", $_SESSION["massmail"]);
        }

        $_SESSION["massmail"] = session()->get("massmail");
        $mail_attachments = [];
        if (isset($_SESSION["massmail"]["attachments"])) {
            $attachments = $_SESSION["massmail"]["attachments"];
            foreach ($attachments as $parts) {
                $mail_attachments[] = [
                    "displayname" => $parts["filename"], 
                    "path" => asset("/attachments/{$parts["path"]}"),
                ];
            }
        }

        $massmail = $request->get("massmail");
        $sendforeach = $request->get("sendforeach");
        $multiple = $request->get("multiple");
        $safeStoredQuery = null;
        try {
            $safeStoredQuery = session()->get("massmailquery");
            $safeStoredQuery = Crypt::decryptString($safeStoredQuery);
        } catch (\Throwable $th) {
            $safeStoredQuery = null;
        }

        if ($massmail && $safeStoredQuery) {
            $massmailquery = $safeStoredQuery;
            $emailoptout = $request->get("emailoptout");

            if ($emailoptout || session()->get("massmailemailoptout")) {
                session()->put("massmailemailoptout", true);

                if (EmailSubscription::isUsingOptInField()) {
                    $thisCriteria = "marketing_emails_opt_in = '1'";
                } else {
                    $thisCriteria = "emailoptout = '0'";
                }

                $massmailquery .= " AND {$pfx}clients." . $thisCriteria;
            }

            $sentids = $_SESSION["massmail"]["sentids"] ?? [];
            $massmailamount = (int) $_SESSION["massmail"]["massmailamount"] ?? 0;
            $massmailinterval = (int) $_SESSION["massmail"]["massmailinterval"] ?? 0;

            if (!$massmailamount) {
                $massmailamount = 25;
            }

            if (!$massmailinterval) {
                $massmailinterval = 30;
            }
            
            $result = \DB::select($massmailquery);
            $totalemails = is_array($result) ? count($result) : 0;

            $totalsteps = ceil($totalemails / $massmailamount);
            $esttotaltime = ($totalsteps - ($step + 1)) * $massmailinterval;

            // $infobox = AdminFunctions::infoBoxMessage(__("admin.sendmessagemassmailqueue"), $totalemails . __("admin.sendmessagemassmailspart1") . ($step + 1) . __("admin.sendmessagemassmailspart2") . $totalsteps . __("admin.sendmessagemassmailspart3") . $esttotaltime . __("admin.sendmessagemassmailspart4"));
            $progress = AdminFunctions::infoBoxMessage(__("admin.sendmessagemassmailqueue"), $totalemails . __("admin.sendmessagemassmailspart1") . ($step + 1) . __("admin.sendmessagemassmailspart2") . $totalsteps . __("admin.sendmessagemassmailspart3") . $esttotaltime . __("admin.sendmessagemassmailspart4"));      
            // echo $progress;
            
            ob_start();
            
            // $result =  full_query($massmailquery . " LIMIT " . (int)($step * $massmailamount) . "," . (int)$massmailamount);
            $result = \DB::select($massmailquery . " LIMIT " . (int) ($step * $massmailamount) . "," . (int) $massmailamount);
            $result = $result ?? [];

            foreach ($result as $data) {
                if (isset($data->aid)) {
                    $additionalMergeFields["addonid"] = $data->aid;
                }

                if ($sendforeach || !$sendforeach && !in_array($data->userid, $sentids)) {
                    $result = Functions::sendMessage("Mass Mail Template", $data->id, $additionalMergeFields, true, $mail_attachments);
                    $sentids[] = $data->userid;
                } else {
                    echo "<li>" . __("admin.sendmessageskippedduplicate") . $data->userid . "<br>";
                }
            }

            $_SESSION["massmail"]["sentids"] = $sentids;
            session()->put("massmail", $_SESSION["massmail"]);

            $content = ob_get_contents();
            ob_end_clean();
            
            // echo "<ul>" . str_replace(array("<p>", "</p>"), array("<li>", "</li>"), $content) . "</ul>";
            $result = "Batch: " .($step + 1)
                    ."<ul>" 
                        . str_replace(array("<p>", "</p>"), array("<li>", "</li>"), $content) 
                    . "</ul>";
            
            $totalsent = $step * $massmailamount + $massmailamount;
            if ($totalemails <= $totalsent) {
                $done = true;
            } else {
                // TODO: Return ajax call here to update progress batch
                // $massmaillink = route("admin.pages.clients.massmail.send", ["sendforeach" => $sendforeach, "massmail" => 1, "step" => ($step + 1)]); //"sendmessage.php?action=send&sendforeach=" . $sendforeach . "&massmail=1&step=" . ($step + 1) ;
                // echo "<p><a href=\"" . $massmaillink . "\">" . __("admin.sendmessageforcenextbatch") . "</a></p><meta http-equiv=\"refresh\" content=\"" . $massmailinterval . ";url=" . $massmaillink . "\">";

                return ResponseAPI::Success([
                    'message' => "OK!",
                    'data' => [
                        "massmailinterval" => $massmailinterval,
                        "progress" => $progress,
                        "result" => $result,
                        "isDone" => $done,
                        "step" => ($step + 1),
                    ],
                ]);
            }
        } else if ($multiple) {
            $selectedclients = $request->get("selectedclients");
            $emailoptout = $request->get("emailoptout");

            ob_start();
            foreach ($selectedclients as $selectedclient) {
                $skipemail = false;
                $checkValue = true;
                
                if ($emailoptout) {
                    if (EmailSubscription::isUsingOptInField()) {
                        $field = "marketing_emails_opt_in";
                        $checkValue = false;
                    } else {
                        $field = "emailoptout";
                        $checkValue = true;
                    }

                    if ($type == "general") {
                        $q = Client::where("id", $selectedclient)->first();
                    } else if ($type == "product") {
                        $q = Hosting::where("{$pfx}hosting.id", $selectedclient)
                                        ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid")
                                        ->first();
                    } else if ($type == "domain") {
                        $q = Domain::where("{$pfx}domains.id", $selectedclient)
                                        ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}domains.userid")
                                        ->first();
                    } else if ($type == "affiliate") {
                        $q = Affiliate::where("{$pfx}affiliates.id", $selectedclient)
                                        ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}affiliates.clientid")
                                        ->first();
                    }

                    $skipemail = isset($q) ? (bool)(int) $q->{$field} : false;
                }

                if ($skipemail === $checkValue) {
                    echo "<p>Email Skipped for ID " . $selectedclient . " due to Marketing Email Opt-Out</p>";
                } else {
                    $result = Functions::sendMessage("Mass Mail Template", $selectedclient, "", true, $mail_attachments);
                }

                $done = true;
            }
            
            $result = ob_get_contents();
            ob_end_clean();
        } else {
            ob_start();

            $id = $request->id;
            $result = Functions::sendMessage("Mass Mail Template", $id, "", true, $mail_attachments);
            $done = true;

            $result = ob_get_contents();
            ob_end_clean();
        }

        if ($done) {
            $complete = "<p><strong>" . __("admin.sendmessagesendingcompleted") . "</strong></p>";
            Emailtemplate::where("name", "Mass Mail Template")->delete();

            // Delete file attachment after complete
            $_SESSION["massmail"] = session()->get("massmail");
            if (isset($_SESSION["massmail"]["attachments"])) {
                $attachments = $_SESSION["massmail"]["attachments"];
                foreach ($attachments as $parts) {
                    FileUploader::setDiskName("attachments")->delete("/{$parts["filename"]}");
                }
            }

            unset($_SESSION["massmail"]);
            session()->forget("massmail");
            session()->forget("massmailemailoptout");

            return ResponseAPI::Success([
                'message' => "OK!",
                'data' => [
                    "progress" => isset($progress) ? str_replace("Please wait...", "Completed!", $progress) : "Completed!",
                    "result" => $result,
                    "complete" => $complete,
                    "isDone" => $done,
                    "textloading" => "Mass Mail Queue Completed",
                ],
            ]);
        }

    }

    public function saveMailTemplate(Request $request)
    {
        $cc = explode(",", $request->get("cc"));
        $bcc = explode(",", $request->get("bcc"));

        $template = new Emailtemplate();
        $template->type = $request->type;
        $template->name = $request->savename;
        $template->subject = Sanitize::decode($request->subject);
        $template->message = Sanitize::decode($request->message);
        $template->fromName = $request->fromname;
        $template->fromEmail = $request->fromemail;
        $template->copyTo = $cc;
        $template->blindCopyTo = $bcc;
        $template->custom = true;
        $template->save();
    }

}
