<?php

namespace App\Http\Controllers\Admin\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\Client as HelpersClient;
use App\Helpers\Format;
use App\Helpers\Functions;
use App\Helpers\Gateway;
use App\Helpers\Hooks;
use App\Helpers\Invoice as HelpersInvoice;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;

// Models
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Invoiceitem;
use App\Models\Order;
use App\Models\Note;

// Traits
use App\Traits\DatatableFilter;

class ClientInvoiceController extends Controller
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
            return redirect()
                    ->route("admin.pages.clients.viewclients.index")
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> ') .__('admin.clientsinvalidclientid'));
        }
        
        $userid = $request->userid;
        $serviceid = $request->serviceid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["serviceid"] = $serviceid;
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        return view('pages.clients.viewclients.clientinvoices.index', $templatevars);
    }

    public function create(Request $request)
    {
        $userid = $request->userid;

        if (!auth()->user()->checkPermissionTo("Create Invoice")) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientinvoices.index", ["userid" => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b>&nbsp;You don\'t have permission to access the action.'));
        }

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
        ]);
        
        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientinvoices.index", ["userid" => $userid])
                    ->withErrors($validator)
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> ') .__('admin.clientsinvalidclientid'));
        }

        if (!Gateway::CheckActiveGateway()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clientinvoices.index", ["userid" => $userid])
                    ->with('type', 'danger')
                    ->with('message', __("admin.gatewaysnonesetup"));
        }

        $gateway = Gateway::getClientsPaymentMethod($userid);
        $invoice = Invoice::newInvoice($userid, $gateway);
        $invoice->save();
        $invoiceid = $invoice->id;

        LogActivity::Save("Created Manual Invoice - Invoice ID: $invoiceid", $userid);
        $invoiceArr = [
            "source" => "adminarea", 
            "user" => auth()->user()->id,
            "invoiceid" => $invoiceid, 
            "status" => "Draft"
        ];

        Hooks::run_hook("InvoiceCreation", $invoiceArr);
        Hooks::run_hook("InvoiceCreationAdminArea", $invoiceArr);

        return redirect()
                ->route("admin.pages.billing.invoices.edit", ["id" => $invoiceid])
                ->with('type', 'success')
                ->with('message', "Invoice created succesfully.");
    }

    public function delete(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Delete Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $returnCredit = $request->returnCredit;
        $isMassDelete = $request->massDelete;
        $selectedInvoicesId = $request->selectedInvoicesId;

        if ($isMassDelete) {
            $invoiceID = implode(",", $selectedInvoicesId);
            $data = Invoice::whereIn("id", $selectedInvoicesId);
            $userId = $request->userid;   
        } else {
            $invoiceID = $request->id;
            $data = Invoice::find($invoiceID);
            $userId = $data->userid;
        }

        if (!$data) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> Invalid invoice ID.'),
            ]);
        }

        if ($returnCredit) {
            HelpersInvoice::removeCreditOnInvoiceDelete($invoiceID);
        }

        if (!$data->delete()) {
            return ResponseAPI::Error([
                'message' => "Internal server error!",
            ]);
        }

        LogActivity::Save("Deleted Invoice - Invoice ID: $invoiceID", $userId);
        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function markPaidInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }

        $failedInvoices = [];
        $successfulInvoicesCount = [];
        $invoiceCount = 0;
        $selectedInvoicesId = $request->selectedInvoicesId;

        foreach ($selectedInvoicesId as $invid) {
            $invoice = Invoice::where("id", $invid)->first();
            $invoiceStatus = $invoice->status;
            $paymentMethod = $invoice->paymentmethod;

            if ($invoiceStatus == "Paid") {
                continue;
            }

            if (HelpersInvoice::AddInvoicePayment($invid, "", "", "", $paymentMethod) === false) {
                $failedInvoices[] = $invid;
            }

            $invoiceCount++;
        }

        if (0 < count($selectedInvoicesId)) {
            $successfulInvoicesCount["successfulInvoicesCount"] = $invoiceCount - count($failedInvoices);
        }

        if (count($failedInvoices)) {
            $failedInvoicesString = implode(", ", $failedInvoices);
            $description = sprintf(__("admin.invoicesmarkPaidError"), $failedInvoicesString);
            $description .= "<br>" . __("admin.invoicesmarkPaidErrorInfo") . "<br><a href=\"https://docs.whmcs.com/Clients:Invoices_Tab#Mark_Paid\" target=\"_blank\">" . __("admin.findoutmore") . "</a>";

            return ResponseAPI::Error([
                'message' => "Oh No! Something went wrong!<br>Failed invoice: " .$description,
            ]); 
        }

        return ResponseAPI::Success([
            'message' => "The data successfully updated!<br>Failed invoice: " .implode(", ", $failedInvoices) ."<br>Successfull Invoices Count: " .implode(", ", $successfulInvoicesCount),
        ]);
    }

    public function markUnpaidInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }

        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;

        Invoice::whereIn("id", $selectedInvoicesId)->update(["status" => Invoice::STATUS_UNPAID, "datepaid" => "0000-00-00 00:00:00", "date_cancelled" => "0000-00-00 00:00:00", "date_refunded" => "0000-00-00 00:00:00"]);
        LogActivity::Save("Reactivated Invoice - Invoice ID: " .implode(", ", $selectedInvoicesId), $userId);
        Hooks::run_hook("InvoiceUnpaid", ["invoiceid" => implode(", ", $selectedInvoicesId)]);

        return ResponseAPI::Success([
            'message' => "The data successfully updated!<br>Successfull Invoices Count: " .implode(", ", $selectedInvoicesId),
        ]);
    }

    public function markCancelledInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;

        Invoice::whereIn("id", $selectedInvoicesId)->update(["status" => Invoice::STATUS_CANCELLED, "date_cancelled" => Carbon::now()->toDateTimeString()]);
        LogActivity::Save("Cancelled Invoice - Invoice ID: " .implode(", ", $selectedInvoicesId), $userId);
        Hooks::run_hook("InvoiceCancelled", ["invoiceid" => implode(", ", $selectedInvoicesId)]);

        return ResponseAPI::Success([
            'message' => "The data successfully updated!<br>Successfull Invoices Count: " .implode(", ", $selectedInvoicesId),
        ]);
    }
    
    public function paymentReminderInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;

        foreach ($selectedInvoicesId as $invid) {
            Functions::sendMessage("Invoice Payment Reminder", $invid);
            LogActivity::Save("Invoice Payment Reminder Sent - Invoice ID: $invid", $userId);
        }

        return ResponseAPI::Success([
            'message' => "Payment reminders send successfully!",
        ]);
    }

    public function duplicateInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;

        foreach ($selectedInvoicesId as $invid) {
            HelpersInvoice::duplicate($invid);
        }

        return ResponseAPI::Success([
            'message' => "Duplicate invoice successfully!",
        ]);
    }

    public function mergeInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;
        sort($selectedInvoicesId);
        $endinvoiceid = end($selectedInvoicesId);

        Invoiceitem::whereIn("invoiceid", $selectedInvoicesId)->update(["invoiceid" => $endinvoiceid]);
        Account::whereIn("invoiceid", $selectedInvoicesId)->update(["invoiceid" => $endinvoiceid]);
        Order::whereIn("invoiceid", $selectedInvoicesId)->update(["invoiceid" => $endinvoiceid]);

        foreach ($selectedInvoicesId as $replaceInvoiceId) {
            if ($replaceInvoiceId !== $endinvoiceid) {
                \DB::connection()->update("UPDATE {$this->prefix}credit SET description=CONCAT(description, \". Merged to Invoice #" . (int) $endinvoiceid . "\") WHERE description LIKE \"%Invoice #" . (int) $replaceInvoiceId . "\"");
            }
        }

        $totalcredit = Invoice::select("id", "credit")->whereIn("id", $selectedInvoicesId)->get()->sum('credit');

        Invoice::where("id", $endinvoiceid)->update(["credit" => $totalcredit]);
        unset($selectedInvoicesId[count($selectedInvoicesId) - 1]);

        Invoice::whereIn("id", $selectedInvoicesId)->delete();
        HelpersInvoice::UpdateInvoiceTotal($endinvoiceid);

        LogActivity::Save("Merged Invoice IDs " .implode(",", $selectedInvoicesId) ." to Invoice ID: $endinvoiceid", $userId);

        return ResponseAPI::Success([
            'message' => "Merge invoice successfully!",
        ]);
    }

    public function masspayInvoice(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b> You don\'t have permission to access the action.'),
            ]);
        }
        
        $userId = $request->userid;
        $selectedInvoicesId = $request->selectedInvoicesId;

        // TODO: Need merge feature createInvoices
        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userId);
        $paymentmethod = Gateway::getClientsPaymentMethod($userId);
        $invoiceitems = [];

        foreach ($selectedInvoicesId as $invoiceid) {
            $data = Invoice::where("id", $invoiceid)->first()->toArray();

            $subtotal = 0;
            $credit = 0;
            $tax = 0;
            $tax2 = 0;

            $subtotal += $data["subtotal"];
            $credit += $data["credit"];
            $tax += $data["tax"];
            $tax2 += $data["tax2"];
            $thistotal = $data["total"];

            $thispayments = Account::select("invoiceid", "amountin")->where("invoiceid", $invoiceid)->get()->sum("amountin");
            $thistotal = $thistotal - $thispayments;
            
            $item = new Invoiceitem();
            $item->userid = $userId;
            $item->type = "Invoice";
            $item->relid = $invoiceid;
            $item->description = __("client.invoicenumber") .$invoiceid;
            $item->amount = $thistotal; 
            $item->duedate = now();
            $item->paymentmethod = $paymentmethod;
            $item->save();
        }

        // TODO: Need merge feature createInvoices
        $invoiceid = \App\Helpers\ProcessInvoices::createInvoices($userId, true, true, ["invoices" => $selectedInvoicesId]);

        return ResponseAPI::Success([
            'message' => "Mass Pay Invoice Created Successfully!",
        ]);
    }

    public function dtClientInvoice(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $gatewaysarray = Gateway::getGatewaysArray();
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
            "gatewaysarray" => $gatewaysarray,
        ];

        $query = Invoice::select("{$pfx}invoices.*", "{$pfx}clients.firstname", "{$pfx}clients.lastname")
                            ->join("{$pfx}clients", "{$pfx}clients.id", "{$pfx}invoices.userid")
                            ->where("userid", $userid)
                            ->orderBy('date', 'desc');

        $filters = $this->dtClientInvoiceFilters($dataFiltered);
        if ($filters) {
            $query->whereRaw($filters);
        }

        return datatables()->of($query)
            ->editColumn('invoicenum', function($row) {
                if (!$row->invoicenum) {
                    $row->invoicenum = $row->id;
                }

                return $row->invoicenum;
            })
            ->editColumn('date', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->date);
            })
            ->editColumn('duedate', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->duedate);
            })
            ->editColumn('datepaid', function($row) {
                return $row->datepaid == "0000-00-00 00:00:00" ? "-" : (new HelpersClient())->fromMySQLDate($row->datepaid);
            })
            ->editColumn('total', function($row) {
                // TODO: Open invtooltip with ajaxrequest or bootstrap modal 
                // "<a href=\"invoices.php?action=invtooltip&id=" . $id . "&userid=" . $userid . generate_token("link") . "\" class=\"invtooltip\" lang=\"\">" . $total . "</a>"

                return Format::formatCurrency($row->credit + $row->total);
            })
            ->editColumn('paymentmethod', function($row) use($params) {
                $gatewaysarray = $params["gatewaysarray"];

                if (array_key_exists($row->paymentmethod, $gatewaysarray)) {
                    return $gatewaysarray[$row->paymentmethod];
                }

                return $row->paymentmethod;
            })
            ->editColumn('status', function($row) {
                return HelpersInvoice::getInvoiceStatusColour($row->status, false);
            })
            ->addColumn('raw_id', function($row) {
                $route = route('admin.pages.billing.invoices.edit', ['id' => $row->id]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->addColumn('actions', function($row) {
                $route = route('admin.pages.billing.invoices.edit', ['id' => $row->id]);
                $action = "";
                $flag = "";
                $payments = Account::where("invoiceid", $row->id)->count();
                $credit = $row->credit;

                if (0 < $credit && 0 < $payments) {
                    $flag = "ExistingCreditAndPayments";
                } elseif(0 < $credit && $payments == 0) {
                    $flag = "ExistingCredit";
                } elseif($credit == 0 && 0 < $payments) {
                    $flag = "ExistingPayments";
                }

                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-flag=\"{$flag}\" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['raw_id', 'status', 'actions'])
            ->addIndexColumn()
            ->toJson();
    }

    private function dtClientInvoiceFilters($criteria)
    {
        $filters = [];
        $pfx = $this->prefix;
        $userid = $criteria["userid"];
        $clientHelper = new HelpersClient();
        $dateFilters = [
            "date" => (isset($criteria["invoicedate_from"]) && isset($criteria["invoicedate_to"])) ? "{$criteria["invoicedate_from"]} - {$criteria["invoicedate_to"]}" : null, 
            "duedate" => (isset($criteria["duedate_from"]) && isset($criteria["duedate_to"])) ? "{$criteria["duedate_from"]} - {$criteria["duedate_to"]}" : null, 
            "datepaid" => (isset($criteria["datepaid_from"]) && isset($criteria["datepaid_to"])) ? "{$criteria["datepaid_from"]} - {$criteria["datepaid_to"]}" : null, 
            "last_capture_attempt" => (isset($criteria["last_capture_from"]) && isset($criteria["last_capture_to"])) ? "{$criteria["last_capture_from"]} - {$criteria["last_capture_to"]}" : null, 
            "date_refunded" => (isset($criteria["date_refunded_from"]) && isset($criteria["date_refunded_to"])) ? "{$criteria["date_refunded_from"]} - {$criteria["date_refunded_to"]}" : null, 
            "date_cancelled" =>(isset($criteria["date_cancelled_from"]) && isset($criteria["date_cancelled_to"])) ? "{$criteria["date_cancelled_from"]} - {$criteria["date_cancelled_to"]}" : null,
        ];

        if (isset($criteria["serviceid"])) {
            $filters[] = $this->filterValue("{$pfx}invoices.id", "IN", "(SELECT invoiceid FROM {$pfx}invoiceitems WHERE type='Hosting' AND relid='{$criteria["serviceid"]}')");
            // $filters[] = $this->filterValue("relid", "=", "'{$criteria["serviceid"]}'");
        }

        if (isset($criteria["clientname"])) {
            $filters[] = $this->filterValue("concat(firstname, ' ', lastname)", "LIKE", "'%{$criteria["clientname"]}%'");
        }

        if (isset($criteria["invoicenum"])) {
            $filters[] = $this->filterValue("{$pfx}invoices.id", "=", "'{$criteria["invoicenum"]}'");
        }

        if (isset($criteria["lineitem"])) {
            $filters[] = $this->filterValue("{$pfx}invoices.id", "IN", "(SELECT invoiceid FROM {$pfx}invoiceitems WHERE userid=$userid AND description LIKE '%{$criteria["lineitem"]}%')");
        }

        if (isset($criteria["paymentmethod"]) && $criteria["paymentmethod"] != "Any") {
            $filters[] = $this->filterValue("{$pfx}invoices.paymentmethod", "=", "'{$criteria["paymentmethod"]}'");
        }

        if (isset($criteria["invstatus"]) && $criteria["invstatus"] != "Any") {
            if ($criteria["invstatus"] == "Overdue") {
                $date = date("Ymd");

                $filters[] = $this->filterValue("{$pfx}invoices.status", "=", "'Unpaid'");
                $filters[] = $this->filterValue("{$pfx}invoices.duedate", "<", "'$date'");
            } else {
                $filters[] = $this->filterValue("{$pfx}invoices.status", "=", "'{$criteria["invstatus"]}'");
            }
        }

        if (isset($criteria["totalfrom"])) {
            $filters[] = $this->filterValue("{$pfx}invoices.total", ">=", "'{$criteria["totalfrom"]}'");
        }
        
        if (isset($criteria["totalto"])) {
            $filters[] = $this->filterValue("{$pfx}invoices.total", "<=", "'{$criteria["totalto"]}'");
        }

        foreach ($dateFilters as $fieldName => $date) {
            if ($date) {
                $dateRange = $clientHelper->parseDateRangeValue($date);
                $datefrom = $dateRange['from'];
                $dateto = $dateRange['to'];

                $filters[] = $this->filterValue("{$pfx}invoices.$fieldName", "BETWEEN", "'{$datefrom->toDateTimeString()}' AND '{$dateto->toDateTimeString()}'");
            }
        }

        return $this->buildRawFilters($filters);
    }

}
