<?php

namespace App\Http\Controllers\API\Tickets;

use DB;
use Validator;
use Auth;
use ResponseAPI;
use Ticket as TicketHelper;
use App\Helpers\Database;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\Ticketstatus;
use App\Models\Ticketnote;
use App\Models\Ticketreply;
use App\Models\Ticketpredefinedreply;
use App\Models\Ticketpredefinedcat;
use App\Models\Ticketdepartment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Tickets
 * 
 * APIs for managing tickets
 */
class TicketsController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * GetSupportDepartments
     * 
     * Get the support departments and associated ticket counts
     */
    public function GetSupportDepartments()
    {
        $rules = [
            // Pass as true to not adhere to the departments the API user is a member of.
            'ignore_dept_assignments' => ['nullable', 'boolean'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $ignore_dept_assignments = $this->request->input('ignore_dept_assignments');

        $activestatuses = $awaitingreplystatuses = array();
        $result = Ticketstatus::selectRaw("title,showactive,showawaiting")->get();
        foreach ($result->toArray() as $data) {
            if ($data["showactive"]) {
                $activestatuses[] = $data['title'];
            }
            if ($data["showawaiting"]) {
                $awaitingreplystatuses[] = $data['title'];
            }
        }
        $deptfilter = "";
        if (!$ignore_dept_assignments) {
            $auth = Auth::guard('admin')->user();
            $adminId = $auth ? $auth->id : 0;
            $admin = Admin::select('supportdepts')->where('id', $adminId)->first();
            $supportdepts = $admin->supportdepts;
            $supportdepts = explode(",", $supportdepts);
            $deptids = array();
            foreach ($supportdepts as $id) {
                if (trim($id)) {
                    $deptids[] = trim($id);
                }
            }
            if (count($deptids)) {
                $deptfilter = "tblticketdepartments.id IN (" . Database::db_build_in_array($deptids) . ") ";
            }
        }

        $result = Ticketdepartment::selectRaw("id,name,(SELECT COUNT(id) FROM tbltickets WHERE merged_ticket_id = 0 AND did=tblticketdepartments.id AND status IN (" . Database::db_build_in_array($awaitingreplystatuses) . ")) AS awaitingreply,(SELECT COUNT(id) FROM tbltickets WHERE merged_ticket_id = 0 AND did=tblticketdepartments.id AND status IN (" . Database::db_build_in_array($activestatuses) . ")) AS opentickets")
            ->whereRaw($deptfilter)
            ->orderBy("name", "ASC")
            ->get();
        $apiresults = array("totalresults" => $result->count());
        foreach ($result->toArray() as $data) {
            $apiresults["departments"]["department"][] = array("id" => $data["id"], "name" => $data["name"], "awaitingreply" => $data["awaitingreply"], "opentickets" => $data["opentickets"]);
        }

        // $query = Ticketdepartment::query();
        // $query->orderBy('name', 'ASC');
        // $results = $query->get();

        // $response = [];
        // $response['totalresults'] = $results->count();
        // foreach ($results as $data) {
        //     $response["departments"]["department"][] = [
        //         "id" => $data->id,
        //         "name" => $data->name,
        //         "awaitingreply" => $data->awaitingreply,
        //         "opentickets" => $data->opentickets,
        //     ];
        // }

        return ResponseAPI::Success($apiresults);
    }

    /**
     * GetSupportStatuses
     * 
     * Get the support statuses and number of tickets in each status
     */
    public function GetSupportStatuses()
    {
        $rules = [
            // Obtain counts for a specific department id
            'deptid' => ['nullable', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $deptid = $this->request->input('deptid');

        $filters = [
            'deptid' => $deptid,
        ];

        $query = Ticketstatus::query();
        $query->filter($filters);
        $query->orderBy('sortorder', 'ASC');
        $results = $query->get();
        $totalresults = $results->count();

        $data = [];
        foreach ($results as $ticketstatus) {
            $data[] = [
                'title' => $ticketstatus->title,
                'count' => $ticketstatus->tickets->count(),
            ];
        }

        $response = [
            'status' => $data,
        ];

        return ResponseAPI::Success([
            'totalresults' => $totalresults,
            'statuses' => $totalresults > 0 ? $response : [],
        ]);
    }

    /**
     * GetTicketNotes
     * 
     * Obtain a specific ticket notes
     */
    public function GetTicketNotes()
    {
        $rules = [
            // Obtain the ticket for the specific ticket id
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $ticketid = $this->request->input('ticketid');

        $query = Ticketnote::query();
        $query->where('ticketid', $ticketid);
        $query->orderBy('date', 'ASC');
        $results = $query->get();

        return ResponseAPI::Success([
            'totalresults' => $results->count(),
            'notes' => [
                'note' => $results,
            ],
        ]);
    }

    /**
     * GetTicket
     * 
     * Obtain a specific ticket
     */
    public function GetTicket()
    {
        $rules = [
            // A specific Client Ticket Number to find tickets for.
            'ticketnum' => ['nullable', 'integer', 'exists:App\Models\Ticket,tid'],
            // A specific ticket ID to find tickets for (either $ticketnum or $ticketid is required).
            'ticketid' => ['nullable', 'integer', 'exists:App\Models\Ticket,id'],
            // ASC or DESC. The order to use to organise the ticket replies.
            'repliessort' => ['nullable', 'string', Rule::in(['ASC', 'DESC'])],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
            'ticketnum.exists' => "Ticket ID Not Found",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $ticketnum = $this->request->input('ticketnum');
        $ticketid = $this->request->input('ticketid');
        $repliessort = $this->request->input('repliessort') ?? 'ASC';

        $query = Ticket::query();
        if ($ticketnum) {
            $query->where('tid', $ticketnum);
        } else {
            $query->where('id', $ticketid);
        }
        $results = $query->first();

        if (!$results) {
            return ResponseAPI::Error([
                'message' => 'Ticket ID Not Found',
            ]);
        }

        $id = $results->id;
        $tid = $results->tid;
        $deptid = $results->did;
        $userid = $results->userid;
        $contactID = $results->contactid;
        $name = $results->name;
        $email = $results->email;
        $cc = $results->cc;
        $c = $results->c;
        $date = $results->date;
        $subject = $results->title;
        $message = $results->message;
        $status = $results->status;
        $priority = $results->urgency;
        $admin = $results->admin;
        $attachment = $results->attachment;
        $attachmentsRemoved = (bool) (int) $results->attachments_removed;
        $lastreply = $results->lastreply;
        $flag = $results->flag;
        $service = $results->service;
        $message = strip_tags($message);

        if ($userid) {
            $client = $results->client;
            $name = $client->firstname . " " . $client->lastname;
            if ($client->companyname) {
                $name .= " (" . $client->companyname . ")";
            }
            $email = $client->email;
            if ($contactID) {
                $contact = $results->contact;
                $contactName = (string) $contact->firstname . " " . $contact->lastname;
                if ($contact->companyname) {
                    $contactName .= " (" . $contact->companyname . ")";
                }
                $contactEmail = $contact->email;
            }
        }

        $response = [
            "ticketid" => $id,
            "tid" => $tid,
            "c" => $c,
            "deptid" => $deptid,
            "deptname" => TicketHelper::GetDepartmentName($deptid),
            "userid" => $userid,
            "contactid" => $contactID,
            "name" => $name,
            "email" => $email,
            "cc" => $cc,
            "date" => $date,
            "subject" => $subject,
            "status" => $status,
            "priority" => $priority,
            "admin" => $admin,
            "lastreply" => $lastreply,
            "flag" => $flag,
            "service" => $service
        ];

        $first_reply = [
            "replyid" => "0",
            "userid" => $userid,
            "contactid" => $contactID,
            "name" => isset($contactName) ? $contactName : $name,
            "email" => isset($contactEmail) ? $contactEmail : $email,
            "date" => $date,
            "message" => $message,
            "attachment" => $attachment,
            "attachments_removed" => $attachmentsRemoved,
            "admin" => $admin
        ];

        $sortorder = $repliessort;
        if ($sortorder == "ASC") {
            $response["replies"]["reply"][] = $first_reply;
        }

        $ticketreplies = Ticketreply::where('tid', $id)->orderBy('id', $sortorder)->get();
        foreach ($ticketreplies as $ticketreply) {
            $replyid = $ticketreply->id;
            $userid = $ticketreply->userid;
            $contactID = $ticketreply->contactid;
            $name = $ticketreply->name;
            $email = $ticketreply->email;
            $date = $ticketreply->date;
            $message = $ticketreply->message;
            $attachment = $ticketreply->attachment;
            $attachmentsRemoved = (bool) (int) $ticketreply->attachments_removed;
            $admin = $ticketreply->admin;
            $rating = $ticketreply->rating;
            $message = strip_tags($message);
            if ($userid) {
                $client = $ticketreply->client;
                $name = $client->firstname . " " . $client->lastname;
                if ($client->companyname) {
                    $name .= " (" . $client->companyname . ")";
                }
                $email = $client->email;
                if ($contactID) {
                    $contact = $results->contact;
                    $name = (string) $contact->firstname . " " . $contact->lastname;
                    if ($contact->companyname) {
                        $name .= " (" . $contact->companyname . ")";
                    }
                    $email = $contact->email;
                }
            }
            $response["replies"]["reply"][] = [
                "replyid" => $replyid,
                "userid" => $userid,
                "contactid" => $contactID,
                "name" => $name,
                "email" => $email,
                "date" => $date,
                "message" => $message,
                "attachment" => $attachment,
                "attachments_removed" => $attachmentsRemoved,
                "admin" => $admin,
                "rating" => $rating,
            ];
        }

        if ($sortorder != "ASC") {
            $response["replies"]["reply"][] = $first_reply;
        }
        $response["notes"] = array();
        foreach ($results->notes as $ticketnote) {
            $noteid = $ticketnote->id;
            $admin = $ticketnote->admin;
            $date = $ticketnote->date;
            $message = $ticketnote->message;
            $attachment = $ticketnote->attachments;
            $attachmentsRemoved = (bool) (int) $ticketnote->attachments_removed;
            $response["notes"]["note"][] = [
                "noteid" => $noteid,
                "date" => $date,
                "message" => $message,
                "attachment" => $attachment,
                "attachments_removed" => $attachmentsRemoved,
                "admin" => $admin,
            ];
        }

        return ResponseAPI::Success($response);
    }

    /**
     * GetTicketPredefinedReplies
     * 
     * Obtain the Predefined Ticket Replies
     */
    public function GetTicketPredefinedReplies()
    {
        $rules = [
            // Obtain predefined replies for a specific category id
            'catid' => ['nullable', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $catid = $this->request->input('catid');

        $filters = [
            'catid' => $catid,
        ];

        $query = Ticketpredefinedreply::query();
        $query->filter($filters);
        $totalresults = $query->count();
        $query->orderBy('name', 'ASC');
        $results = $query->get();

        $response = [];
        $response['totalresults'] = $totalresults;
        foreach ($results as $data) {
            $response["predefinedreplies"]["predefinedreply"][] = [
                "name" => $data->name,
                "reply" => $data->reply,
            ];
        }

        return ResponseAPI::Success($response);
    }

    /**
     * GetTicketPredefinedCats
     * 
     * Obtain the Predefined Ticket Reply Categories
     */
    public function GetTicketPredefinedCats()
    {
        $ticketpredefinedreplyTable = (new Ticketpredefinedreply)->getTableName();
        $ticketpredefinedcatTable = (new Ticketpredefinedcat)->getTableName();

        $totalresults = Ticketpredefinedcat::all()->count();

        $query = Ticketpredefinedcat::query();
        $query->select(["{$ticketpredefinedcatTable}.*", DB::raw("COUNT(r.id) as replycount")]);
        $query->leftJoin("{$ticketpredefinedreplyTable} as r", "{$ticketpredefinedcatTable}.id", "=", "r.catid");
        $query->groupBy("{$ticketpredefinedcatTable}.id");
        $query->orderBy("{$ticketpredefinedcatTable}.name", "ASC");
        $results = $query->get();

        $response = [];
        $response['totalresults'] = $totalresults;
        foreach ($results as $data) {
            $response["categories"]["category"][] = $data;
        }

        return ResponseAPI::Success($response);
    }

    /**
     * GetTicketAttachment
     * 
     * Retrieve a single attachment.
     * 
     * Retrieves a single attachment from a ticket, reply or note with filename and base64 encoded file contents.
     */
    public function GetTicketAttachment()
    {
        $rules = [
            // The unique id for the type
            'relatedid' => ['required', 'integer'],
            // One of ticket, reply, note
            'type' => ['required', 'string', Rule::in(['ticket', 'reply', 'note'])],
            // The numerical index of the attachment to get
            'index' => ['required', 'integer'],
        ];

        $messages = [
            'type.in' => "Invalid Type. Must be one of ticket, reply, note",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $relatedId = $this->request->input("relatedid");
        $type = $this->request->input("type");
        $index = $this->request->input("index");

        $field = "attachment";
        switch ($type) {
            case "reply":
                $table = "tblticketreplies";
                break;
            case "note":
                $table = "tblticketnotes";
                $field = "attachments";
                break;
            default:
                $table = "tbltickets";
        }
        $relatedData = DB::table($table)->find($relatedId, array($field, "attachments_removed"));
        if (!$relatedData) {
            return ResponseAPI::Error([
                'message' => "Related ID Not Found",
            ]);
        }
        if (!$relatedData->{$field}) {
            return ResponseAPI::Error([
                'message' => "No Attachments Found",
            ]);
        }
        if ($relatedData->attachments_removed) {
            return ResponseAPI::Error([
                'message' => "Attachments Deleted",
            ]);
        }
        $attachments = explode("|", $relatedData->{$field});
        if (!array_key_exists($index, $attachments)) {
            return ResponseAPI::Error([
                'message' => "Invalid Attachment Index",
            ]);
        }
        $file = $attachments[$index];
        $fileName = substr($file, 7);
        // $fileName = $file;
        $storage = \Storage::disk('attachments');
        try {
            if (!$storage->exists($file)) {
                throw new \Exception("File not found at path - {$fileName}");
            }
            $stream = $storage->readStream($file);
            $data = base64_encode(stream_get_contents($stream));
            fclose($stream);
            // $data = $storage->get($fileName);
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }

        return ResponseAPI::Success([
            "filename" => $fileName,
            "data" => $data,
        ]);
    }

    /**
     * GetTickets
     * 
     * Obtain tickets matching the passed criteria
     */
    public function GetTickets()
    {
        $auth = Auth::guard('admin')->user();

        $rules = [
            // The offset for the returned quote data (default: 0)
            'limitstart' => ['nullable', 'integer'],
            // The number of records to return (default: 25)
            'limitnum' => ['nullable', 'integer'],
            // Obtain tickets in a specific department
            'deptid' => ['nullable', 'integer'],
            // Find tickets for a specific client id
            'clientid' => ['nullable', 'integer'],
            // Find tickets for a specific non-client email address
            'email' => ['nullable', 'string'],
            // Find tickets matching a specific status. Any configured status plus: Awaiting Reply, All Active Tickets, My Flagged Tickets
            'status' => ['nullable', 'string'],
            // Find tickets containing a specific subject - uses approximate string matching.
            'subject' => ['nullable', 'string'],
            // Pass as true to not adhere to the departments the API user is a member of.
            'ignore_dept_assignments' => ['nullable', 'boolean'],
        ];

        $messages = [];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $limitstart = $this->request->input("limitstart");
        $limitnum = $this->request->input("limitnum");
        $status = $this->request->input("status");
        $subject = $this->request->input("subject");
        $email = $this->request->input("email");

        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $deptid = (int) $this->request->input("deptid");
        $clientid = (int) $this->request->input("clientid");
        $filters = array("merged_ticket_id=0");
        if ($deptid) {
            $filters[] = "did=" . (int) $deptid;
        }
        if ($clientid) {
            $filters[] = "userid=" . (int) $clientid;
        }
        if (!empty($email)) {
            $filters[] = "(email='" . \App\Helpers\Database::db_escape_string($email) . "' OR userid=(SELECT id FROM tblclients WHERE email='" . \App\Helpers\Database::db_escape_string($email) . "'))";
        }
        if ($status == "Awaiting Reply") {
            $statusfilters = array();
            $result = \App\Models\Ticketstatus::select('title')->where(array("showawaiting" => "1"))->get();
            foreach ($result->toArray() as $data) {
                $statusfilters[] = $data['title'];
            }
            $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilters) . ")";
        } else {
            if ($status == "All Active Tickets") {
                $statusfilters = array();
                $result = \App\Models\Ticketstatus::select('title')->where(array("showactive" => "1"))->get();
                foreach ($result->toArray() as $data) {
                    $statusfilters[] = $data['title'];
                }
                $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilters) . ")";
            } else {
                if ($status == "My Flagged Tickets") {
                    $statusfilters = array();
                    $result = \App\Models\Ticketstatus::select('title')->where(array("showactive" => "1"))->get();
                    foreach ($result->toArray() as $data) {
                        $statusfilters[] = $data['title'];
                    }
                    $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilters) . ") AND flag=" . $auth->id;
                } else {
                    if ($status) {
                        $filters[] = "status='" . \App\Helpers\Database::db_escape_string($status) . "'";
                    }
                }
            }
        }
        if (isset($subject)) {
            $filters[] = "title LIKE '%" . \App\Helpers\Database::db_escape_string($subject) . "%'";
        }
        if (empty($ignore_dept_assignments)) {
            $result = \App\Models\Admin::select('supportdepts')->where('id', $auth->id)->first();
            $data = $result->toArray();
            $supportdepts = $data['supportdepts'];
            $supportdepts = explode(",", $supportdepts);
            $deptids = array();
            foreach ($supportdepts as $id) {
                if (trim($id)) {
                    $deptids[] = trim($id);
                }
            }
            if (0 < count($deptids)) {
                $filters[] = "did IN (" . \App\Helpers\Database::db_build_in_array($deptids) . ")";
            } else {
                $filters[] = "did = 0";
            }
        }
        $where = implode(" AND ", $filters);
        $result = \App\Models\Ticket::selectRaw("COUNT(id) as total")->whereRaw($where)->first();
        $data = $result->toArray();
        $totalresults = $data['total'];
        $apiresults = array("totalresults" => $totalresults, "startnumber" => $limitstart);
        $result = \App\Models\Ticket::whereRaw($where)->orderBy('lastreply', 'DESC')->offset($limitstart)->limit($limitnum)->get();
        $result = $result->makeVisible(array("flag", "adminunread", "clientunread", "replyingadmin", "replyingtime"));
        $apiresults["numreturned"] = $result->count();
        foreach ($result->toArray() as $data) {
            $id = $data["id"];
            $tid = $data["tid"];
            $deptid = $data["did"];
            $userid = $data["userid"];
            $name = $data["name"];
            $email = $data["email"];
            $cc = $data["cc"];
            $c = $data["c"];
            $date = $data["date"];
            $subject = $data["title"];
            $message = $data["message"];
            $status = $data["status"];
            $priority = $data["urgency"];
            $admin = $data["admin"];
            $attachment = $data["attachment"];
            $attachmentsRemoved = (bool) (int) $data["attachments_removed"];
            $lastreply = $data["lastreply"];
            $flag = $data["flag"];
            $service = $data["service"];
            if ($userid) {
                $result2 = \App\Models\Client::find($userid);
                if ($result2) {
                    $data = $result2->toArray();
                    $name = $data["firstname"] . " " . $data["lastname"];
                    if ($data["companyname"]) {
                        $name .= " (" . $data["companyname"] . ")";
                    }
                    $email = $data["email"];
                }
            }
            $apiresults["tickets"]["ticket"][] = array("id" => $id, "tid" => $tid, "deptid" => $deptid, "userid" => $userid, "name" => $name, "email" => $email, "cc" => $cc, "c" => $c, "date" => $date, "subject" => $subject, "status" => $status, "priority" => $priority, "admin" => $admin, "attachment" => $attachment, "attachments_removed" => $attachmentsRemoved, "lastreply" => $lastreply, "flag" => $flag, "service" => $service);
        }
        return ResponseAPI::Success($apiresults);
    }

    public function GetTicketCounts()
    {
        $auth = Auth::guard('admin')->user();

        $rules = [
            // Pass as true to not adhere to the departments the API user is a member of.
            'ignoreDepartmentAssignments' => ['nullable', 'boolean'],
            // Pass as true to not adhere to the departments the API user is a member of.
            'includeCountsByStatus' => ['nullable', 'boolean'],
        ];

        $messages = [];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $adminId = $auth ? $auth->id : 0;
        
        $showActive = $showAwaiting = array();
        $ticketStatuses = DB::table("tblticketstatuses")->get(array("title", "showactive", "showawaiting"));
        foreach ($ticketStatuses as $status) {
            if ($status->showactive) {
                $showActive[] = $status->title;
            }
            if ($status->showawaiting) {
                $showAwaiting[] = $status->title;
            }
        }
        $applyDepartmentFilter = (bool) (!$this->request->input("ignoreDepartmentAssignments"));
        $adminSupportDepartmentsQuery = array();
        if ($applyDepartmentFilter) {
            // $adminSupportDepartments = get_query_val("tbladmins", "supportdepts", array("id" => $adminId));
            $adminSupportDepartments = \App\Models\Admin::where(array("id" => $adminId))->value("supportdepts") ?? "";
            $adminSupportDepartments = explode(",", $adminSupportDepartments);
            foreach ($adminSupportDepartments as $departmentId) {
                if (trim($departmentId)) {
                    $adminSupportDepartmentsQuery[] = (int) $departmentId;
                }
            }
        }
        $appConfig = config("portal.config");
        if (array_key_exists("disable_admin_ticket_page_counts", $appConfig) && $appConfig["disable_admin_ticket_page_counts"]) {
            $allActive = "x";
            $awaitingReply = "x";
            $flaggedTickets = "x";
        } else {
            $flaggedTickets =DB::table("tbltickets")->where("merged_ticket_id", 0)->whereIn("status", $showActive)->where("flag", (int) $adminId)->count();
            $query =DB::table("tbltickets")->where("merged_ticket_id", 0);
            if (0 < count($adminSupportDepartmentsQuery)) {
                $query->whereIn("did", $adminSupportDepartmentsQuery);
            }
            $allActive = $query->whereIn("status", $showActive)->count();
            $query =DB::table("tbltickets")->where("merged_ticket_id", 0);
            if (0 < count($adminSupportDepartmentsQuery)) {
                $query->whereIn("did", $adminSupportDepartmentsQuery);
            }
            $awaitingReply = $query->whereIn("status", $showAwaiting)->count();
            unset($allTickets);
        }
        $apiresults = array("result" => "success", "filteredDepartments" => $adminSupportDepartmentsQuery, "allActive" => $allActive, "awaitingReply" => $awaitingReply, "flaggedTickets" => $flaggedTickets);
        if ($this->request->input("includeCountsByStatus")) {
            $ticketCounts = array();
            $ticketStatuses = DB::table("tblticketstatuses")->pluck(DB::raw("0"), "title");
            $tickets = DB::table("tbltickets")->where("merged_ticket_id", "=", "0")->selectRaw("status, COUNT(*) as count")->groupBy("status")->pluck("count", "status");
            foreach ($tickets as $status => $count) {
                $ticketStatuses[$status] = $count;
            }
            foreach ($ticketStatuses as $ticketStatus => $ticketCount) {
                $ticketCounts[preg_replace("/[^a-z0-9]/", "", strtolower($ticketStatus))] = array("title" => $ticketStatus, "count" => $ticketCount);
            }
            $apiresults["status"] = $ticketCounts;
        }

        return ResponseAPI::Success($apiresults);
    }
}
