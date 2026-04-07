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
use App\Models\Quote;

// Traits
use App\Traits\DatatableFilter;

class ClientQuoteController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $userid = $request->userid;

        if (!AdminFunctions::checkPermission("Manage Quotes")) {
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

        return view('pages.clients.viewclients.clientquotes.index', $templatevars);
    }

    public function delete(Request $request)
    {
        if (!AdminFunctions::checkPermission("Manage Quotes")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $quoteId = $request->id;
        $userId = $request->userid;
        $quote = Client::find($userId)->quotes()->find($quoteId);

        if (!$quote) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }
        
        $quote->delete();
        LogActivity::Save("Deleted Quote (ID: {$quote->id} - User ID: $userId)", $userId);

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', 'The data deleted successfully!'),
        ]);
    }

    public function dtClientQuote(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = Quote::where("userid", $userid);

        return datatables()->of($query)
            ->addColumn('raw_id', function($row) use($params) {
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
            ->editColumn('stage', function($row) {
                return __("admin.status" .str_replace(" ", "", strtolower($row->stage)));
            })
            ->addColumn('actions', function($row) use($params) {
                $route = route('admin.pages.clients.viewclients.clientquotes.index', [
                            'action' => 'manage',
                            'userid' => $params["userid"], 
                            'id' => $row->id, 
                        ]);
                
                $action = "";
                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\"><i class=\"fa fa-trash\"></i></button>";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy("id", $order);
            })
            ->rawColumns(['raw_id', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }
    
}
