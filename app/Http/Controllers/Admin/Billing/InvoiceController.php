<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Database;
use DataTables;
use App;
use \App\Helpers\Cfg;
use Illuminate\Support\Carbon;
use \App\Helpers\Format;
use App\Helpers\LogActivity;
use Validator;
use \App\Helpers\HelperApi as LocalApi;
use App\Helpers\HelperApi;
use \App\Helpers\Invoice;
use App\Helpers\ResponseAPI;
use App\Models\Invoiceitem;
use App\Helpers\InvoiceClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Global_;
use Illuminate\Support\Facades\Mail;
// use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = Database::prefix();
        $this->adminURL = request()->segment(1) . '/' . request()->segment(2) . '/';

        // Pastikan BillingNotificationReceiver sudah diset
        if (!isset($CONFIG["BillingNotificationReceiver"])) {
            $CONFIG["BillingNotificationReceiver"] = "";

            // Tambahkan ke database jika belum ada
            $result = DB::table('tblconfiguration')
                ->where('setting', 'BillingNotificationReceiver')
                ->first();

            if (!$result) {
                DB::table('tblconfiguration')->insert([
                    'setting' => 'BillingNotificationReceiver',
                    'value' => '',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    public function Invoices()
    {

        $count = \App\Helpers\Invoice::getInvoiceTotals();
        $count = collect($count)->map(function ($item) {

            //dd($item['paid']->price);
            //Format::Currency((int)$data->total,null,['prefix' => 'Rp', 'format' => '3']);
            return [
                'currencycode' => $item['currencycode'],
                'paid' => Format::Currency($item['paid']->toNumeric(), null, ['prefix' => $item['paid']->getCurrency()['prefix'] . ' ', 'format' => $item['paid']->getCurrency()['format']]),
                'unpaid' => Format::Currency($item['unpaid']->toNumeric(), null, ['prefix' => $item['unpaid']->getCurrency()['prefix'] . ' ', 'format' => $item['unpaid']->getCurrency()['format']]),
                'overdue' => Format::Currency($item['overdue']->toNumeric(), null, ['prefix' => $item['overdue']->getCurrency()['prefix'] . ' ', 'format' => $item['overdue']->getCurrency()['format']]),
            ];
        });
        $gateway = \App\Helpers\Gateway::GetGatewaysArray();
        return view('pages.billing.invoices.index', ['baseURL' =>  $this->adminURL, 'gateway' => $gateway, 'count' => $count]);
    }

    public function invoicesData(Request $request)
    {
        // dd($request->all());
        $invoice = DB::table("{$this->prefix}invoices as invoices")->select('invoices.id', 'clients.firstname', 'clients.lastname', 'invoices.date', 'invoices.duedate', 'invoices.datepaid', 'invoices.last_capture_attempt', 'invoices.total', 'invoices.status', 'invoices.paymentmethod', 'invoices.userid');
        $invoice->join("{$this->prefix}clients as clients", 'invoices.userid', '=', 'clients.id');

        //$invoice->whereDate('invoices.date',Carbon::createFromFormat('d/m/Y','18/09/2019')->format('Y-m-d'));
        // dd($invoice->toSql());
        if ($request->client) {
            $invoice->where('invoices.userid', (int)$request->client);
        }
        if ($request->invoicenum) {
            $invoice->where('invoices.id', (int)$request->invoicenum);
        }
        if ($request->lineitem) {
            //$invoice->where('invoices.lineitem',(int)$request->invoicenum);
        }
        if ($request->paymentmethod) {
            $invoice->where('invoices.paymentmethod', $request->paymentmethod);
        }
        if ($request->status) {
            $invoice->where('invoices.status', $request->status);
        }
        if ($request->status) {
            $invoice->where('invoices.status', $request->status);
        }
        if (!is_null($request->totalfrom) && !is_null($request->totalto)) {
            $invoice->whereBetween('invoices.total', [$request->totalfrom, $request->totalto]);
        }
        if ($request->invoicedate) {
            $invoice->whereDate('invoices.date', Carbon::createFromFormat('d/m/Y', $request->invoicedate)->format('Y-m-d'));
        }

        if ($request->last_capture_attempt) {
            $invoice->whereDate('invoices.last_capture_attempt', Carbon::createFromFormat('d/m/Y', $request->last_capture_attempt)->format('Y-m-d'));
        }
        if ($request->date_refunded) {
            $invoice->whereDate('invoices.date_refunded', Carbon::createFromFormat('d/m/Y', $request->date_refunded)->format('Y-m-d'));
        }

        if ($request->date_cancelled) {
            $invoice->whereDate('invoices.date_cancelled', Carbon::createFromFormat('d/m/Y', $request->date_cancelled)->format('Y-m-d'));
        }

        if ($request->datepaid_from && $request->datepaid_to) {
            $invoice->whereBetween('invoices.datepaid', [
                Carbon::parse($request->datepaid_from)->startOfDay(),
                Carbon::parse($request->datepaid_to)->endOfDay()
            ]);
        }

        $gatewayDATA = \App\Helpers\Gateway::GetGatewaysArray();
        //dd($gatewayDATA);

        return Datatables::of($invoice)
            ->addColumn('checkbox', function ($data) {
                return $data->id;
            })
            ->addColumn('client', function ($data) {
                return '<a target="_blank" href="' . url(request()->segment(1) . '/clients/clientsummary?userid=' . $data->userid) . '">' . ucwords($data->firstname) . ' ' . ucwords($data->lastname) . '</a>';
            })
            ->editColumn('id', function ($data) {
                return '<a href="' . url($this->adminURL . 'invoices/edit/' . $data->id) . '">' . $data->id . '</a>';
            })
            ->editColumn('date', function ($data) {
                return  Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat'));
            })
            ->editColumn('duedate', function ($data) {
                return  Carbon::parse($data->duedate)->isoFormat(Cfg::get('DateFormat'));
            })
            ->editColumn('last_capture_attempt', function ($data) {
                return ($data->last_capture_attempt != '0000-00-00 00:00:00') ? Carbon::parse($data->last_capture_attempt)->isoFormat(Cfg::get('DateFormat')) : 'NA';
            })
            ->editColumn('datepaid', function ($data) {
                return ($data->datepaid != '0000-00-00 00:00:00') ? Carbon::parse($data->datepaid)->isoFormat(Cfg::get('DateFormat')) : 'NA';
            })
            ->editColumn('total', function ($data) {
                $link = "";
                $invoice = new InvoiceClass($data->id);
                $dataInv = $invoice->getOutput();
                $invoiceItems = $invoice->getLineItems();
                $invoiceItems = collect($invoiceItems)->map(function ($item) {
                    return [
                        'id'             =>    $item['id'],
                        'description'    =>    $item['description'],
                        'type'           =>    $item['type'],
                        'relid'          =>    $item['relid'],
                        'rawamount'      =>    Format::Currency($item['amount']->toNumeric(), null, ['prefix' => $item['amount']->getCurrency()['prefix'] . ' ', 'format' => $item['amount']->getCurrency()['format']]),
                        'taxed'          =>    $item['taxed'] ?? "",
                        'taxamount'      =>    $item['taxamount'] ?? "",
                        'taxrate'        =>    $item['taxrate'] ?? "",
                    ];
                });

                $invoiceItems2 = $invoiceItems->toArray();

                $invoiceItemDesc = [];
                foreach ($invoiceItems2 as $k => $v) {
                    // $contentPop = "";
                    // $contentPop .= $v['description'] . " " . $v['rawamount'];
                    $invoiceItemDesc[] = $v['description'] . "<br>" . $v['rawamount'];
                }

                // dd($invoiceItemDesc);
                $text = implode("<br><br>", $invoiceItemDesc);
                // $link .= "<a href=\"$data->id\">" . Format::Currency((int)$data->total, null, ['prefix' => 'Rp', 'format' => '3']) . "</a>";
                $link .= "<a tabindex=\"1\" role=\"button\" data-toggle=\"popover\" data-trigger=\"focus\" title=\"Invoice Details\" data-content=\"$text\">" . Format::Currency((int)$data->total, null, ['prefix' => 'Rp', 'format' => '3']) . "</a>";

                return $link;
            })
            ->editColumn('paymentmethod', function ($data) use ($gatewayDATA) {
                return $gatewayDATA[$data->paymentmethod] ?? '';
            })

            /*    ->addColumn('client', function($data) {
            return $data->firstname.' '.$data->lastname;
         })
        ->editColumn('date', function($data) {
            return  Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat'));
        })
        ->editColumn('description', function($data) {
            return $data->description.' (#'.$data->invoiceid.') Trans ID: '.$data->transid;
        })
        ->editColumn('amountin', function($data) {
            return Format::Currency((int)$data->amountin,null,['prefix' => 'Rp', 'format' => '3']);
        })
        ->editColumn('amountout', function($data) {
            return Format::Currency((int)$data->amountout,null,['prefix' => 'Rp', 'format' => '3']);
        })
        ->editColumn('fees', function($data) {
            return Format::Currency((int)$data->fees,null,['prefix' => 'Rp', 'format' => '3']);
        })
        */
            ->addColumn('action', function ($data) {
                return '
                    <form id="fd' . $data->id . '" action="' . url($this->adminURL . 'invoices/destroy/') . '" method="POST">
                        <input type="hidden" name="_token" value="' . csrf_token() . '" />
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="id" value="' . $data->id . '">
                        <a href="' . url($this->adminURL . 'invoices/edit/' . $data->id) . '" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                        <button  type="button" data-id="' . $data->id . '"  class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                    </form>
                        ';
            })
            // >removeColumn('password')
            ->rawColumns(['checkbox', 'action', 'client', 'id', 'total'])
            ->toJson();
    }

    public function InvoicesDestroy(Request $request)
    {
        /* dd($request->all()); */
        $id = (int) $request->id;
        $invoice = \App\Models\Invoice::find($id);
        $userID = $invoice->userid;
        $invoice->delete();
        LogActivity::Save("Deleted Invoice - Invoice ID: " . $id, $userID);
        return back()->with('success', 'Deleted Invoice successfully');
    }


    public function Action(Request $request)
    {
        //dd($request->all());
        //$invoice=$request->invoice ?? array();
        if (isset($request->markpaid)) {
            if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
                return back()->with('error', '<b>Oh No!</b> You don\'t have permission to access the action.');
            }
            $failedInvoices = [];
            $successfulInvoicesCount = [];
            $invoiceCount = 0;
            $selectedInvoicesId = $request->invoice ?? array();

            foreach ($selectedInvoicesId as $invid) {
                $invoice = \App\Models\Invoice::where("id", $invid)->first();
                $invoiceStatus = $invoice->status;
                $paymentMethod = $invoice->paymentmethod;

                if ($invoiceStatus == "Paid") {
                    continue;
                }

                if (\App\Helpers\Invoice::AddInvoicePayment($invid, "", "", "", $paymentMethod) === false) {
                    $failedInvoices[] = $invid;
                }

                $invoiceCount++;
            }

            if (0 < count($selectedInvoicesId)) {
                $successfulInvoicesCount["successfulInvoicesCount"] = $invoiceCount - count($failedInvoices);
            }

            return back()->with('success', "The data successfully updated!<br>Failed invoice: " . implode(", ", $failedInvoices) . "<br>Successfull Invoices Count: " . implode(", ", $successfulInvoicesCount));
        }

        if (isset($request->markunpaid)) {
            if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
                return back()->with('error', '<b>Oh No!</b> You don\'t have permission to access the action.');
            }

            $selectedInvoicesId = $request->invoice ?? array();
            foreach ($selectedInvoicesId as $invid) {
                $invoice = \App\Models\Invoice::find($invid);
                $invoice->status = 'Unpaid';
                $invoice->date_cancelled = "0000-00-00 00:00:00";
                $invoice->save();
                LogActivity::Save("Reactivated Invoice - Invoice ID: " . $invid, $invoice->userid);
                \App\Helpers\Hooks::run_hook("InvoiceUnpaid", ['invoiceid' => $invid]);
            }

            return back()->with('success', "Successfull Reactivated Invoice");
        }

        if (isset($request->markcancelled)) {
            $selectedInvoicesId = $request->invoice ?? array();
            foreach ($selectedInvoicesId as $invid) {
                $invoice = \App\Models\Invoice::find($invid);
                $invoice->status = 'Cancelled';
                $invoice->date_cancelled = Carbon::now();
                $invoice->save();
                LogActivity::Save("Cancelled Invoice - Invoice ID: " . $invid, $invoice->userid);
                \App\Helpers\Hooks::run_hook("InvoiceCancelled", ['invoiceid' => $invid]);
            }
            return back()->with('success', "Successfull Cancelled Invoice");
        }

        if (isset($request->duplicateinvoice)) {
            $selectedInvoicesId = $request->invoice ?? array();
            foreach ($selectedInvoicesId as $invid) {
                \App\Helpers\Invoice::duplicate($invid);
            }
            return back()->with('success', "Successfull Duplicate Invoice");
        }
        if (isset($request->massdelete)) {
            $selectedInvoicesId = $request->invoice ?? array();
            foreach ($selectedInvoicesId as $invid) {
                $invoice = \App\Models\Invoice::find($invid);
                $userID = $invoice->userid;
                $invoice->delete();

                \App\Models\Invoiceitem::where('invoiceid', $invid)->delete();

                LogActivity::Save("Deleted Invoice - Invoice ID: " . $invid, $userID);
            }
            return back()->with('success', 'Deleted Invoice successfully');
        }

        if (isset($request->paymentreminder)) {
            $selectedInvoicesId = $request->invoice ?? array();
            foreach ($selectedInvoicesId as $invid) {
                $invoice = \App\Models\Invoice::find($invid);
                $userID = $invoice->userid;
                \App\Helpers\Functions::sendMessage("Invoice Payment Reminder", $invid);
                LogActivity::Save("Invoice Payment Reminder Sent - Invoice ID: " . $invid, $userID);
            }
            return back()->with('success', 'sent invoice Payment Reminde Invoice successfully');
        }

        return back()->with('success', 'No action select');
        // dd( $request->all());

    }



    public function Invoices_add()
    {
        return view('pages.billing.invoices.add');
    }

    public function InvoicesEditX(Request $request, $id)
    {
        try {
            $invoice = new \App\Helpers\InvoiceClass($id);
        } catch (\Exception $e) {
            abort(404);
        }
        $invoiceModel = $invoice->getModel();

        $saveoptions = $request->get("saveoptions");
        $save = $request->get("save");
        $sub = $request->get("sub");
        $addcredit = $request->get("addcredit");
        $removecredit = $request->get("removecredit");
        $creditapply = $request->get("creditapply");
        $creditremove = $request->get("creditremove");
        $tplname = $request->get("tplname");
        $error = $request->get("error");
        $refundattempted = $request->get("refundattempted");
        $publishInvoice = $request->get("publishInvoice");
        $publishAndSendEmail = $request->get("inputPublishAndSendEmail");
        $userid = $invoice->getData("userid");
        $oldpaymentmethod = $invoice->getData("paymentmethod");

        $gatewaysarray = \App\Helpers\Gateway::getGatewaysArray();
        $data = (array) DB::table("tblinvoices")->join("tblclients", "tblclients.id", "=", "tblinvoices.userid")->join("tblpaymentgateways", "tblpaymentgateways.gateway", "=", "tblinvoices.paymentmethod")->where("tblinvoices.id", $id)->where("tblpaymentgateways.setting", "=", "type")->first(array("tblinvoices.*", "tblclients.firstname", "tblclients.lastname", "tblclients.companyname", "tblclients.groupid", "tblclients.state", "tblclients.country", "tblpaymentgateways.value"));
        $paymentmethod = $data["paymentmethod"];
        $type = $data["value"];
        // loadGatewayModule($paymentmethod);

        $id = $data["id"];
        $invoicenum = $data["invoicenum"];
        $date = $data["date"];
        $duedate = $data["duedate"];
        $datepaid = $data["datepaid"];
        $subtotal = $data["subtotal"];
        $credit = $data["credit"];
        $tax = $data["tax"];
        $tax2 = $data["tax2"];
        $total = $data["total"];
        $taxrate = $data["taxrate"];
        $taxrate2 = $data["taxrate2"];
        $status = $data["status"];
        $paymentmethod = $data["paymentmethod"];
        $payMethodId = $data["paymethodid"];
        $notes = $data["notes"];
        $userid = $data["userid"];
        $firstname = $data["firstname"];
        $lastname = $data["lastname"];
        $companyname = $data["companyname"];
        $groupid = $data["groupid"];
        $clientstate = $data["state"];
        $clientcountry = $data["country"];
        $date = (new \App\Helpers\Functions)->fromMySQLDate($date);
        $duedate = (new \App\Helpers\Functions)->fromMySQLDate($duedate);
        $datepaid = (new \App\Helpers\Functions)->fromMySQLDate($datepaid, "time");
        $lastCaptureAttempt = $invoice->getData("last_capture_attempt");
        $payMethod = NULL;
        if ($payMethodId) {
            $payMethod = \App\Models\Paymethod::find($payMethodId);
        }
        $currency = \App\Helpers\Format::getCurrency($userid);
        $result = \App\Models\Account::selectRaw("COUNT(id) AS transcount,SUM(amountin)-SUM(amountout) AS amountpaid")->where(array("invoiceid" => $id))->first();
        $data = $result->toArray();
        $transcount = $data['transcount'];
        $amountpaid = $data['amountpaid'] ?? 0;
        $balance = $total - $amountpaid;
        $balance = $rawbalance = sprintf("%01.2f", $balance);
        $paymentmethodfriendly = "";
        if ($status == "Unpaid") {
            $paymentmethodfriendly = $gatewaysarray[$paymentmethod];
        } else {
            if ($transcount == 0) {
                $paymentmethodfriendly = \Lang::get("admin.invoicesnotransapplied");
            } else {
                $paymentmethodfriendly = $gatewaysarray[$paymentmethod];
            }
        }
        if (0 < $credit) {
            if ($total == 0) {
                $paymentmethodfriendly = \Lang::get("admin.invoicesfullypaidcredit");
            } else {
                $paymentmethodfriendly .= " + " . \Lang::get("admin.invoicespartialcredit");
            }
        }

        \App\Helpers\Hooks::run_hook("ViewInvoiceDetailsPage", array("invoiceid" => $id));

        $params = array_merge($data);
        return view('pages.billing.invoices.edit', $params);
    }

    public function InvoicesEdit(Request $request, $id)
    {
        $id = (int) $id;
        try {
            $invoice = new \App\Helpers\InvoiceClass($id);
        } catch (\Exception $e) {
            abort(404);
        }
        $data = $invoice->getOutput();

        // Debug initial credit value from invoice data
        //   dd('Initial credit from invoice data:', [
        //       'raw_credit' => $data['credit'],
        //       'credit_numeric' => $data['credit']->toNumeric(),
        //       'credit_currency' => $data['credit']->getCurrency()
        //   ]);

        // $invoice = DB::table("{$this->prefix}invoices as invoices")
        // ->join("{$this->prefix}clients as clients", "invoices.userid", "=", "clients.id")
        // ->where('invoices.id', $id)
        // ->select('invoices.*', 'clients.firstname', 'clients.lastname')
        // ->first();
        $invoice = DB::table("{$this->prefix}invoices as invoices")
            ->join("{$this->prefix}clients as clients", "invoices.userid", "=", "clients.id")
            ->leftJoin("{$this->prefix}clientgroups as groups", "clients.groupid", "=", "groups.id")
            ->where('invoices.id', $id)
            ->select('invoices.*', 'clients.firstname', 'clients.lastname', 'clients.groupid', 'groups.groupname')
            ->first();

        $client = DB::table("{$this->prefix}clients")
            ->where('id', $invoice->userid)
            ->first();
        $trans = \App\Models\Account::where('invoiceid', $invoice->id)->selectRaw('COUNT(id) as transcount')->selectRaw('SUM(amountin)-SUM(amountout) as amountpaid')->first();
        // Check transaction summary
        // dd($trans);

        $transcount = $trans->transcount;
        $amountpaid = $trans->amountpaid;

        $total = $invoice->total;
        $invoice->date =  Carbon::parse($invoice->date)->isoFormat(Cfg::get('DateFormat'));
        $invoice->duedate =  Carbon::parse($invoice->duedate)->isoFormat(Cfg::get('DateFormat'));

        // Tambahkan format untuk datepaid
        if ($invoice->datepaid) {
            $invoice->datepaid = Carbon::parse($invoice->datepaid)->format('Y-m-d H:i:s');
        }

        $invoice->totalFormat = Format::Currency((int)$invoice->total, null, ['prefix' => 'Rp ', 'format' => '3']);
        /*   if($invoice->total <=  $amountpaid){
            $balance = $invoice->total - $amountpaid ;
            $balancecek=true;
        }else{
            $balance = $amountpaid -  $invoice->total;
            $balancecek=false;
        } */
        $balance = $total - $amountpaid;
        $rawbalance =  sprintf("%01.2f", $balance);
        $balance =  sprintf("%01.2f", $balance);

        //dd($balance);
        @$invoice->balance = Format::Currency((int)$invoice->balance, null, ['prefix' => 'Rp ', 'format' => '3']);
        // /$invoice->subtotal = Format::Currency((int)$invoice->subtotal, null, ['prefix' => 'Rp ', 'format' => '3']);
        $invoice->tax = Format::Currency((int)$invoice->tax, null, ['prefix' => 'Rp ', 'format' => '3']);
        $invoice->credit = Format::Currency((int)$invoice->credit, null, ['prefix' => 'Rp ', 'format' => '3']);
        @$invoice->alltotal = Format::Currency((int)$invoice->total, null, ['prefix' => 'Rp ', 'format' => '3']);


        //get credit
        $query = \App\Models\Credit::query();
        $query->where('clientid',  $invoice->userid);
        $query->orderBy('date', 'ASC');
        $GetCredits = $query->sum('amount');

        $getCreaditData = \App\Models\Client::find($invoice->userid);
        $getCreadit = $getCreaditData->credit;

        // Tambahkan fungsi untuk mendapatkan detail relid
        $invoiceitems = \App\Models\Invoiceitem::where('invoiceid', $invoice->id)
            ->get()
            ->map(function ($item) {
                $relidDetail = $this->getRelidDetail($item->type, $item->relid);
                $item->relid_detail = $relidDetail;
                return $item;
            });

        $relidOptions = \App\Models\Invoiceitem::whereNotNull('relid')
            ->where('relid', '!=', '')
            ->where('relid', '!=', '0')
            ->where('userid', $invoice->userid) // tambahkan filter berdasarkan userid
            ->distinct()
            ->get(['type', 'relid'])
            ->map(function ($item) {
                return [
                    'id' => $item->relid,
                    'detail' => $this->getRelidDetail($item->type, $item->relid)
                ];
            })
            ->filter(function ($item) {
                return !empty($item['detail']) && $item['detail'] !== $item['id'];
            });

        //($GetCredits < 0)

        // Tambahkan ini untuk mendapatkan distinct types
        $types = \App\Models\Invoiceitem::select('type')
            ->distinct()
            ->whereNotNull('type')
            ->pluck('type');

        $gateway = \App\Helpers\Orders::filterGatewaysByRestrictionPeriod(\App\Helpers\Gateway::GetGatewaysArray());
        /* get Transaction */
        $transactionData = \App\Models\Account::where('invoiceid', $invoice->id)->get();

        $transaction = collect($transactionData)->map(function ($item) use ($gateway) {
            return [
                'id'             =>    $item->id,
                'date'          =>  $item->date,
                //'date'          => Carbon::parse($item->date)->isoFormat(Cfg::get('DateFormat').' hh:mm'),
                'amountin'     =>  Format::Currency((int)$item->amountin, null, ['prefix' => 'Rp ', 'format' => '3']),
                'fees'          =>  Format::Currency((int)$item->fees, null, ['prefix' => 'Rp ', 'format' => '3']),
                // 'gateway'       => in_array($item->gateway, array_keys($gateway)) ? $gateway[$item->gateway] : "Invalid",
                'gateway'       => $item->gateway,
                'transid'       => $item->transid,

            ];
        });
        //mailtempplate
        $template = \App\Models\Emailtemplate::where("type", "=", "invoice")->where("language", "=", "")->select('id', 'name')->get();

        $emailtplsoutput = ["Invoice Created", "Credit Card Invoice Created", "Invoice Payment Reminder", "First Invoice Overdue Notice", "Second Invoice Overdue Notice", "Third Invoice Overdue Notice", "Credit Card Payment Due", "Credit Card Payment Failed", "Invoice Payment Confirmation", "Credit Card Payment Confirmation", "Invoice Refund Confirmation"];
        $emailtplsarray = array();
        foreach ($template as $r) {
            $emailtplsarray[$r->name] = $r->id;
        }

        if ($invoice->status == 'Paid') {
            $emailtplsoutput = array_merge(["Invoice Payment Confirmation", "Credit Card Payment Confirmation"], $emailtplsoutput);
        }

        if ($invoice->status == 'Refunded') {
            $emailtplsoutput = array_merge(["Invoice Refund Confirmation"], $emailtplsoutput);
        }
        $selectTempalte = array();
        foreach ($emailtplsoutput as $tplname) {
            if (array_key_exists($tplname, $emailtplsarray)) {
                $selectTempalte[] = $tplname;
                unset($emailtplsarray[$tplname]);
            }
        }
        foreach ($emailtplsarray as $tplname => $k) {
            $selectTempalte[] = $tplname;
        }

        $tabledata = $this->getTransactions($id);
        // dd($transactionData);

        $aInt = new \App\Helpers\Admin();
        $params = [
            'data'          => $data,
            'invoice'       => $invoice,
            'gateway'       => $gateway,
            'invoiceitems'  => $invoiceitems,
            'transaction'   => $transaction,
            'conttrans'     => count($transactionData),
            'credit'        => [
                'ori' => $getCreadit,
                'format' =>  Format::Currency($GetCredits, null, ['prefix' => 'Rp ', 'format' => '3']),
            ],
            'tempalte'      =>  $selectTempalte,
            'baseURL'       => $this->adminURL,
            'balance'            =>  $balance,
            'aInt' => $aInt,
            'transactions' => $tabledata,
            'client'        => $client, // Tambahkan data client ke params
            'types'         => $types,  // Tambahkan ini
            'relidOptions' => $relidOptions,
        ];

        //dd($params['data']['credit']->toNumeric());
        //dd($invoice);
        return view('pages.billing.invoices.edit', $params);
    }

    // untuk save changes status paid, unpaid di atas invoiceitems
    public function InvoicesUpdate(Request $request, $id)
    {
        /* dd($request->all()); */
        if (isset($request->updateinvoice)) {
            if ($request->description) {
                foreach ($request->description as $lineId => $desc) {
                    $updateAmount = $request->amount[$lineId] ?? 0;
                    $updateTaxed = $request->taxed[$lineId] ?? 0;
                    $update = \App\Models\Invoiceitem::find($lineId);
                    $update->description =  $desc;
                    $update->type = $type;
                    $update->relid = $relid;
                    $update->amount =  $updateAmount;
                    $update->taxed =  $updateTaxed;
                    //dd($update);
                    $update->save();
                }
            }
            // if ($request->item) {
            //    foreach ($request->item as $k => $v) {
            //       $id = (int)$k;
            //       $description = $v['description'];
            //       $type = $v['type'] ?? null;
            //       $relid = $v['relid'] ?? null;
            //       $amount = $v['amount'];
            //       $taxed = $v['taxed'] ?? 0;
            //       $update = \App\Models\Invoiceitem::find($id);
            //       $update->description =  $description;
            //       $create->type = $request->addtype;
            //       $create->relid = $request->addrelid;
            //       $update->amount =  $amount;
            //       $update->taxed =  $taxed;
            //       $update->save();
            //    }
            // }
            if ($request->item) {
                foreach ($request->item as $k => $v) {
                    $id = (int)$k;
                    $description = $v['description'];
                    $type = $v['type'] ?? null;        // tambahkan ini dengan null check
                    $relid = $v['relid'] ?? null;      // tambahkan ini dengan null check
                    $amount = $v['amount'];
                    $taxed = $v['taxed'] ?? 0;

                    $update = \App\Models\Invoiceitem::find($id);
                    if ($update) {  // tambahkan pengecekan
                        $update->description = $description;
                        $update->type = $type;         // gunakan $type bukan $request->addtype
                        $update->relid = $relid;       // gunakan $relid bukan $request->addrelid
                        $update->amount = $amount;
                        $update->taxed = $taxed;
                        $update->save();
                    }
                }
            }



            if ($request->adddescription) {
                $create = new \App\Models\Invoiceitem();
                $create->invoiceid = (int) $request->id;
                $create->userid = (int) $request->userid;
                $create->description = $request->adddescription;
                $create->amount = $request->addamount ?? 0;
                $create->taxed = $request->addtaxed ?? 0;
                $create->save();
            }

            \App\Helpers\Invoice::UpdateInvoiceTotal((int) $request->id);

            return back()->with('success', "Successfull Add Invoice Items");
        }

        /* add credit */

        if (isset($request->optionaddcredit)) {
            //dd('hallooo',$request->all());
            global $CONFIG;
            $id = (int) $request->id;
            $addcredit = $request->addcredit;
            if ($addcredit != "0.00") {
                $invoice = \App\Models\Invoice::find($id);
                $userid     = $invoice->userid;
                $subtotal   = $invoice->subtotal;
                $credit     = $invoice->credit;
                //dd($credit);
                $total      = $invoice->total;
                //dd($total);
                $account = \App\Models\Account::where('invoiceid', $id)->selectRaw('SUM(amountin)-SUM(amountout) as amountpaid')->first();
                $amountpaid = (int) $account->amountpaid;
                $balance = $total - $amountpaid;
                if ($CONFIG["TaxType"] == "Inclusive") {
                    $subtotal = $total;
                }
                $addcredit = round($addcredit, 2);
                $balance = round($balance, 2);

                $client = \App\Models\Client::find($userid);
                $totalcredit = $client->credit;
                // dd($totalcredit);
                if ($totalcredit < $addcredit) {
                    return redirect()->back()->withErrors(["An Error Occurred You cannot apply more credit than the client's credit balance"]);
                } else {
                    if ($balance < $addcredit) {
                        return redirect()->back()->withErrors(["An Error Occurred You cannot apply more credit than the invoice total"]);
                    } else {
                        \App\Helpers\Invoice::applyCredit($id, $userid, $addcredit);
                        $currency = (new \App\Helpers\AdminFunctions())->getCurrency($userid);
                        return back()->with('success', "credit was successfully added to the invoice " . $addcredit);
                    }
                }
            }
        }
        //dd($request->all());
        /* remove cradet */
        if (isset($request->removeCredit)) {
            if ($request->removeCredit != '0.00' || $removecredit != "") {
                $id = (int) $request->id;
                $removecredit = $request->removeCredit;
                $invoice = \App\Models\Invoice::find($id);
                $credit = $invoice->credit;
                //dd($credit,$removecredit);
                $userid     = $invoice->userid;
                $subtotal   = $invoice->subtotal;
                $credit     = $invoice->credit;
                $total      = $invoice->total;
                $status     = $invoice->status;
                if ($credit < $removecredit) {
                    return redirect()->back()->withErrors(["An Error Occurred You cannot apply more credit than the invoice total"]);
                } else {
                    $getInvoice = \App\Models\Invoice::find($id);
                    $credit =  $credit - $removecredit;
                    $getInvoice->credit = $credit;
                    $getInvoice->save();

                    \App\Helpers\Invoice::UpdateInvoiceTotal($id);

                    $client = \App\Models\Client::find($userid);
                    $getcredit = $client->credit;
                    $client->credit = $getcredit + $removecredit;
                    $client->save();

                    $tblCredit = new \App\Models\Credit();
                    $tblCredit->clientid    =  $userid;
                    $tblCredit->date        =  Carbon::now()->format('Y-m-d');
                    $tblCredit->description = "Credit Removed from Invoice #" . $id;
                    $tblCredit->amount = $removecredit;

                    $tblCredit->save();

                    \App\Helpers\LogActivity::Save("Credit Removed - Amount: " . $removecredit . " - Invoice ID: " . $id, $userid);
                    if ($status == "Paid") {
                        $update = \App\Models\Invoice::find($id);
                        $update->status = 'Refunded';
                        $update->date_refunded = Carbon::now();
                        $update->save();
                    }

                    return back()->with('success', "credit was successfully removed from the invoice ");
                }
            }
        }


        /* add payment */
        // if (isset($request->addpayment)) {
        //    $error = array();
        //    $id = (int) $request->id;

        //    // Perbaikan format tanggal
        //    try {
        //       $date = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        //    } catch (\Exception $e) {
        //       return redirect()->back()
        //          ->withErrors(['Format tanggal tidak valid. Gunakan format dd/mm/yyyy'])
        //          ->withInput($request->all())
        //          ->with('tabpayment', 1);
        //    }

        //    $transactionID = $request->transid ?? "";
        //    $amount = $request->amount;
        //    $fees = $request->fees;
        //    $paymentMethod = $request->paymentmethod;
        //    $sendConfirmation = $request->sendconfirmation;

        //    // Validasi lainnya
        //    if ($amount < 0) {
        //       $error[] = 'Amount In cannot be less than zero.';
        //    }
        //    if ((!$amount || $amount == 0) && (!$fees || $fees == 0)) {
        //       $error[] = 'Amount or Fee is required.';
        //    }
        //    /* $validate =new \App\Helpers\Validate();
        //       $invalidFormatLangKey = ["transactions", "amountOrFeeInvalidFormat"];
        //       if ($amount && !$validate->validate("decimal", "amount", $invalidFormatLangKey) || $fees && !$validate->validate("decimal", "fees", $invalidFormatLangKey)) {
        //           $error[]= implode(PHP_EOL, array_unique($validate->getErrors()));
        //       } */

        //    if ($amount && $fees && $amount < $fees) {
        //       $error[] = 'The fee being entered must be less than the amount in value.';
        //    }
        //    if ($amount && $fees && $fees < 0) {
        //       $error[] = 'Fee for Amount In transaction must be a positive value.';
        //    }

        //    if (!empty($error)) {
        //       return redirect()->back()->withErrors($error)->withInput($request->all())->with('tabpayment', 1);
        //    }

        //    $sendConfirmation = ($sendConfirmation == 1) ? true : false;

        //    \App\Helpers\Invoice::AddInvoicePayment($id, $transactionID, $amount, $fees, $paymentMethod, $sendConfirmation, $date);
        //    return back()->with('success', 'Add payment has been added successfully.');
        // }

        if (isset($request->addpayment)) {
            // checkPermission("Add Transaction");
            $error = [];
            $id = (int) $request->id;
            $date = $request->date;
            // $date = Carbon::parse($request->date)->format('d-m-Y');
            // $date = $request->date ? Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d') : date('Y-m-d');
            $transactionID = $request->transid ?? "";
            $amount = $request->amount;
            $fees = $request->fees;
            $paymentMethod = $request->paymentmethod;
            $sendConfirmation = $request->sendconfirmation;

            if ($amount < 0) {
                $error[] = 'Amount In cannot be less than zero.';
            }
            if ((!$amount || $amount == 0) && (!$fees || $fees == 0)) {
                $error[] = 'Amount or Fee is required.';
            }
            /* $validate =new \App\Helpers\Validate();
            $invalidFormatLangKey = ["transactions", "amountOrFeeInvalidFormat"];
            if ($amount && !$validate->validate("decimal", "amount", $invalidFormatLangKey) || $fees && !$validate->validate("decimal", "fees", $invalidFormatLangKey)) {
                $error[]= implode(PHP_EOL, array_unique($validate->getErrors()));
            } */

            if ($amount && $fees && $amount < $fees) {
                $error[] = 'The fee being entered must be less than the amount in value.';
            }
            if ($amount && $fees && $fees < 0) {
                $error[] = 'Fee for Amount In transaction must be a positive value.';
            }

            if (!empty($error)) {
                return redirect()->back()->withErrors($error)->withInput($request->all())->with('tabpayment', 1);
            }

            $sendConfirmation = ($sendConfirmation == 1) ? true : false;

            \App\Helpers\Invoice::AddInvoicePayment($id, $transactionID, $amount, $fees, $paymentMethod, $sendConfirmation, $date);
            return back()->with('success', 'Add payment has been added successfully.');
        }



        // if (isset($request->option)) {
        //   $invoice = \App\Models\Invoice::find((int)$request->id);
        //   $oldStatus = $invoice->status;

        //    $invoice->date = $request->invoicedate ? Carbon::createFromFormat('d/m/Y', $request->invoicedate)->format('Y-m-d') : date('Y-m-d');
        //    $invoice->duedate = $request->datedue ? Carbon::createFromFormat('d/m/Y', $request->datedue)->format('Y-m-d') : date('Y-m-d');


        //    if ($request->has('status') && $request->input('status') == 'Paid') {
        //       $oldStatus = DB::table('tblinvoices')->where('id', $id)->value('status');

        //       if ($oldStatus == 'Collections') {
        //          // Ambil email BillingNotificationReceiver dari konfigurasi
        //          $billingEmail = \App\Helpers\Cfg::get("BillingNotificationReceiver");

        //          // Pisahkan email jika ada multiple (dipisahkan dengan koma)
        //          $emailAddresses = array_map('trim', explode(',', $billingEmail));

        //          if (!empty($emailAddresses)) {
        //                // Ambil data invoice
        //                $invoice = \App\Models\Invoice::find($id);

        //                // Ambil template email
        //                $template = DB::table('tblemailtemplates')
        //                   ->where('name', 'Collection to Paid Status Change')
        //                   ->first();

        //                if ($template) {
        //                   // Siapkan data untuk merge fields
        //                   $mergeFields = [
        //                      '{$invoice_id}' => $id,
        //                      '{$paid_date}' => Carbon::now()->format('Y-m-d'),
        //                      '{$collection_date}' => Carbon::parse($invoice->date)->format('Y-m-d'),
        //                      '{$reason}' => 'SSL (Automated By System)'
        //                   ];

        //                   // Replace merge fields dalam template
        //                   $emailContent = str_replace(
        //                      array_keys($mergeFields),
        //                      array_values($mergeFields),
        //                      $template->message
        //                   );

        //                   // Replace merge fields dalam subject
        //                   $emailSubject = str_replace(
        //                      array_keys($mergeFields),
        //                      array_values($mergeFields),
        //                      $template->subject
        //                   );

        //                   // Kirim email ke setiap alamat
        //                   foreach ($emailAddresses as $email) {
        //                      Mail::send([], [], function($mail) use ($email, $emailSubject, $emailContent) {
        //                            $mail->to($email)
        //                               ->subject($emailSubject)
        //                               ->setBody($emailContent, 'text/html');
        //                      });
        //                   }
        //                }
        //          }
        //       }
        //    }

        //    // Logika untuk mengatur datepaid berdasarkan status
        //   if ($request->status === 'Paid' && $oldStatus !== 'Paid') {
        //       $invoice->datepaid = Carbon::now()->format('Y-m-d H:i:s');
        //   } elseif ($request->status !== 'Paid' && $oldStatus === 'Paid') {
        //       $invoice->datepaid = null;
        //   } elseif ($request->filled('datepaid') && $request->filled('datepaid_time')) {
        //       try {
        //           $datepaidStr = $request->datepaid . ' ' . $request->datepaid_time;
        //           $datepaid = Carbon::createFromFormat('d/m/Y H:i', $datepaidStr);
        //           $invoice->datepaid = $datepaid->format('Y-m-d H:i:s');
        //       } catch (\Exception $e) {
        //           return redirect()->back()
        //               ->withInput()
        //               ->with('error', 'Invalid date paid format');
        //       }
        //   }

        //    // Update datepaid dengan jam
        //    // if ($request->filled('datepaid') && $request->filled('datepaid_time')) {
        //    //    try {
        //    //          // Gabungkan tanggal dan jam
        //    //          $datepaidStr = $request->datepaid . ' ' . $request->datepaid_time;
        //    //          $datepaid = Carbon::createFromFormat('d/m/Y H:i', $datepaidStr);
        //    //          $invoice->datepaid = $datepaid->format('Y-m-d H:i:s');
        //    //    } catch (\Exception $e) {
        //    //          return redirect()->back()
        //    //             ->withInput()
        //    //             ->with('error', 'Invalid date paid format');
        //    //    }
        //    // }


        //    $invoice->invoicenum = $request->invoicenum ?? '';
        //    $invoice->taxrate = $request->taxrate;
        //    $invoice->taxrate2 = $request->taxrate2;
        //    $invoice->status = $request->status;
        //    $oldpaymentmethod = $invoice->paymentmethod;
        //    if ($oldpaymentmethod !=  $request->paymentmethod) {
        //       $invoice->paymethodid = 'mailin';
        //    }
        //    $invoice->paymentmethod = $request->paymentmethod;
        //    //dd($invoice);
        //    $invoice->save();

        //    \App\Helpers\Invoice::UpdateInvoiceTotal((int) $request->id);
        //    if ($oldpaymentmethod != $request->paymentmethod) {
        //       \App\Helpers\Hooks::run_hook("InvoiceChangeGateway", ['invoiceid' => (int) $request->id, "paymentmethod" => $request->paymentmethod]);
        //    }
        //    LogActivity::Save("Modified Invoice Options - Invoice ID: " . $request->id, $request->userid);
        //    return back()->with('success', 'Update Invoice has been added successfully.');
        // }

        if (isset($request->option)) {
            $invoice = \App\Models\Invoice::find((int)$request->id);
            $oldStatus = $invoice->status;

            $invoice->date = $request->invoicedate
                ? Carbon::createFromFormat('d/m/Y', $request->invoicedate)->format('Y-m-d')
                : date('Y-m-d');
            $invoice->duedate = $request->datedue
                ? Carbon::createFromFormat('d/m/Y', $request->datedue)->format('Y-m-d')
                : date('Y-m-d');

            if ($request->has('status')) {
                $newStatus = $request->input('status');

                if ($newStatus == 'Paid') {
                    $oldStatus = DB::table('tblinvoices')->where('id', $invoice->id)->value('status');

                    if ($oldStatus == 'Collections') {
                        $billingEmail = \App\Helpers\Cfg::get("BillingNotificationReceiver");
                        $emailAddresses = array_map('trim', explode(',', $billingEmail));

                        if (!empty($emailAddresses)) {
                            $template = DB::table('tblemailtemplates')
                                ->where('name', 'Collection to Paid Status Change')
                                ->first();

                            if ($template) {
                                $mergeFields = [
                                    '{$invoice_id}' => $invoice->id,
                                    '{$paid_date}' => Carbon::now()->format('Y-m-d'),
                                    '{$collection_date}' => Carbon::parse($invoice->date)->format('Y-m-d'),
                                    '{$reason}' => 'SSL (Automated By System)',
                                ];

                                $emailContent = str_replace(array_keys($mergeFields), array_values($mergeFields), $template->message);
                                $emailSubject = str_replace(array_keys($mergeFields), array_values($mergeFields), $template->subject);

                                foreach ($emailAddresses as $email) {
                                    Mail::send([], [], function ($mail) use ($email, $emailSubject, $emailContent) {
                                        $mail->to($email)
                                            ->subject($emailSubject)
                                            ->setBody($emailContent, 'text/html');
                                    });
                                }
                            }
                        }
                    }
                }

                // Update `datepaid` berdasarkan status
                if ($newStatus === 'Paid' && $oldStatus !== 'Paid') {
                    $invoice->datepaid = Carbon::now()->format('Y-m-d H:i:s');
                } elseif ($newStatus !== 'Paid' && $oldStatus === 'Paid') {
                    $invoice->datepaid = null;
                } elseif ($request->filled('datepaid') && $request->filled('datepaid_time')) {
                    try {
                        $datepaidStr = $request->datepaid . ' ' . $request->datepaid_time;
                        $datepaid = Carbon::createFromFormat('d/m/Y H:i', $datepaidStr);
                        $invoice->datepaid = $datepaid->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Invalid date paid format');
                    }
                }

                $invoice->status = $newStatus;
            }

            $invoice->invoicenum = $request->invoicenum ?? '';
            $invoice->taxrate = $request->taxrate;
            $invoice->taxrate2 = $request->taxrate2;

            $oldPaymentMethod = $invoice->paymentmethod;
            if ($oldPaymentMethod != $request->paymentmethod) {
                $invoice->paymethodid = 'mailin';
            }
            $invoice->paymentmethod = $request->paymentmethod;

            $invoice->save();

            \App\Helpers\Invoice::UpdateInvoiceTotal((int) $request->id);
            if ($oldPaymentMethod != $request->paymentmethod) {
                \App\Helpers\Hooks::run_hook("InvoiceChangeGateway", ['invoiceid' => (int) $request->id, "paymentmethod" => $request->paymentmethod]);
            }

            LogActivity::Save("Modified Invoice Options - Invoice ID: " . $request->id, $request->userid);
            return back()->with('success', 'Update Invoice has been added successfully.');
        }

        //dd($request->all());
        if (isset($request->refund)) {
            //return back()->with('success', 'Refund  Invoice successfully.');
            $error = array();
            if ($request->transid == null) {
                $error[] = 'Select transtion ';
            }

            if (!empty($error)) {
                return redirect()->back()->withErrors($error)->withInput($request->all());
            }
            //dd($request->all());
            $transid = $request->transid ?? '';
            LogActivity::Save("Admin Initiated Refund - Invoice ID: " . $request->id . " - Transaction ID: " . $transid);
            $amount = $request->amount ?? 0;
            $sendemail = $request->sendemail;
            $refundtransid = $request->refundtransid ?? '';
            $refundtype = $request->refundtype;
            $reverse = (bool)(int)$request->refundtype ?? 0;
            $sendtogateway = $addascredit = false;
            if ($refundtype == "sendtogateway") {
                $sendtogateway = true;
            } else {
                if ($refundtype == "addascredit") {
                    $addascredit = true;
                }
            }
            //dd($amount);
            $result = \App\Helpers\Invoice::refundInvoicePayment($transid, $amount, $sendtogateway, $addascredit, $sendemail, $refundtransid, $reverse);
            //dd($result);
            return back()->with('success', 'Refund ' . $result . ' Invoice successfully.');
        }

        if (isset($request->MarkCancelled)) {
            $id = (int)$request->id;
            $invoice = \App\Models\Invoice::find($id);
            $invoice->status = 'Cancelled';
            $invoice->datepaid = '0000-00-00 00:00:00';
            $invoice->date_cancelled = '0000-00-00 00:00:00';
            $invoice->date_refunded = '0000-00-00 00:00:00';
            $invoice->save();
            LogActivity::Save("Cancelled Invoice - Invoice ID: " . $request->id, $request->userid);
            \App\Helpers\Hooks::run_hook("InvoiceCancelled", ['invoiceid' => (int) $request->id]);
            return back()->with('success', 'Cancelled Invoice successfully.');
        }

        if (isset($request->MarkUnpaid)) {
            $id = (int)$request->id;
            $invoice = \App\Models\Invoice::find($id);
            $invoice->status = 'Unpaid';
            $invoice->datepaid = '0000-00-00 00:00:00';
            $invoice->date_cancelled = '0000-00-00 00:00:00';
            $invoice->date_refunded = '0000-00-00 00:00:00';
            $invoice->save();
            LogActivity::Save("Reactivated Invoice - Invoice ID: " . $request->id, $request->userid);
            \App\Helpers\Hooks::run_hook("InvoiceUnpaid", ['invoiceid' => (int) $request->id]);
            return back()->with('success', 'Reactivated Invoice successfully.');
        }

        if (isset($request->zeroPaid)) {
            $id = (int)$request->id;
            $invoice = \App\Models\Invoice::find($id);
            if ($invoice->status == 'Unpaid') {
                \App\Helpers\Invoice::processPaidInvoice($id, true);
            }
            return back()->with('success', 'Mark Paid Invoice successfully.');
        }

        if (isset($request->addnotes)) {
            $id = (int)$request->id;
            $invoice = \App\Models\Invoice::find($id);
            $invoice->notes = $request->notes;
            $invoice->save();
            return back()->with('success', 'Save Notes Invoice successfully.');
        }

        if (isset($request->SendEmail)) {
            $id = (int)$request->id;
            $result = \App\Helpers\Functions::sendMessage($request->invoice_stats, $id, "", true);
            // return back()->with('success', 'Send Email ' . $request->invoice_stats . '  successfully.');
            return back()->with('success', $result);
        }


        if (isset($request->publish)) {

            //dd(Auth::guard('admin')->user()->id);
            //check_token("WHMCS.admin.default");
            // /Draft
            $id = (int)$request->id;
            $userid = (int)$request->userid;
            $invoice = \App\Models\Invoice::find($id);
            $invoice->status    = "Unpaid";
            $invoice->date      = Carbon::now();
            $invoice->save();
            $adminID = Auth::guard('admin')->user()->id;
            $invoiceArr = array("source" => "adminarea", "user" =>  $adminID ? $adminID : "system", "invoiceid" => $id, "status" => "Unpaid");
            LogActivity::Save("Modified Invoice Options - Invoice ID:" . $id, $userid);
            \App\Helpers\Hooks::run_hook('InvoiceCreationPreEmail', $invoiceArr);
            if ($request->publish != 'publish') {
                \App\Helpers\Hooks::run_hook('InvoiceCreationPreEmail', $invoiceArr);
                $emailName = "Invoice Created";
                $paymentMethod = \App\Helpers\Gateway::getClientsPaymentMethod($userid);
                //dd($paymentMethod);
                $paymentType = DB::table($this->prefix . "paymentgateways")->where("setting", "type")->where("gateway", $paymentMethod)->value("value");
                if ($paymentType == 'CC') {
                    $emailName = "Credit Card Invoice Created";
                }
                \App\Helpers\Functions::sendMessage($emailName, $id);
            }
            return back()->with('success', 'successfully invoice publish');
        }



        //dd($request->all());
        return back()->with('success', '');
    }

    // Fungsi untuk mendapatkan relid details berdasarkan type
    private function getRelidDetail($type, $relid)
    {
        if (empty($type) || empty($relid)) {
            return '';
        }

        switch (strtolower($type)) {
            case 'hosting':
                $hosting = DB::table('tblhosting')
                    ->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')
                    ->where('tblhosting.id', $relid)
                    ->select('tblhosting.*', 'tblproducts.name as package_name')
                    ->first();

                if ($hosting) {
                    return "Hosting - {$hosting->package_name} - {$hosting->domain}";
                }
                break;

            case 'domain':
                $domain = DB::table('tbldomains')
                    ->where('id', $relid)
                    ->first();

                if ($domain) {
                    return "Domain - {$domain->domain}";
                }
                break;
        }

        return $relid;
    }

    public function deletetrans(Request $request)
    {
        $ide = $request->input('ide');
        \App\Helpers\AdminFunctions::checkPermission("Delete Transaction");
        $transaction = \App\Models\Account::findOrFail($ide);
        $userId = $transaction->clientId;
        $transaction->delete();
        \App\Helpers\LogActivity::Save("Deleted Transaction - Transaction ID: " . $ide, $userId);
        // redir("action=edit&id=" . $id);
        return redirect()->back();
    }

    public function deleteTransaction($id)
    {
        try {
            $transaction = \App\Models\Account::findOrFail($id);
            $transaction->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function getTransactions($id)
    {
        $tabledata = [];
        $paymentGateways = new \App\Helpers\Gateways();
        $transactions = array();
        $paymentTransactions = \App\Models\Account::where("invoiceid", "=", (int) $id)->orderBy("date")->orderBy("id")->get();
        foreach ($paymentTransactions as $transaction) {
            $paymentmethod = "";
            if ($transaction->paymentGateway) {
                $paymentmethod = $paymentGateways->getDisplayName($transaction->paymentGateway);
            }
            if (!$paymentmethod) {
                $paymentmethod = "-";
            }
            $transactions[(string) $transaction->date][] = array(
                (new \App\Helpers\Functions)->fromMySQLDate($transaction->date, 1),
                $paymentmethod,
                $transaction->transactionId,
                \App\Helpers\Format::formatCurrency($transaction->amountin - $transaction->amountout),
                \App\Helpers\Format::formatCurrency($transaction->fees),
                "<a href=\"#\" onClick=\"doDeleteTransaction('" . $transaction->id . "');return false\"><img src=\"" . \Theme::asset('img/delete.gif') . "\" width=\"16\" height=\"16\" border=\"0\" alt=\"Delete\"></a>"
            );
        }
        $creditTransactions = DB::table("tblcredit")->where("description", "LIKE", "%Invoice #" . (int) $id)->get();
        foreach ($creditTransactions as $transaction) {
            if (0 < $transaction->amount) {
                if (strpos($transaction->description, "Overpayment") !== false || strpos($transaction->description, "Mass Invoice Payment Credit") !== false) {
                    continue;
                }
                $creditMsg = \Lang::get("admin.invoicescreditRemoved");
            } else {
                $creditMsg = \Lang::get("admin.invoicescreditApplied");
            }
            $transactions[$transaction->date . " 25:59:59"][] = array(
                (new \App\Helpers\Functions)->fromMySQLDate($transaction->date),
                $creditMsg,
                "-",
                \App\Helpers\Format::formatCurrency($transaction->amount * -1),
                "-",
                ""
            );
        }
        ksort($transactions);
        foreach ($transactions as $date => $trans) {
            foreach ($trans as $transaction) {
                $tabledata[] = $transaction;
            }
        }

        return $tabledata;
    }

    public function InvoicesView(Request $request, $id)
    {

        /*         if (isset($_SESSION["adminid"]) && $request->get("view_as_client")) {
            $userId = WHMCS\Invoice::getUserIdByInvoiceId($invoiceid);
            if ($userId) {
                $existingLanguage = getUsersLang($userId);
            }
        }
        initialiseClientArea($whmcs->get_lang("invoicestitle") . $invoiceid, "", "", "", $breadcrumbnav);
        if (!isset($_SESSION["uid"]) && !isset($_SESSION["adminid"])) {
            $goto = "viewinvoice";
            require "login.php";
            exit;
        }
         */
        //phpinfo(); exit();

        //$PDF->loadHTML('<h1>Test</h1>');
        //return $PDF->stream();
        $invoiceId = (int)$id;
        $invoice = new \App\Helpers\InvoiceClass($invoiceId);
        $sysurl = config('app.url');
        $data = $invoice->getOutput();

        $client = \App\Models\Client::where('id', $data['userid'])->select('firstname', 'lastname', 'companyname', 'address1', 'city', 'state', 'postcode')->first();

        $invoiceitems = $invoice->getLineItems();

        $invoiceitems = collect($invoiceitems)->map(function ($item) {
            // dd($item);
            return [
                'id'             =>    $item['id'],
                'description'    =>    $item['description'],
                'type'           =>    $item['type'],
                'relid'          =>    $item['relid'],
                'rawamount'      =>    Format::Currency($item['amount']->toNumeric(), null, ['prefix' => $item['amount']->getCurrency()['prefix'] . ' ', 'format' => $item['amount']->getCurrency()['format']]),
                'taxed'          =>    $item['taxed'] ?? '',
                'taxamount'      =>    $item['taxamount'] ?? '',
                'taxrate'        =>    $item['taxrate'] ?? '',
            ];
        });

        //dd($invoiceitems);

        // $gateway=$invoice->initialiseGatewayAndParams();
        //TODO
        //Gateway Module 'banktransfer' is Missing or Invalid
        //dd($gateway);
        $transactions = $invoice->getTransactions();
        $getBalance = $invoice->getData('balance');
        //   dd((double)$getBalance);
        $balance = new \App\Helpers\FormatterPrice($getBalance, 1);
        //   dd($balance);
        $invoiceexists = true;
        try {
            $invoice->setID($invoiceId);
        } catch (Exception $e) {
            $invoiceexists = false;
        }
        //$data = $invoice->getOutput();
        //TODO
        //$allowedaccess = isset($_SESSION["adminid"]) ? checkPermission("Manage Invoice", true) : $invoice->isAllowed();

        $param = array();
        $param['error'] = '';
        $allowedaccess = true;
        $error = false;
        if (!$invoiceexists || !$allowedaccess) {
            $param['error'] = 'invalid invoice';
        }
        //dd($data['clientpreviousbalance']->getCurrency()['prefix']);
        //   $data['clienttotaldue'] = Format::Currency($data['clienttotaldue']->toNumeric(), null, ['prefix' => $data['clienttotaldue']->getCurrency()['prefix'] . ' ', 'format' => $data['clienttotaldue']->getCurrency()['format']]);
        //   $data['clientpreviousbalance'] = Format::Currency($data['clientpreviousbalance']->toNumeric(), null, ['prefix' => $data['clientpreviousbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientpreviousbalance']->getCurrency()['format']]);
        //   $data['clientbalanceduefomat'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
        //   $data['clientbalancedue'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
        $data['clientdepositbalance'] = Format::Currency($data['clientdepositbalance']->toNumeric(), null, ['prefix' => $data['clientdepositbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientdepositbalance']->getCurrency()['format']]);
        $data['clienttotaldue'] = Format::Currency($data['clienttotaldue']->toNumeric(), null, ['prefix' => $data['clienttotaldue']->getCurrency()['prefix'] . ' ', 'format' => $data['clienttotaldue']->getCurrency()['format']]);
        $data['clientpreviousbalance'] = Format::Currency($data['clientpreviousbalance']->toNumeric(), null, ['prefix' => $data['clientpreviousbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientpreviousbalance']->getCurrency()['format']]);
        $data['clientbalanceduefomat'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
        $data['clientbalancedue'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
        $data['credit'] = Format::Currency($data['credit']->toNumeric(), null, ['prefix' => $data['credit']->getCurrency()['prefix'] . ' ', 'format' => $data['credit']->getCurrency()['format']]);
        $data['total'] = Format::Currency($data['total']->toNumeric(), null, ['prefix' => $data['total']->getCurrency()['prefix'] . ' ', 'format' => $data['total']->getCurrency()['format']]);
        $param['invoice'] = $data;
        $param['item'] = $invoiceitems;
        $param['baseURL'] = $sysurl;
        $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
        $param['CompanyName'] = \App\Helpers\Cfg::get('CompanyName');
        $param['allowchangegateway'] = \App\Helpers\Cfg::get('AllowCustomerChangeInvoiceGateway');
        $param['gateway'] = \App\Helpers\Orders::filterGatewaysByRestrictionPeriod(\App\Helpers\Gateway::GetGatewaysArray());
        $param['client'] = $client;
        $paymentbutton = $invoice->getData("status") == "Unpaid" && 0 < $invoice->getData("balance") ? $invoice->getPaymentLink() : "";
        $param["paymentbutton"] = $paymentbutton;
        $param['print'] = false;
        if (isset($request->print)) {
            $param['print'] = true;
        }
        $param['transactions'] = $transactions;

        $data = $invoice->getOutput();
        $paymentmethod = $data['paymentmethod'] ?? null;
        $param['paymentmethod'] = $paymentmethod;

        //dd($param);
        //dd($data);
        // $invoice->pdfCreate();
        // $invoice->pdfInvoicePage();
        //$invoice->pdfOutput();
        // dd($data);
        //$pdf = App::make('dompdf.wrapper');
        //$pdf =PDF::loadview('invoice.index',['companyname' =>  'wojeowjekwjekw']);
        //return $pdf->download('laporan-pegawai-pdf.pdf');
        return view('invoice.invoice', $param);
    }

    // public function InvoicesDownload($id)
    // {
    //    $invoiceId = (int)$id;
    //    $invoice = new \App\Helpers\InvoiceClass($invoiceId);
    //    $sysurl = config('app.url');
    //    $data = $invoice->getOutput();
    //    $client = \App\Models\Client::where('id', $data['userid'])->select('firstname', 'lastname', 'companyname', 'address1', 'city', 'state', 'postcode')->first();
    //    $invoiceitems = $invoice->getLineItems();
    //    $getTransactions = $invoice->getTransactions();
    //    //dd($getTransactions);
    //    $invoiceitems = collect($invoiceitems)->map(function ($item) {
    //       // dd($item);
    //       return [
    //          'id'             =>    $item['id'],
    //          'description'    =>    $item['description'],
    //          'type'           =>    $item['type'],
    //          'relid'          =>    $item['relid'],
    //          'rawamount'      =>    Format::Currency($item['amount']->toNumeric(), null, ['prefix' => $item['amount']->getCurrency()['prefix'] . ' ', 'format' => $item['amount']->getCurrency()['format']]),

    //       ];
    //    });

    //    //TODO
    //    //Gateway Module 'banktransfer' is Missing or Invalid
    //    //dd($gateway);
    //    $invoiceexists = true;
    //    try {
    //       $invoice->setID($invoiceId);
    //    } catch (Exception $e) {
    //       $invoiceexists = false;
    //    }
    //    $param = array();
    //    $param['error'] = '';
    //    $allowedaccess = true;
    //    $error = false;
    //    if (!$invoiceexists || !$allowedaccess) {
    //       $param['error'] = 'invalid invoice';
    //    }
    //    //dd($data['clientpreviousbalance']->getCurrency()['prefix']);
    //    $data['clienttotaldue'] = Format::Currency($data['clienttotaldue']->toNumeric(), null, ['prefix' => $data['clienttotaldue']->getCurrency()['prefix'] . ' ', 'format' => $data['clienttotaldue']->getCurrency()['format']]);
    //    $data['clientpreviousbalance'] = Format::Currency($data['clientpreviousbalance']->toNumeric(), null, ['prefix' => $data['clientpreviousbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientpreviousbalance']->getCurrency()['format']]);
    //    $data['clientbalanceduefomat'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
    //    $data['clientbalancedue'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
    //    $param['invoice'] = $data;
    //    $param['item'] = $invoiceitems;
    //    $param['baseURL'] = $sysurl;
    //    $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
    //    $param['CompanyName'] = \App\Helpers\Cfg::get('CompanyName');
    //    $param['allowchangegateway'] = \App\Helpers\Cfg::get('AllowCustomerChangeInvoiceGateway');
    //    $param['gateway'] = \App\Helpers\Gateway::GetGatewaysArray();
    //    $param['client'] = $client;
    //    $param['transactions'] = $getTransactions;
    //    $pdf = PDF::loadview('invoice.index', $param);
    //    return $pdf->download('invoice_' . time() . '.pdf');
    //    //return view('invoice.index',$param);
    // }

    public function InvoicesDownload($id)
    {
        $invoiceId = (int)$id;
        $invoice = new \App\Helpers\InvoiceClass($invoiceId);
        $sysurl = config('app.url');
        $data = $invoice->getOutput();
        $client = \App\Models\Client::where('id', $data['userid'])->select('firstname', 'lastname', 'companyname', 'address1', 'city', 'state', 'postcode')->first();
        $invoiceitems = $invoice->getLineItems();
        $getTransactions = $invoice->getTransactions();

        // Fetch VA numbers from fixedva table
        $vaNumbers = \DB::table('fixedva')
            ->where('clientid', $data['userid'])
            ->first();

        // Tambahkan logging untuk VA
        if (stripos($data['paymentmethod'], 'va') !== false) {
            \Log::info('VA Numbers for client ' . $data['userid'] . ':', [
                'paymentmethod' => $data['paymentmethod'],
                'vaNumbers' => $vaNumbers,
                'vaFields' => [
                    'atmbersamava' => $vaNumbers->atmbersamava ?? 'null',
                    'biiva' => $vaNumbers->biiva ?? 'null',
                    'permatabankva' => $vaNumbers->permatabankva ?? 'null',
                    'briva' => $vaNumbers->briva ?? 'null',
                    'cimbva' => $vaNumbers->cimbva ?? 'null',
                    'danamonva' => $vaNumbers->danamonva ?? 'null',
                    'bniva' => $vaNumbers->bniva ?? 'null',
                    'mandiriva' => $vaNumbers->mandiriva ?? 'null',
                    'bcava' => $vaNumbers->bcava ?? 'null'
                ]
            ]);
        }

        //dd($getTransactions);
        $invoiceitems = collect($invoiceitems)->map(function ($item) {
            // dd($item);
            return [
                'id'             =>    $item['id'],
                'description'    =>    $item['description'],
                'type'           =>    $item['type'],
                'relid'          =>    $item['relid'],
                'rawamount'      =>    Format::Currency($item['amount']->toNumeric(), null, ['prefix' => $item['amount']->getCurrency()['prefix'] . ' ', 'format' => $item['amount']->getCurrency()['format']]),

            ];
        });

        //TODO
        //Gateway Module 'banktransfer' is Missing or Invalid
        //dd($gateway);
        $invoiceexists = true;
        try {
            $invoice->setID($invoiceId);
        } catch (Exception $e) {
            $invoiceexists = false;
        }
        $param = array();
        $param['error'] = '';
        $allowedaccess = true;
        $error = false;
        if (!$invoiceexists || !$allowedaccess) {
            $param['error'] = 'invalid invoice';
        }
        //dd($data['clientpreviousbalance']->getCurrency()['prefix']);
        $data['clienttotaldue'] = Format::Currency($data['clienttotaldue']->toNumeric(), null, ['prefix' => $data['clienttotaldue']->getCurrency()['prefix'] . ' ', 'format' => $data['clienttotaldue']->getCurrency()['format']]);
        $data['clientpreviousbalance'] = Format::Currency($data['clientpreviousbalance']->toNumeric(), null, ['prefix' => $data['clientpreviousbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientpreviousbalance']->getCurrency()['format']]);
        $data['clientbalanceduefomat'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
        $data['clientbalancedue'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);

        $param['invoice'] = $data;
        $param['item'] = $invoiceitems;
        $param['baseURL'] = $sysurl;
        $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
        $param['CompanyName'] = \App\Helpers\Cfg::get('CompanyName');
        $param['allowchangegateway'] = \App\Helpers\Cfg::get('AllowCustomerChangeInvoiceGateway');
        $param['gateway'] = \App\Helpers\Orders::filterGatewaysByRestrictionPeriod(\App\Helpers\Gateway::GetGatewaysArray());
        $param['client'] = $client;
        $param['transactions'] = $getTransactions;
        // Tambahkan baris ini
        $paymentmethod = $data['paymentmethod'] ?? null;
        \Log::info('Payment method di download : ' . $paymentmethod);
        $param['paymentmethod'] = $paymentmethod;
        $param['vaNumbers'] = $vaNumbers; // Add VA numbers to parameters
        $pdf = PDF::loadview('invoice.index', $param);
        return $pdf->download('invoice_' . time() . '.pdf');
        //return view('invoice.index',$param);
    }

    public function deleteItemOnInvoice(Request $request)
    {
        $itemId = $request->iid;
        $invoiceItems = Invoiceitem::findOrFail($itemId);
        $invoiceItems->delete();
        \App\Helpers\Invoice::UpdateInvoiceTotal((int) $request->invoiceId);

        return ResponseAPI::Success([
            'message' => 'success',
            'text' => 'Item sucessfully deleted!'
        ]);
    }
}
