<?php

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Helpers\Cfg;
use Illuminate\Support\Facades\DB;
use App\Helpers\Database;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Ticket as TicketHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class SupportticketsController extends Controller
{
    protected $prefix;
    protected $adminURL;

    public function __construct()
    {
        $this->prefix = Database::prefix();
        $this->adminURL = request()->segment(1) . '/';
    }

    public function index(Request $request)
    {
        $user = (int)$request->userid;
        $client = \App\Models\Client::find($user);
        $loggedInUser = Auth::guard('admin')->user()->id;
        $request->session()->put('adminid', $loggedInUser);

        $getDepartemet = \App\Models\Ticketdepartment::select('id', 'name')->get();
        $getStatus = \App\Models\Ticketstatus::select('id', 'title')->get();
        $getTag = \App\Models\Tickettag::select('id', 'tag')->get();
        $params = [
            'dep' => $getDepartemet,
            'status' => $getStatus,
            'tag' => $getTag,
            'user' => $client
        ];

        return view('pages.support.supporttickets.index', $params);
    }

public function getTablesWithUserId($userid)
    {
        // Retrieve tables with a 'userid' column
        $tables = \DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = 'userid' AND TABLE_SCHEMA = DATABASE()");
        $relatedData = [];

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            $records = \DB::table($tableName)->where('userid', $userid)->get();

            if (!$records->isEmpty()) {
                $relatedData[$tableName] = $records;
            }
        }

        return $relatedData;
    }
    
    public function SupportTicketsGet(Request $request)
    {
        $userid = (int) $request->client;
        $dep = $request->department_name ?? [];
        $status = $request->status ?? [];
        $urgency = $request->priority;
        $subject = $request->subject ?? '';
        $email = $request->email ?? '';
        $ticket = $request->ticket ?? '';

        $data = DB::table("{$this->prefix}tickets as t")
            ->join("{$this->prefix}clients as c", "t.userid", "=", "c.id")
            ->join("{$this->prefix}ticketdepartments as d", "t.did", "=", "d.id")
            ->select('t.id', 't.tid', 't.userid', 't.title as subject', 't.message', 't.adminunread', 't.urgency', 'd.name as departement', 'c.firstname', 'c.lastname', 't.status', 't.lastreply');

        if ($request->client) {
            $data->where('t.userid', $userid);
        }

        if ($dep) {
            $data->whereIn('t.did', $dep);
        }
        if ($status) {
            $data->whereIn('t.status', $status);
        }

        if (!empty($urgency)) {
            $data->whereIn('t.urgency', $urgency);
        }

        if ($subject) {
            $data->where('t.title', $subject);
        }

        if ($email) {
            $data->where('t.email', $email);
        }

        if ($ticket) {
            $data->where('t.id', $ticket);
        }

        return Datatables::of($data)
            ->addColumn('submitter', function ($data) {
                $url = url($this->adminURL . 'clients/clientsummary?userid=' . $data->userid);
                return '<a href="' . $url . '" class="text-dark">' . $data->firstname . ' ' . $data->lastname . '</a>';
            })
            ->addColumn('urgency', function ($data) {
                switch ($data->urgency) {
                    case 'Medium':
                        return "<div class=\"text-warning\">" . $data->urgency . "</div>";
                    case 'High':
                        return "<div class=\"text-danger\">" . $data->urgency . "</div>";
                    default:
                        return "<div class=\"text-info\">" . $data->urgency . "</div>";
                }
            })
            ->addColumn('checkbox', function ($data) {
                return '<div class="custom-control custom-checkbox"><input type="checkbox" name="selectedtickets[]" value="' . $data->id . '" class="custom-control-input" id="ordercheck1"><label class="custom-control-label" for="ordercheck1">&nbsp;</label></div>';
            })
            ->editColumn('lastreply', function ($data) {
                $now = Carbon::now();
                $datework = Carbon::parse($data->lastreply);
                $diff = $datework->diff($now);
                return "{$diff->d}d {$diff->h}h {$diff->i}m ";
            })
            ->editColumn('subject', function ($data) use ($request) {
                $adminread = explode(',', $data->adminunread);
                if (!in_array($request->session()->get('adminid'), $adminread)) {
                    return "<a title=\"Unread\" href=\"" . url($this->adminURL . 'support/supporttickets/' . $data->id . '/view') . "\" class=\"text-dark\" style=\"font-weight: 900; text-decoration: underline ;\"><div class=\"badge badge-danger mr-1\">New</div>#{$data->tid} - {$data->subject}</a>";
                } else {
                    return "<a href=\"" . url($this->adminURL . 'support/supporttickets/' . $data->id . '/view') . "\" class=\"text-dark\">#{$data->tid} - {$data->subject}</a>";
                }
            })
            ->rawColumns(['urgency', 'submitter', 'checkbox', 'subject'])
            ->toJson();
    }

    public function OpenNewTickets(Request $request)
    {
        $user = (int)$request->userid;
        $client = \App\Models\Client::find($user);
        $getDepartemet = \App\Models\Ticketdepartment::select('id', 'name')->get();

        $products = \App\Models\Product::select('id', 'name', 'gid')
            ->with(['group' => function($query) {
                $query->select('id', 'name');
            }])
            ->get()
            ->groupBy(function($product) {
                return $product->group ? $product->group->name : 'None';
            });

        $params = [
            'dep' => $getDepartemet,
            'user' => $client,
            'products' => $products
        ];

        return view('pages.support.opennewtickets.index', $params);
    }

    public function OpenNewTicketsStore(Request $request)
    {
        $rules = [
            'clientid' => 'required|int',
            'deptid' => 'required|int',
            'subject' => 'required',
            'message' => 'required',
        ];
        $messages = [
            'client.required' => 'client required.',
            'client.int' => 'client required.',
            'department.required' => 'select department.',
            'message.required' => 'message required.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $cliendDAta = \App\Models\Client::find($request->clientid);
        $from = [
            'name' => $cliendDAta->firstname . ' ' . $cliendDAta->lastname,
            'email' => $cliendDAta->email
        ];

        $service = ($request->serviceid != '') ? 'd' . $request->serviceid : '';
        $serviceid = $service . $request->related_service;
        $attachments = [];

        if ($request->serviceid != '') {
            $serviceid = 'S' . $request->serviceid;
        }

        if ($request->domainid != '') {
            $serviceid = 'D' . $request->domainid;
        }

        $attachmentString = $this->attachmentString($request);

        $ticketdata = TicketHelper::OpenNewTicket(
            $request->clientid,
            $contactid = '',
            $request->deptid,
            $request->subject,
            $request->message,
            $request->priority,
            $attachmentString,
            $from,
            $serviceid,
            $cc = "",
            $noemail = '',
            $treatAsAdmin = true,
            'markdown'
        );
        $msg = implode(' | ', $ticketdata);
        return redirect($this->adminURL . 'support/supporttickets/' . $ticketdata['ID'] . '/view')->with('success', 'Ticket created successfully. ' . $msg);
    }

    public function getClientsWithProduct(Request $request)
    {
        $productId = $request->product_id;
        
        // Get all clients who have this product
        $clients = \App\Models\Hosting::where('packageid', $productId)
            ->with('client')
            ->get()
            ->map(function($hosting) {
                return $hosting->client;
            })
            ->unique('id');

        $html = '';
        $clientIds = [];
        
        foreach ($clients as $client) {
            $clientIds[] = $client->id;
            $html .= '<tr>';
            $html .= '<td><input type="checkbox" name="client_checkbox[]" value="'.$client->id.'" checked></td>';
            $html .= '<td>'.$client->firstname.' '.$client->lastname.'</td>';
            $html .= '<td>'.$client->email.'</td>';
            // Add other columns as needed
            $html .= '</tr>';
        }

        return response()->json([
            'success' => true,
            'html' => $html,
            'client_ids' => $clientIds
        ]);
    }

    public function clientlog(Request $request)
    {
        $userID = (int) $request->userID;
        $data = \App\Models\ActivityLog::where('userid', $userID);
        return Datatables::of($data)->toJson();
    }

    public function tiketother(Request $request)
    {
        $userID = (int) $request->userID;
        $data = DB::table($this->prefix . 'tickets as t')
            ->join($this->prefix . 'ticketdepartments as d', 't.did', 'd.id')
            ->where('t.userid', $userID)
            ->select('t.id', 't.title', 't.status', 't.lastreply', 't.date', 'd.name');
        return Datatables::of($data)->toJson();
    }

    public function ViewTicketNEW(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            return redirect()->route('admin.pages.support.supporttickets.view', $id);
        } else {
            return redirect()->route('admin.pages.support.supporttickets.index');
        }
    }

    public function ViewTicket(Request $request, $id)
    {
        $action = $request->input('action');
        $id = (int)$id;
        $aInt = new \App\Helpers\Admin();
        $tiket = \App\Models\Ticket::findOrFail($id);
        $status = \App\Models\Ticketstatus::orderBy('title')->select('id', 'title', 'color')->get();
        $tiketlog = \App\Models\Ticketlog::where('tid', $id)->get();
        $deptID = (int)$tiket->did ?? 0;
        $getCustomFields = \App\Helpers\Customfield::getCustomFields('support', $deptID, $id, true);
        $getDepartemet = \App\Models\Ticketdepartment::select('id', 'name')->get();
        $signature = $request->input('signature');
        $flag = (int) $tiket->flag;
        $admin = \App\Models\Admin::select("id", "firstname", "lastname", "supportdepts")->where('disabled', 0)->orWhere('id', $flag)->orderBy('firstname')->get();
        // $rep = \App\Helpers\HelperTickets::getReplayAndNote($id);
        // // Kemudian urutkan replies nya
        // if (isset($rep['replies'])) {
        //     $rep['replies'] = collect($rep['replies'])->sortByDesc('date')->values()->all();
        // }

        
        // $rep = \App\Helpers\HelperTickets::getReplayAndNote($id);
        // // Convert the replies array to a collection and sort it by timestamp
        // $rep['replies'] = collect($rep['replies'])->sortByDesc(function($reply) {
        //     // Create timestamp from friendlydate and friendlytime
        //     return strtotime($reply['friendlydate'] . ' ' . $reply['friendlytime']);
        // })->values()->all();

//         $rep = \App\Helpers\HelperTickets::getReplayAndNote($id);

// // Urutkan replies dari yang lama ke yang baru
// $rep['replies'] = collect($rep['replies'])
//     ->sortBy(function($reply) {
//         // Jika format lengkap (yyyy-mm-dd)
//         if (strpos($reply['friendlydate'], '-') !== false) {
//             return strtotime($reply['friendlydate'] . ' ' . $reply['friendlytime']);
//         }
//         // Jika format pendek (hanya waktu)
//         else {
//             // Asumsikan tanggal paling awal untuk format pendek
//             return strtotime('2000-01-01 ' . $reply['friendlytime']);
//         }
//     })
//     ->values()
//     ->all();
$rep = \App\Helpers\HelperTickets::getReplayAndNote($id);

// Pisahkan replies menjadi dua kelompok
$shortFormat = [];
$longFormat = [];

foreach ($rep['replies'] as $reply) {
    if (!strpos($reply['friendlydate'], '-')) {
        $shortFormat[] = $reply;
    } else {
        $longFormat[] = $reply;
    }
}

// Urutkan format pendek berdasarkan waktu
$shortFormat = collect($shortFormat)
    ->sortByDesc(function($reply) {
        return strtotime('2025-02-26 ' . $reply['friendlytime']);
    })
    ->values()
    ->all();

// Urutkan format panjang berdasarkan date field
$longFormat = collect($longFormat)
    ->sortByDesc(function($reply) {
        // Konversi format tanggal "dd/mm/yyyy HH:MM" ke timestamp
        $dateParts = explode(' ', $reply['date']);
        $date = str_replace('/', '-', $dateParts[0]); // convert dd/mm/yyyy to dd-mm-yyyy
        return strtotime($date . ' ' . $dateParts[1]);
    })
    ->values()
    ->all();

// Debug log untuk memeriksa pengurutan
\Log::info('Sorted Long Format Timestamps:', array_map(function($reply) {
    $dateParts = explode(' ', $reply['date']);
    $date = str_replace('/', '-', $dateParts[0]);
    return [
        'time' => $reply['friendlytime'],
        'date' => $reply['date'],
        'timestamp' => strtotime($date . ' ' . $dateParts[1])
    ];
}, $longFormat));

// Gabungkan kembali dengan format pendek di atas
$rep['replies'] = array_merge($shortFormat, $longFormat);


        \App\Helpers\Ticket::AdminRead($id);

        $jquerycode = "\n(function() {\n    var fieldSelection = {\n        addToReply: function() {\n            var url = arguments[0] || '',\n                title = arguments[1] || ''\n                e = this.jquery ? this[0] : this,\n                text = '';\n\n            if (title != '') {\n                text = '[' + title + '](' + url + ')';\n            } else {\n                text = url;\n            }\n\n            return (\n                ('selectionStart' in e && function() {\n                    if (e.value==\"\\n\\n" . str_replace("\r\n", "\\n", $signature) . "\") {\n                        e.selectionStart=0;\n                        e.selectionEnd=0;\n                    }\n                    e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);\n                    e.focus();\n                    return this;\n                }) ||\n                (document.selection && function() {\n                    e.focus();\n                    document.selection.createRange().text = text;\n                    return this;\n                }) ||\n                function() {\n                    e.value += text;\n                    return this;\n                }\n            )();\n        }\n    };\n    jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });\n    })();\n\$(\"#addfileupload\").click(function () {\n    \$(\"#fileuploads\").append(\"<input type=\\\"file\\\" name=\\\"attachments[]\\\" class=\\\"form-control top-margin-5\\\">\");\n    return false;\n});\n\$(\"#predefq\").keyup(function () {\n    var intellisearchlength = \$(\"#predefq\").val().length;\n    if (intellisearchlength>2) {\n    WHMCS.http.jqClient.post(\"supporttickets.php\", { action: \"loadpredefinedreplies\", predefq: \$(\"#predefq\").val(), token: \"" . csrf_token() . "\" },\n        function(data){\n            \$(\"#prerepliescontent\").html(data);\n        });\n    }\n});\n\$(\"#frmOpenTicket\").submit(function (e, options) {\n    options = options || {};\n\n    \$(\"#btnOpenTicket\").attr(\"disabled\", \"disabled\");\n    \$(\"#btnOpenTicket i\").removeClass(\"fa-plus\").addClass(\"fa-spinner fa-spin\");\n\n    if (options.skipValidation) {\n        return true;\n    }\n\n    e.preventDefault();\n\n    var gotValidResponse = false,\n        postReply = false,\n        responseMsg = '',\n        thisElement = jQuery(this);\n\n    WHMCS.http.jqClient.post(\n        \"supporttickets.php\",\n        {\n            action: \"validatereply\",\n            id: 'opening',\n            status: 'new',\n            token: '" . csrf_token() . "'\n        },\n        function(data){\n            gotValidResponse = true;\n            if (data.valid) {\n                postReply = true;\n            } else {\n                // access denied\n                responseMsg = 'Access Denied. Please try again.';\n            }\n        }, \"json\")\n        .always(function() {\n            if (!gotValidResponse) {\n                responseMsg = 'Session Expired. Please <a href=\"javascript:location.reload()\" class=\"alert-link\">reload the page</a> before continuing.';\n            }\n\n            if (responseMsg) {\n                postReply = false;\n                \$(\"#replyingAdminMsg\").html(responseMsg);\n                \$(\"#replyingAdminMsg\").removeClass('alert-info').addClass('alert-warning');\n                if (!\$(\"#replyingAdminMsg\").is(\":visible\")) {\n                    \$(\"#replyingAdminMsg\").hide().removeClass('hidden').slideDown();\n                }\n                \$('html, body').animate({\n                    scrollTop: \$(\"#replyingAdminMsg\").offset().top - 15\n                }, 400);\n            }\n\n            if (postReply) {\n                \$(\"#replyingAdminMsg\").slideUp();\n                thisElement.attr('data-no-clear', 'false');\n                \$(\"#frmOpenTicket\").trigger('submit', { 'skipValidation': true });\n            } else {\n                \$(\"#btnOpenTicket\").removeAttr(\"disabled\");\n                \$(\"#btnOpenTicket i\").removeClass(\"fa-spinner fa-spin\").addClass(\"fa-plus\");\n            }\n        }\n    );\n});\n";
        $jquerycode .= "function insertKBLink(url, title) {\n    \$(\"#replymessage\").addToReply(url, title);\n}\nfunction selectpredefcat(catid) {\n    WHMCS.http.jqClient.post(\"supporttickets.php\", { action: \"loadpredefinedreplies\", cat: catid, token: \"" . csrf_token() . "\" },\n    function(data){\n        \$(\"#prerepliescontent\").html(data);\n    });\n}\nfunction loadpredef(catid) {\n    \$(\"#prerepliescontainer\").slideToggle();\n    \$(\"#prerepliescontent\").html('<img src=\"images/loading.gif\" align=\"top\" /> " . $aInt->lang("global", "loading") . "');\n    \$ajax(\"supporttickets.php\", { action: \"loadpredefinedreplies\", cat: catid, token: \"" . csrf_token() . "\" },\n    function(data){\n        \$(\"#prerepliescontent\").html(data);\n    });\n}\nfunction selectpredefreply(artid) {\n    WHMCS.http.jqClient.post(\"supporttickets.php\", { action: \"getpredefinedreply\", id: artid, token: \"" . csrf_token() . "\" },\n    function(data){\n        \$(\"#replymessage\").addToReply(data);\n    });\n    \$(\"#prerepliescontainer\").slideToggle();\n}\nfunction dropdownSelectClient(userId, name, email) {\n    jQuery(\"#clientinput\").val(userId);\n    jQuery(\"#name\").val(name).prop(\"disabled\", true);\n    jQuery(\"#email\").val(email).prop(\"disabled\", true);\n    WHMCS.http.jqClient.post(\n        \"supporttickets.php\",\n        {\n            action: \"getcontacts\",\n            userid: userId,\n            token: \"" . csrf_token() . "\"\n        },\n        function(data)\n        {\n            if (data) {\n                jQuery(\"#contacthtml\").html(data);\n                jQuery(\"#contactrow\").show();\n            } else {\n                jQuery(\"#contactrow\").hide();\n            }\n        }\n    );\n}\n";
        $params = [
            'dep' => $getDepartemet,
            'tiket' => $tiket,
            'status' => $status,
            'tiketlog' => $tiketlog,
            'customFields' => $getCustomFields,
            'admin' => $admin,
            'replay' => $rep,
            'jquerycode' => $jquerycode
        ];

        if ($action == "saveoption") {
            DB::beginTransaction();
            try {
                $deptid = $request->input("deptid");
                $subject = $request->input("subject");
                $status = $request->input("status");
                $cc = $request->input("cc");
                $userid = $request->input("userid");
                $flagto = $request->input("flagto");
                $priority = $request->input("priority");
                $mergetid = $request->input("mergetid");

                $authadmin = Auth::guard('admin')->user();
                $adminID = $authadmin ? $authadmin->id : 0;

                $adminname = \App\Helpers\AdminFunctions::getAdminName();
                $result = \App\Models\Ticket::where(array("id" => $id));
                $changes = [];
                $data = $result;
                $orig_userid = $data->value("userid");
                $orig_contactid = $data->value("contactid");
                $orig_deptid = $data->value("did");
                $orig_title = $data->value("title");
                $orig_status = $data->value("status");
                $orig_priority = $data->value("urgency");
                $orig_flag = $data->value("flag");
                $orig_cc = $data->value("cc");
                if ($orig_title != $subject) {
                    $changes["Subject"] = ["old" => $orig_title, "new" => $subject];
                    \App\Helpers\Ticket::addTicketLog($id, "Ticket subject changed from \"" . $orig_title . "\" to \"" . $subject . "\"");
                    \App\Helpers\Hooks::run_hook("TicketSubjectChange", ["ticketid" => $id, "subject" => $subject]);
                }
                if ($orig_userid != $userid) {
                    $changes["User ID"] = ["old" => $orig_userid ?: "No User", "new" => $userid ?: "No User"];
                    \App\Helpers\Ticket::addTicketLog($id, "Ticket Assigned to User ID " . $userid);
                }
                if ($orig_deptid != $deptid) {
                    $ticket = new \App\Helpers\Tickets();
                    $ticket->setID($id);
                    if ($ticket->changeDept($deptid)) {
                        $changes["Department"] = ["old" => $ticket->getDeptName($orig_deptid), "new" => $ticket->getDeptName(), "newId" => $deptid];
                    }
                }
                if ($orig_status != $status) {
                    if ($status == "Closed") {
                        \App\Helpers\Ticket::closeTicket($id);
                    } else {
                        \App\Helpers\Ticket::addTicketLog($id, "Status changed to " . $status);
                    }
                    $changes["Status"] = ["old" => $orig_status, "new" => $status];
                    \App\Helpers\Hooks::run_hook("TicketStatusChange", ["adminid" => $adminID, "status" => $status, "ticketid" => $id]);
                }
                if ($orig_priority != $priority) {
                    \App\Helpers\Ticket::addTicketLog($id, "Priority changed to " . $priority);
                    $changes["Priority"] = ["old" => $orig_priority, "new" => $priority];
                    \App\Helpers\Hooks::run_hook("TicketPriorityChange", ["ticketid" => $id, "priority" => $priority]);
                }
                if ($orig_cc != $cc) {
                    \App\Helpers\Ticket::addTicketLog($id, "Modified CC Recipients");
                    $changes["CC Recipients"] = ["old" => $orig_cc, "new" => $cc];
                }
                if ($orig_flag != $flagto) {
                    $ticket = new \App\Helpers\Tickets();
                    $ticket->setID($id);
                    $ticket->setFlagTo($flagto);
                    $changes["Assigned To"] = ["old" => $orig_flag ? \App\Helpers\AdminFunctions::getAdminName($orig_flag) : "Unassigned", "oldId" => $orig_flag ?: 0, "new" => $flagto ? \App\Helpers\AdminFunctions::getAdminName($flagto) : "Unassigned", "newId" => $flagto ?: 0];
                }
                $table = "tbltickets";
                $array = ["status" => $status, "urgency" => $priority, "title" => $subject, "userid" => $userid, "cc" => $cc];
                $where = ["id" => $id];
                \App\Models\Ticket::where($where)->update($array);
                if ($changes) {
                    $changes["Who"] = \App\Helpers\AdminFunctions::getAdminName($adminID);
                    \App\Helpers\Tickets::notifyTicketChanges($id, $changes);
                }
                if ($orig_status != "Closed" && $status == "Closed") {
                    \App\Helpers\Hooks::run_hook("TicketClose", ["ticketid" => $id]);
                }
                if ($mergetid) {
                    return redirect()->route('admin.pages.support.supporttickets.view', ['id' => $id, 'action' => 'mergeticket', 'mergetid' => $mergetid]);
                }

                DB::commit();
                return redirect()->back()->with('success', 'Options Saved');
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        if ($action == "mergeticket") {
            DB::beginTransaction();
            try {
                $mergetid = $request->input('mergetid');
                $result = \App\Models\Ticket::where(["tid" => $mergetid]);
                $data = $result;
                $mergeid = $data->value("id");
                if (!$mergeid) {
                    throw new \Exception($aInt->lang("support", "mergeidnotfound"));
                }
                if ($mergeid == $id) {
                    throw new \Exception($aInt->lang("support", "mergeticketequal"));
                }
                $mastertid = $id;
                if ($mergeid < $mastertid) {
                    $mastertid = $mergeid;
                    $mergeid = $id;
                }
                $adminname = \App\Helpers\AdminFunctions::getAdminName();
                \App\Helpers\Ticket::addTicketLog($mastertid, "Merged Ticket " . $mergeid);
                $adminname = "";
                $masterTicketData = DB::table("tbltickets")->find($mastertid, ["title", "userid", "lastreply"]);
                $userID = $masterTicketData->userid;
                $merge = Lang::get("client.ticketmerge");
                if (!$merge) {
                    $merge = "MERGED";
                }
                $subject = strpos($masterTicketData->title, " [" . $merge . "]") === false ? $masterTicketData->title . " [" . $merge . "]" : $masterTicketData->title;
                DB::table("tbltickets")->where("id", "=", $mastertid)->update(["title" => $subject]);
                DB::table("tblticketlog")->where("tid", "=", $mergeid)->update(["tid" => $mastertid]);
                DB::table("tblticketnotes")->where("ticketid", "=", $mergeid)->update(["ticketid" => $mastertid]);
                DB::table("tblticketreplies")->where("tid", "=", $mergeid)->update(["tid" => $mastertid]);
                $ticketData = DB::table("tbltickets")->find($mergeid);
                DB::table("tblticketreplies")->insert(["tid" => $mastertid, "userid" => $userID, "name" => $ticketData->name, "email" => $ticketData->email, "date" => $ticketData->date, "message" => $ticketData->message, "admin" => $ticketData->admin, "attachment" => $ticketData->attachment]);
                $masterTicketLastReply = \App\Helpers\Carbon::createFromTimestamp(strtotime($masterTicketData->lastreply));
                $ticketLastReply = \App\Helpers\Carbon::createFromTimestamp(strtotime($ticketData->lastreply));
                if ($masterTicketLastReply < $ticketLastReply) {
                    DB::table("tbltickets")->where("id", "=", $mastertid)->update(["lastreply" => $ticketData->lastreply, "status" => $ticketData->status]);
                }
                DB::table("tbltickets")->where("id", "=", $mergeid)->update(["merged_ticket_id" => $mastertid, "status" => "Closed", "message" => "", "admin" => "", "attachment" => "", "email" => "", "flag" => 0]);
                DB::table("tbltickets")->where("merged_ticket_id", "=", $mergeid)->update(["merged_ticket_id" => $mastertid]);
                \App\Helpers\Ticket::addTicketLog($id, "Ticket ID: " . $mergeid . " Merged with Ticket ID: " . $mastertid);

                DB::commit();
                return redirect()->back()->with('success', 'Options Saved. Ticket Merged.');
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
        
        return view('pages.support.supporttickets.edit', $params);
    }

    public function SupportTicketUpdate(Request $request)
    {
        $action = $request->action;
        $id = (int) $request->id;
        $message = $request->message;
        $message = preg_replace('/(\r\n)/', "$1\r\n", $message);
        if ($action == 'addnote') {
            $attachmentString = $this->attachmentString($request);
            \App\Helpers\Ticket::AddNote($id, $message, true, $attachmentString);
            return back()->with('success', 'Added note successfully.');
        }

        if ($action == 'customFields') {
            $deptID = (int) $request->did;
            $customfields = \App\Helpers\Customfield::getCustomFields('support', $deptID, $id, true);
            $customfield = $request->customfield;
            foreach ($customfields as $v) {
                $k = $v["id"];
                $customfieldsarray[$k] = $customfield[$k];
            }
            \App\Helpers\Customfield::SaveCustomFields($id, $customfieldsarray, 'support', true);
            return back()->with('success', 'Saved custom fields successfully.');
        }

        // Debug: Log admin data
        \Log::info('Current Admin:', [
            'admin_id' => auth()->guard('admin')->user()->id,
            'signature' => auth()->guard('admin')->user()->signature ?? 'No signature'
        ]);

        if ($action == 'postreply') {

            // Debug: Log message content
            \Log::info('Reply Message:', [
                'message' => $request->message
            ]);
            
            $data = \App\Models\Ticket::find($id);
            $originalPriority = $data->urgency;
            $originalDepartmentId = $data->did;
            $originalFlag = $data->flag;
            $originalStatus = $data->status;

            $priority = $request->priority;
            $deptid = $request->deptid;
            $flagto = $request->flagto;
            $status = $request->status;

            $message = $request->message;

            $update = [];
            $changes = [];
            if ($status != "nochange") {
                $update["status"] = $status;
            }
            if ($priority != "nochange" && $originalPriority != $priority) {
                \App\Helpers\Ticket::addTicketLog($id, "Priority changed to " . $priority);
                $update["urgency"] = $priority;
                $changes["Priority"] = ["old" => $originalPriority, "new" => $priority];
            }
            if ($deptid != "nochange" && $originalDepartmentId != $deptid) {
                \App\Helpers\Customfield::migrateCustomFields("support", $id, $deptid);
                $update = \App\Models\Ticket::find($id);
                $update->did = $deptid;
                $update->save();
                $changes["Department"] = [
                    "old" => \App\Helpers\HelperTickets::getDeptName($originalDepartmentId),
                    "new" => \App\Helpers\HelperTickets::getDeptName($deptid),
                    "newId" => $deptid
                ];
            }
            if ($flagto != "nochange" && $originalFlag != $flagto) {
                // Handle flag change logic here
            }
            if (count($update) > 0) {
                DB::table($this->prefix . "tickets")->where("id", "=", $id)->update($update);
            }
            $attachmentString = $this->attachmentString($request);
            // $attachmentStringOld = $this->attachmentStringOLD($request);
            $adminID = auth()->guard('admin')->user()->id;
            $form = auth()->guard('admin')->user()->id;
            $newstatus = $status == "nochange" ? "Answered" : $status;
            // \App\Helpers\Ticket::AddReply($id, '', '', $request->message, $adminID, $attachmentString, $form, $newstatus, false, false, true, $changes);
            \App\Helpers\Ticket::AddReply($id, '', '', $message, $adminID, $attachmentString, $form, $newstatus, false, false, true, $changes);

            if ($originalStatus != $newstatus) {
                \App\Helpers\Hooks::run_hook("TicketStatusChange", ["adminid" => $adminID, 'status' => $newstatus, 'ticketid' => $id]);
                if ($newstatus == "Closed") {
                    $t = \App\Models\Ticket::find($id);
                    $t->status = 'Answered';
                    $t->save();
                    \App\Helpers\Ticket::CloseTicket($id);
                }
            }
            $updateTiket = \App\Models\Ticket::find($id);
            $updateTiket->replyingadmin = '';
            $updateTiket->replyingtime = '';
            $updateTiket->save();
        }

        return back()->with('success', 'Reply Successfully Sent!');
    }

    public function ReplayDestroy(Request $request)
    {
        $id = (int)$request->id;
        $tid = (int)$request->tid;
        \App\Helpers\Ticket::DeleteTicket($tid, $id);
        return back()->with('success', 'Successfully deleted post reply.');
    }

    public function delnoteDestroy(Request $request)
    {
        $id = (int)$request->id;
        $noteAttachments = DB::table($this->prefix . "ticketnotes")->find($id, ["attachments"]);
        if (!is_null($noteAttachments) && $noteAttachments->attachments) {
            $noteAttachments = explode("|", $noteAttachments->attachments);
            foreach ($noteAttachments as $attachment) {
                File::delete(public_path('/attachments/' . $attachment));
            }
        }
        \App\Models\Ticketnote::find($id)->delete();
        return back()->with('success', 'Note successfully deleted.');
    }

    public function replayUpdate(Request $request)
    {
        $id = (int)$request->id;
        $message = $request->message;
        $type = $request->type;
        if ($type == 'reply') {
            $update = \App\Models\Ticketreply::find($id);
            $update->message = $message;
            $update->save();

            $update = \App\Models\Ticketreply::find($id);

            return response()->json([
                'error' => false,
                'id' => $id,
                'message' => $update->message
            ]);
        }

        if ($type == 'ticket') {
            $update = \App\Models\Ticket::find($id);
            $update->message = $message;
            $update->save();

            $update = \App\Models\Ticket::find($id);
            return response()->json([
                'error' => false,
                'id' => $id,
                'message' => $update->message
            ]);
        }
    }

    public function deleteattAchments(Request $request)
    {
        $id = (int)$request->id;
        $i = (int)$request->i;
        $type = $request->type;
        if ($type == 'noted') {
            $attachments = \App\Models\Ticketnote::find($id);
            if ($attachments) {
                $attachments = $attachments->attachments;
            }
            $attachments = explode("|", $attachments);

            $filename = isset($attachments[$i]) ? $attachments[$i] : null;
            Storage::delete(public_path('/attachments/' . $filename));
            unset($attachments[$i]);
            $update = \App\Models\Ticketnote::find($id);
            $update->attachments = implode("|", $attachments);
            $update->save();

            return response()->json([
                'error' => false,
                'id' => $id,
                'loop' => $i
            ]);
        }
    }

    public function SupportTicketSplit(Request $request)
    {
        $rids = array_map("intval", $request->rids);
        $splitCount = count($rids);
        $id = (int) $request->id;
        $dep = (int) $request->dep;
        $title = $request->title;
        $notif = $request->notif;

        $data = \App\Models\Ticket::find($id);

        $splitsubject = $request->title;
        $splitdeptid = $request->dep;
        $splitpriority = $request->priority;
        $noemail = !$request->notif ? true : false;

        $oldTicketID = $data->tid;
        $newTicketUserid = $data->userid;
        $newTicketContactid = $data->contactid;
        $newTicketdepartmentid = $data->did;
        $newTicketName = $data->name;
        $newTicketEmail = $data->email;
        $newTicketAttachment = $data->attachment;
        $newTicketUrgency = $data->urgency;
        $newTicketCC = $data->cc;
        $newTicketService = $data->service;
        $newTicketTitle = $data->title;
        $newTicketEditor = $data->editor;

        $data = \App\Models\Ticketreply::whereIn('id', $rids)->orderBy('date')->first();
        $messageEarliestID = $data->id;
        $messageEarliest = $data->message;
        $messageAdmin = $data->admin;
        $messageAttachments = $data->attachment;
        $messageEarliestDate = $data->date;
        if ($messageAttachments) {
            $newTicketAttachment .= trim($newTicketAttachment) ? "|" . $messageAttachments : $messageAttachments;
        }
        $subject = trim($splitsubject) ? $splitsubject : $newTicketTitle;
        $deptid = trim($splitdeptid) ? $splitdeptid : $newTicketdepartmentid;
        $priority = trim($splitpriority) ? $splitpriority : $newTicketUrgency;
        $newOpenedTicketResults = \App\Helpers\Ticket::OpenNewTicket($newTicketUserid, $newTicketContactid, $deptid, $subject, $messageEarliest, $priority, $newTicketAttachment, ["name" => $newTicketName, "email" => $newTicketEmail], $newTicketService, $newTicketCC, $noemail, 'admin', $newTicketEditor == "markdown");
        $newTicketID = $newOpenedTicketResults["ID"];
        \App\Helpers\Customfield::copyCustomFieldValues("support", $id, $newTicketID);
        DB::table($this->prefix . "tickets")->where("id", "=", $newTicketID)->update(["date" => $messageEarliestDate]);
        $repliesPlural = 1 < $splitCount ? "Replies" : "Reply";
        \App\Helpers\Ticket::addTicketLog($id, "Ticket " . $repliesPlural . " Split to New Ticket #" . @$newOpenedTicketResults["TID"]);
        \App\Helpers\Ticket::addTicketLog($newTicketID, "Ticket " . $repliesPlural . " Split from Ticket #" . $oldTicketID);

        DB::table($this->prefix . "ticketreplies")->where("id", "=", $messageEarliestID)->delete();
        DB::table($this->prefix . "ticketreplies")->whereIn("id", $rids)->update(["tid" => $newTicketID]);

        return response()->json([
            'error' => false,
            'url' => url($this->adminURL . 'support/supporttickets/' . $newTicketID . '/view'),
        ]);
    }

    public function PredefinedReplies(Request $request)
    {
        $action = $request->action;
        $cat = $request->input('cat');
        $catid = $request->input('id');
        $predefq = $request->input('predefq');
        switch ($action) {
            case 'loadpredefinedreplies':
                $getPredefinedCat = \App\Helpers\Predefined::genPredefinedCat($cat, $predefq);
                return $getPredefinedCat;
            case 'getpredefinedreply':
                $getPredefinedReplies = \App\Helpers\Predefined::genPredefinedReplies($catid);
                return $getPredefinedReplies;
            default:
                break;
        }
    }

    private function attachmentStringOLD(Request $request)
    {
        $attachmentString = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $uuid = (string) Str::uuid();
                $fileNameToSave = Str::random(6) . "_" . $attachment->getClientOriginalName();
                $filename = $fileNameToSave;
                $filepath = "{$filename}";
                $upload = Storage::disk('attachments')->put($filepath, file_get_contents($attachment), 'public');
                $attachmentString[] = $filename;
            }
        }
        return implode('|', $attachmentString);
    }

    private function attachmentString(Request $request)
    {
        $attachmentString = [];
        if ($request->hasFile('attachments')) {
            // $directory = '/home/hostingnvme/public_html/Files/';
            $directory = 'Files/';
            foreach ($request->file('attachments') as $attachment) {
                $uuid = (string) Str::uuid();
                $filename = $uuid . "." . $attachment->getClientOriginalExtension();
                $content = file_get_contents($attachment);

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save the file
                file_put_contents($directory . $filename, $content);

                // Store the full path in the attachmentString
                $attachmentString[] = 'Files/' . $filename;
            }
        }
        return implode('|', $attachmentString);
    }
}
