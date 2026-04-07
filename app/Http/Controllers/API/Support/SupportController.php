<?php

namespace App\Http\Controllers\API\Support;

use Validator;
use Auth;
use ResponseAPI, Cfg;
use Ticket as TicketHelper;
use Customfield as CustomfieldHelper;

use App\Models\Announcement;
use App\Models\Note;
use App\Models\Ticket;
use App\Models\Ticketnote;
use App\Models\Ticketreply;
use App\Models\Ticketstatus;
use App\Models\Contact;
use App\Models\Hosting;
use App\Models\Domain;
use App\Models\Client;
use App\Models\Customfieldsvalue;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @group Support
 * 
 * APIs for managing support
 */
class SupportController extends Controller
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * AddAnnouncement
     * 
     * Adds an announcement.
     */
    public function AddAnnouncement()
    {
        $rules = [
            'date' => ['required', 'date_format:Y-m-d H:i:s'],
            'title' => ['required', 'string'],
            'announcement' => ['required', 'string'],
            'published' => ['nullable', 'boolean'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $date = $this->request->input('date');
        $title = $this->request->input('title');
        $announcement = $this->request->input('announcement');
        $published = $this->request->input('published');

        $isPublished = $published ? "1" : "0";

        $table = new Announcement;
        $table->date = $date;
        $table->title = $title;
        $table->announcement = $announcement;
        $table->published = $isPublished;
        $table->save();
        $id = $table->id;

        \App\Helpers\Hooks::run_hook("AnnouncementAdd", array("announcementid" => $id, "date" => $date, "title" => $title, "announcement" => $announcement, "published" => $isPublished));
        return ResponseAPI::Success([
            'announcementid' => $table->id,
        ]);
    }

    /**
     * DeleteAnnouncement
     * 
     * Delete an announcement
     */
    public function DeleteAnnouncement()
    {
        $rules = [
            'announcementid' => ['required', 'integer', 'exists:App\Models\Announcement,id'],
        ];

        $messages = [
            'announcementid.exists' => 'Announcement ID Not Found',
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $announcementid = $this->request->input('announcementid');

        Announcement::where('id', $announcementid)->delete();
        Announcement::where('parentid', $announcementid)->delete();

        return ResponseAPI::Success([
            'announcementid' => $announcementid,
        ]);
    }

    /**
     * AddClientNote
     * 
     * Adds a Client Note
     */
    public function AddClientNote()
    {
        $rules = [
            'userid' => ['required', 'integer', 'exists:App\Models\Client,id'],
            'notes' => ['required', 'string'],
            'sticky' => ['nullable', 'boolean'],
        ];

        $messages = [
            'userid.exists' => 'Client ID Not Found',
            'notes.required' => 'Notes can not be empty',
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $userid = $this->request->input('userid');
        $notes = $this->request->input('notes');
        $sticky = $this->request->input('sticky');

        $sticky = $sticky ? 1 : 0;
        $auth = Auth::guard('admin')->user();

        $table = new Note;
        $table->userid = $userid;
        $table->adminid = $auth ? $auth->id : 0;
        // $table->created = "now()";
        // $table->modified = "now()";
        $table->note = $notes;
        $table->sticky = $sticky;
        $table->save();

        return ResponseAPI::Success([
            'noteid' => $table->id,
        ]);
    }

    /**
     * GetAnnouncements
     * 
     * Obtain an array of announcements
     */
    public function GetAnnouncements()
    {
        $rules = [
            'limitstart' => ['nullable', 'integer'],
            'limitnum' => ['nullable', 'integer'],
        ];
    
        $validator = Validator::make($this->request->all(), $rules);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $limitstart = $this->request->input('limitstart') ?? 0;
        $limitnum = $this->request->input('limitnum') ?? 25;

        $page = $limitstart + 1;
        $mulai = ($page > 1) ? ($page * $limitnum) - $limitnum : 0;

        $query = Announcement::query();
        $totalresults = $query->count();
        $query->offset($mulai);
        $query->limit($limitnum);
        $query->orderBy('date', 'DESC');
        $results = $query->get();

        $response = [
            'announcement' => $results,
        ];

        return ResponseAPI::Success([
            'totalresults' => $totalresults,
            'startnumber' => $limitstart,
            'numreturned' => $results->count(),
            'announcements' => $results->count() > 0 ? $response : [],
        ]);
    }

    /**
     * DeleteTicketNote
     * 
     * Deletes a ticket note.
     * Removes a ticket note from the system. This cannot be undone.
     */
    public function DeleteTicketNote()
    {
        $rules = [
            'noteid' => ['required', 'integer', 'exists:App\Models\Ticketnote,id'],
        ];

        $messages = [
            'noteid.exists' => 'Note ID Not Found',
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $noteid = $this->request->input('noteid');

        Ticketnote::where('id', $noteid)->delete();

        return ResponseAPI::Success([
            'noteid' => $noteid,
        ]);
    }

    /**
     * OpenTicket
     * 
     * Open a new ticket
     */
    public function OpenTicket()
    {
        $request = $this->request;
        $contactTable = (new Contact)->getTableName();

        $rules = [
            'deptid' => ['required', 'integer', 'exists:App\Models\Ticketdepartment,id'],
            'subject' => ['required', 'string'],
            'message' => ['required', 'string'],
            'clientid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            'contactid' => [
                'nullable',
                'integer',
                Rule::exists($contactTable, 'id')->where(function($q) use ($request)  {
                    $clientid = $request->input('clientid');
                    $q->where('userid', $clientid);
                }),
            ],
            'name' => ['required_without:clientid'],
            'email' => ['nullable', 'required_without:clientid', 'email'],
            'priority' => ['nullable', 'string', Rule::in(['Low', 'Medium', 'High'])],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'serviceid' => ['nullable', 'integer'],
            'domainid' => ['nullable', 'integer'],
            'admin' => ['nullable', 'boolean'],
            'markdown' => ['nullable', 'boolean'],
            'customfields' => ['nullable', 'string'],
            // 'attachments' => ['nullable'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3000'],
        ];

        $messages = [
            'deptid.exists' => 'Department ID Not Found',
            'clientid.exists' => 'Client ID Not Found',
            'contactid.exists' => 'Contact ID Not Found',
            'name.required_without' => 'Name and email address are required if not a client',
            'email.required_without' => 'Name and email address are required if not a client',
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $deptid = $this->request->input('deptid');
        $subject = $this->request->input('subject');
        $message = $this->request->input('message');
        $clientid = $this->request->input('clientid');
        $contactid = $this->request->input('contactid') ?? 0;
        $name = $this->request->input('name');
        $email = $this->request->input('email');
        $priority = $this->request->input('priority');
        $created = $this->request->input('created');
        $serviceid = $this->request->input('serviceid') ?? 0;
        $domainid = $this->request->input('domainid');
        $admin = $this->request->input('admin');
        $useMarkdown = $this->request->input('markdown');
        $customfields = $this->request->input('customfields');
        $attachments = $this->request->file('attachments');

        if ($customfields) {
            $customfields = base64_decode($customfields);
            $customfields = (new \App\Helpers\Client())->safe_unserialize($customfields);
        }
        if (!is_array($customfields)) {
            $customfields = array();
        }

        $from = array("name" => "", "email" => "");
        if (!$clientid) {
            $from = array("name" => $name, "email" => $email);
        }

        if ($serviceid) {
            if (is_numeric($serviceid) || substr($serviceid, 0, 1) == "S") {
                $hosting = Hosting::where('id', $serviceid)->where('userid', $clientid)->first();
                if (!$hosting) {
                    return ResponseAPI::Error([
                        'message' => 'Service ID Not Found',
                    ]);
                }
                $serviceid = "S" . $hosting->id;
            } else {
                $serviceid = substr($serviceid, 1);
                $domain = Domain::where('id', $serviceid)->where('userid', $clientid)->first();
                if (!$domain) {
                    return ResponseAPI::Error([
                        'message' => 'Service ID Not Found',
                    ]);
                }
                $serviceid = "D" . $domain->id;
            }
        }

        if ($domainid) {
            $domain = Domain::where('id', $domainid)->where('userid', $clientid)->first();
            if (!$domain) {
                return ResponseAPI::Error([
                    'message' => 'Domain ID Not Found',
                ]);
            }
            $serviceid = "D" . $domain->id;
        }

        $treatAsAdmin = $admin ? true : false;
        $validationData = array("clientId" => $clientid, "contactId" => $contactid, "name" => $name, "email" => $email, "isAdmin" => $treatAsAdmin, "departmentId" => $deptid, "subject" => $subject, "message" => $message, "priority" => $priority, "relatedService" => $serviceid, "customfields" => $customfields);
        $ticketOpenValidateResults = \App\Helpers\Hooks::run_hook("TicketOpenValidation", $validationData);
        if (is_array($ticketOpenValidateResults)) {
            $hookErrors = array();
            foreach ($ticketOpenValidateResults as $hookReturn) {
                if (is_string($hookReturn) && ($hookReturn = trim($hookReturn))) {
                    $hookErrors[] = $hookReturn;
                }
            }
            if ($hookErrors) {
                return ResponseAPI::Error([
                    'message' => implode(". ", $hookErrors),
                ]);
            }
        }

        $attachmentString = [];
        if ($this->request->hasFile('attachments')) {
            foreach ($attachments as $attachment) {
                $uuid = (string) Str::uuid();
                $fileNameToSave = Str::random(6)."_".$attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
        
                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
        
                // if (!$upload) {
                //     return ResponseAPI::Error([
                //         'message' => 'File can not be uploaded to storage',
                //     ]);
                // }
            }
        }
        $attachmentString = implode('|', $attachmentString);

        $noemail = "";
        // TODO: get admin name in Ticket helper, method OpenNewTicket
        // TODO: notifyTicketChanges
        $ticketdata = TicketHelper::OpenNewTicket($clientid, $contactid, $deptid, $subject, $message, $priority, $attachmentString, $from, $serviceid, $cc = "", $noemail, $treatAsAdmin, $useMarkdown);

        if ($customfields) {
            CustomfieldHelper::SaveCustomFields($ticketdata["ID"], $customfields, "support", true);
        }

        return ResponseAPI::Success([
            "id" => $ticketdata["ID"],
            "tid" => $ticketdata["TID"],
            "c" => $ticketdata["C"],
        ]);
    }

    /**
     * AddTicketReply
     * 
     * Add a reply to a ticket by Ticket ID.
     */
    public function AddTicketReply()
    {
        $request = $this->request;
        $contactTable = (new Contact)->getTableName();

        $rules = [
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
            'message' => ['required', 'string'],
            'markdown' => ['nullable', 'boolean'],
            'clientid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            'contactid' => [
                'nullable',
                'integer',
                Rule::exists($contactTable, 'id')->where(function($q) use ($request)  {
                    $clientid = $request->input('clientid');
                    $q->where('userid', $clientid);
                }),
            ],
            'adminusername' => ['nullable', 'string'],
            'name' => ['required_without:clientid'],
            'email' => ['nullable', 'required_without:clientid', 'email'],
            'status' => ['nullable', 'string'],
            'noemail' => ['nullable', 'boolean'],
            'customfields' => ['nullable', 'string'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3000'],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];

        $messages = [
            'ticketid.exists' => 'Ticket ID Not Found',
            'clientid.exists' => 'Client ID Not Found',
            'contactid.exists' => 'Contact ID Not Found',
            'name.required_without' => 'Name and email address are required if not a client',
            'email.required_without' => 'Name and email address are required if not a client',
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
        $message = $this->request->input('message');
        $useMarkdown = (bool) (int) $this->request->input('markdown');
        $clientid = $this->request->input('clientid');
        $contactid = $this->request->input('contactid') ?? 0;
        $adminusername = $this->request->input('adminusername');
        $name = $this->request->input('name');
        $email = $this->request->input('email');
        $created = $this->request->input('created');
        $customfields = $this->request->input('customfields');
        $attachments = $this->request->file('attachments');
        $from = "";

        $attachmentString = [];
        if ($this->request->hasFile('attachments')) {
            foreach ($attachments as $attachment) {
                $uuid = (string) Str::uuid();
                $fileNameToSave = Str::random(6)."_".$attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
        
                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);

        if (!$clientid) {
            $from = array("name" => $name, "email" => $email);
        }

        // TODO: getAdminName((int) $admin)
        // TODO: notifyTicketChanges($ticketid, $changes, $recipients);
        TicketHelper::AddReply($ticketid, $clientid, $contactid, $message, $adminusername, $attachmentString, $from, $status = "", $noemail = "", true, $useMarkdown);

        if ($customfields) {
            $customfields = base64_decode($customfields);
            CustomfieldHelper::SaveCustomFields($ticketid, $customfields, "support", true);
        }

        return ResponseAPI::Success();
    }

    /**
     * AddTicketNote
     * 
     * Add a note to a ticket by Ticket ID or Ticket Number.
     */
    public function AddTicketNote()
    {
        $rules = [
            'message' => ['required', 'string'],
            'ticketnum' => ['nullable', 'integer', 'exists:App\Models\Ticket,tid'],
            'ticketid' => ['nullable', 'integer', 'exists:App\Models\Ticket,id'],
            'markdown' => ['nullable', 'boolean'],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3000'],
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
        $message = $this->request->input('message');
        $ticketnum = $this->request->input('ticketnum');
        $ticketid = $this->request->input('ticketid');
        $useMarkdown = (bool) (int) $this->request->input('markdown');
        $attachments = $this->request->file('attachments');

        $attachmentString = [];
        if ($this->request->hasFile('attachments')) {
            foreach ($attachments as $attachment) {
                $uuid = (string) Str::uuid();
                $fileNameToSave = Str::random(6)."_".$attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
        
                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);

        if ($ticketnum) {
            $table = Ticket::select('id')->where('tid', $ticketnum)->first();
        } else {
            $table = Ticket::select('id')->where('id', $ticketid)->first();
        }

        TicketHelper::AddNote($table->id ?? 0, $message, $useMarkdown, $attachmentString);

        return ResponseAPI::Success();
    }

    /**
     * DeleteTicket
     * 
     * Deletes a ticket.
     * Removes a ticket and all replies from the system. This cannot be undone.
     */
    public function DeleteTicket()
    {
        $rules = [
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

        // TODO: addticketlog($ticketid, "Deleted Ticket Reply (ID: " . $replyid . ")");
        // TODO: get admin id
        // TODO: delete tblcustomfieldsvalues
        TicketHelper::DeleteTicket($ticketid);

        return ResponseAPI::Success();
    }

    /**
     * DeleteTicketReply
     * 
     * Deletes a ticket reply.
     * Removes a specific ticket reply from the system. This cannot be undone.
     */
    public function DeleteTicketReply()
    {
        $rules = [
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
            'replyid' => ['required', 'integer', 'exists:App\Models\Ticketreply,id'],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
            'replyid.exists' => "Reply ID Not Found",
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
        $replyid = $this->request->input('replyid');

        Ticketreply::where('id', $replyid)->where('tid', $ticketid)->delete();

        return ResponseAPI::Success();
    }

    /**
     * UpdateTicketReply
     * 
     * Updates a ticket reply message.
     */
    public function UpdateTicketReply()
    {
        $rules = [
            'replyid' => ['required', 'integer', 'exists:App\Models\Ticketreply,id'],
            'message' => ['required', 'string'],
            'markdown' => ['nullable', 'boolean'],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ];

        $messages = [
            'replyid.required' => "Reply ID Required",
            'message.required' => "Message ID Required",
            'replyid.exists' => "Reply ID Not Found",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $replyid = $this->request->input('replyid');
        $message = $this->request->input('message');
        $useMarkdown = (bool) $this->request->input('markdown');
        $created = $this->request->input('created');

        $reply = Ticketreply::find($replyid);
        $reply->message = $message;
        if ($useMarkdown) {
            $editor = "plain";
            if ($useMarkdown) {
                $editor = "markdown";
            }
            $reply->editor = $editor;
        }
        $reply->save();

        return ResponseAPI::Success([
            'replyid' => $replyid,
        ]);
    }

    /**
     * UpdateTicket
     * 
     * Updates an existing ticket
     */
    public function UpdateTicket()
    {
        $rules = [
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
            'deptid' => ['nullable', 'integer', 'exists:App\Models\Ticketdepartment,id'],
            'status' => ['nullable', 'string', 'exists:App\Models\Ticketstatus,title'],
            'subject' => ['nullable', 'string'],
            'userid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
            'name' => ['required_without:userid'],
            'email' => ['nullable', 'required_without:userid', 'email'],
            'cc' => ['nullable', 'string'],
            'priority' => ['nullable', 'string', Rule::in(['Low', 'Medium', 'High'])],
            'created' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'flag' => ['nullable', 'integer'],
            'removeFlag' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string'],
            'markdown' => ['nullable', 'boolean'],
            'customfields' => ['nullable', 'string'],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
            'ticketid.required' => "Ticket ID Required",
            'priority.in' => "Invalid Ticket Priority. Valid priorities are: Low,Medium,High",
            'deptid.exists' => 'Department ID Not Found',
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
        $deptid = $this->request->input('deptid');
        $status = $this->request->input('status');
        $subject = $this->request->input('subject');
        $userid = $this->request->input('userid');
        $name = $this->request->input('name');
        $email = $this->request->input('email');
        $cc = $this->request->input('cc');
        $priority = $this->request->input('priority');
        $created = $this->request->input('created');
        $flag = $this->request->input('flag');
        $removeFlag = $this->request->input('removeFlag');
        $message = $this->request->input('message');
        $useMarkdown = $this->request->input('markdown');
        $customfields = $this->request->input('customfields');

        $ticket = Ticket::find($ticketid);

        if ($status && $status == "Closed" && $status != $ticket->status) {
            // TODO: changes
            // TODO: WHMCS\Tickets::notifyTicketChanges($id, $changes);
            TicketHelper::CloseTicket($ticketid);
        }

        if ($userid && $userid != (int) $ticket->userid) {
            $ticket->userid = $userid;
        }

        if ($name && $name != $ticket->name) {
            $ticket->name = $name;
        }

        if ($email && $email != $ticket->email) {
            $ticket->email = $email;
        }

        if ($cc && $cc != $ticket->cc) {
            $ticket->cc = $cc;
        }

        if ($message && $message != $ticket->message) {
            $ticket->message = $message;
        }

        if ($this->request->has('markdown')) {
            $markdown = "plain";
            if ($useMarkdown) {
                $markdown = "markdown";
            }
            $ticket->editor = $markdown;
        }
        $ticket->save();

        if ($customfields) {
            CustomfieldHelper::SaveCustomFields($ticketid, $customfields, "support", true);
        }

        return ResponseAPI::Success([
            'ticketid' => $ticketid,
        ]);
    }

    /**
     * AddCancelRequest
     * 
     * Adds a Cancellation Request
     * 
     * @bodyParam serviceid required|integer|exists:\App\Models\Hosting The Service ID to cancel
     * @bodyParam type nullable|string The type of cancellation. ‘Immediate’ or ‘End of Billing Period’
     * @bodyParam reason nullable|string The customer reason for cancellation
     * 
     * @return App\Helpers\ResponseAPI
     */
    public function AddCancelRequest() {
        $rules = [
            'serviceid' => ['required', 'integer', 'exists:App\Models\Hosting,id'],
            'type' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
        ];

        $messages = [
            'serviceid.required' => "Service ID Required",
            'serviceid.exists' => "Service ID Not Found",
        ];

        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        $serviceid = (int) $this->request->input('serviceid');
        $type = (string) $this->request->input('type');
        $reason = (string) $this->request->input('reason');

        $result = Hosting::select('id', 'userid')->find($serviceid);

        $serviceid = $result->id; 
        $userid = $result->userid;

        $validtypes = ["Immediate", "End of Billing Period"];

        if (!in_array($type, $validtypes)) $type = "End of Billing Period";
        if (!$reason) $reason = "None Specified (API Submission)";

        $result = (new \App\Helpers\Client())->CreateCancellationRequest($userid, $serviceid, $reason, $type);

        if ($result == "success") {
            return ResponseAPI::Success([
                'serviceid' => $serviceid,
                'userid' => $userid,
            ]);
        }
            
        return ResponseAPI::Error([
            'message' => $result,
            'serviceid' => $serviceid,
            'userid' => $userid,
        ]);

    }

    /**
     * BlockTicketSender
     * 
     * Blocks a ticket sender.
     * 
     * Blocks an unregistered ticket sender, optionally deleting the ticket. Deleting the ticket cannot be undone.
     */
    public function BlockTicketSender()
    {
        $rules = [
            // The ticket the sender opened
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
            // hould the ticket also be deleted
            'delete' => ['nullable', 'boolean'],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
            'ticketid.required' => "Ticket ID Required",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $ticketId = $this->request->input('ticketid');
        $delete = (bool) $this->request->input('delete');

        $ticket = \App\Models\Ticket::find($ticketId);
        if ($ticket->userid) {
            return ResponseAPI::Error([
                'message' => "A Client Cannot Be Blocked",
            ]);
        }
        $email = $ticket->email;
        if (!$email) {
            return ResponseAPI::Error([
                'message' => "Missing Email Address",
            ]);
        }
        $blockedAlready = \App\Models\Ticketspamfilter::where("type", "sender")->where("content", $email)->count();
        if ($blockedAlready === 0) {
            \App\Models\Ticketspamfilter::insert(array("type" => "sender", "content" => $email));
        }
        $apiresults = array("deleted" => false);
        if ($delete) {
            try {
                \App\Helpers\Ticket::deleteTicket($ticketId);
                $apiresults["deleted"] = true;
                return ResponseAPI::Success($apiresults);
            } catch (\Exception $e) {
                return ResponseAPI::Error([
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * MergeTicket
     * 
     * Merge tickets.
     * 
     * Merges multiple tickets into a single ticket. This cannot be undone.
     */
    public function MergeTicket()
    {
        $rules = [
            // The unique ticket id that mergeticketids will be merged into
            'ticketid' => ['required', 'integer', 'exists:App\Models\Ticket,id'],
            // A comma separated list of ticket ids to merge into ticketid
            'mergeticketids' => ['required', 'string'],
            // An optional subject to be set on the ticketid
            'newsubject' => ['nullable', 'string'],
        ];

        $messages = [
            'ticketid.exists' => "Ticket ID Not Found",
            'ticketid.required' => "Ticket ID Required",
        ];
    
        $validator = Validator::make($this->request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            $error = $validator->errors()->first();
    
            return ResponseAPI::Error([
                'message' => $error,
            ]);
        }

        // vars
        $masterTicketId = (int) $this->request->input("ticketid");
        $mergeTicketIds = array_filter(explode(",", $this->request->input("mergeticketids")));
        $newSubject = $this->request->input("newsubject");

        $masterTicket = \App\Models\Ticket::where("merged_ticket_id", 0)->findOrFail($masterTicketId);
        if (count($mergeTicketIds) === 0) {
            return ResponseAPI::Error([
                'message' => "Merge Ticket IDs Required",
            ]);
        }
        $invalidMergeTicketIds = array();
        foreach ($mergeTicketIds as $mergeTicketId) {
            try {
                $mergeTicket = \App\Models\Ticket::findOrFail($mergeTicketId);
            } catch (\Exception $e) {
                $invalidMergeTicketIds[] = $mergeTicketId;
            }
        }
        if (0 < count($invalidMergeTicketIds)) {
            return ResponseAPI::Error([
                'message' => "Invalid Merge Ticket IDs: " . implode(", ", $invalidMergeTicketIds),
            ]);
        }
        if ($newSubject) {
            $masterTicket->title = $newSubject;
            $masterTicket->save();
        }
        $masterTicket->mergeOtherTicketsInToThis($mergeTicketIds);
        return ResponseAPI::Success([
            "ticketid" => $masterTicketId,
        ]);
    }
}
