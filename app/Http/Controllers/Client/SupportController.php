<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Hosting;
use App\Models\Ticket;
use App\Models\Ticketdepartment;
use App\Models\Ticketstatus;
use App\Helpers\Carbon;
use App\Helpers\Ticket as TicketHelper;
use App\Models\Client;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Validator;
use Auth, DB;

class SupportController extends Controller
{
    public function Support_OpenTicketOLD()
    {
        $deptId = Ticketdepartment::all();
        $departmentCollection = Ticketdepartment::where("hidden", "");
        $totaldepartments = $departmentCollection->count();
        if (!\App\Helpers\Cfg::getValue("ShowClientOnlyDepts") && !Auth::guard('web')->check()) {
            $departmentCollection = $departmentCollection->where("clientsonly", "");
        }
        $departments = array();
        foreach ($departmentCollection->get() as $department) {
            $departments[] = array("id" => $department->id, "name" => $department->name, "description" => $department->description);
        }
        return view('pages.support.openticket.index', [
            'depts' => $deptId,
            'departments' => $departments,
        ]);
    }
    public function Support_OpenTicket(Request $request)
    {
        $auth = Auth::guard('web')->user();
        $route = "pages.support.openticket.index";
        $remote_ip = $request->ip();
        $action = $request->input("action");
        $deptid = (int) $request->input("deptid");
        $step = $request->input("step");
        $name = $request->input("name") ?? "";
        $email = $request->input("email") ?? "";
        $urgency = $request->input("urgency") ?? "";
        $subject = $request->input("subject") ?? "";
        $message = $request->input("message");
        $reqmanual = $request->input('request') ?? "";
        $attachments = $request->input("attachments");
        $relatedservice = $request->input("relatedservice") ?? "";
        $customfield = $request->input("customfield");
        $file_too_large = $request->input("file_too_large");
        $smartyvalues["loggedin"] = Auth::guard('web')->check();
        if ($action == "getkbarticles") {
            $kbarticles = \App\Helpers\Ticket::getKBAutoSuggestions($text);
            if (count($kbarticles)) {
                $smartyvalues["kbarticles"] = $kbarticles;
                // echo $smarty->fetch($whmcs->getClientAreaTemplate()->getName() . "/supportticketsubmit-kbsuggestions.tpl");
                return view("supportticketsubmit-kbsuggestions", $smartyvalues);
            }
            // exit;
        }
        if ($action == "getcustomfields") {
            $customfields = \App\Helpers\Customfield::getCustomFields("support", $deptid, "", "", "", $customfield);
            $smartyvalues["customfields"] = $customfields;
            return view("supportticketsubmit-customfields", $smartyvalues);
        }
        if ($action == "markdown") {
            $response = new \Illuminate\Http\JsonResponse();
            $templatefile = "markdown-guide";
            $response->setData(array("body" => view($templatefile, array())->render()));
            $response->send();
            \App\Helpers\Termius::getInstance()->doExit();
        } else {
            if ($action == "markdown-page") {
                // TODO: here
                // $ca = new WHMCS\ClientArea();
                // $ca->setPageTitle(\Lang::get("client.markdown.title"));
                // $ca->addToBreadCrumb("index.php", \Lang::get("client.globalsystemname"));
                // $ca->addToBreadCrumb("submitticket.php?action=markdown-page", \Lang::get("client.markdown.title"));
                // $ca->setTemplate("markdown-guide");
                // $ca->initPage();
                // $ca->output();
            }
        }
        $recentTickets = array();
        $result = \App\Models\Ticket::where(array("userid" => $auth ? $auth->id : 0))->orderBy("id", "DESC")->limit(0,5)->get();
        foreach ($result->toArray() as $data) {
            $recentTickets[] = array("id" => $data["id"], "tid" => $data["tid"], "c" => $data["c"], "date" => (new \App\Helpers\Functions)->fromMySQLDate($data["date"], 1, 1), "department" => $data["did"], "subject" => $data["title"], "status" => \App\Helpers\Ticket::getStatusColour($data["status"]), "urgency" => \Lang::get("client.client.supportticketsticketurgency" . strtolower($data["urgency"])), "lastreply" => (new \App\Helpers\Functions)->fromMySQLDate($data["lastreply"], 1, 1), "unread" => $data["clientunread"]);
        }
        $smartyvalues["recenttickets"] = $recentTickets;
        // TODO: $captcha = new WHMCS\Utility\Captcha();
        $captcha = "";
        $validate = new \App\Helpers\Validate();
        if ($step == "3") {
            $request_method = strtolower($request->method());
            if ($request_method != "post") {
                return redirect()->route($route, ['file_too_large' => '1']);
            }
            if (\App\Helpers\Ticket::checkTicketAttachmentSize()) {
                if (!$auth) {
                    $validate->validate("required", "name", "client.supportticketserrornoname");
                    if ($validate->validate("required", "email", "client.supportticketserrornoemail")) {
                        $validate->validate("email", "email", "client.clientareaerroremailinvalid");
                    }
                }
                $validate->validate("required", "subject", "client.supportticketserrornosubject");
                $validate->validate("required", "message", "client.supportticketserrornomessage");
                $validate->validate("fileuploads", "attachments", "client.supportticketsfilenotallowed");
                $validate->validateCustomFields("support", $deptid);
                // TODO: $captcha->validateAppropriateCaptcha(WHMCS\Utility\Captcha::FORM_SUBMIT_TICKET, $validate);
                if (!$validate->hasErrors()) {
                    $clientid = $contactid = 0;
                    if ($auth) {
                        $clientid = $auth->id;
                        if (session("cid")) {
                            $contactid = session("cid");
                        }
                    }
                    $customfields = array();
                    if (is_array($customfield)) {
                        $customfields = \App\Helpers\Customfield::getCustomFields("support", $deptid, "", "", "", $customfield);
                    }
                    $validationData = array("clientId" => $clientid, "contactId" => $contactid, "name" => $name, "email" => $email, "isAdmin" => false, "departmentId" => $deptid, "subject" => $subject, "message" => $message, "priority" => $urgency, "relatedService" => $relatedservice, "customfields" => $customfields);
                    $ticketOpenValidateResults = \App\Helpers\Hooks::run_hook("TicketOpenValidation", $validationData);
                    if (is_array($ticketOpenValidateResults)) {
                        foreach ($ticketOpenValidateResults as $hookReturn) {
                            if (is_string($hookReturn) && ($hookReturn = trim($hookReturn))) {
                                $validate->addError($hookReturn);
                            }
                        }
                    }
                }
                if ($validate->hasErrors()) {
                    $step = "2";
                }
            } else {
                if (empty($_POST)) {
                    // redir("file_too_large=1", "submitticket.php");
                    return redirect()->route($route, ['file_too_large' => '1']);
                } else {
                    $step = 2;
                    $file_too_large = true;
                }
            }
        }
        if ($file_too_large) {
            $validate->addError(\Lang::get("client.supportticketsuploadtoolarge"));
        }
        // TODO: checkContactPermission("tickets");
        $usingsupportmodule = false;
        if (\App\Helpers\Cfg::getValue("SupportModule")) {
            // TODO: if (!\Module::find(\App\Helpers\Cfg::getValue("SupportModule"))) {
            //     exit("Invalid Support Module");
            // }
            // TODO: $supportmodulepath = "modules/support/" . \App\Helpers\Cfg::getValue("SupportModule") . "/submitticket.php";
            // if (file_exists($supportmodulepath)) {
            //     if (!isset($_SESSION["uid"])) {
            //         $goto = "submitticket";
            //         require "login.php";
            //     }
            //     $usingsupportmodule = true;
            //     $templatefile = "";
            //     require $supportmodulepath;
            //     outputClientArea($templatefile);
            //     exit;
            // }
        }
        switch ($step) {
            case '2':
                $templatefile = "supportticketsubmit-steptwo";
                $department = Ticketdepartment::find($deptid);
                if (!$department) {
                    // redir("", "submitticket.php");
                    return redirect()->route($route);
                }
                $deptid = $department->id;
                $deptname = $department->name;
                $clientsonly = $department->clientsOnly;
                if ($clientsonly && !$auth) {
                    $templatefile = "supportticketsubmit-stepone";
                    $goto = "submitticket";
                    // include "login.php";
                    return view("auth.login");
                }
                $smartyvalues["deptid"] = $deptid;
                $smartyvalues["department"] = $deptname;
                $departmentCollection = Ticketdepartment::enforceUserVisibilityPermissions()->orWhere("id", $deptid);
                $departments = array();
                foreach ($departmentCollection->get() as $department) {
                    $departments[] = array("id" => $department->id, "name" => $department->name, "description" => $department->description);
                }
                $smartyvalues["departments"] = $departments;
                $clientname = "";
                $relatedservices = array();
                if ($auth) {
                    $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($auth->id, session("cid"));
                    $clientname = $clientsdetails["firstname"] . " " . $clientsdetails["lastname"];
                    $email = $clientsdetails["email"];
                    $result = \App\Models\Hosting::selectRaw("tblhosting.id,tblhosting.domain,tblhosting.domainstatus,tblhosting.packageid,tblproducts.name as product_name")
                    ->where(array("userid" => $auth->id))
                    ->orderBy("domain", "ASC")
                    ->join("tblproducts", "tblproducts.id","=","tblhosting.packageid")
                    ->get();
                    foreach ($result->toArray() as $data) {
                        $productname = \App\Models\Product::getProductName($data["packageid"], $data["product_name"]);
                        if ($data["domain"]) {
                            $productname .= " - " . $data["domain"];
                        }
                        $relatedservices[] = array("id" => "S" . $data["id"], "name" => $productname, "status" => \Lang::get("client.clientarea" . strtolower($data["domainstatus"])));
                    }
                    $result = \App\Models\Domain::where(array("userid" => $auth->id))->orderBy("domain", "ASC")->get();
                    foreach ($result->toArray() as $data) {
                        $relatedservices[] = array("id" => "D" . $data["id"], "name" => \Lang::get("client.clientareahostingdomain") . " - " . $data["domain"], "status" => \Lang::get("client.clientarea" . strtolower(str_replace(" ", "", $data["status"]))));
                    }
                }
                $smartyvalues["name"] = $name;
                $smartyvalues["clientname"] = $clientname;
                $smartyvalues["email"] = $email;
                $smartyvalues["relatedservices"] = $relatedservices;
                $customfields = \App\Helpers\Customfield::getCustomFields("support", $deptid, "", "", "", $customfield);
                $tickets = new \App\Helpers\Tickets();
                $smartyvalues["customfields"] = $customfields;
                $smartyvalues["allowedfiletypes"] = implode(", ", $tickets->getAllowedAttachments());
                $smartyvalues["errormessage"] = $validate->getHTMLErrorOutput();
                $smartyvalues["urgency"] = $urgency;
                $smartyvalues["subject"] = $subject;
                $smartyvalues["message"] = $message;
                $smartyvalues["captcha"] = $captcha;
                $smartyvalues["isManual"] = $reqmanual;
                // $smartyvalues["captchaForm"] = WHMCS\Utility\Captcha::FORM_SUBMIT_TICKET;
                // $smartyvalues["recaptchahtml"] = clientAreaReCaptchaHTML();
                // $smartyvalues["capatacha"] = $captcha;
                // $smartyvalues["recapatchahtml"] = clientAreaReCaptchaHTML();
                $smartyvalues["captchaForm"] = "";
                $smartyvalues["recaptchahtml"] = "";
                $smartyvalues["capatacha"] = "";
                $smartyvalues["recapatchahtml"] = "";
                if (\App\Helpers\Cfg::getValue("SupportTicketKBSuggestions")) {
                    $smartyvalues["kbsuggestions"] = true;
                }
                $locale = preg_replace("/[^a-zA-Z0-9_\\-]*/", "", \Lang::getLocale());
                $locale = $locale == "locale" ? "en" : substr($locale, 0, 2);
                $smartyvalues["mdeLocale"] = $locale;
                $smartyvalues["loadMarkdownEditor"] = true;
            break;

            case '3':
                $userId = $auth ? $auth->id : 0;
                $contactId = session("cid") ?? 0;
                $ticketDepartment = Ticketdepartment::find($deptid);
                if (!$ticketDepartment || $ticketDepartment->clientsonly && !$userId) {
                    // redir("", "submitticket.php");
                    return redirect()->route($route);
                }
                $attachments = \App\Helpers\Ticket::uploadTicketAttachments();
                $from = array();
                $from["name"] = $name;
                $from["email"] = $email;
                $message .= "\n\n----------------------------\nIP Address: " . $remote_ip;
                $cc = "";
                if ($contactId) {
                    $cc = \App\Models\Contact::where(array("id" => $contactId, "userid" => $userId))->value("email") ?? "";
                }
                $ticketdetails = \App\Helpers\Ticket::openNewTicket($userId, $contactId, $deptid, $subject, $message, $urgency, $attachments, $from, $relatedservice, $cc, false, false, true);
                \App\Helpers\Customfield::saveCustomFields($ticketdetails["ID"], $customfield);
                // $_SESSION["tempticketdata"] = $ticketdetails;
                session()->put("tempticketdata", $ticketdetails);
                // redir("step=4", "submitticket.php");
                return redirect()->route($route, ['step' => '4']);
            break;

            case '4':
                // $ticketdetails = $_SESSION["tempticketdata"];
                $ticketdetails = session("tempticketdata");
                $templatefile = "supportticketsubmit-confirm";
                $smartyvalues["tid"] = $ticketdetails["TID"];
                $smartyvalues["c"] = $ticketdetails["C"];
                $smartyvalues["subject"] = $ticketdetails["Subject"];
            break;
            
            case '':
            default:
                $templatefile = "supportticketsubmit-stepone";
                $departmentCollection = Ticketdepartment::where("hidden", "");
                $totaldepartments = $departmentCollection->count();
                if (!\App\Helpers\Cfg::getValue("ShowClientOnlyDepts") && !Auth::guard('web')->check()) {
                    $departmentCollection = $departmentCollection->where("clientsonly", "");
                }
                $departments = array();
                foreach ($departmentCollection->get() as $department) {
                    $departments[] = array("id" => $department->id, "name" => $department->name, "description" => $department->description);
                }
                if (!$departments && $totaldepartments) {
                    $goto = "submitticket";
                    return view("auth.login");
                }
                if (count($departments) == 1) {
                    // redir("step=2&deptid=" . $departments[0]["id"] . ($file_too_large ? "&file_too_large=1" : ""));
                    return redirect()->route($route, ['step' => '1', 'deptid' => $departments[0]["id"], 'file_too_large' => $file_too_large ? 1 : '']);
                }
                $smartyvalues["departments"] = $departments;
                $smartyvalues["errormessage"] = $validate->getHTMLErrorOutput();
            break;
        }

        // outputClientArea($templatefile, false, array("ClientAreaPageSubmitTicket"));
        return view($templatefile, $smartyvalues);
    }
    public function Support_SubmitTicket($id)
    {
        $auth = Auth::user();
        $userid = $auth->id;

        $relService = Hosting::selectRaw("tblhosting.id, tblhosting.domain, tblproducts.name, tblhosting.domainstatus, tblhosting.firstpaymentamount, tblhosting.amount, tblhosting.nextduedate")->where("userid", $userid)->orderBy("domain", "ASC")->join("tblproducts", "tblhosting.packageid", "tblproducts.id")->get();

        $deptId = Ticketdepartment::all();

        return view('pages.support.openticket.submitticket', ['relService' => $relService, 'deptId' => $deptId, 'clickedDept' => $id]);
    }
    public function Support_PostTicket(Request $request)
    {
        
        $userid = Auth::user()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['error' => 'Please fill ticket form correctly!']);
        }

        $from = [
            'name' => $request->name,
            'email' => $request->email
        ];

        $attachmentString = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $randStr = (string) Str::random(6);
                $filename = $randStr . "_" . $attachment->getClientOriginalName();
                $filepath = "{$filename}";
                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);
        $ticketdata = TicketHelper::OpenNewTicket(
            $userid,
            $contactid = '',
            $request->deptid,
            $request->subject,
            $request->message,
            $request->urgency,
            $attachmentString,
            $from,
            $request->relatedservice,
            $cc = "",
            $noemail = '',
            $treatAsAdmin = '',
            // $useMarkdown = 'markdown'
        );
        $msg = implode(' | ', $ticketdata);
        return back()->with('success', 'Ticket created successfully. ' . $msg);
    }

    public function Support_MyTickets()
    {
        $auth = Auth::user();
        $userid = $auth->id;
        $department = Ticketdepartment::all();
   
        $departmentCollection = Ticketdepartment::enforceUserVisibilityPermissions();
        
        $departments = array();
        foreach ($departmentCollection->get() as $department) {
            $departments[] = array("id" => $department->id, "name" => $department->name, "description" => $department->description);
        }
        $ticketStats = Ticketstatus::all();
        $getTicket = Ticket::where("userid", $userid)->orderBy("id", "DESC")->get();
        return view('pages.support.mytickets.index', ['getTicket' => $getTicket, 'ticketStats' => $ticketStats, 'departments' => $departments]);
    }
    public function dt_myTickets()
    {
        $auth = Auth::user();
        $userid = $auth->id;
        $ticketStats = Ticketstatus::all();
        $getTicket = Ticket::where("userid", $userid)->orderBy("id", "DESC")->get();
        return datatables()->of($getTicket)->editColumn('department', function ($row) {
            $department = Ticketdepartment::where('id', $row->did)->orderBy('id', 'ASC')->pluck('name')->toArray();
            return $department;
        })
            ->editColumn('title', function ($row) use ($ticketStats) {

                $title = '';
                foreach ($ticketStats as $id => $stats) {
                    if ($row->status === $stats->title) {
                        $title .= "<span style=\"color:{$stats->color}\">#{$row->tid} </span>" . $row->title;
                    }
                }
                return $title;
            })
            ->editColumn('date', function ($row) {
                $carbon = new Carbon($row->date);
                $dateNew = $carbon->toClientDateTimeFormat();
                // dd($dateNew);
                return $dateNew;
            })
            ->editColumn('status', function ($row) use ($ticketStats) {
                $badge = '';
                foreach ($ticketStats as $id => $stats) {
                    if ($row->status === $stats->title) {
                        $badge .= "<div class=\"badge p-1 font-weight-bold\" style=\"background-color: {$stats->color}; color: #fff; font-size: 10px;\">{$row->status}</div>";
                    }
                }
                return $badge;
            })
            ->editColumn('actions', function ($row) {
                $action = '';
                $detailRoute = route('pages.support.mytickets.ticketdetails', ['tid' => $row->tid, 'c' => $row->c]);

                $action .= "<a href=\"{$detailRoute}\" type=\"button\" id=\"act-details\" class=\"btn btn-xs btn-success p-1 \" data-id=\"\" title=\"Details\">Details</a>";
                return $action;
            })
            ->rawColumns(['title', 'actions', 'status'])
            ->addIndexColumn()
            ->toJson();
    }
    public function Support_TicketDetails(Request $request)
    {
        DB::beginTransaction();
        try {
            global $_LANG, $CONFIG;
            $auth = Auth::guard('web')->user();
            $route = "pages.support.mytickets.ticketdetails";
            $tid = $request->input("tid");
            $c = $request->input("c");
            $closeticket = $request->input("closeticket");
            $postreply = $request->input("postreply");
            $replyname = $request->input("replyname");
            $replyemail = $request->input("replyemail");
            $replymessage = $request->input("replymessage");
            $loggedInUserId = $auth ? $auth->id : 0;
            $loggedInContactId = \Session::get("cid");
            $c = preg_replace("/[^A-Za-z0-9]/", "", $c);
            $clientname = $clientemail = "";
            $pagetitle = $_LANG["supportticketsviewticket"];
            $breadcrumbnav = "<a href=\"index.php\">" . $_LANG["globalsystemname"] . "</a> > <a href=\"clientarea.php\">" . $_LANG["clientareatitle"] . "</a> > <a href=\"supporttickets.php\">" . $_LANG["supportticketspagetitle"] . "</a> > <a href=\"viewticket.php?tid=" . $tid . "&amp;c=" . $c . "\">" . $_LANG["supportticketsviewticket"] . "</a>";
            $pageicon = "images/supporttickets_big.gif";
            $templatefile = "viewticket";
            $displayTitle = \Lang::get("client.supportticketsviewticket");
            $tagline = "";
            // TODO: initialiseClientArea($pagetitle, $displayTitle, $tagline, $pageicon, $breadcrumbnav);
            // TODO: checkContactPermission("tickets");
            $usingsupportmodule = false;
            // TODO: if ($CONFIG["SupportModule"]) {
            //     if (!isValidforPath($CONFIG["SupportModule"])) {
            //         exit("Invalid Support Module");
            //     }
            //     $supportmodulepath = "modules/support/" . $CONFIG["SupportModule"] . "/viewticket.php";
            //     if (file_exists($supportmodulepath)) {
            //         $usingsupportmodule = true;
            //         $templatefile = "";
            //         require $supportmodulepath;
            //         outputClientArea($templatefile);
            //         exit;
            //     }
            // }
            $result = \App\Models\Ticket::where(array("tid" => $tid, "c" => $c));
            $data = $result;
            $ticketData = $data;
            $ticketId = $data->value("id");
            $tid = $data->value("tid");
            $c = $data->value("c");
            $userid = $data->value("userid");
            $cc = $data->value("cc");
            if ($data->value("merged_ticket_id")) {
                $ticket = DB::table("tbltickets")->find($data->value("merged_ticket_id"), array("tid", "c"));
                // redir("tid=" . $ticket->tid . "&c=" . $ticket->c);
                return redirect()->route($route, ['tid' => $ticket->tid, 'c' => $ticket->c]);
            }
            if (!$ticketId) {
                $smartyvalues["error"] = true;
                $smartyvalues["invalidTicketId"] = true;
            } else {
                $smartyvalues["invalidTicketId"] = false;
                if ($CONFIG["RequireLoginforClientTickets"] && $userid && (!$loggedInUserId || $loggedInUserId != $userid)) {
                    $goto = "viewticket";
                    // require "login.php";
                    return view("auth.login");
                }
                $tickets = new \App\Helpers\Tickets();
                $tickets->setID($ticketId);
                $AccessedTicketIDs = \Session::get("AccessedTicketIDs");
                $AccessedTicketIDsArray = explode(",", $AccessedTicketIDs);
                $AccessedTicketIDsArray[] = $ticketId;
                session()->put("AccessedTicketIDs", implode(",", $AccessedTicketIDsArray));
                if ($request->input("feedback") && $tickets->getDepartmentFeedbackNotifications()) {
                    $templatefile = "ticketfeedback";
                    $smartyvalues["displayTitle"] = \Lang::get("client.ticketfeedbackrequest");
                    $smartyvalues["tagline"] = \Lang::get("client.ticketfeedbackforticket") . $tid;
                    $smartyvalues["id"] = $ticketId;
                    $smartyvalues["tid"] = $tid;
                    $smartyvalues["c"] = $c;
                    $status = $data["status"];
                    $closedcheck = \App\Models\Ticketstatus::where(array("title" => $status, "showactive" => "0"))->value("id");
                    $smartyvalues["stillopen"] = !$closedcheck ? true : false;
                    $feedbackcheck = \App\Models\Ticketfeedback::where(array("ticketid" => $ticketId))->value("id");
                    $smartyvalues["feedbackdone"] = $feedbackcheck;
                    $date = $data["date"];
                    $smartyvalues["opened"] = \App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l, jS F Y H:ia");
                    $lastreply = \App\Models\Ticketreply::where(array("tid" => $ticketId))->orderBy("id", "DESC")->value("data");
                    if (!$lastreply) {
                        $lastreply = $date;
                    }
                    $smartyvalues["lastreply"] = \App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $lastreply)->format("l, jS F Y H:ia");
                    $duration = \App\Helpers\Ticket::getTicketDuration($date, $lastreply);
                    $smartyvalues["duration"] = $duration;
                    $ratings = array();
                    for ($i = 1; $i <= 10; $i++) {
                        $ratings[] = $i;
                    }
                    $smartyvalues["ratings"] = $ratings;
                    $comments = $request->input("comments");
                    $staffinvolved = array();
                    $sql = "SELECT DISTINCT tblticketreplies.admin,tbladmins.id AS staffid FROM tblticketreplies" . " LEFT JOIN tbladmins ON CONCAT(tbladmins.firstname, \" \", tbladmins.lastname)=tblticketreplies.admin" . " WHERE tblticketreplies.tid=?";
                    $staffList = DB::connection()->select($sql, array($ticketId));
                    foreach ($staffList as $staffMember) {
                        $adminInvolved = trim($staffMember->admin);
                        if ($adminInvolved) {
                            $staffinvolved[$staffMember->staffid] = $adminInvolved;
                        }
                        if (!isset($comments[$staffMember->staffid])) {
                            $comments[$staffMember->staffid] = "";
                        }
                    }
                    $smartyvalues["staffinvolved"] = $staffinvolved;
                    $smartyvalues["staffinvolvedtext"] = implode(", ", $staffinvolved);
                    $smartyvalues["rate"] = $request->input("rate");
                    if (!isset($comments["generic"])) {
                        $comments["generic"] = "";
                    }
                    $smartyvalues["comments"] = $comments;
                    $errormessage = "";
                    $smartyvalues["success"] = false;
                    if ($request->input("validate")) {
                        foreach ($staffinvolved as $staffid => $staffname) {
                            if (!$request->input("rate.$staffid")) {
                                $errormessage .= "<li>" . \Lang::get("client.feedbacksupplyrating", array("staffname" => $staffname)) . "</li>";
                            }
                        }
                        $smartyvalues["errormessage"] = $errormessage;
                        if (!$errormessage) {
                            foreach ($staffinvolved as $staffid => $staffname) {
                                \App\Models\Ticketfeedback::insert(array("ticketid" => $ticketId, "adminid" => $staffid, "rating" => $request->input("rate", $staffid), "comments" => $request->input("comments", $staffid), "datetime" => \Carbon\Carbon::now(), "ip" => $request->ip()));
                            }
                            if (trim($request->input("comments.generic"))) {
                                \App\Models\Ticketfeedback::insert(array("ticketid" => $ticketId, "adminid" => "0", "rating" => "0", "comments" => $request->input("comments", "generic"), "datetime" => \Carbon\Carbon::now(), "ip" => $request->ip()));
                            }
                            $smartyvalues["success"] = true;
                        }
                    }
                    // outputClientArea($templatefile);
                    // exit;
                    DB::commit();
                    return view($templatefile, $smartyvalues);
                } else {
                    if ($closeticket) {
                        \App\Helpers\Ticket::closeTicket($ticketId);
                        // redir("tid=" . $tid . "&c=" . $c);
                        return redirect()->route($route, ['tid' => $tid, 'c' => $c]);
                    }
                    $rating = $request->input("rating");
                    if ($rating) {
                        $rating = explode("_", $rating);
                        $replyid = isset($rating[0]) && 4 < strlen($rating[0]) ? substr($rating[0], 4) : "";
                        $ratingscore = isset($rating[1]) ? $rating[1] : "";
                        if (is_numeric($replyid) && is_numeric($ratingscore)) {
                            \App\Models\Ticketreply::where(array("id" => $replyid, "tid" => $ticketId))->update(array("rating" => $ratingscore));
                        }
                        // redir("tid=" . $tid . "&c=" . $c);
                        return redirect()->route($route, ['tid' => $tid, 'c' => $c]);
                    }
                    $action = $request->input("action");
                    if ($action) {
                        $email = trim($request->input("email"));
                        try {
                            $cc = explode(",", $cc);
                            switch ($action) {
                                case "delete":
                                    if (!in_array($email, $cc)) {
                                        throw new \App\Exceptions\Validation\InvalidValue(\Lang::get("client.support.deleteEmailNotExisting", array("email" => $email)));
                                    }
                                    $cc = array_flip($cc);
                                    unset($cc[$email]);
                                    $cc = array_filter(array_flip($cc));
                                    $data = array("success" => true, "message" => \Lang::get("client.support.successDelete", array("email" => $email)));
                                    break;
                                case "add":
                                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                        throw new \App\Exceptions\Validation\InvalidValue(\Lang::get("client.support.invalidEmail", array("email" => $email)));
                                    }
                                    if (in_array($email, $cc)) {
                                        throw new \App\Exceptions\Validation\InvalidValue(\Lang::get("client.support.addEmailExists", array("email" => $email)));
                                    }
                                    $clientEmail = DB::table("tblclients")->where("id", $data["userid"])->value("email");
                                    if ($email == $clientEmail) {
                                        throw new \App\Exceptions\Validation\InvalidValue(\Lang::get("client.support.clientEmail", array("email" => $email)));
                                    }
                                    $existingContacts = DB::table("tblcontacts")->where("email", $email)->where("userid", "!=", $data["userid"])->count("id");
                                    $existingClients = DB::table("tblclients")->where("email", $email)->where("id", "!=", $data["userid"])->count("id");
                                    if (0 < $existingContacts + $existingClients) {
                                        throw new \App\Exceptions\Validation\InvalidValue(\Lang::get("client.support.emailNotPossible", array("email" => $email)));
                                    }
                                    $cc[] = $email;
                                    $cc = array_filter($cc);
                                    $data = array("success" => true, "message" => \Lang::get("client.support.successAdd", array("email" => $email)));
                                    break;
                                default:
                                    $data = array("error" => "An invalid request was made. Please try again.");
                            }
                            if (array_key_exists("success", $data) && $data["success"]) {
                                DB::table("tbltickets")->where("id", $ticketId)->update(array("cc" => implode(",", $cc)));
                                \App\Helpers\Ticket::addTicketLog($data["id"], $data["message"]);
                            }
                        } catch (\Exception $e) {
                            DB::rollback();
                            $data = array("error" => $e->getMessage());
                        }
                        DB::commit();
                        $response = new \Illuminate\Http\JsonResponse();
                        $response->setData($data);
                        $response->send();
                        \App\Helpers\Termius::getInstance()->doExit();
                    }
                    $errormessage = "";
                    if ($postreply) {
                        $smartyvalues["postingReply"] = true;
                        $validate = new \App\Helpers\Validate();
                        if (!$loggedInUserId) {
                            $validate->validate("required", "replyname", "client.supportticketserrornoname");
                            if ($validate->validate("required", "replyemail", "client.supportticketserrornoemail")) {
                                $validate->validate("email", "replyemail", "client.clientareaerroremailinvalid");
                            }
                        }
                        $validate->validate("required", "replymessage", "client.supportticketserrornomessage");
                        if ($validate->hasErrors()) {
                            $errormessage .= $validate->getHTMLErrorOutput();
                        }
                        if ($request->hasFile("attachments")) {
                            foreach ($_FILES["attachments"]["name"] as $num => $filename) {
                                $filename = trim($filename);
                                if ($filename) {
                                    $filenameparts = explode(".", $filename);
                                    $extension = end($filenameparts);
                                    $filename = implode(array_slice($filenameparts, 0, -1));
                                    $filename = preg_replace("/[^a-zA-Z0-9-_ ]/", "", $filename);
                                    $filename .= "." . $extension;
                                    $validextension = \App\Helpers\Ticket::checkTicketAttachmentExtension($filename);
                                    if (!$validextension) {
                                        $errormessage .= "<li>" . $_LANG["supportticketsfilenotallowed"];
                                    }
                                }
                            }
                        }
                        if (!$errormessage) {
                            $attachments = \App\Helpers\Ticket::uploadTicketAttachments();
                            $from = array("name" => $replyname, "email" => $replyemail);
                            DB::commit();
                            \App\Helpers\Ticket::AddReply($ticketId, $loggedInUserId, $loggedInContactId, $replymessage, "", $attachments, $from, "", false, false, true);
                            // redir("tid=" . $tid . "&c=" . $c);
                            return redirect()->route($route, ['tid' => $tid, 'c' => $c]);
                        }
                    } else {
                        $smartyvalues["postingReply"] = false;
                    }
                    $data = $data->first();
                    $data = $data->makeVisible(['editor']);
                    $data = $data->toArray();
                    $ticketId = $data["id"];
                    $userid = $data["userid"];
                    $contactid = $data["contactid"];
                    $deptid = $data["did"];
                    $date = $data["date"];
                    $subject = $data["title"];
                    $message = $data["message"];
                    $status = $data["status"];
                    $attachment = $data["attachment"];
                    $attachmentsRemoved = (bool) (int) $data["attachments_removed"];
                    $urgency = $data["urgency"];
                    $name = $data["name"];
                    $email = $data["email"];
                    $lastreply = $data["lastreply"];
                    $admin = $data["admin"];
                    $date = (new \App\Helpers\Functions)->fromMySQLDate($date, 1, 1);
                    $lastreply = (new \App\Helpers\Functions)->fromMySQLDate($lastreply, 1, 1);
                    $markup = new \App\Helpers\ViewMarkup();
                    $markupFormat = $markup->determineMarkupEditor("ticket_msg", isset($data["editor"]) ? $data["editor"] : "");
                    $message = $markup->transform($message, $markupFormat);
                    $closedTicketStatuses = DB::table("tblticketstatuses")->where("showactive", "=", "0")->where("showawaiting", "=", "0")->pluck("title");
                    $showclosebutton = !in_array($status, $closedTicketStatuses->toArray());
                    $status = \App\Helpers\Ticket::getStatusColour($status);
                    $urgency = $_LANG["supportticketsticketurgency" . strtolower($urgency)];
                    $customfields = \App\Helpers\Customfield::getCustomFields("support", $deptid, $ticketId, "", "", "", true);
                    \App\Helpers\Ticket::ClientRead($ticketId);
                    if ($admin) {
                        $user = "<strong>" . $admin . "</strong><br />" . $_LANG["supportticketsstaff"];
                    } else {
                        if (0 < $userid) {
                            $clientsdata = \App\Models\Client::where(array("id" => $userid));
                            $clientname = $clientsdata->value("firstname") . " " . $clientsdata->value("lastname");
                            $clientemail = $clientsdata->value("email");
                            $user = "<strong>" . $clientname . "</strong><br />" . $_LANG["supportticketsclient"];
                            if (0 < $contactid) {
                                $contactdata = \App\Models\Contact::where(array("id" => $contactid, "userid" => $userid));
                                $clientname = $contactdata->value("firstname") . " " . $contactdata->value("lastname");
                                $clientemail = $contactdata->value("email");
                                $user = "<strong>" . $clientname . "</strong><br />" . $_LANG["supportticketscontact"];
                            }
                        } else {
                            $clientname = $name;
                            $clientemail = $email;
                            $user = "<strong>" . $clientname . "</strong><br />" . $clientemail;
                        }
                    }
                    $department = \App\Helpers\Ticket::getDepartmentName($deptid);
                    $attachments = array();
                    if ($attachment) {
                        $attachment = explode("|", $attachment);
                        foreach ($attachment as $filename) {
                           //  $filename = substr($filename, 7);
                            $attachments[] = $filename;
                        }
                    }
                    $smartyvalues["id"] = $ticketId;
                    $smartyvalues["c"] = $c;
                    $smartyvalues["tid"] = $tid;
                    $smartyvalues["date"] = $date;
                    $smartyvalues["departmentid"] = $deptid;
                    $smartyvalues["department"] = $department;
                    $smartyvalues["subject"] = $subject;
                    $smartyvalues["message"] = $message;
                    $smartyvalues["status"] = $status;
                    $smartyvalues["urgency"] = $urgency;
                    $smartyvalues["attachments"] = $attachments;
                    $smartyvalues["attachments_removed"] = $attachmentsRemoved;
                    $smartyvalues["user"] = $user;
                    $smartyvalues["lastreply"] = $lastreply;
                    $smartyvalues["showclosebutton"] = $showclosebutton;
                    $smartyvalues["closedticket"] = !$showclosebutton;
                    $smartyvalues["customfields"] = $customfields;
                    $smartyvalues["ratingenabled"] = $CONFIG["TicketRatingEnabled"];
                    $locale = preg_replace("/[^a-zA-Z0-9_\\-]*/", "", app()->getLocale());
                    $locale = $locale == "locale" ? "en" : substr($locale, 0, 2);
                    $smartyvalues["mdeLocale"] = $locale;
                    $smartyvalues["loadMarkdownEditor"] = true;
                    $replies = $ascreplies = array();
                    $ascreplies[] = array("id" => "", "userid" => $userid, "contactid" => $contactid, "name" => $admin ? $admin : $clientname, "email" => $admin ? "" : $clientemail, "admin" => $admin ? true : false, "user" => $user, "admin" => $admin, "date" => $date, "message" => $message, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "rating" => $rating);
                    $allattachments = array();
                    $result = \App\Models\Ticketreply::where(array("tid" => $ticketId))->orderBy("date", "ASC")->get();
                    $result = $result->makeVisible(['editor']);
                    foreach ($result->toArray() as $data) {
                        $replyId = $data["id"];
                        $userid = $data["userid"];
                        $contactid = $data["contactid"];
                        $admin = $data["admin"];
                        $name = $data["name"];
                        $email = $data["email"];
                        $date = $data["date"];
                        $message = $data["message"];
                        $attachment = $data["attachment"];
                        $attachmentsRemoved = (bool) (int) $data["attachments_removed"];
                        $rating = $data["rating"];
                        $date = (new \App\Helpers\Functions)->fromMySQLDate($date, 1, 1);
                        $markupFormat = $markup->determineMarkupEditor("ticket_reply", isset($data["editor"]) ? $data["editor"] : "");
                        $message = $markup->transform($message, $markupFormat);
                        if ($admin) {
                            $user = "<strong>" . $admin . "</strong><br />" . $_LANG["supportticketsstaff"];
                        } else {
                            if (0 < $userid) {
                                $clientsdata = \App\Models\Client::where(array("id" => $userid));
                                $clientname = $clientsdata->value("firstname") . " " . $clientsdata->value("lastname");
                                $clientemail = $clientsdata->value("email");
                                $user = "<strong>" . $clientname . "</strong><br />" . $_LANG["supportticketsclient"];
                                if (0 < $contactid) {
                                    $contactdata = \App\Models\Contact::where(array("id" => $contactid, "userid" => $userid));
                                    $clientname = $contactdata->value("firstname") . " " . $contactdata->value("lastname");
                                    $clientemail = $contactdata->value("email");
                                    $user = "<strong>" . $clientname . "</strong><br />" . $_LANG["supportticketscontact"];
                                }
                            } else {
                                $clientname = $name;
                                $clientemail = $email;
                                $user = "<strong>" . $clientname . "</strong><br />" . $clientemail;
                            }
                        }
                        $attachments = array();
                        // $attachments = \App\Helpers\HelperTickets::getTicketAttachmentsInfo($tid, $attachment, 'ticket');
                        if ($attachment) {
                            $attachment = explode("|", $attachment);
                            $attachmentCount = 0;
                            foreach ($attachment as $filename) {
                              //   $filename = substr($filename, 7);
                                $attachments[] = $filename;
                                $allattachments[] = array("replyid" => $replyId, "i" => $attachmentCount, "filename" => $filename, "removed" => $attachmentsRemoved);
                                $attachmentCount++;
                            }
                        }
                        $ascreplies[] = array("id" => $replyId, "userid" => $userid, "contactid" => $contactid, "name" => $admin ? $admin : $clientname, "email" => $admin ? "" : $clientemail, "admin" => $admin ? true : false, "user" => $user, "date" => $date, "message" => $message, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "rating" => $rating);
                        $replies[] = $ascreplies;
                    }
                    $smartyvalues["replies"] = $replies;
                    $smartyvalues["ascreplies"] = $ascreplies;
                    krsort($ascreplies);
                    $smartyvalues["descreplies"] = $ascreplies;
                    $ratings = array();
                    for ($counter = 1; $counter <= 5; $counter++) {
                        $ratings[] = $counter;
                    }
                    $smartyvalues["ratings"] = $ratings;
                    if ($loggedInUserId) {
                        $smartyvalues["loggedin"] = (bool) $loggedInUserId;
                        $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($loggedInUserId);
                        $clientname = $clientsdetails["firstname"] . " " . $clientsdetails["lastname"];
                        $clientemail = $clientsdetails["email"];
                        if ($loggedInContactId) {
                            $contactdata = \App\Models\Contact::where(array("id" => $loggedInContactId, "userid" => $loggedInUserId));
                            $clientname = $contactdata->value("firstname") . " " . $contactdata->value("lastname");
                            $clientemail = $contactdata->value("email");
                        }
                    }
                    if (!$replyname) {
                        $replyname = $clientname;
                    }
                    if (!$replyemail) {
                        $replyemail = $clientemail;
                    }
                    $smartyvalues["errormessage"] = $errormessage;
                    $smartyvalues["clientname"] = $clientname;
                    $smartyvalues["email"] = $clientemail;
                    $smartyvalues["replyname"] = $replyname;
                    $smartyvalues["replyemail"] = $replyemail;
                    $smartyvalues["replymessage"] = $replymessage;
                    $smartyvalues["allowedfiletypes"] = implode(", ", $tickets->getAllowedAttachments());
                }
            }
            
            DB::commit();
            // outputClientArea($templatefile, false, array("ClientAreaPageViewTicket"));
            return view($templatefile, $smartyvalues);
        } catch (\Exception $e) {
            DB::rollback();
            // dd($e);
            echo $e->getMessage();
        }
    }
    public function Support_TicketDetailsOLD($id)
    {
        $ticketId = $id;
        $ticketDetails = Ticket::where('tid', $ticketId)->get();
        foreach ($ticketDetails as $ticket) {
            $tid = $ticket->tid;
            $userid = $ticket->userid;
            $title = $ticket->title;
            $message = $ticket->message;
            $date = $ticket->date;
            $attachment = $ticket->attachment;
        }
        // dd($ticketDetails);
        $clientData = Client::where('id', $userid)->get();
        foreach ($clientData as $client) {
            $fullname = "$client->firstname " . "$client->lastname";
        }
        // $test = Storage::get($attachment);
        // dd($test);
       $attachment = explode("|", $attachment);

        $carbon = new Carbon($date);
        $dateNew = $carbon->toClientDateTimeFormat();

        return view('pages.support.mytickets.ticketdetails', [
            'ticketDetails' => $ticketDetails, 'tid' => $tid, 'userid' => $userid, 'title' => $title, 'message' => $message, 'date' => $dateNew, 'fullname' => $fullname, 'attachment' => $attachment
        ]);
    }
    public function Support_NetworkStatus()
    {
        return view('pages.support.networkstatus.index');
    }
}
