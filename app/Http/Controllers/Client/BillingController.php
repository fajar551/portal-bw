<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Helpers\Cfg;
use App\Helpers\Format;
use App\Helpers\Invoice as HelpersInvoice;
use App\Helpers\ResponseAPI;
use App\Helpers\InvoiceClass;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Auth;
use API;
use App\Models\Paymentgateway;
use App\Models\Tax;
use PDF;


class BillingController extends Controller
{
   public function Billing_MyInvoices()
   {
      $auth = Auth::user();
      $userid = $auth->id;

      $getInvoice = Invoice::where("userid", $userid)->orderBy("id", "desc")->get();

      return view('pages.billing.myinvoices.index', ['getInvoice' => $getInvoice]);
   }
   public function dt_myInvoices()
   {
      $auth = Auth::user();
      $userid = $auth->id;

      $getInvoice = Invoice::where("userid", $userid)->orderBy("id", "DESC")->get();
      return datatables()->of($getInvoice)->editColumn('invoicenum', function ($row) {
         return "#" . $row->id;
      })
         ->editColumn('date', function ($row) {
            $invoiceDate = $row->date;
            return date('Y-m-d', strtotime($invoiceDate));
         })
         ->editColumn('duedate', function ($row) {
            $dueDate = $row->duedate;
            return date('Y-m-d', strtotime($dueDate));
         })
         ->editColumn('total', function ($row) use ($userid) {
            $currencyId = session("currency");
            $currency = \App\Helpers\Format::getCurrency($userid, $currencyId);
            $resTotal = new \App\Helpers\FormatterPrice($row->total, $currency);
            return $resTotal;
         })
         ->editColumn('status', function ($row) {
            $status = $row->status;
            switch ($status) {
               case 'Paid':
                  return "<span class=\"badge badge-success\">{$status}</span>";
                  break;
               case 'Unpaid':
                  return "<span class=\"badge badge-danger\">{$status}</span>";
                  break;
               case 'Cancelled':
                  return  "<span class=\"badge badge-info\">{$status}</span>";
                  break;
               case 'Draft':
                  return  "<span class=\"badge badge-secondary\">{$status}</span>";
                  break;
               default:
                  return "<span class=\"badge badge-dark\">Unknown</span>";
                  break;
            }
         })
         ->editColumn('actions', function ($row) {
            $viewInvoicePDF = route('pages.services.mydomains.viewinvoice', $row->id);
            $webInvoicePDF = route('pages.services.mydomains.viewinvoiceweb', $row->id);
            $action = '';
            // $action .= "<a href=\"{$viewInvoiceRoute}\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs btn-success p-1 \" data-id=\"\" title=\"Details\" target=\"_blank\">Details</a>";
            $action .= "<div class=\"btn-group\">
                <button type=\"button\" class=\"btn btn-outline-success dropdown-toggle\" data-toggle=\"dropdown\" data-display=\"static\" aria-haspopup=\"true\" aria-expanded=\"false\">
                  Details <i class=\"fas fa-caret-down ml-2\"></i>
                </button>
                <div class=\"dropdown-menu dropdown-menu-lg-right\">
                <a href=\"{$webInvoicePDF}\" target=\"_blank\">
                  <button class=\"dropdown-item\" type=\"button\">View Invoice Details</button>
                </a>
                <a href=\"{$viewInvoicePDF}\" target=\"_blank\">
                  <button class=\"dropdown-item\" type=\"button\">PDF Invoice</button>
                </a>
                </div>
              </div>";
            return $action;
         })
         ->rawColumns(['status', 'actions'])
         ->addIndexColumn()
         ->toJson();
   }
   public function Billing_ViewInvoice($id)
   {
      $invoice = new InvoiceClass($id);
      $url = config('app.url');
      $data = $invoice->getOutput();
      $client = Client::where('id', $data['userid'])->select('id', 'firstname', 'lastname', 'companyname', 'address1', 'city', 'state', 'postcode', 'credit')->first();
      $invoiceItems = $invoice->getLineItems();
      $getTransactions = $invoice->getTransactions();
      $invoiceItems = collect($invoiceItems)->map(function ($item) {
         return [
            'id' => $item['id'],
            'description' => $item['description'],
            'type' => $item['type'],
            'relid' => $item['relid'],
            'rawamount' => Format::Currency($item['amount']->toNumeric(), null, ['prefix' => $item['amount']->getCurrency()['prefix'] . ' ', 'format' => $item['amount']->getCurrency()['format']]),

         ];
      });
      $invoiceexists = true;
      try {
         $invoice->setID($id);
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

      $data['clientdepositbalance'] = Format::Currency($data['clientdepositbalance']->toNumeric(), null, ['prefix' => $data['clientdepositbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientdepositbalance']->getCurrency()['format']]);
      $data['clienttotaldue'] = Format::Currency($data['clienttotaldue']->toNumeric(), null, ['prefix' => $data['clienttotaldue']->getCurrency()['prefix'] . ' ', 'format' => $data['clienttotaldue']->getCurrency()['format']]);
      $data['clientpreviousbalance'] = Format::Currency($data['clientpreviousbalance']->toNumeric(), null, ['prefix' => $data['clientpreviousbalance']->getCurrency()['prefix'] . ' ', 'format' => $data['clientpreviousbalance']->getCurrency()['format']]);
      $data['clientbalanceduefomat'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
      $data['clientbalancedue'] = Format::Currency($data['clientbalancedue']->toNumeric(), null, ['prefix' => $data['clientbalancedue']->getCurrency()['prefix'] . ' ', 'format' => $data['clientbalancedue']->getCurrency()['format']]);
      $data['credit'] = Format::Currency($data['credit']->toNumeric(), null, ['prefix' => $data['credit']->getCurrency()['prefix'] . ' ', 'format' => $data['credit']->getCurrency()['format']]);
      $data['total'] = Format::Currency($data['total']->toNumeric(), null, ['prefix' => $data['total']->getCurrency()['prefix'] . ' ', 'format' => $data['total']->getCurrency()['format']]);

      $param['invoice'] = $data;
      $param['item'] = $invoiceItems;
      $param['baseURL'] = $url;
      $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
      // $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
      // $img = file_get_contents($param['logo'] ?? "");
      // $encodedLogo = base64_encode($img);
      // $param['logo'] = $encodedLogo ?? "";
      $param['CompanyName'] = \App\Helpers\Cfg::get('CompanyName');
      $param['allowchangegateway'] = \App\Helpers\Cfg::get('AllowCustomerChangeInvoiceGateway');
      $param['payto'] = \App\Helpers\Cfg::get('InvoicePayTo');
      $param['gateway'] = \App\Helpers\Gateway::GetGatewaysArray();
      $param['client'] = $client;
      $param['transactions'] = $getTransactions;

      // try {
      //    // $param['logo'] = \App\Helpers\Cfg::get('LogoURL');
      //    // $img = file_get_contents($param['logo'] ?? "");
      //    // $encodedLogo = base64_encode($img);
      //    // $param['logo'] = $encodedLogo ?? "";
      // } catch (\Throwable $th) {
      //    $param['logo'] = '';
      // }
      $pdf = PDF::loadView('clientinvoices.index', $param);
      // $pdf->setOptions([
      //     'enable_remote' => true,
      //     'chroot' => public_path('themes\qwords\one\public\assets\images\WHMCEPS-dark.png')
      // ]);
      return $pdf->stream('invoice_' . time() . '.pdf');
   }
   public function Billing_ViewInvoiceWeb_existing(Request $request)
   {
      $id = $request->input('id');
      return redirect()->route("pages.services.mydomains.viewinvoiceweb", $id);
   }
   public function Billing_ViewInvoiceWeb(Request $request, $reqid)
   {
      // Hapus session cart_summary
      session()->forget('cart_summary');
      $id = $invoiceid = $request->input("id") ?? $reqid;
      $breadcrumbnav = "";
      $existingLanguage = NULL;

      \App\Helpers\ClientareaFunctions::initialiseClientArea(\App\Helpers\Cfg::get("invoicestitle") . $invoiceid, "", "", "", $breadcrumbnav);

      $invoice = new \App\Helpers\InvoiceClass();
      $invoiceexists = true;
      try {
         $invoice->setID($invoiceid);
      } catch (\Exception $e) {
         $invoiceexists = false;
      }
      $allowedaccess = $invoice->isAllowed();
      if (!$invoiceexists || !$allowedaccess) {
         $smartyvalues["error"] = "on";
         $smartyvalues["invalidInvoiceIdRequested"] = true;
         return view('clientinvoices.invoice', $smartyvalues);
      }
      $smartyvalues["invalidInvoiceIdRequested"] = false;
      \App\Helpers\ClientHelper::checkContactPermission("invoices");
      if ($invoice->getData("status") == "Paid" && session('orderdetails') && session('orderdetails.InvoiceID') == $invoiceid && !session('orderdetails.paymentcomplete')) {
         //  $_SESSION["orderdetails"]["paymentcomplete"] = true;
         
          session()->put("orderdetails.paymentcomplete", true);
         //  redir("a=complete", "cart.php");
         return redirect()->route('cart', ['a' => 'complete']);
      }
      $gateway = $request->input("gateway");
      if ($gateway) {
         $gateways = new \App\Helpers\Gateways();
         $validgateways = $gateways->getAvailableGateways($invoiceid);
         if (array_key_exists($gateway, $validgateways)) {
            \App\Models\Invoice::where(array("id" => $invoiceid))->update(array("paymentmethod" => $gateway));
            \App\Helpers\Hooks::run_hook("InvoiceChangeGateway", array("invoiceid" => $invoiceid, "paymentmethod" => $gateway));
         }
         // redir("id=" . $invoiceid);
         return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceid);
      }
      $creditbal = \App\Models\Client::where(array("id" => $invoice->getData("userid")))->value("credit") ?? 0;
      $smartyvalues["creditamount"] = 0;
      $smartyvalues["manualapplycredit"] = false;
      $smartyvalues["totalcredit"] = 0;
      if ($invoice->getData("status") == "Unpaid" && 0 < $creditbal && !$invoice->isAddFundsInvoice()) {
         $balance = $invoice->getData("balance");
         $creditamount = $request->input("creditamount") ?? 0;
         if ($request->input("applycredit") && 0 < $creditamount) {
            if ($creditbal < $creditamount) {
               // echo $_LANG["invoiceaddcreditovercredit"];
               // exit;
               return \Lang::get("client.invoiceaddcreditovercredit");
            }
            if ($balance < $creditamount) {
               // echo $_LANG["invoiceaddcreditoverbalance"];
               // exit;
               return \Lang::get("client.invoiceaddcreditoverbalance");
            }
            \App\Helpers\Invoice::applyCredit($invoiceid, $invoice->getData("userid"), $creditamount);
            // redir("id=" . $invoiceid);
            return redirect()->route('pages.services.mydomains.viewinvoiceweb', $invoiceid);
         }
         $smartyvalues["manualapplycredit"] = true;
         $clientCurrency = \App\Helpers\Format::getCurrency($invoice->getData("userid"));
         $smartyvalues["totalcredit"] = \App\Helpers\Format::formatCurrency($creditbal, $clientCurrency["id"]);
         if (!$creditamount) {
            $creditamount = $balance <= $creditbal ? $balance : $creditbal;
         }
         $smartyvalues["creditamount"] = $creditamount;
      }
      $outputvars = $invoice->getOutput();
      $smartyvalues = array_merge($smartyvalues, $outputvars);
      $invoiceitems = $invoice->getLineItems();
      $smartyvalues["invoiceitems"] = $invoiceitems;
      $transactions = $invoice->getTransactions();
      $smartyvalues["transactions"] = $transactions;
      $paymentbutton = $invoice->getData("status") == "Unpaid" && 0 < $invoice->getData("balance") ? $invoice->getPaymentLink() : "";
      $smartyvalues["paymentbutton"] = $paymentbutton;
      $smartyvalues["paymentSuccess"] = (bool) $request->input("paymentsuccess");
      $smartyvalues["paymentFailed"] = (bool) $request->input("paymentfailed");
      $smartyvalues["pendingReview"] = (bool) $request->input("pendingreview");
      $smartyvalues["offlineReview"] = (bool) $request->input("offlinepaid");
      $smartyvalues["offlinepaid"] = (bool) $request->input("offlinepaid");
      $smartyvalues["paymentSuccessAwaitingNotification"] = $invoice->showPaymentSuccessAwaitingNotificationMsg($smartyvalues["paymentSuccess"]);
      if (\App\Helpers\Cfg::get("AllowCustomerChangeInvoiceGateway")) {
         $smartyvalues["allowchangegateway"] = true;
         $gateways = new \App\Helpers\Gateways();
         $availablegateways = $gateways->getAvailableGateways($invoiceid);
         $availablegateways = \App\Helpers\Orders::filterGatewaysByRestrictionPeriod($availablegateways);
         $frm = new \App\Helpers\Form();
         $gatewaydropdown = $frm->dropdown("gateway", $availablegateways, $invoice->getData("paymentmodule"), "submit()");
         $smartyvalues["gatewaydropdown"] = $gatewaydropdown;
      } else {
         $smartyvalues["allowchangegateway"] = false;
         $smartyvalues["gatewaydropdown"] = "";
      }
      $smartyvalues["taxIdLabel"] = \Lang::get(\App\Helpers\Vat::getLabel());

      // if ($existingLanguage) {
      //    swapLang($existingLanguage);
      // }
      return \App\Helpers\ClientareaFunctions::outputClientArea("clientinvoices.invoice", true, array("ClientAreaPageViewInvoice"), $smartyvalues);
   }
   public function Billing_ViewInvoiceWebOLD($id)
   {
      $invoiceId = (int)$id;
      try {
         $invoice = new \App\Helpers\InvoiceClass($invoiceId);
         // dd($invoice);
      } catch (\Exception $e) {
         abort(404);
      }
      $sysurl = config('app.url');
      $data = $invoice->getOutput();
      $subtotal = 0;

      $client = \App\Models\Client::where('id', $data['userid'])->select('firstname', 'lastname', 'companyname', 'address1', 'city', 'state', 'postcode', 'credit', 'currency')->first();
      $invoiceitems = $invoice->getLineItems();
      foreach ($invoiceitems as $item) {
         $subtotal += $item['amount']->toNumeric();
      }

      $creditbal = Client::select('credit')->where(['id' => $invoice->getData('userid')])->first();
      if ($invoice->getData('status') == 'Unpaid' && 0 < $creditbal->credit && !$invoice->isAddFundsInvoice()) {
         $manualapplycredit = true;
      }

      $invoiceitems = collect($invoiceitems)->map(function ($item) {
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

      // $gateway=$invoice->initialiseGatewayAndParams();
      //TODO
      //Gateway Module 'banktransfer' is Missing or Invalid
      //dd($gateway);
      $transactions = $invoice->getTransactions();
      $balance = $invoice->getData("balance");

      $invoiceexists = true;
      try {
         $invoice->setID($invoiceId);
      } catch (\Exception $e) {
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
      $param['gateway'] = \App\Helpers\Gateway::GetGatewaysArray();
      $param['client'] = $client;
      $paymentbutton = $invoice->getData("status") == "Unpaid" && 0 < $invoice->getData("balance") ? $invoice->getPaymentLink() : "";
      $param["paymentbutton"] = $paymentbutton;
      $param['subtotal'] = $subtotal;
      $param['manualapplycredit'] = $manualapplycredit ?? null;
      $param['transactions'] = $transactions;
      $param['balance'] = new \App\Helpers\FormatterPrice($balance);




      return view('clientinvoices.invoice', array_merge($param, $data));
   }
   public function BillingInvoice_ApplyCredit(Request $request, $id)
   {
      $userid = $request->userid;
      $creditamount = $request->creditamount;
      HelpersInvoice::applyCredit($id, $userid, $creditamount, true);
      return redirect()->route('pages.services.mydomains.viewinvoiceweb', $id);
   }
   public function BillingInvoice_UpdatePayment(Request $request)
   {
      $invoiceId = $request->id;
      $paymentMethod = $request->paymentmethod;
      $invoicePayMethod = Invoice::findOrFail($invoiceId);
      $invoicePayMethod->paymentmethod = $paymentMethod;
      $invoicePayMethod->save();
      $updatedInvoice =  $invoicePayMethod->paymentmethod;
      $paymentLists = Paymentgateway::where('setting', 'name')->where('gateway', $updatedInvoice)->get();
      foreach ($paymentLists as $key => $payType) {
         $strPayment = $payType->value;
      }
      \App\Helpers\Hooks::run_hook("InvoiceChangeGateway", array("invoiceid" => $invoiceId, "paymentmethod" => $paymentMethod));
      return response()->json($strPayment);
   }
   public function Billing_ManualRequest()
   {
      return view('pages.billing.manualbillingrequest.index');
   }
   public function Billing_TaxRequest()
   {
      return view('pages.billing.requesttaxinvoice.index');
   }
   public function Billing_Refund()
   {
      return view('pages.billing.refund.index');
   }
   public function Billing_Offer()
   {
      return view('pages.billing.offerforme.index');
   }
}