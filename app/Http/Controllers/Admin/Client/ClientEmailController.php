<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\FileUploader;
use App\Helpers\Functions;
use App\Helpers\LogActivity;
use App\Helpers\Message;
use App\Helpers\ResponseAPI;
use App\Helpers\Sanitize;

// Models
use App\Models\Client;
use App\Models\Email;
use App\Models\Emailtemplate;
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientEmailController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $userid = $request->userid;

        if (!AdminFunctions::checkPermission("View Email Message Log", true)) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::getNoPermissionMessage());
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientsummary.index", ['userid', $userid])
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", 'admin.clientsinvalidclientid'));
        }

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();
        
        return view('pages.clients.viewclients.clientemails.index', $templatevars);
    }

    public function displayMessage(Request $request)
    {    
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
        $content .= "<b>" .__("admin.emailssubject") .":</b> <span id=\"subject\">" .Sanitize::makeSafeForOutput($subject) ."</span></p>\n $message";

        return view('pages.clients.viewclients.clientemails.display-message', compact('title', 'content', 'message'));
    }

    public function delete(Request $request)
    {
        /*
        if (!AdminFunctions::checkPermission("Manage Quotes")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }
        */

        $id = $request->id;
        $userid = $request->userid;
        $email = Email::where("id", $id)->where("userid", $userid)->first();

        if (!$email) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }
        
        $email->delete();
        LogActivity::Save("Deleted Email (ID: {$email->id} - User ID: $userid)", $userid);

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', 'The data deleted successfully!'),
        ]);
    }

    public function resend(Request $request)
    {
        $userid = $request->userid;
        $emailid = $request->emailid;

        if (!AdminFunctions::checkPermission("View Email Message Log")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientemails.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()));
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientemails.index', ['userid' => $userid])
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid Client ID'));
        }

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["emailid"] = $emailid;

        $emailid = $request->emailid;
        $email = Email::find($emailid);

        if (!$email) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientemails.index", ['userid', $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'));
        }

        global $CONFIG;

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
        $todata = $this->getToData($type, $id);

        $numRecipients = count($todata);
        $noRecipent = false;
        if (!$numRecipients) {
           $noRecipent = AdminFunctions::infoBoxMessage(__("admin.sendmessagenoreceiptients"), __("admin.sendmessagenoreceiptientsdesc"));
        }

        $templatevars["CONFIG"] = $CONFIG;
        $templatevars["subject"] = $subject;
        $templatevars["message"] = $message;
        $templatevars["type"] = $type;
        $templatevars["noRecipent"] = $noRecipent;
        $templatevars["numRecipients"] = $numRecipients;
        $templatevars["todata"] = $todata;
        $templatevars["fromname"] = $fromname ?? $CONFIG["CompanyName"];
        $templatevars["fromemail"] = $fromemail ?? $CONFIG["Email"];

        return view('pages.clients.viewclients.clientemails.resend', $templatevars);
    }

    public function getToData($type, $id)
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
                    $todata[] = (string) $data["firstname"] . " " . $data["lastname"] . " - " . $data["domain"] . " &lt;" . $data["email"] . "&gt;";
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
                    $todata[] = (string) $data["firstname"] . " " . $data["lastname"] . " - " . $data["domain"] . " &lt;" . $data["email"] . "&gt;";
                }
            }
        }

        return $todata;
    }

    public function sendMessage(Request $request)
    {
        $messageID = $request->messageID;
        $id = $request->id;
        $userid = $request->userid;
        $action = $request->action;

        // TODO: Do action if $messageID = 0 / New Message
        // if ($action == "send" && $messageID == 0) {
        //     redir("type=" . $type . "&id=" . $id, "sendmessage.php");
        // }
        
        $emailTemplate = Emailtemplate::find($messageID);
        if (!$emailTemplate) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientemails.index", ["userid" => $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID'));
        }

        $result = Functions::sendMessage($emailTemplate, $id, "", true);

        return $this->emailResponse($result, ['userid' => $userid]);
    }

    // Deprecated: Migrated to @send function on MassmailController
    public function doResend(Request $request)
    {
        $userid = $request->userid;
        $resend = $request->resend;
        $emailid = $request->emailid;

        $qparams = [
            'userid' => $userid,
            'resend' => $resend,
            'emailid' => $emailid,
        ];

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'subject' => 'required|string',
            'fromname' => 'required|string',
            'fromemail' => 'required|string|email',
            // 'recipent' => 'required|string',
            'save' => $request->savename ? 'required_with:savename|numeric|in:0,1' : "nullable|numeric|in:0,1",
            'savename' => $request->save ? 'required_with:save|string|unique:App\Models\Emailtemplate,name,'.$request->emailid : "nullable|string",
            'attachment' => 'nullable|array',
            'attachment.*' => !empty($request->attachment) ? 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:2000' : '',
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientemails.resend', $qparams)
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $save = $request->get("save");
        $savename = $request->get("savename");
        $message = $request->get("message");
        $subject = $request->get("subject");
        $fromemail = $request->get("fromemail");
        $attachment = $request->file("attachment");
        $cc = explode(",", trim($request->get("cc")));
        $bcc = explode(",", trim($request->get("bcc")));

        if ($save && $savename) {
            $this->saveMailTemplate($request);
        }

        if (!isset($step)) {
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
        }
                    
        $attachments = [];
        if ($request->hasFile('attachment')) {
            $attachments = FileUploader::setDiskName("attachments")->multipleUpload($attachment, [
                'file_name_prefix' => 'attch',
                'file_attachment_type' => 'attachment',
                'file_upload_path' => "/",
            ]);
        }

        $mail_attachments = [];
        if ($attachments) {
            foreach ($attachments as $parts) {
                $mail_attachments[] = [
                    "displayname" => $parts["filename"], 
                    "path" => asset("/attachments/{$parts["path"]}"),
                ];
            }
        }

        $id = $userid;
        $result = Functions::sendMessage("Mass Mail Template", $id, "", true, $mail_attachments);

        // NOTE: Do we need delete attachment after sendmessage
        // if ($attachments) {
        //     foreach ($attachments as $parts) {
        //         \Storage::disk("attachments")->delete($parts["filename"]);
        //     }
        // }

        return $this->emailResponse($result, ['userid' => $userid]);
    }

    public function saveMailTemplate($request)
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

    public function emailResponse($result, $qparams)
    {
        if ($result === true) {
            return redirect()
                ->route("admin.pages.clients.viewclients.clientemails.index", $qparams)
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage(__("admin.success"), __("admin.emailsentSuccessfully")));
        }

        if ($result === false) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientemails.index", $qparams)
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage(__("admin.erroroccurred"), __("admin.emailemailAborted")));
        } 

        if (0 < strlen($result)) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientemails.index", $qparams)
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage(__("admin.erroroccurred"), __($result)));
        }
    }

    public function dtClientEmail(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = Email::where("userid", $userid);

        return datatables()->of($query)
            ->editColumn('date', function($row) {
                $date = (new HelpersClient())->fromMySQLDate($row->date, "time");
                
                return Sanitize::makeSafeForOutput($date);
            })
            ->editColumn('subject', function($row) {
                if ($row->subject == "") {
                    $row->subject = __("admin.emailsnosubject");
                }
                
                $route = route('admin.pages.clients.viewclients.clientemails.displayMessage', ['id' => $row->id]);
                $subject = "<a href=\"#\" onClick=\"window.open('$route', '', 'width=650, height=400, scrollbars=yes'); return false\">" .Sanitize::makeSafeForOutput($row->subject) ."</a>";
                
                return $subject;
            })
            ->addColumn('raw_id', function($row) use($params) {
                extract($params);

                $route = route('admin.pages.clients.viewclients.clientemails.index', [
                    'userid' => $userid, 
                    'id' => $row->id, 
                ]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->addColumn('actions', function($row) use($params) {
                extract($params);

                // TODO: Route for resend email
                $route = route('admin.pages.clients.viewclients.clientemails.resend', [
                            'userid' => $userid, 
                            'emailid' => $row->id, 
                            'resend' => true,
                        ]);
                
                $action = "";
                $action .= "<a href=\"javascript:void(0);\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-resend\" data-id=\"{$row->id}\" onclick=\"resend(this);\" title=\"Resend Email\"><i class=\"fa fa-envelope\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\"><i class=\"fa fa-trash\"></i></button>";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy("id", $order);
            })
            ->rawColumns(['raw_id', 'actions', 'subject'])
            ->addIndexColumn()
            ->toJson();
    }
    
}
