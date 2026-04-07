<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\Format;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Client;
use App\Models\Credit;

// Traits
use App\Traits\DatatableFilter;

class ClientCreditController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $userid = $request->userid;

        if (!AdminFunctions::checkPermission("Manage Credits")) {
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
        $templatevars["creditbalance"] = Format::formatCurrency($clientsdetails["model"]->credit);

        return view('pages.clients.viewclients.clientcredit.index', $templatevars);
    }

    public function create(Request $request)
    {
        $userid = $request->userid;
        $type = $request->type;
        $types = ['add', 'remove'];

        if (!AdminFunctions::checkPermission("Manage Credits")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcredit.index', ['userid' => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::getNoPermissionMessage());
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'type' => 'nullable|in:'.implode(",", $types),
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.index", ['userid', $userid])
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", 'admin.clientsinvalidclientid') .' or Invalid Action Type (Type can only be add or remove)');
        }

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["type"] = $type;
        $templatevars["creditbalance"] = Format::formatCurrency($clientsdetails["model"]->credit);

        return view('pages.clients.viewclients.clientcredit.create', $templatevars);
    }

    public function store(Request $request)
    {
        $userid = $request->userid;
        $type = $request->type;
        $types = ['add', 'remove'];

        if (!AdminFunctions::checkPermission("Manage Credits")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcredit.create', ['userid' => $userid, "type" => $type])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::getNoPermissionMessage());
        }

        $messages = [
            'type.in' => "Type can only be add or remove",
            'userid.exists' => "Client ID not found",
            'amount.required' => "No Amount Provided",
            'amount.regex' => "Amount must be in decimal format: ### or ###.##"
        ];

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'date' => "required|date_format:d/m/Y",
            'amount' => ['required', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
            'type' => 'nullable|in:'.implode(",", $types),
            'description' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.create", ["userid" => $userid, "type" => $type])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", 'Please ensure to fill all fields correctly and re-submit the form.'));
        }
        
        $response = (new HelpersClient())->AddCredit(
            $request->type, 
            $userid, 
            $request->description,
            $request->amount,
            $request->date,
            auth()->user()->id,
        );

        if ($response["result"] == "error") {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.create", ["userid" => $userid, "type" => $type])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", $response["message"]));
        }

        return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.index", ["userid" => $userid])
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Well Done!</b>", $response["message"]));
    }

    public function edit(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;

        if (!AdminFunctions::checkPermission("Manage Credits")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcredit.index', ['userid' => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::getNoPermissionMessage());
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'id' => "required|integer|exists:App\Models\Credit,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.index", ['userid', $userid])
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", 'admin.clientsinvalidclientid'));
        }

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $credit = Credit::find($id);
        $credit->date = (new HelpersClient())->fromMySQLDate($credit->date);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["credit"] = $credit;
        $templatevars["userid"] = $userid;
        $templatevars["creditbalance"] = Format::formatCurrency($clientsdetails["model"]->credit);

        return view('pages.clients.viewclients.clientcredit.edit', $templatevars);
    }

    public function update(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;

        if (!AdminFunctions::checkPermission("Manage Credits")) {
            return redirect()
                    ->route('admin.pages.clients.viewclients.clientcredit.edit', ['userid' => $userid, 'id' => $id])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::getNoPermissionMessage());
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'id' => "required|integer|exists:App\Models\Credit,id",
            'date' => "required|date_format:d/m/Y",
        ]);

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.edit", ["userid" => $userid, 'id' => $id])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Oh No!</b>", 'Please ensure to fill all fields correctly and re-submit the form.'));
        }

        $credit = Credit::findOrFail($id);
        $credit->date = (new \App\Helpers\SystemHelper())->toMySQLDate($request->date);
        $credit->description = $request->description;
        $credit->save();
        LogActivity::Save("Edited Credit - Credit ID: $id - User ID: $userid", $userid);

        return redirect()
                    ->route("admin.pages.clients.viewclients.clientcredit.index", ["userid" => $userid])
                    ->with('type', 'success')
                    ->with('message', AdminFunctions::infoBoxMessage("<b>Well Done!</b>", "The data updated succesfully!"));
    }

    public function delete(Request $request)
    {
        if (!AdminFunctions::checkPermission("Manage Credits")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $ide = $request->id;
        $userid = $request->userid;
        $credit = Credit::find($ide);

        if (!$credit) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        $data = $credit->toArray();
        $client = Client::select("firstname", "lastname", "credit")->find($userid)->toArray();
        $creditbalance = $client["credit"];

        if ($data["clientid"] == $userid) {
            $amount = $data["amount"];

            if ($amount <= $creditbalance) {
                $creditbalance = $creditbalance - $amount;

                Client::where("id", (int) $userid)->update(["credit" => $creditbalance]);
                Credit::find($ide)->delete();

                LogActivity::Save("Deleted Credit - Credit ID: $ide - User ID: $userid", $userid);
            } else {
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', "admin.creditnonegativebalance"),
                ]);
            }
        }
        
        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
        ]);
    }

    public function dtClientCredit(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];

        $params = [
            "userid" => $userid,
            "pfx" => $pfx,
        ];

        $query = Credit::where("clientid", $userid)->orderBy("date", "DESC");
        
        return datatables()->of($query)
            ->editColumn('description', function($row) {
                $route = str_replace(":id", "\$1", route('admin.pages.billing.invoices.edit', ['id' => ":id"]));
                $patterns = "/ Invoice #(.*?) /";
                $replacements = "<a href=\"{$route}\" class=\"p-1 act-edit-invoice\" data-id=\"{$row->id}\" target=\"_blank\" title=\"Edit Invoice\">Invoice #\$1</a> ";

                $description = preg_replace($patterns, $replacements, $row->description ." ");

                return nl2br(trim($description));
            })
            ->editColumn('admin_id', function($row) {
                $adminName = "-";
                $adminId = $row->admin_id;
                if ($adminId) {
                    $adminName = AdminFunctions::getAdminName($adminId);
                }

                return $adminName;
            })
            ->editColumn('amount', function($row) {
                return Format::formatCurrency($row->amount);
            })
            ->addColumn('actions', function($row) use($params) {
                extract($params);
                $route = route('admin.pages.clients.viewclients.clientcredit.edit', ['action' => 'edit', 'userid' => $userid, 'id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-userid=\"{$userid}\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\"><i class=\"fa fa-trash\"></i></button>";

                return $action;
            })
            ->rawColumns(['description', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }
    
}
