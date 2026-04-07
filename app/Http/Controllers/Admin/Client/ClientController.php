<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\Client as HelpersClient;
use App\Helpers\Database;
use App\Helpers\Format;
use App\Models\Account;
use App\Models\Client;
use App\Models\Clientgroup;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Traits\DatatableFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ClientController extends Controller
{
    use DatatableFilter;
    protected $prefix;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = Database::prefix();
    }

    public function index() {
        $clientGroup = Clientgroup::all();
        return view('pages.clients.viewclients.index', compact("clientGroup"));
    }

    public function reportStatementIndex(Request $request) {
        $pfx = $this->prefix;
        $userid = $request->userid;
        $client = null;
        $currency = Format::getCurrency($userid);
        $statement = [];
        $count = $balance = $totalcredits = $totaldebits = 0;

        if ($userid) {
            $client = Client::find($userid);
            $results = Invoice::where('userid', $userid)
                ->whereIn('status', ['Unpaid', 'Paid', 'Collections'])
                ->orderBy('date', 'ASC')
                ->get();

            foreach ($results as $result) {
                $invoiceid = $result->id;
                $date = $result->date;
                $total = ($result->credit + $result->total);
                $addfunds = Invoiceitem::where('invoiceid', $invoiceid)
                    ->whereIn('type', ['AddFunds', 'Invoice'])
                    ->value('id');
                if (!$addfunds) {
                    $statement[str_replace('-', '', $date) . "_" . $count] = [
                        "Invoice", 
                        $date, 
                        "<a href=\"invoices.php?action=edit&id=$invoiceid\" target=\"_blank\">#$invoiceid</a>", 
                        0, 
                        $total
                    ];
                }
                $count++;
            }

            $results = Account::where('userid', $userid)->orderBy('date', 'ASC')->get();
            foreach ($results as $result) {
                $transid = $result->id;
                $date = substr($result->date, 0, 10);
                $description = $result->description;
                $amountin = $result->amountin;
                $amountout = $result->amountout;
                $invoiceid = $result->invoiceid;

                $itemtype = Invoiceitem::where('invoiceid', '=', $invoiceid)->value('type');

                if ($itemtype == "AddFunds") {
                    $description = "Credit Prefunding";
                } elseif ($itemtype == "Invoice") {
                    $description = "Mass Invoice Payment - ";
                    $relids = Invoiceitem::where('invoiceid', $invoiceid)->orderBy('relid', 'ASC')->pluck('relid');
                    foreach ($relids as $relid) {
                        $description .= "<a href=\"invoices.php?action=edit&id=$relid\" target=\"_blank\">#$relid</a>, ";
                    }
                    $description = rtrim($description, ', ');
                } else {
                    if ($invoiceid) {
                        $description .= " - <a href=\"invoices.php?action=edit&id=$invoiceid\" target=\"_blank\">#$invoiceid</a>";
                    }
                }

                $statement[str_replace('-', '', $date) . "_" . $count] = [
                    "Transaction",
                    $date,
                    $description,
                    $amountin,
                    $amountout,
                ];
                $count++;
            }
        }

        $range = ($request->start && $request->end) ? "$request->start - $request->end" : null;
        $datefrom = $dateto = "";

        if ($range) {
            $dateRange = (new HelpersClient())->parseDateRangeValue($range);
            $datefrom = $dateRange['from'];
            $dateto = $dateRange['to'];
        }

        ksort($statement);
        $reportdata = [];

        foreach ($statement as $date => $entry) {
            $date = Carbon::createFromFormat('Ymd', substr($date, 0, 8));
            if ((!$dateto) || ($date->lt($dateto))) {
                $totalcredits += $entry[3];
                $totaldebits -= $entry[4];
                $balance += ($entry[3] - $entry[4]);
            }

            if ((!$dateto) || ($date->gt($datefrom)) && ($date->lt($dateto))) {
                $reportdata["tablevalues"][] = [
                    $entry[0],
                    (new HelpersClient())->fromMySQLDate($entry[1]),
                    $entry[2],
                    Format::formatCurrency($entry[3]),
                    Format::formatCurrency($entry[4]),
                    Format::formatCurrency($balance)
                ];
            }
        }

        $reportdata["tablevalues"][] = [
            '#efefef',
            '',
            '',
            '<b>Ending Balance</b>',
            '<b>'.Format::formatCurrency($totalcredits).'</b>',
            '<b>'.Format::formatCurrency($totaldebits).'</b>',
            '<b>'.Format::formatCurrency($balance).'</b>'
        ];

        $start = $request->start;
        $end = $request->end;
        $reportTime = __("admin.reportsgeneratedon") ." " .(new HelpersClient())->fromMySQLDate(date("Y-m-d H:i:s"), "time");

        return view('pages.clients.viewclients.clientreportstatement.index', compact(
            'reportdata', 
            'client',
            'start',
            'end',
            'reportTime'
        ));
    }

    public function dtClient(Request $request) {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $query = Client::select("id", "firstname", "lastname", "companyname", "email", "datecreated", "groupid", "status", "phonenumber");

        $filters = $this->buildClientFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->addColumn('services', function($row) use($pfx) {
                $serviceQuery = DB::select(DB::raw("SELECT 
                    (SELECT COUNT(*) FROM {$pfx}hosting WHERE userid={$pfx}clients.id AND domainstatus IN ('Active','Suspended'))+
                    (SELECT COUNT(*) FROM {$pfx}hostingaddons WHERE hostingid IN (SELECT id FROM {$pfx}hosting WHERE userid={$pfx}clients.id) AND status IN ('Active','Suspended'))+
                    (SELECT COUNT(*) FROM {$pfx}domains WHERE userid={$pfx}clients.id AND status IN ('Active')) AS services,
                    (SELECT COUNT(*) FROM {$pfx}hosting WHERE userid={$pfx}clients.id)+
                    (SELECT COUNT(*) FROM {$pfx}hostingaddons WHERE hostingid IN (SELECT id FROM {$pfx}hosting WHERE userid={$pfx}clients.id))+
                    (SELECT COUNT(*) FROM {$pfx}domains WHERE userid={$pfx}clients.id) AS totalservices 
                    FROM {$pfx}clients 
                    WHERE {$pfx}clients.id={$row->id} 
                    LIMIT 1"));

                return $serviceQuery ? "{$serviceQuery[0]->services} ({$serviceQuery[0]->totalservices})" : "N/A";
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $row->id]);
                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->editColumn('firstname', function($row) {
                $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $row->id]);
                return "<a href=\"{$route}\">{$row->firstname}</a>";
            })
            ->editColumn('lastname', function($row) {
                $route = route('admin.pages.clients.viewclients.clientsummary.index', ['userid' => $row->id]);
                return "<a href=\"{$route}\">{$row->lastname}</a>";
            })
            ->editColumn('companyname', function($row) {
                return $row->companyname;
            })
            ->editColumn('email', function($row) {
                return "<a href=\"mailto:{$row->email}\">{$row->email}</a>";
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['raw_id', 'firstname', 'lastname', 'email'])
            ->addIndexColumn()
            ->toJson();
    }

    private function buildClientFilters($criteria) {
        $filters = [];

        foreach (['client', 'email', 'phonenumber', 'groupid', 'status'] as $field) {
            if (isset($criteria[$field]) && $criteria[$field] !== "Any") {
                $operator = $field === 'client' ? 'LIKE' : '=';
                $value = $operator === 'LIKE' ? "'%{$criteria[$field]}%'" : "'{$criteria[$field]}'";
                $filters[] = $this->filterValue($field === 'client' ? "concat(firstname, ' ', lastname, ' ', companyname)" : $field, $operator, $value);
            }
        }

        return $this->buildRawFilters($filters);
    }
}