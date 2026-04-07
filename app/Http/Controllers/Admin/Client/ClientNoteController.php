<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\Functions;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientNoteController extends Controller
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
            return view('pages.clients.viewclients.clientnotes.index', [
                'invalidClientId' => true,
            ]);
        }

        $userid = $request->userid;
        $action = $request->action;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $note = null;
        if ($action == "edit") {
            $note = Note::find($request->id);
            if (!$note) {
                $request->session()->flash('type', 'danger');
                $request->session()->flash('message', 'Invalid ID');
            }
        }

        // Get notes count
        $notesCount = Note::where('userid', $userid)->count();

        // Template vars for view usage
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["note"] = $note;
        $templatevars["notesCount"] = $notesCount;

        return view('pages.clients.viewclients.clientnotes.index', $templatevars);
    }

    public function store(Request $request)
    {
        $userid = $request->userid;
        $note = trim($request->get("note"));
        $sticky = $request->get("sticky");

        if (!AdminFunctions::checkPermission("Add/Edit Client Notes")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()));
        }

        $validator = Validator::make(request()->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'note' => "required|string",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientnotes.index", ['userid' => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        // TODO
        // $mentionedAdminIds = WHMCS\Mentions\Mentions::getIdsForMentions($note);

        $newNote = new Note();
        $newNote->userid = $userid;
        $newNote->adminid = auth()->user()->id;
        $newNote->created = now();
        $newNote->modified = now();
        $newNote->note = $note;
        $newNote->sticky = $sticky ?? 0;
        $newNote->save();

        // TODO:
        // if ($mentionedAdminIds) {
        //     WHMCS\Mentions\Mentions::sendNotification("note", $userId, $note, $mentionedAdminIds);
        // }

        LogActivity::Save("Added Note - User ID: $userid", $userid);
        return redirect()
                ->route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $userid])
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage('<b>Well Done!</b>', 'Data saved successfully!'));
    }

    public function update(Request $request)
    {
        $userid = $request->userid;
        $note = $request->get("note");
        $id = $request->get("id");
        $sticky = $request->get("sticky");

        if (!AdminFunctions::checkPermission("Add/Edit Client Notes")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()));
        }

        $validator = Validator::make(request()->all(), [
            'id' => "required|integer|exists:App\Models\Note,id",
            'userid' => "required|integer|exists:App\Models\Client,id",
            'note' => "required|string",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientnotes.index", ['action' => 'edit', 'userid' => $userid, 'id' => $id])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $newNote = Note::find($id);
        if (!$newNote || (isset($newNote) && $newNote->userid != $userid)) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $userid])
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "Invalid ID"));
        }

        $newNote->modified = now();
        $newNote->note = $note;
        $newNote->sticky = $sticky ?? 0;
        $newNote->save();

        LogActivity::Save("Updated Note - User ID: $userid - ID: $id", $userid);
        return redirect()
                ->route('admin.pages.clients.viewclients.clientnotes.index', ['userid' => $userid])
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage('<b>Well Done!</b>', 'Data updated successfully!'));
    }

    public function delete(Request $request)
    {
        if (!AdminFunctions::checkPermission("Delete Client Notes")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $id = $request->id;
        $userid = $request->userid;

        $note = Note::find($id);
        if (!$note) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        $note->delete();
        LogActivity::Save("Deleted Note - User ID: $userid - ID: $id", $userid);
        return ResponseAPI::Success([
            'message' => "Note deleted successfully!",
        ]);
    }

    public function dtClientNote(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = Note::selectRaw("{$pfx}notes.*,(SELECT CONCAT(firstname,' ',lastname) FROM {$pfx}admins WHERE {$pfx}admins.id={$pfx}notes.adminid) AS adminuser")->where("userid", $userid);

        return datatables()->of($query)
            ->editColumn('created', function($row) {
                return (new Functions())->fromMySQLDate($row->created, "time");
            })
            ->editColumn('note', function($row) {
                // TODO:
                // $note = $markup->transform($row->note, $markupFormat);
                // $mentions = WHMCS\Mentions\Mentions::getMentionReplacements($note);
                // if (0 < count($mentions)) {
                //     $note = str_replace($mentions["find"], $mentions["replace"], $note);
                // }

                return "<div class=\"card p-3\" style=\"max-width:450px; overflow: auto\">"
                            . \Markdown::convertToHtml($row->note)
                        ."</div>";
            })
            ->addColumn('admin', function($row) {
                return $row->adminuser ?: "Admin Deleted";
            })
            ->addColumn('last_modified', function($row) {
                return (new Functions())->fromMySQLDate($row->modified, "time");
            })
            ->addColumn('raw_id', function($row) {
                $route = "javascript:void(0);";

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->addColumn('actions', function($row) {
                $route = route('admin.pages.clients.viewclients.clientnotes.index', ['action' => 'edit', 'userid' => $row->userid, 'id' => $row->id]);
                $action = "";

                // Flag
                $sticky = $row->sticky ? "high" : "low";
                $src = asset("/assets/flag/" . $sticky ."priority.gif");
                $title = $alt = __("admin.status" . $sticky);

                $action .= sprintf("<img src=\"%s\" data-toggle=\"tooltip\" title=\"%s\" alt=\"%s\" >&nbsp;&nbsp;", $src, $title, $alt);
                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\" title=\"Delete\" onclick=\"actDelete(this);\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('last_modified', function($query, $order) {
                $query->orderBy('modified', $order);
            })
            ->orderColumn('admin', function($query, $order) {
                $query->orderBy('adminuser', $order);
            })
            ->rawColumns(['raw_id', 'note', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

}
