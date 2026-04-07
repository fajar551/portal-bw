<?php

namespace App\Http\Controllers\Admin\Client;

use App\Helpers\AdminFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

// Helpers
use App\Helpers\Client as HelpersClient;
use App\Helpers\Format;
use App\Helpers\Gateway;
use App\Helpers\Invoice as HelpersInvoice;
use App\Helpers\LogActivity;
use App\Helpers\ResponseAPI;
use App\Helpers\SystemHelper;

// Models
use App\Models\Account;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Invoice;
use App\Models\Note;
use App\Rules\FloatValidator;

// Traits
use App\Traits\DatatableFilter;

class ClientTransactionController extends Controller
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
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $currency = \App\Helpers\Format::GetCurrency();   
        $statAccount = Account::selectRaw("SUM(amountin) as amountin, SUM(fees) as fees, SUM(amountout) as amountout, SUM(amountin-fees-amountout) as balance")
                                    ->where("userid", $userid)
                                    ->first();

        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["amountIn"] = Format::formatCurrency($statAccount->amountin)->toPrefixed();
        $templatevars["amountOut"] = Format::formatCurrency($statAccount->amountout)->toPrefixed();
        $templatevars["fees"] = Format::formatCurrency($statAccount->fees)->toPrefixed();
        $templatevars["balance"] = Format::formatCurrency($statAccount->balance)->toPrefixed();
        $templatevars["notesCount"] = Note::where('userid', $userid)->count();

        return view('pages.clients.viewclients.clienttransactions.index', $templatevars);
    }

    public function create(Request $request)
    {
        $userid = $request->userid;
        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();

        return view('pages.clients.viewclients.clienttransactions.create', $templatevars);
    }

    public function edit(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;

        $clientsdetails = (new HelpersClient())->DataClientsDetails($userid);
        $transaction = Account::findOrFail($id);
        $transaction->date = (new HelpersClient())->fromMySQLDate($transaction->date);

        $templatevars["transaction"] = $transaction;
        $templatevars["clientsdetails"] = $clientsdetails;
        $templatevars["userid"] = $userid;
        $templatevars["paymentmethodlist"] = (new Gateway($request))->paymentMethodsList();

        return view('pages.clients.viewclients.clienttransactions.edit', $templatevars);
    }
    
    public function store(Request $request)
    {
        $userid = $request->userid;

        if (!auth()->user()->checkPermissionTo("Add Transaction")) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clienttransactions.create", ["userid" => $userid])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b>&nbsp;You don\'t have permission to access the action.'));
        }

        $messages = [
            'description.required_without' => __("admin.transactionsinvoiceIdOrDescriptionRequired"),
            'invoiceid.required' => __("admin.transactionsinvoiceIdOrDescriptionRequired"),
            'amountin.required' => __("admin.transactionsamountInOutOrFeeRequired"),
            'amountout.required' => __("admin.transactionsamountInOutOrFeeRequired"),
            'fees.required' => __("admin.transactionsamountInOutOrFeeRequired"),
        ];

        $validator = Validator::make($request->all(), [
            'userid' => "required|integer|exists:App\Models\Client,id",
            'date' => "required|date_format:d/m/Y",
            'description' => "required_without:invoiceid|nullable",
            'transid' => "nullable|string",
            'invoiceid' => "nullable|string",
            'paymentmethod' => "nullable|string",
            'amountin' => ["required", new FloatValidator()],
            'fees' => ["required", new FloatValidator()],
            'amountout' => ["required", new FloatValidator()],
            'addcredit' => 'nullable',
        ], $messages);
        
        $params = [
            "paymentMethod" => $request->paymentmethod ?? "",
            "invoiceID" => $request->invoiceid ?? 0,
            "transactionID" => $request->transid ?? "",
            "amountIn" => $request->amountin,
            "fees" => $request->fees,
            "date" => $request->date,
            "amountOut" => $request->amountout,
            "description" => $request->description,
            "addCredit" => $request->addcredit,
        ];

        $validator->after(function ($v) use($params) {
            extract($params);

            if ($amountIn < 0) {
                $v->errors()->add('amountin', __("admin.transactionsamountInLessThanZero") );
            }
        
            if ($amountOut < 0) {
                $v->errors()->add('amountout', __("admin.transactionsamountOutLessThanZero") );
            }

            if (!$invoiceID && !$description) {
                $v->errors()->add('description', __("admin.transactionsinvoiceIdOrDescriptionRequired") );
                $v->errors()->add('invoiceid', __("admin.transactionsinvoiceIdOrDescriptionRequired") );
            }
        
            if ((!$amountOut || $amountOut == 0) && (!$amountIn || $amountIn == 0) && (!$fees || $fees == 0)) {
                $v->errors()->add('amountin', __("admin.transactionsamountInOutOrFeeRequired") );
                $v->errors()->add('amountout', __("admin.transactionsamountInOutOrFeeRequired") );
                $v->errors()->add('fees', __("admin.transactionsamountInOutOrFeeRequired") );
            }

            if ($amountIn && $fees && $amountIn < $fees) {
                $v->errors()->add('fees', __("admin.transactionsfeeMustBeLessThanAmountIn") );
            }

            if ($amountIn && $fees && $fees < 0) {
                $v->errors()->add('amountin', __("admin.transactionsamountInFeeMustBePositive") );
            }

            if (0 < $amountIn && 0 < $amountOut) {
                $v->errors()->add('amountin', __("admin.transactionsamountInFeeMustBePositive") );
            }

            if ($addCredit && 0 < $amountOut) {
                $v->errors()->add('amountout', __("admin.transactionsamountOutCannotBeUsedWithAddCredit") );
            }

            if ($addCredit && $invoiceID) {
                $v->errors()->add('invoiceid', __("admin.transactionsinvoiceIDAndCreditInvalid") );
                $v->errors()->add('addCredit', __("admin.transactionsinvoiceIDAndCreditInvalid") );
            }

            if ($transactionID && !HelpersInvoice::isUniqueTransactionID($transactionID, $paymentMethod)) {
                $v->errors()->add('transid', __("admin.transactionsrequireUniqueTransaction") );
            }
        });

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clienttransactions.create", ["userid" => $userid])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        extract($params);

        if ($invoiceID) {
            $transactionUserID = Invoice::find($invoiceID);
            if (!$transactionUserID) {
                $validator->errors()->add('invoiceid', __("admin.invoicesinvalidInvoiceID") );
                
                return redirect()
                        ->route("admin.pages.clients.viewclients.clienttransactions.create", ["userid" => $userid])
                        ->withErrors($validator)
                        ->withInput()
                        ->with('type', 'danger')
                        ->with('message', AdminFunctions::infoBoxMessage(__("admin.invoicescheckInvoiceID"), __("admin.invoicesinvalidInvoiceID")) );
            } else {
                $transactionUserID = $transactionUserID->userid;
                if ($transactionUserID != $userid) {
                    $validator->errors()->add('invoiceid', __("admin.invoiceswrongUser") );

                    return redirect()
                        ->route("admin.pages.clients.viewclients.clienttransactions.create", ["userid" => $userid])
                        ->withErrors($validator)
                        ->withInput()
                        ->with('type', 'danger')
                        ->with('message', AdminFunctions::infoBoxMessage(__("admin.invoicescheckInvoiceID"), __("admin.invoiceswrongUser")) );
                }
            }

            HelpersInvoice::addInvoicePayment($invoiceID, $transactionID, $amountIn, $fees, $paymentMethod, "", $date);
        } else {
            HelpersInvoice::addTransaction($userid, 0, $description, $amountIn, $fees, $amountOut, $paymentMethod, $transactionID, $invoiceID, $date);
        }

        if ($addCredit) {
            if ($transactionID) {
                $description .= " (Trans ID: " . $transactionID . ")";
            }

            $credit = new Credit();
            $credit->clientid = $userid;
            $credit->date = (new SystemHelper())->toMySQLDate($date);
            $credit->description = $description;
            $credit->amount = $amountIn;
            $credit->save();

            Client::find($userid)->increment('credit', $amountIn);
        }

        return redirect()
                ->route("admin.pages.clients.viewclients.clienttransactions.index", ["userid" => $userid])
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage(__("<b>Well Done!</b>)"), __("The data has created successfully.")) );
    }

    public function update(Request $request)
    {
        $userid = $request->userid;
        $id = $request->id;

        if (!auth()->user()->checkPermissionTo("Edit Transaction")) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clienttransactions.edit", ["userid" => $userid, "id" => $id])
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b>&nbsp;You don\'t have permission to access the action.'));
        }

        $messages = [
            'description.required_without' => __("admin.transactionsinvoiceIdOrDescriptionRequired"),
            'invoiceid.required' => __("admin.transactionsinvoiceIdOrDescriptionRequired"),
            'amountin.required' => __("admin.transactionsamountInOutOrFeeRequired"),
            'amountout.required' => __("admin.transactionsamountInOutOrFeeRequired"),
            'fees.required' => __("admin.transactionsamountInOutOrFeeRequired"),
        ];

        $validator = Validator::make($request->all(), [
            'id' => "required|integer|exists:App\Models\Account,id",
            'userid' => "required|integer|exists:App\Models\Client,id",
            'date' => "required|date_format:d/m/Y",
            'description' => "required_without:invoiceid|nullable",
            'transid' => "nullable|string",
            'invoiceid' => "nullable|string",
            'paymentmethod' => "nullable|string",
            'amountin' => ["required", new FloatValidator()],
            'fees' => ["required", new FloatValidator()],
            'amountout' => ["required", new FloatValidator()],
            'addcredit' => 'nullable',
        ], $messages);

        // NOTE: Do we need to add custom validation like @store function? 

        if ($validator->fails()) {
            return redirect()
                    ->route("admin.pages.clients.viewclients.clienttransactions.edit", ["userid" => $userid, "id" => $id])
                    ->withErrors($validator)
                    ->withInput()
                    ->with('type', 'danger')
                    ->with('message', __('<b>Oh No!</b> Please ensure to fill all fields correctly and re-submit the form.'));
        }

        extract($request->all());

        Account::where("id", $id)->update([
            "gateway" => $paymentmethod, 
            "date" => (new SystemHelper())->toMySQLDate($date), 
            "description" => $description, 
            "amountin" => $amountin, 
            "fees" => $fees, 
            "amountout" => $amountout, 
            "transid" => $transid, 
            "invoiceid" => $invoiceid
        ]);

        LogActivity::Save("Modified Transaction (User ID: $userid - Transaction ID: $id)", $userid);

        return redirect()
                ->route("admin.pages.clients.viewclients.clienttransactions.index", ["userid" => $userid])
                ->with('type', 'success')
                ->with('message', AdminFunctions::infoBoxMessage(__("<b>Well Done!</b>)"), __("The data has updated successfully.")) );
    }

    public function delete(Request $request)
    {
        if (!auth()->user()->checkPermissionTo("Delete Transaction")) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b>&nbsp;You don\'t have permission to access the action.'),
            ]);
        }

        $userid = $request->userid;
        $id = $request->id;

        $transaction = Client::find($userid)->transactions()->find($id);
        if (!$transaction) {
            return ResponseAPI::Error([
                'message' =>  __('<b>Oh No!</b>&nbsp;Invalid ID.'),
            ]);
        }
        
        $transaction->delete();
        LogActivity::Save("Deleted Transaction (ID: $id - User ID: $userid)", $userid);

        return ResponseAPI::Success([
            'message' => "The data successfully deleted!",
        ]);
    }

    public function dtClientTransaction(Request $request)
    {
        $pfx = $this->prefix;
        $dataFiltered = $request->dataFiltered;
        $userid = $dataFiltered["userid"];
        $params = [
            "pfx" => $pfx,
            "userid" => $userid,
            "gatewaysarray" => Gateway::getGatewaysArray(),
        ];

        $query = Account::where("userid", $userid);

        return datatables()->of($query)
            ->editColumn('date', function($row) {
                return (new HelpersClient())->fromMySQLDate($row->date);
            })
            ->editColumn('amountin', function($row) {
                return Format::formatCurrency($row->amountin);
            })
            ->editColumn('fees', function($row) {
                return Format::formatCurrency($row->fees);
            })
            ->editColumn('amountout', function($row) {
                return Format::formatCurrency($row->amountout);
            })
            ->editColumn('gateway', function($row) use($params) {
                extract($params);
                
                if (array_key_exists($row->gateway, $gatewaysarray)) {
                    return $gatewaysarray[$row->gateway];
                }
                
                return $row->gateway;
            })
            ->editColumn('description', function($row) {
                $description = $row->description;
                $invoiceid = $row->invoiceid;
                $transid = $row->transid;
                
                if ($invoiceid != "0") {
                    $route = route("admin.pages.billing.invoices.edit", ["id" => $invoiceid]);

                    $description .= " (<a href=\"{$route}\">#{$invoiceid}</a>)";
                }

                if ($transid != "") {
                    $description .= " - Trans ID: " . $transid;
                }

                return $description;
            })
            ->addColumn('raw_id', function($row) use($params) {
                extract($params);
                $route = route('admin.pages.clients.viewclients.clienttransactions.edit', ['action' => 'edit', 'userid' => $userid, 'id' => $row->id]);

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            // ->addColumn('totalin', function($row) {
            //     return $totalin = $totalin + $row->amountin;
            // })
            // ->addColumn('totalout', function($row) {
            //     return $totalout = $totalout + $row->amountout;
            // })
            // ->addColumn('totalfees', function($row) {
            //     return $totalfees = $totalfees + $row->fees;
            // })
            ->addColumn('actions', function($row) use($params) {
                extract($params);

                $route = route('admin.pages.clients.viewclients.clienttransactions.edit', ['action' => 'edit', 'userid' => $userid, 'id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$route}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button type=\"button\" class=\"btn btn-xs text-danger p-1 act-delete\" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['raw_id', 'actions', 'description'])
            ->addIndexColumn()
            ->toJson();
    }

}
