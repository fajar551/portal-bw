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
use App\Helpers\Sanitize;
use App\Helpers\SystemHelper;
use App\Models\ActivityLog;
// Models
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientLogController extends Controller
{
    
    use DatatableFilter;

    protected $outputFormatting = true;

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
            return view('pages.clients.viewclients.clientlog.index', [
                'invalidClientId' => true,
            ]);
        }

        $userid = $request->userid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        // Note: Need long time to take ActivityLog data even though limited to 100 data
        // $user = ActivityLog::limit(100)->orderBy("user", "ASC")->distinct('user')->get();

        // Template vars for view usage
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        return view('pages.clients.viewclients.clientlog.index', $templatevars);
    }

    public function dtClientLog(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
        ];

        $query = ActivityLog::query();

        $filters = $this->dtClientLogFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('id', function($row) {
                return $row->id;
            })
            ->editColumn('userid', function($row) {
                return $row->userid;
            })
            ->editColumn('date', function($row) {
                return (new Functions())->fromMySQLDate($row->date, true);
            })
            ->editColumn('description', function($row) {
                $description = Sanitize::makeSafeForOutput($row->description);
                if ($this->getOutputFormatting()) {
                    $description = $this->autoLink($description, $row->userid);
                }

                return "<div class=\"card p-3\" style=\"width:450px; overflow: auto\">"
                            . $description
                        ."</div>";
            })
            ->addColumn('username', function($row) {
                return Sanitize::makeSafeForOutput($row->user);
            })
            ->editColumn('ipaddr', function($row) {
                return Sanitize::makeSafeForOutput($row->ipaddr);
            })
            ->addColumn('raw_id', function($row) {
                $route = "javascript:void(0);";

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['raw_id', 'description'])
            ->addIndexColumn()
            ->toJson();
    }

    public function setOutputFormatting($enable)
    {
        $this->outputFormatting = $enable ? true : false;
    }

    public function getOutputFormatting()
    {
        return $this->outputFormatting;
    }

    public function autoLink($description, $userid)
    {
        $patterns = $replacements = array();
        $patterns[] = "/User ID: (.*?) - Contact ID: (.*?) /";
        $patterns[] = "/User ID: (.*?) (?!- Contact)/";
        $patterns[] = "/Service ID: (.*?) /";
        $patterns[] = "/Service Addon ID: (\\d+)(\\D*?)/";
        $patterns[] = "/Domain ID: (.*?) /";
        $patterns[] = "/Invoice ID: (.*?) /";
        $patterns[] = "/Quote ID: (.*?) /";
        $patterns[] = "/Order ID: (.*?) /";
        $patterns[] = "/Transaction ID: (.*?) /";
        $patterns[] = "/Product ID: (\\d+)(\\D*?)/";

        $contactRoute = request()->root() ."/admin/clients/clientsummary?userid=\$1&contactid=\$2";
        $clientSummaryRoute = request()->root() ."/admin/clients/clientsummary?userid=\$1";
        $clientsservicesRoute = request()->root() ."/admin/clients/clientservices?userid=$userid&id=\$1";
        $clientsservicesAidRoute = request()->root() ."/admin/clients/clientservices/edit-addon?userid=$userid&aid=\$1";
        $clientsDomainRoute = request()->root() ."/admin/clients/clientdomain?userid=$userid&domainid=\$1";
        $invoicesRoute = request()->root() ."/admin/billing/invoices/edit/\$1";
        $transactionsRoute = request()->root() ."/admin/billing/transactionlist/edit/\$1";
        $viewOrderRoute = request()->root() ."/admin/orders/view-order?action=view&id=\$1";
        $configProductsRoute = request()->root() ."/admin/setup/productservices/product/edit/\$1";

        // $replacements[] = "<a href=\"clientscontacts.php?userid=\$1&contactid=\$2\">Contact ID: \$2</a> ";
        // $replacements[] = "<a href=\"clientssummary.php?userid=\$1\">User ID: \$1</a> ";
        // $replacements[] = "<a href=\"clientsservices.php?id=\$1\">Service ID: \$1</a> ";
        // $replacements[] = "<a href=\"clientsservices.php?aid=\$1\">Service Addon ID: \$1</a>";
        // $replacements[] = "<a href=\"clientsdomains.php?id=\$1\">Domain ID: \$1</a> ";
        // $replacements[] = "<a href=\"invoices.php?action=edit&id=\$1\">Invoice ID: \$1</a> ";
        // $replacements[] = "<a href=\"quotes.php?action=manage&id=\$1\">Quote ID: \$1</a> ";
        // $replacements[] = "<a href=\"orders.php?action=view&id=\$1\">Order ID: \$1</a> ";
        // $replacements[] = "<a href=\"transactions.php?action=edit&id=\$1\">Transaction ID: \$1</a> ";
        // $replacements[] = "<a href=\"configproducts.php?action=edit&id=\$1\">Product ID: \$1</a>";
        
        $replacements[] = "<a href=\"$contactRoute\">Contact ID: \$2</a> ";
        $replacements[] = "<a href=\"$clientSummaryRoute\">User ID: \$1</a> ";
        $replacements[] = "<a href=\"$clientsservicesRoute\">Service ID: \$1</a> ";
        $replacements[] = "<a href=\"$clientsservicesAidRoute\">Service Addon ID: \$1</a>";
        $replacements[] = "<a href=\"$clientsDomainRoute\">Domain ID: \$1</a> ";
        $replacements[] = "<a href=\"$invoicesRoute\">Invoice ID: \$1</a> ";
        $replacements[] = "<a href=\"#\">Quote ID: \$1</a> ";
        $replacements[] = "<a href=\"$viewOrderRoute\">Order ID: \$1</a> ";
        $replacements[] = "<a href=\"$transactionsRoute\">Transaction ID: \$1</a> ";
        $replacements[] = "<a href=\"$configProductsRoute\">Product ID: \$1</a>";
        
        $description = preg_replace($patterns, $replacements, $description . " ");

        return trim($description);
    }

    private function dtClientLogFilters($criteria)
    {
        $filters = [];
        $pfx = $this->prefix;

        if (isset($criteria["userid"])) {
            $filters[] = $this->filterValue("userid", "=", "'{$criteria["userid"]}'");
        }
        
        if (isset($criteria["date"])) {
            $date = (new SystemHelper())->toMySQLDate($criteria["date"]);
            
            $filters[] = $this->filterValue("date", ">", "'{$date}'");
            $filters[] = $this->filterValue("date", "<=", "'{$date} 23:59:59'");
        }

        if (isset($criteria["description"])) {
            $filters[] = $this->filterValue("description", "LIKE", "'%{$criteria["description"]}%'");
        }

        if (isset($criteria["username"])) {
            $filters[] = $this->filterValue("user", "=", "'{$criteria["username"]}'");
        }

        if (isset($criteria["ipaddress"])) {
            $filters[] = $this->filterValue("ipaddr", "=", "'{$criteria["ipaddress"]}'");
        }

        return $this->buildRawFilters($filters);
    }

}
