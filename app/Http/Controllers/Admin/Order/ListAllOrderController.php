<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\AdminFunctions;
use App\Helpers\Client as HelpersClient;
use App\Helpers\ClientHelper;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Orders;
use App\Helpers\ResponseAPI;
use App\Models\Client;

// Models
use App\Models\Order;
use App\Models\Orderstatus;

// Traits
use App\Traits\DatatableFilter;

class ListAllOrderController extends Controller
{
    
    use DatatableFilter;

    public function __construct() {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function index(Request $request)
    {
        $orderStatus = Orderstatus::orderBy("sortorder", "ASC")->get();
        $client = $request->clientid ? Client::find($request->clientid) : null;
        $orderip = $request->orderip ?? "";

        $templatevars = [
            'orderStatus' => $orderStatus,
            'client' => $client,
            'orderip' => $orderip,
        ];
        
        return view('pages.orders.listallorders.index', $templatevars);
    }

    public function actionCommand(Request $request)
    {
        if (!AdminFunctions::checkPermission("View Order Details") || !AdminFunctions::checkPermission("Delete Order")) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', AdminFunctions::getNoPermissionMessage()),
            ]);
        }

        $action = $request->action;

        switch ($action) {
            case 'delete':
                return $this->delete($request);
            case 'massAccept':
                return $this->massAccept($request);
            case 'massCancel':
                return $this->massCancel($request);
            case 'massDelete':
                return $this->massDelete($request);
            case 'sendMessage':
                return $this->sendMessage($request);
            case 'cancelDelete':
                return $this->cancelDelete($request);
            default:
                # code...
                break;
        }

        return abort(404, "Ups... Action not found!");
    }

    private function delete(Request $request)
    {
        $id = $request->id;
        $order = Order::find($id);

        if (!$order) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        if (Orders::CanOrderBeDeleted($id)) {
            Orders::DeleteOrder($id);
            
            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
            ]);
        }

        return ResponseAPI::Error([
            'message' => AdminFunctions::infoBoxMessage("admin.error", "admin.ordersnoDelete"),
        ]);
    }

    private function massAccept(Request $request)
    {
        $acceptErrors = [];
        $successes = $failures = 0;
        $selectedOrdersId = $request->selectedOrdersId;

        if (is_array($selectedOrdersId)) {
            foreach ($selectedOrdersId as $orderid) {
                $errors = Orders::AcceptOrder($orderid);
                
                if (empty($errors)) {
                    $successes++;
                } else {
                    $acceptErrors[] = $orderid;
                    $failures++;
                }
            }
        }

        $massstatus = "$successes,$failures";
        
        list($massSuccesses, $massFailures) = explode(",", $massstatus);
        $massSuccesses = (int) $massSuccesses;
        $massFailures = (int) $massFailures;

        if (empty($acceptErrors)) {
            $masssuccess = 1;
            return ResponseAPI::Success([
                'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusmassaccept", "admin.ordersstatusmassacceptmsg"),
            ]);
        } else {
            $masserror = implode(",", $acceptErrors);

            if (0 < $massFailures) {
                $massErrors = explode(",", $masserror);
                foreach ($massErrors as $key => $value) {
                    $massErrors[$key] = (int) $value;
                }

                $massErrors = implode(", ", $massErrors);
                return ResponseAPI::Error([
                    'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusmassfailures", sprintf(__("admin.ordersstatusmassfailuresmsg"), $massSuccesses, $massFailures, $massErrors)),
                ]);
            }

            // Last result
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage("admin.ordersstatusmassfailures", $masserror),
            ]);
        }
    }

    private function massCancel(Request $request)
    {
        $selectedOrdersId = $request->selectedOrdersId;
        if (is_array($selectedOrdersId)) {
            foreach ($selectedOrdersId as $orderid) {
                Orders::ChangeOrderStatus($orderid, "Cancelled");
            }
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("<b>Well Done!</b>", "admin.ordersstatuscancelled"),
        ]);
    }

    private function massDelete(Request $request)
    {
        $deleteError = false;
        $orderNotDeleted = [];
        $selectedOrdersId = $request->selectedOrdersId;
        if (is_array($selectedOrdersId)) {
            foreach ($selectedOrdersId as $orderid) {
                if (Orders::CanOrderBeDeleted($orderid)) {
                    Orders::DeleteOrder($orderid);
                } else {
                    $deleteError = true;
                    $orderNotDeleted[] = "#$orderid";
                }
            }
        }

        $massDeleteError = $deleteError;
        if ($massDeleteError) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage("admin.error", "admin.ordersmassDeleteError") ."<br>Failed order(s): " .implode(", ", $orderNotDeleted),
            ]);
        }

        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
        ]);
    }

    private function sendMessage(Request $request)
    {
        return ResponseAPI::Success([
            'message' => AdminFunctions::infoBoxMessage("N/A", "Not available!"),
        ]);
    }

    private function cancelDelete(Request $request)
    {
        $id = $request->id;
        $order = Order::find($id);

        if (!$order) {
            return ResponseAPI::Error([
                'message' => AdminFunctions::infoBoxMessage('<b>Oh No!</b>', 'Invalid ID.'),
            ]);
        }

        if (AdminFunctions::checkPermission("View Order Details"))  {
            Orders::ChangeOrderStatus($id, "Cancelled");
        }

        if (AdminFunctions::checkPermission("Delete Order")) {
            if (Orders::CanOrderBeDeleted($id)) {
                Orders::DeleteOrder($id);
                
                return ResponseAPI::Success([
                    'message' => AdminFunctions::infoBoxMessage('<b>Well Done!</b>', "The data deleted successfully!"),
                ]);
            }
        }

        return ResponseAPI::Error([
            'message' => AdminFunctions::infoBoxMessage("admin.error", "admin.ordersnoDelete"),
        ]);
    }

    public function dtOrder(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $gatewaysarray = Gateway::getGatewaysArray();
        $params = [
            "pfx" => $pfx,
            "gatewaysarray" => $gatewaysarray,
        ];

        $query = Order::selectRaw("{$pfx}orders.id, {$pfx}orders.ordernum, {$pfx}orders.userid, {$pfx}orders.date, {$pfx}orders.amount, {$pfx}orders.paymentmethod, {$pfx}orders.status, {$pfx}orders.invoiceid, {$pfx}orders.ipaddress, {$pfx}clients.firstname, {$pfx}clients.lastname, {$pfx}clients.companyname, {$pfx}clients.groupid, {$pfx}clients.currency, (SELECT status FROM {$pfx}invoices WHERE id={$pfx}orders.invoiceid) AS invoicestatus ")
                        ->leftJoin("{$pfx}clients", "{$pfx}clients.id", "{$pfx}orders.userid")
                        ->leftJoin("{$pfx}invoices", "{$pfx}invoices.id", "{$pfx}orders.invoiceid");

        if (isset($dataFiltered["userid"])) {
            $query->where("{$pfx}orders.userid", $dataFiltered["userid"]);
        }
                        
        /*
        if (isset($dataFiltered["paymentstatus"])) {
            $query->join("{$pfx}invoices", "{$pfx}invoices.id", "{$pfx}orders.invoiceid");
        }
        */

        $filters = $this->dtOrderFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('id', function($row) {
                return $row->id;
            })
            ->editColumn('ordernum', function($row) {
                return $row->ordernum;
            })
            ->editColumn('date', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->date, true);
            })
            ->editColumn('paymentmethod', function($row) use($params) {
                // $gatewaysarray = $params["gatewaysarray"];
                
                // if (array_key_exists($row->paymentmethod, $gatewaysarray)) {
                //     return $gatewaysarray[$row->paymentmethod];
                // }

                return (new \App\Module\Gateway())->getDisplayNameModule($row->paymentmethod);
            })
            ->editColumn('status', function($row) {
                return $row->status;
            })
            ->editColumn('amount', function($row) {
                return Format::formatCurrency($row->amount);
            })
            ->addColumn('gateway', function($row) {
                return $row->paymentmethod;
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.orders.vieworder.index', ['action' => 'view', 'id' => $row->id]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->addColumn('clientname', function($row) {
                return ClientHelper::outputClientLink($row->userid, $row->firstname, $row->lastname, $row->companyname, $row->groupid);
            })
            ->addColumn('paymentstatus', function($row) {
                $paymentstatus = Orders::getFormatedPaymentStatus($row->invoiceid, $row->invoicestatus);
                
                return strip_tags($paymentstatus);
            })
            ->addColumn('paymentstatusformatted', function($row) {
                return Orders::getFormatedPaymentStatus($row->invoiceid, $row->invoicestatus);
            })
            ->addColumn('statusformatted', function($row) {
                return Orders::formatStatus($row->status, false);
            })
            ->addColumn('actions', function($row) {
                $action = $function = $alt = $lang = "";

                if (Orders::CanOrderBeDeleted($row->id, $row->status)) {
                    $function = "doDelete";
                    $alt = __("admin.delete");
                    $lang = __("admin.ordersconfirmdelete");
                } else {
                    $function = "doCancelDelete";
                    $alt = __("admin.cancelAndDelete");
                    $lang = __("admin.ordersconfirmCancelDelete");
                }

                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\" data-function=\"{$function}\" data-alt=\"{$alt}\" data-lang=\"{$lang}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->orderColumn('paymentstatusformatted', function($query, $order) {
                $query->orderBy('invoicestatus', $order);
            })
            ->orderColumn('statusformatted', function($query, $order) {
                $query->orderBy('status', $order);
            })
            ->rawColumns(['raw_id', 'status', 'actions', 'clientname', 'paymentstatus', 'paymentstatusformatted', 'statusformatted'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtOrderFilters($criteria)
    {
        $filters = [];
        $pfx = $this->prefix;
        $clientHelper = new HelpersClient();

        if (isset($criteria["orderstatus"]) && $criteria["orderstatus"] != "Any") {
            if ($criteria["orderstatus"] == "Pending" || $criteria["orderstatus"] == "Active" || $criteria["orderstatus"] == "Cancelled") {
                $statusfilters = [];
                $result = Orderstatus::select("title")->where("show" .strtolower($criteria["orderstatus"]), "1")->get();
                foreach ($result as $data) {
                    $statusfilters[] = $data->title;
                }

                $filters[] =  $this->filterValue("{$pfx}orders.status", "IN", "(" . \App\Helpers\Database::db_build_in_array($statusfilters) . ")");
            } else {
                $filters[] =  $this->filterValue("{$pfx}orders.status", "=", "'{$criteria["orderstatus"]}'");
            }
        }

        if (isset($criteria["clientid"])) {
            $filters[] = $this->filterValue("{$pfx}orders.userid", "=", "'{$criteria["clientid"]}'");
        }

        if (isset($criteria["amount"])) {
            $filters[] = $this->filterValue("{$pfx}orders.amount", "=", "'{$criteria["amount"]}'");
        }

        if (isset($criteria["orderid"])) {
            $filters[] = $this->filterValue("{$pfx}orders.id", "=", "'{$criteria["orderid"]}'");
        }

        if (isset($criteria["ordernum"])) {
            $filters[] = $this->filterValue("{$pfx}orders.ordernum", "=", "'{$criteria["ordernum"]}'");
        }

        if (isset($criteria["orderip"])) {
            $filters[] = $this->filterValue("{$pfx}orders.ipaddress", "=", "'{$criteria["orderip"]}'");
        }

        if (isset($criteria["orderdate_from"]) && isset($criteria["orderdate_to"])) {
            $date = "{$criteria["orderdate_from"]} - {$criteria["orderdate_to"]}";
            $dateRange = $clientHelper->parseDateRangeValue($date);
            $datefrom = $dateRange['from'];
            $dateto = $dateRange['to'];

            $filters[] = $this->filterValue("{$pfx}orders.date", ">=", "'{$datefrom->toDateTimeString()}'");
            $filters[] = $this->filterValue("{$pfx}orders.date", "<=", "'{$dateto->toDateTimeString()}'");
        }
        
        if (isset($criteria["clientname"])) {
            $filters[] = $this->filterValue("concat(firstname, ' ', lastname)", "LIKE", "'%{$criteria["clientname"]}%'");
        }

        if (isset($criteria["paymentstatus"]) && $criteria["paymentstatus"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}invoices.status", "=", "'{$criteria["paymentstatus"]}'");
        }

        return $this->buildRawFilters($filters);
    }

}
