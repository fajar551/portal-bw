<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Helpers
use App\Helpers\ResponseAPI;
use App\Helpers\ClientHelper;

// Models
use App\Models\Cancelrequest;

// Traits
use App\Traits\DatatableFilter;

class CancellationRequestController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index()
    {
        return view('pages.clients.cancellationrequests.index');
    }

    public function deleteCancellation()
    {
        $data = Cancelrequest::findOrFail(request()->id);

        if (!$data->delete()) {
            return ResponseAPI::Error([
                'message' => "Internal server error!",
            ]);
        }

        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function dtCancellationRequest(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $query = Cancelrequest::select(
                                    "{$pfx}cancelrequests.*", 
                                    "{$pfx}hosting.domain", 
                                    "{$pfx}hosting.nextduedate", 
                                    "{$pfx}products.name AS productname", 
                                    "{$pfx}productgroups.name AS groupname", 
                                    "{$pfx}hosting.id AS productid", 
                                    "{$pfx}hosting.userid", 
                                    "{$pfx}clients.firstname", 
                                    "{$pfx}clients.lastname", 
                                    "{$pfx}clients.companyname", 
                                    "{$pfx}clients.groupid"
                                )
                                ->join("{$pfx}hosting", "{$pfx}hosting.id", "{$pfx}cancelrequests.relid")
                                ->join("{$pfx}products", "{$pfx}products.id", "{$pfx}hosting.packageid")
                                ->join("{$pfx}productgroups", "{$pfx}productgroups.id", "{$pfx}products.gid")
                                ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}hosting.userid");
        
        $completed = ((boolean) $dataFiltered["completed"]) ?? false;
        $filters = $this->dtCancellationFilters($dataFiltered);
        
        if ($filters) {
            $query->whereRaw($filters);
        } 

        if ($completed) {
            $query->whereIn("{$pfx}hosting.domainstatus", ["Cancelled", "Terminated"]);
        } else {
            $query->whereNotIn("{$pfx}hosting.domainstatus", ["Cancelled", "Terminated"]);
        }

        return datatables()->of($query)
            ->addColumn('product_service', function ($row) {
                $route = route('admin.pages.clients.viewclients.clientservices.index', ['userid' => $row->userid, 'id' => $row->productid,]);

                $link = "<a href=\"$route\">{$row->groupname} - {$row->productname}</a><br>";
                $link .= ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);

                return $link;
            })
            ->addColumn('actions', function($row) {
                $action = "";

                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\"><i class=\"fa fa-trash\"></i></button>";

                return $action;
            })
            ->rawColumns(['product_service', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtCancellationFilters($criteria)
    {
        $filters = [];
        $pfx = $this->prefix;

        if (isset($criteria["reason"])) {
            $filters[] = $this->filterValue("{$pfx}cancelrequests.reason", "LIKE", "'%{$criteria["reason"]}%'");
        }

        if (isset($criteria["domain"])) {
            $filters[] = $this->filterValue("{$pfx}hosting.domain", "LIKE", "'%{$criteria["domain"]}%'");
        }

        if (isset($criteria["userid"]) && $criteria["userid"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}hosting.userid", "=", "'{$criteria["userid"]}'");
        }

        if (isset($criteria["relid"])) {
            $filters[] = $this->filterValue("{$pfx}cancelrequests.relid", "=", "'{$criteria["relid"]}'");
        }

        if (isset($criteria["type"]) && $criteria["type"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}cancelrequests.type", "=", "'{$criteria["type"]}'");
        }

        return $this->buildRawFilters($filters);
    }
    
}
