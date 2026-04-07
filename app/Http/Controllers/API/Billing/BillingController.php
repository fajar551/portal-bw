<?php

namespace App\Http\Controllers\API\Billing;

use DB;
use Validator;
use Auth;
use ResponseAPI, Format, Gateway, LogActivity;
use App\Rules\FloatValidator;
use App\Helpers\Hooks;

use App\Models\Credit;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Account;
use App\Models\Paymentgateway;
use App\Models\Invoiceitem;
use App\Models\Paymethod;
use App\Models\Quote;
use App\Models\Quoteitem;
use App\Models\Billableitem;
use App\Models\Admin;
use App\Models\Currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @group Billing
 * 
 * APIs for managing billing
 */
class BillingController extends Controller
{
   protected $request;
   public function __construct(Request $request)
   {
      $this->request = $request;
   }

   /**
    * GetCredits
    * 
    * Obtain the Credit Log for a Client Account
    */
   public function GetCredits()
   {
      $rules = [
         // The Client to obtain the log for
         'clientid' => ['required', 'integer'],
      ];

      $validator = Validator::make($this->request->all(), $rules);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientid = $this->request->input('clientid');

      $client = Client::find($clientid);

      if (!$client) {
         return ResponseAPI::Error([
            'message' => 'Client ID Not Found',
         ]);
      }

      $query = Credit::query();
      $query->where('clientid', $clientid);
      $query->orderBy('date', 'ASC');
      $results = $query->get();

      $response = [
         'credit' => $results,
      ];

      return ResponseAPI::Success([
         'totalresults' => $results->count(),
         'clientid' => $clientid,
         'credits' => $results->count() > 0 ? $response : [],
      ]);
   }

   /**
    * GetInvoice
    * 
    * Retrieve a specific invoice
    */
   public function GetInvoice()
   {
      $rules = [
         // The ID of the invoice to retrieve.
         'invoiceid' => ['required', 'integer'],
      ];

      $validator = Validator::make($this->request->all(), $rules);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $invoiceid = $this->request->input('invoiceid');

      $invoice = Invoice::find($invoiceid);

      if (!$invoice) {
         return ResponseAPI::Error([
            'message' => 'Invoice ID Not Found',
         ]);
      }

      $data = $invoice->toArray();

      $userid = $data["userid"];
      $invoicenum = $data["invoicenum"];
      $date = $data["date"];
      $duedate = $data["duedate"];
      $datepaid = $data["datepaid"];
      $lastCaptureAttempt = $data["last_capture_attempt"];
      $subtotal = $data["subtotal"];
      $credit = $data["credit"];
      $tax = $data["tax"];
      $tax2 = $data["tax2"];
      $total = $data["total"];
      $taxrate = $data["taxrate"];
      $taxrate2 = $data["taxrate2"];
      $status = $data["status"];
      $paymentmethod = $data["paymentmethod"];
      $notes = $data["notes"];

      $account = Account::select(DB::raw('SUM(amountin)-SUM(amountout) as sumamount'))->where('invoiceid', $invoiceid)->first();
      $amountpaid = $account->sumamount;
      $balance = $total - $amountpaid;
      $balance = Format::AsCurrency($balance);

      $paymentgateway = Paymentgateway::where('gateway', $paymentmethod)->where('setting', 'type');
      $gatewaytype = $paymentgateway->value('value') ?? "";
      $ccgateway = $gatewaytype == "CC" || $gatewaytype == "OfflineCC" ? true : false;

      $response = [
         "invoiceid" => $invoiceid,
         "invoicenum" => $invoicenum,
         "userid" => $userid,
         "date" => $date,
         "duedate" => $duedate,
         "datepaid" => $datepaid,
         "lastcaptureattempt" => $lastCaptureAttempt,
         "subtotal" => $subtotal,
         "credit" => $credit,
         "tax" => $tax,
         "tax2" => $tax2,
         "total" => $total,
         "balance" => $balance,
         "taxrate" => $taxrate,
         "taxrate2" => $taxrate2,
         "status" => $status,
         "paymentmethod" => $paymentmethod,
         "notes" => $notes,
         "ccgateway" => $ccgateway,
      ];

      $invoiceitems = Invoiceitem::where('invoiceid', $invoiceid)->get();
      foreach ($invoiceitems as $invoiceitem) {
         $response["items"]["item"][] = [
            "id" => $invoiceitem->id,
            "type" => $invoiceitem->type,
            "relid" => $invoiceitem->relid,
            "description" => $invoiceitem->description,
            "amount" => $invoiceitem->amount,
            "taxed" => $invoiceitem->taxed,
         ];
      }

      $response["transactions"] = [];
      $accounts = Account::where('invoiceid', $invoiceid)->get();
      foreach ($accounts as $account) {
         $response["transactions"]["transaction"][] = $account;
      }
      if (empty($response["transactions"])) {
         $response["transactions"] = "";
      }

      return ResponseAPI::Success($response);
   }

   /**
    * GetInvoices
    * 
    * Retrieve a list of invoices.
    */
   public function GetInvoices()
   {
      $rules = [
         // The offset for the returned invoice data (default: 0). Example: 0
         'limitstart' => ['nullable', 'integer'],
         // The number of records to return (default: 25). Example: 25
         'limitnum' => ['nullable', 'integer'],
         // Find invoices for a specific client id
         'userid' => ['nullable', 'integer'],
         // Find invoices for a specific status. Standard Invoice statuses plus Overdue. No-example
         'status' => ['nullable', 'string'],
         // The field to sort results by. Accepted values are: id, invoicenumber, date, duedate, total, status. Example: id
         'orderby' => ['nullable', 'string'],
         // Order sort attribute. Accepted values are: asc or desc. Example: asc
         'order' => ['nullable', 'string', Rule::in(['asc', 'desc', 'ASC', 'DESC'])],
      ];

      $validator = Validator::make($this->request->all(), $rules);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $orderby = $this->request->input('orderby');
      $order = $this->request->input('order') ?? 'asc';
      $userid = $this->request->input('userid');
      $status = $this->request->input('status');
      $limitstart = $this->request->input('limitstart') ?? 0;
      $limitnum = $this->request->input('limitnum') ?? 25;

      $page = $limitstart + 1;
      $mulai = ($page > 1) ? ($page * $limitnum) - $limitnum : 0;

      $filters = [
         'userid' => $userid,
         'status' => $status,
      ];

      $query = Invoice::query();
      $query->has('client');
      // $query->without('client');
      // $query->with('client:id,firstname');
      $query->filter($filters);
      $totalresults = $query->count();

      switch ($orderby) {
         case "id":
         case "date":
         case "duedate":
         case "total":
         case "status":
            $query->orderBy($orderby, $order);
            break;
         case "invoicenumber":
            $query->orderBy("invoicenum", $order)->orderBy("id", $order);
            break;
         case "default":
         default:
            $query->orderBy("status", "desc")->orderBy("duedate", $order);
      }

      $query->offset($mulai);
      $query->limit($limitnum);
      $results = $query->get();

      $results->transform(function ($invoice) {
         $client = $invoice->client;
         $invoice->firstname = $client ? $client->firstname : "";
         $invoice->lastname = $client ? $client->lastname : "";
         $invoice->companyname = $client ? $client->companyname : "";

         $currency = Format::GetCurrency($invoice->userid);
         // // $data = json_decode(json_encode($invoice), true);
         $invoice->currencycode = $currency["code"];
         $invoice->currencyprefix = $currency["prefix"];
         $invoice->currencysuffix = $currency["suffix"];
         unset($invoice->client);
         return $invoice;
      });

      $response = [
         'invoice' => $results,
      ];

      return ResponseAPI::Success([
         'totalresults' => $totalresults,
         'startnumber' => $limitstart,
         'numreturned' => $results->count(),
         'invoices' => $results->count() > 0 ? $response : [],
      ]);
   }

   /**
    * GetPayMethods
    * 
    * Obtain the Pay Methods associated with a provided client id.
    */
   public function GetPayMethods()
   {
      $rules = [
         // The id of the client to obtain the Pay Methods for
         'clientid' => ['required', 'integer'],
         // The id of a specific Pay Method to retrieve
         'paymethodid' => ['nullable', 'integer'],
         // The type of Pay Methods to return. ‘BankAccount’ or ‘CreditCard’
         'type' => [
            'nullable',
            'string',
            Rule::in([
               strtolower(\App\Payment\PayMethod\Model::TYPE_BANK_ACCOUNT),
               \App\Payment\PayMethod\Model::TYPE_BANK_ACCOUNT,
               strtolower(\App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL),
               \App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL,
            ]),
         ],
      ];

      $messages = [
         'type.in' => "Invalid Pay Method Type. Should be 'BankAccount' or 'CreditCard'",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientId = $this->request->input('clientid');
      $payMethodId = $this->request->input('paymethodid');
      $type = $this->request->input('type');

      try {
         $client = \App\User\Client::with("payMethods")->findOrFail($clientId);
         if ($payMethodId) {
            $payMethods = $client->payMethods()->where("id", $payMethodId)->get();
         } else {
            if ($type) {
               $types = array(\App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL, \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_MANAGED, \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_UNMANAGED);
               if ($type == strtolower(\App\Payment\PayMethod\Model::TYPE_BANK_ACCOUNT)) {
                  $types = array(\App\Payment\PayMethod\Model::TYPE_BANK_ACCOUNT, \App\Payment\PayMethod\Model::TYPE_REMOTE_BANK_ACCOUNT);
               }
               $payMethods = $client->payMethods()->whereIn("payment_type", $types)->get();
            } else {
               $payMethods = $client->payMethods;
            }
         }
         $payMethodResponse = array();
         foreach ($payMethods as $payMethod) {
            $payment = $payMethod->payment;
            if (!$payment->getSensitiveData()) {
               $payMethod->delete();
               continue;
            }
            $response = array("id" => $payMethod->id, "type" => $payMethod->payment_type, "description" => $payMethod->description, "gateway_name" => $payMethod->gateway_name, "contact_type" => $payMethod->contact_type, "contact_id" => $payMethod->contact_id);
            if ($payment instanceof \App\Payment\PayMethod\Adapter\CreditCardModel) {
               $remoteToken = "";
               if ($payment->isRemoteCreditCard()) {
                  $remoteToken = $payment->getRemoteToken();
               }
               $startDate = "";
               if ($payment->getStartDate()) {
                  $startDate = $payment->getStartDate()->toCreditCard();
               }
               $expiryDate = "";
               if ($payment->getExpiryDate()) {
                  $expiryDate = $payment->getExpiryDate()->toCreditCard();
               }
               $response = array_merge($response, array("card_last_four" => $payment->getLastFour(), "expiry_date" => $expiryDate, "start_date" => $startDate, "issue_number" => $payment->getIssueNumber(), "card_type" => $payment->getCardType(), "remote_token" => $remoteToken));
            } else {
               $remoteToken = "";
               if ($payment->isRemoteBankAccount()) {
                  $remoteToken = $payment->getRemoteToken();
               }
               $response = array_merge($response, array("bank_name" => $payment->getName(), "remote_token" => $payment->getRemoteToken()));
            }
            $response["last_updated"] = $payMethod->updated_at->toAdminDateTimeFormat();
            $payMethodResponse[] = $response;
         }

         $apiresults = array("clientid" => $clientId, "paymethods" => $payMethodResponse);
         return ResponseAPI::Success($apiresults);
      } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
         return ResponseAPI::Error([
            'message' => "Client Not Found",
         ]);
      } catch (\Exception $e) {
         return ResponseAPI::Error([
            'message' => $e->getMessage(),
         ]);
      }
   }

   /**
    * GetQuotes
    * 
    * Obtain quotes matching the passed criteria
    */
   public function GetQuotes()
   {
      $stageList = [
         'Draft',
         'Delivered',
         'On Hold',
         'Accepted',
         'Lost',
         'Dead',
      ];

      $stageListString = implode(', ', $stageList);

      $rules = [
         // The offset for the returned quote data (default: 0). Example: 0
         'limitstart' => ['nullable', 'integer'],
         // The number of records to return (default: 25). Example: 25
         'limitnum' => ['nullable', 'integer'],
         // A specific quote ID to find quotes for.
         'quoteid' => ['nullable', 'integer'],
         // A specific client to find quotes for.
         'userid' => ['nullable', 'integer'],
         // A specific subject to find quotes for.
         'subject' => ['nullable', 'string'],
         // A specific stage (‘Draft’,‘Delivered’,‘On Hold’,‘Accepted’,‘Lost’, or ‘Dead’) to find quotes for.
         'stage' => [
            'nullable',
            'string',
            Rule::in($stageList),
         ],
         // A specific created date to find quotes for. Format: Y-m-d
         'datecreated' => ['nullable', 'string', 'date_format:Y-m-d'],
         // A specific last modified date to find quotes for. Format: Y-m-d
         'lastmodified' => ['nullable', 'string', 'date_format:Y-m-d'],
         // A specific valid until date to find quotes for. Format: Y-m-d
         'validuntil' => ['nullable', 'string', 'date_format:Y-m-d'],
      ];

      $messages = [
         'stage.in' => "Invalid specific stage. Should be {$stageListString}",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $limitstart = $this->request->input('limitstart') ?? 0;
      $limitnum = $this->request->input('limitnum') ?? 25;
      $quoteid = $this->request->input('quoteid');
      $userid = $this->request->input('userid');
      $subject = $this->request->input('subject');
      $stage = $this->request->input('stage');
      $datecreated = $this->request->input('datecreated');
      $lastmodified = $this->request->input('lastmodified');
      $validuntil = $this->request->input('validuntil');

      $page = $limitstart + 1;
      $mulai = ($page > 1) ? ($page * $limitnum) - $limitnum : 0;

      $filters = [
         'id' => $quoteid,
         'userid' => $userid,
         'subject' => $subject,
         'stage' => $stage,
         'datecreated' => $datecreated,
         'lastmodified' => $lastmodified,
         'validuntil' => $validuntil,
      ];

      $query = Quote::query();
      $query->filter($filters);
      $totalresults = $query->count();
      $query->offset($mulai);
      $query->limit($limitnum);
      $query->orderBy('id', 'DESC');
      $results = $query->get();

      $data = [];
      foreach ($results as $quote) {
         $items = Quoteitem::select('id', 'description', 'quantity', 'unitprice', 'discount', 'taxable')->where('quoteid', $quote->id)->get();
         $item = [];
         $item['item'] = $items;
         $quote->items = $items->count() > 0 ? $item : [];
         $data[] = $quote;
      }

      $response = [
         'quote' => $data,
      ];

      return ResponseAPI::Success([
         'totalresults' => $totalresults,
         'startnumber' => $limitstart,
         'numreturned' => $results->count(),
         'quotes' => $results->count() > 0 ? $response : [],
      ]);
   }

   /**
    * GetTransactions
    * 
    * Obtain transactions matching the passed criteria
    */
   public function GetTransactions()
   {
      $rules = [
         // Obtain transactions for a specific invoice id
         'invoiceid' => ['nullable', 'integer'],
         // Find transactions for a specific client id
         'clientid' => ['nullable', 'integer'],
         // Find transactions for a specific transaction id
         'transid' => ['nullable', 'string'],
      ];

      $validator = Validator::make($this->request->all(), $rules);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $invoiceid = $this->request->input('invoiceid');
      $clientid = $this->request->input('clientid');
      $transid = $this->request->input('transid');

      $filters = [
         'invoiceid' => $invoiceid,
         'userid' => $clientid,
         'transid' => $transid,
      ];

      $query = Account::query();
      $query->filter($filters);
      $results = $query->get();

      $response = [
         'transaction' => $results,
      ];

      return ResponseAPI::Success([
         'totalresults' => $results->count(),
         'startnumber' => 0,
         'numreturned' => $results->count(),
         'transactions' => $results->count() > 0 ? $response : [],
      ]);
   }

   /**
    * AddBillableItem
    * 
    * Adds a Billable Item
    */
   public function AddBillableItem()
   {
      $request = $this->request;
      $allowedtypes = array("noinvoice", "nextcron", "nextinvoice", "duedate", "recur");
      $cycle = ["Days", "Weeks", "Months", "Years"];

      $rules = [
         // The client to add the item to.
         'clientid' => ['required', 'integer', 'exists:App\Models\Client,id'],
         // The description of the Billable Item. This will appear on the invoice.
         'description' => ['required', 'string'],
         // The total amount to invoice for.
         'amount' => ['required', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // One of ‘noinvoice’, ‘nextcron’, ‘nextinvoice’, ‘duedate’, or ‘recur’.
         'invoiceaction' => [
            'nullable',
            Rule::in($allowedtypes),
         ],
         // When $invoiceaction=recur. The frequency of the recurrence.
         'recur' => [
            'nullable',
            'bail',
            // 'required_if:invoiceaction,recur',
            'integer',
            Rule::requiredIf(function () use ($request) {
               $invoiceaction = $request->input('invoiceaction');
               $recur = $request->input('recur');
               $recurcycle = $request->input('recurcycle');
               $recurfor = $request->input('recurfor');
               return $invoiceaction == "recur" && (!$recur && !$recurcycle || !$recurfor);
            }),
         ],
         // How often to recur the Billable Item. Days, Weeks, Months or Years.
         'recurcycle' => ['nullable', 'string', Rule::in($cycle)],
         // How many times the Billable Item should create an invoice.
         'recurfor' => ['nullable', 'integer'],
         // Date the invoice should be due (only required for due date and recur invoice actions). YYYY-mm-dd
         'duedate' => [
            'nullable',
            'bail',
            'required_if:invoiceaction,duedate',
            'string',
            'date_format:Y-m-d',
            // Rule::requiredIf(function() use ($request) {
            //     $invoiceaction = $request->input('invoiceaction');
            //     return $invoiceaction == 'recur' || $invoiceaction == 'duedate';
            // }),
         ],
         'hours' => ['nullable', 'string'],
      ];

      $messages = [
         'invoiceaction.in' => "Invalid Invoice Action",
         // 'recurcycle.in' => "Invalid recurcycle",
         'clientid.exists' => "Client ID not found",
         'recur.required' => "Recurring must have a unit, cycle and limit",
         'duedate.required_if' => "Due date is required",
         'amount.regex' => "Amount must be in decimal format: ### or ###.##",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientid = $this->request->input('clientid');
      $description = $this->request->input('description');
      $amount = (float) $this->request->input('amount') ?? 0;
      $invoiceaction = $this->request->input('invoiceaction') ?? 0;
      $recur = $this->request->input('recur') ?? 0;
      $recurcycle = $this->request->input('recurcycle') ?? "";
      $recurfor = $this->request->input('recurfor') ?? 0;
      $duedate = $this->request->input('duedate') ?? "0000-00-00";
      $hours = (float) $this->request->input('hours') ?? 0;

      // insert
      switch ($invoiceaction) {
         case 'noinvoice':
            $invoiceaction = "0";
            break;
         case 'nextcron':
            $invoiceaction = "1";
            if (!$duedate) {
               $duedate = date("Y-m-d");
            }
            break;
         case 'nextinvoice':
            $invoiceaction = "2";
            break;
         case 'duedate':
            $invoiceaction = "3";
            break;
         case 'recur':
            $invoiceaction = "4";
            break;
         default:
            $invoiceaction = "0";
            break;
      }

      $billiableitem = new Billableitem;
      $billiableitem->userid = $clientid;
      $billiableitem->description = $description;
      $billiableitem->hours = $hours;
      $billiableitem->amount = $amount;
      $billiableitem->recur = $recur;
      $billiableitem->recurcycle = $recurcycle;
      $billiableitem->recurfor = $recurfor;
      $billiableitem->invoiceaction = $invoiceaction;
      $billiableitem->duedate = $duedate;
      $billiableitem->save();

      return ResponseAPI::Success([
         'billableid' => $billiableitem->id,
      ]);
   }

   /**
    * AddCredit
    * 
    * Adds credit to a given client.
    */
   public function AddCredit()
   {
      $types = ['add', 'remove'];
      $adminTable = (new Admin)->getTableName();

      $rules = [
         // Client ID
         'clientid' => ['required', 'integer', 'exists:App\Models\Client,id'],
         // Admin only notes for credit justification
         'description' => ['required', 'string'],
         // The amount of credit to add or remove. Must be a positive value.
         'amount' => [
            'required',
            'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'
            // new FloatValidator,
         ],
         // The date the credit was added. YYYY-mm-dd format.
         'date' => ['nullable', 'string', 'date_format:Y-m-d'],
         // The active admin id to be associated with the credit record. Defaults to current admin.
         'adminid' => [
            'nullable',
            Rule::exists($adminTable, 'id')->where(function ($query) {
               $query->where('disabled', 0);
            }),
         ],
         // Whether to add or remove credit. One of ‘add’ or ‘remove’
         'type' => ['nullable', Rule::in($types)],
      ];

      $messages = [
         'type.in' => "Type can only be add or remove",
         'clientid.exists' => "Client ID not found",
         'adminid.exists' => "Admin ID not found",
         'amount.required' => "No Amount Provided",
         'amount.regex' => "Amount must be in decimal format: ### or ###.##"
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientid = $this->request->input('clientid');
      $description = $this->request->input('description');
      $amount = (float) $this->request->input('amount') ?? 0;
      $date = $this->request->input('date') ?? \Carbon\Carbon::now()->format('Y-m-d');
      $adminid = $this->request->input('adminid');
      $type = $this->request->input('type') ?? 'add';

      $client = Client::find($clientid);

      if ($type === "remove" && $client->credit < $amount) {
         return ResponseAPI::Error([
            'message' => 'Insufficient Credit Balance',
         ]);
      }

      if (!$adminid) {
         $auth = Auth::guard('admin')->user();
         $adminid = $auth ? $auth->id : 0;
      }

      $relativeChange = $amount;
      if ($type === "remove") {
         $relativeChange = 0 - $relativeChange;
      }

      $credit = new Credit;
      $credit->clientid = $clientid;
      $credit->admin_id = $adminid;
      $credit->date = $date;
      $credit->description = $description;
      $credit->amount = $relativeChange;
      $credit->save();

      $client->credit += $relativeChange;
      $client->save();
      $client = $client->fresh();

      // $currency = Format::GetCurrency($clientid);
      $message = "Added Credit - User ID: " . $clientid . " - Amount: " . Format::Price($amount);
      if ($type == "remove") {
         $message = "Removed Credit - User ID: " . $clientid . " - Amount: " . Format::Price($amount);
      }

      LogActivity::Save($message, $clientid);
      return ResponseAPI::Success([
         'newbalance' => $client->credit,
      ]);
   }

   /**
    * AddInvoicePayment
    * 
    * Adds payment to a given invoice.
    */
   public function AddInvoicePayment()
   {
      $rules = [
         // Invoice ID
         'invoiceid' => [
            'required',
            'integer',
            'exists:App\Models\Invoice,id'
         ],
         // The unique transaction ID that should be applied to the payment.
         'transid' => ['required', 'string'],
         // The gateway used, in system name format (for example, paypal or authorize).
         'gateway' => ['required', 'string'],
         // The date that the payment should have assigned. Format: YYYY-MM-DD HH:mm:ss
         'date' => ['required', 'string', 'date_format:Y-m-d H:i:s'],
         // The amount paid. You can leave this undefined to take the full amount of the invoice.
         'amount' => [
            'nullable',
            // new FloatValidator,
            'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/',
         ],
         // The amount of the payment that was taken as a fee by the gateway.
         'fees' => [
            'nullable',
            // new FloatValidator,
            'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/',
         ],
         // Set this to true to prevent sending an email for the invoice payment.
         'noemail' => ['nullable', 'boolean'],
      ];

      $messages = [
         'invoiceid.exists' => "Invoice ID not found",
         'amount.regex' => "Amount must be in decimal format: ### or ###.##",
         'fees.regex' => "Amount must be in decimal format: ### or ###.##",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $invoiceid = $this->request->input('invoiceid');
      $transid = $this->request->input('transid');
      $amount = $this->request->input('amount');
      $fees = $this->request->input('fees') ?? 0;
      $gateway = $this->request->input('gateway');
      $noemail = (bool) $this->request->input('noemail');
      $date = $this->request->input('date');
      $userAgent = $this->request->header('User-Agent');

      $invoice = Invoice::find($invoiceid);
      $invoice = new \App\Helpers\InvoiceClass($invoiceid);
      $invoiceStatus = $invoice->getData("status");

      switch ($invoiceStatus) {
         case 'Cancelled':
            return ResponseAPI::Error([
               'message' => 'It is not possible to add a payment to an invoice that is Cancelled',
            ]);
            break;
         case 'Draft':
            return ResponseAPI::Error([
               'message' => 'It is not possible to add a payment to an invoice that is a Draft',
            ]);
            break;

         default:
            $date = $date ? (new \App\Helpers\Functions())->fromMySQLDate($date) : "";
            \App\Helpers\Invoice::addInvoicePayment($invoiceid, $transid, $amount, $fees, $gateway, $noemail, $date);

            return ResponseAPI::Success();
            break;
      }
   }

   /**
    * DeleteQuote
    * 
    * Deletes a quote.
    * Removes a quote from the system. This cannot be undone
    */
   public function DeleteQuote()
   {
      $rules = [
         // The quote id to be deleted
         'quoteid' => ['required', 'integer', 'exists:App\Models\Quote,id'],
      ];

      $messages = [
         'quoteid.exists' => "Quote ID Not Found",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $quoteid = $this->request->input('quoteid');

      // delete
      Quote::where('id', $quoteid)->delete();
      Quoteitem::where('quoteid', $quoteid)->delete();

      return ResponseAPI::Success();
   }

   /**
    * UpdateTransaction
    * 
    * Updates a transaction in the system
    */
   public function UpdateTransaction()
   {
      $rules = [
         // The unique ID of the transaction to update.
         'transactionid' => ['required', 'integer', 'exists:App\Models\Account,id'],
         // The unique ID of the transaction that this transaction refunds.
         'refundid' => ['nullable', 'integer'],
         // The ID of the user to apply the transaction to.
         'userid' => ['nullable', 'integer'],
         // The ID of the invoice the transaction is for.
         'invoiceid' => ['nullable', 'integer'],
         // The unique transaction ID for this payment.
         'transid' => ['nullable', 'string'],
         // The date of the transaction in the Y-m-d format.
         'date' => ['nullable', 'string', 'date_format:Y-m-d'],
         // The gateway of the transaction, in system format.
         'gateway' => ['nullable', 'string'],
         // The currency ID for the transaction, if not associated with a user.
         'currency' => ['nullable', 'integer'],
         // The description of the transaction.
         'description' => ['nullable', 'string'],
         // The amount received by the payment.
         'amountin' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The amount of fee charged on the transaction by the merchant. This can be negative.
         'fees' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The amount paid out by the payment.
         'amountout' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The exchange rate for the payment based on the default currency.
         'rate' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // Whether to apply the payment to credit on the client account. Invoice ID must not be provided.
         'credit' => ['nullable', 'boolean'],
      ];

      $messages = [
         'transactionid.exists' => "Transaction ID Not Found",
         'amountin.regex' => "Amount must be in decimal format: ### or ###.##",
         'fees.regex' => "Amount must be in decimal format: ### or ###.##",
         'amountout.regex' => "Amount must be in decimal format: ### or ###.##",
         'rate.regex' => "Amount must be in decimal format: ### or ###.##",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $transactionid = $this->request->input('transactionid');

      $account = Account::find($transactionid);

      if ($this->request->input('userid')) {
         $account->userid = $this->request->input('userid');
      }
      if ($this->request->input('currency')) {
         $account->currency = $this->request->input('currency');
      }
      if ($this->request->input('gateway')) {
         $account->gateway = $this->request->input('gateway');
      }
      if ($this->request->input('date')) {
         $account->date = $this->request->input('date');
      }
      if ($this->request->input('description')) {
         $account->description = $this->request->input('description');
      }
      if ($this->request->input('amountin')) {
         $account->amountin = $this->request->input('amountin');
      }
      if ($this->request->input('fees')) {
         $account->fees = $this->request->input('fees');
      }
      if ($this->request->input('amountout')) {
         $account->amountout = $this->request->input('amountout');
      }
      if ($this->request->input('rate')) {
         $account->rate = $this->request->input('rate');
      }
      if ($this->request->input('transid')) {
         $account->transid = $this->request->input('transid');
      }
      if ($this->request->input('invoiceid')) {
         $account->invoiceid = $this->request->input('invoiceid');
      }
      if ($this->request->input('refundid')) {
         $account->refundid = $this->request->input('refundid');
      }
      $account->save();

      return ResponseAPI::Success();
   }

   /**
    * UpdateQuote
    * 
    * pdates an existing quote
    */
   public function UpdateQuote()
   {
      $stagearray = array("Draft", "Delivered", "On Hold", "Accepted", "Lost", "Dead");

      $rules = [
         // The ID of the quote to update
         'quoteid' => ['required', 'integer', 'exists:App\Models\Quote,id'],
         // The subject of the quote
         'subject' => ['nullable', 'string'],
         // The current stage of the quote (‘Draft’,‘Delivered’,‘On Hold’,‘Accepted’,‘Lost’,‘Dead’)
         'stage' => ['nullable', 'string', Rule::in($stagearray)],
         // The date the quote is valid until in localised format (eg DD/MM/YYYY)
         'validuntil' => ['nullable', 'date_format:d-m-Y'],
         'datecreated' => ['nullable', 'date_format:d-m-Y'],
         'lineitems' => ['nullable', 'string'],
         'userid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
         'lineitems' => ['nullable', 'string'],
         'firstname' => ['nullable', 'string'],
         'companyname' => ['nullable', 'string'],
         'email' => ['nullable', 'string'],
         'address1' => ['nullable', 'string'],
         'address2' => ['nullable', 'string'],
         'city' => ['nullable', 'string'],
         'state' => ['nullable', 'string'],
         'country' => ['nullable', 'string'],
         'phonenumber' => ['nullable', 'string'],
         'tax_id' => ['nullable', 'string'],
         'currency' => ['nullable', 'integer'],
         'proposal' => ['nullable', 'string'],
         'customernotes' => ['nullable', 'string'],
         'adminnotes' => ['nullable', 'string'],
      ];

      $messages = [
         'quoteid.exists' => "Quote ID Not Found",
         'userid.exists' => "Client ID Not Found",
         'stage.exists' => "Invalid Stage",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $quoteid = $this->request->input('quoteid');
      $subject = $this->request->input('subject');
      $stage = $this->request->input('stage');
      $validuntil = $this->request->input('validuntil');
      $datecreated = $this->request->input('datecreated');
      $lineitems = $this->request->input('lineitems');
      $userid = $this->request->input('userid');
      $lineitems = $this->request->input('lineitems');
      $firstname = $this->request->input('firstname');
      $companyname = $this->request->input('companyname');
      $email = $this->request->input('email');
      $address1 = $this->request->input('address1');
      $address2 = $this->request->input('address2');
      $city = $this->request->input('city');
      $state = $this->request->input('state');
      $country = $this->request->input('country');
      $phonenumber = $this->request->input('phonenumber');
      $tax_id = $this->request->input('tax_id');
      $currency = $this->request->input('currency');
      $proposal = $this->request->input('proposal');
      $customernotes = $this->request->input('customernotes');
      $adminnotes = $this->request->input('adminnotes');

      $data = Quote::find($quoteid);
      $subject = is_null($subject) ? $data->subject : $subject;
      $validuntil = is_null($validuntil) ? (new \App\Helpers\Functions())->fromMySQLDate($data->validuntil) : (new \App\Helpers\Functions())->fromMySQLDate($validuntil);
      $userid = is_null($userid) ? $data->userid : $userid;
      if (!$userid) {
         $clienttype = "new";
         $firstname = is_null($firstname) ? $data->firstname : $firstname;
         $lastname = is_null($lastname) ? $data->lastname : $lastname;
         $companyname = is_null($companyname) ? $data->companyname : $companyname;
         $email = is_null($email) ? $data->email : $email;
         $address1 = is_null($address1) ? $data->address1 : $address1;
         $address2 = is_null($address2) ? $data->address2 : $address2;
         $city = is_null($city) ? $data->city : $city;
         $state = is_null($state) ? $data->state : $state;
         $postcode = is_null($postcode) ? $data->postcode : $postcode;
         $country = is_null($country) ? $data->country : $country;
         $phonenumber = is_null($phonenumber) ? $data->phonenumber : $phonenumber;
         $currency = is_null($currency) ? $data->currency : $currency;
         $taxId = $tax_id ? $tax_id : $data->tax_id;
      }
      $proposal = is_null($proposal) ? $data->proposal : $proposal;
      $customernotes = is_null($customernotes) ? $data->customernotes : $customernotes;
      $adminnotes = is_null($adminnotes) ? $data->adminnotes : $adminnotes;
      $datecreated = (new \App\Helpers\Functions())->fromMySQLDate($data->datecreated);

      if ($lineitems) {
         $lineitems = base64_decode($lineitems);
         $lineitemsarray = (new \App\Helpers\Client())->safe_unserialize($lineitems);
      }

      \App\Helpers\Quote::saveQuote($quoteid, $subject, $stage, $datecreated, $validuntil, $clienttype, $userid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $currency, $lineitemsarray, $proposal, $customernotes, $adminnotes, false, $taxId);

      return ResponseAPI::Success();
   }

   /**
    * AddPayMethod
    * 
    * Add a Pay Method to a given client. Supports the creation of credit card and bank account pay methods. Note that some tokenised payment gateways cannot be utilised via the API. Please refer to individual payment gateway documentation for specifics about supported functionality.
    */
   public function AddPayMethod()
   {
      $types = [\App\Payment\PayMethod\Model::TYPE_BANK_ACCOUNT, \App\Payment\PayMethod\Model::TYPE_REMOTE_BANK_ACCOUNT, \App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL, \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_MANAGED, \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_UNMANAGED];
      $typesin = implode(', ', $types);

      $rules = [
         // The id of the client to add the Pay Method
         'clientid' => ['required', 'integer', 'exists:App\Models\Client,id'],
         // The type of Pay Method to add. One of ‘BankAccount’, ‘CreditCard’, or ‘RemoteCreditCard’. Defaults to ‘CreditCard’
         'type' => ['nullable', 'string', Rule::in($types)],
         // The description for the new Pay Method
         'description' => ['nullable', 'string'],
         // Required for use with tokenised payment gateways eg. authorizecim, sagepaytokens, etc…
         'gateway_module_name' => ['nullable', 'string'],
         // Credit Card Number. Required for CreditCard and RemoteCreditCard types
         'card_number' => ['nullable', 'string'],
         // The expiry date for the card. Required for cc type. Format ‘MMYY’ eg 0122
         'card_expiry' => ['nullable', 'string'],
         // The start date for the card. Not required. Format ‘MMYY’ eg 0122
         'card_start' => ['nullable', 'string'],
         // The issue_number for the card. Not required
         'card_issue_number' => ['nullable', 'integer'],
         // The name of the bank. Not required
         'bank_name' => ['nullable', 'string'],
         // The type of bank account (checking, credit etc). Not required
         'bank_account_type' => ['nullable', 'string'],
         // The bank code. Also called sort code or routing number. Required for BankAccount type
         'bank_code' => ['nullable', 'string'],
         // The account number. Required for BankAccount type
         'bank_account' => ['nullable', 'string'],
         // Should the new Pay Method be the client default
         'set_as_default' => ['nullable', 'boolean'],
      ];

      $messages = [
         'clientid.exists' => "Invalid Client ID",
         'type.in' => "Invalid Pay Method Type. Type should be one of {$typesin}",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientId = $this->request->input('clientid');
      $type = $this->request->input('type');
      $description = $this->request->input('description') ?? "";
      $gateway = $this->request->input('gateway_module_name');
      $cardNumber = $this->request->input('card_number');
      $expiryDate = $this->request->input('card_expiry');
      $startDate = $this->request->input('card_start');
      $issueNumber = $this->request->input('card_issue_number');
      $bankName = $this->request->input('bank_name');
      $acctType = $this->request->input('bank_account_type');
      $bankCode = $this->request->input('bank_code');
      $bankAccountNumber = $this->request->input('bank_account');
      $default = $this->request->input('set_as_default');

      $client = \App\User\Client::findOrFail($clientId);
      $workFlowType = "";

      if (!$type) {
         $type = strtolower(\App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL);
      }
      if (!$gateway && $type == strtolower(\App\Payment\PayMethod\Model::TYPE_REMOTE_BANK_ACCOUNT)) {
         return ResponseAPI::Error([
            'message' => 'Gateway is Required for RemoteCreditCard type',
         ]);
      }
      if ($gateway) {
         $gatewayInterface = new \App\Module\Gateway();
         if (!$gatewayInterface->load($gateway)) {
            $gateways = $gatewayInterface->getActiveGateways();
            return ResponseAPI::Error([
               'message' => "Invalid Gateway Module Name. Must be one of: " . implode(", ", $gateways),
            ]);
         }
         $workFlowType = $gatewayInterface->getWorkflowType();
      }
      $billingContact = $client->billingContact;
      if (!$billingContact) {
         $billingContact = $client;
      }
      if (in_array($type, array(strtolower(\App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL), strtolower(\App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_MANAGED)))) {
         if (!$workFlowType) {
            $workFlowType = \App\Module\Gateway::WORKFLOW_MERCHANT;
         }
         if (!$cardNumber) {
            return ResponseAPI::Error([
               'message' => "Card Number is required for '" . \App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL . "'," . " or '" . \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_MANAGED . "' type",
            ]);
         }
         if (!$expiryDate) {
            return ResponseAPI::Error([
               'message' => "Expiry Date is required for '" . \App\Payment\PayMethod\Model::TYPE_CREDITCARD_LOCAL . "'," . " or '" . \App\Payment\PayMethod\Model::TYPE_CREDITCARD_REMOTE_MANAGED . "' type",
            ]);
         }
         try {
            $expiryDate = \App\Helpers\Carbon::createFromCcInput($expiryDate);
         } catch (\Exception $e) {
            return ResponseAPI::Error([
               'message' => "Expiry Date is invalid",
            ]);
         }
         if ($startDate) {
            try {
               $startDate = \App\Helpers\Carbon::createFromCcInput($startDate);
            } catch (\Exception $e) {
               return ResponseAPI::Error([
                  'message' => "Start Date is invalid",
               ]);
            }
         }
         if ($issueNumber && !is_numeric($issueNumber)) {
            return ResponseAPI::Error([
               'message' => "Issue Number is invalid",
            ]);
         }
         switch ($workFlowType) {
            case \App\Module\Gateway::WORKFLOW_TOKEN:
               $payMethod = \App\Payment\PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($client, $billingContact, $description);
               $payMethod->setGateway($gatewayInterface);
               if ($default) {
                  $payMethod->setAsDefaultPayMethod();
               }
               $payMethod->save();
               $newPayment = $payMethod->payment;
               $newPayment->setCardNumber($cardNumber);
               $newPayment->setExpiryDate($expiryDate);
               if ($startDate) {
                  $newPayment->setStartDate($startDate);
               }
               if ($issueNumber) {
                  $newPayment->setIssueNumber($issueNumber);
               }
               try {
                  $newPayment->createRemote()->save();
               } catch (\Exception $e) {
                  return ResponseAPI::Error([
                     'message' => "Error Creating Remote Token: " . $e->getMessage(),
                  ]);
               }
               break;
            case \App\Module\Gateway::WORKFLOW_MERCHANT:
               $payMethod = \App\Payment\PayMethod\Adapter\CreditCard::factoryPayMethod($client, $billingContact, $description);
               if ($default) {
                  $payMethod->setAsDefaultPayMethod();
               }
               $payMethod->save();
               $newPayment = $payMethod->payment;
               $newPayment->setCardNumber($cardNumber);
               $newPayment->setExpiryDate($expiryDate);
               if ($startDate) {
                  $newPayment->setStartDate($startDate);
               }
               if ($issueNumber) {
                  $newPayment->setIssueNumber($issueNumber);
               }
               $newPayment->save();
               break;
            case \App\Module\Gateway::WORKFLOW_ASSISTED:
            case \App\Module\Gateway::WORKFLOW_NOLOCALCARDINPUT:
            case \App\Module\Gateway::WORKFLOW_REMOTE:
            default:
               return ResponseAPI::Error([
                  'message' => "Unsupported Gateway Type for Storage",
               ]);
         }
      } else {
         $payMethod = \App\Payment\PayMethod\Adapter\BankAccount::factoryPayMethod($client, $billingContact, $description);
         if ($default) {
            $payMethod->setAsDefaultPayMethod();
         }
         $payMethod->save();
         $newPayment = $payMethod->payment;
         try {
            $newPayment->setAccountType($acctType)->setAccountHolderName($billingContact->firstName . " " . $billingContact->lastName)->setBankName($bankName)->setRoutingNumber($bankCode)->setAccountNumber($bankAccountNumber)->validateRequiredValuesPreSave()->save();
         } catch (\Exception $e) {
            return ResponseAPI::Error([
               'message' => $e->getMessage(),
            ]);
         }
      }
      $apiresults = array("clientid" => $client->id, "paymethodid" => $payMethod->id);
      return ResponseAPI::Success($apiresults);
   }

   /**
    * AddTransaction
    * 
    * Add a transaction to the system
    */
   public function AddTransaction()
   {
      $rules = [
         // The payment method of the transaction in system format
         'paymentmethod' => ['required', 'string'],
         // The ID of the user to apply the transaction to
         'userid' => ['nullable', 'integer', 'exists:App\Models\Client,id'],
         // The ID of the invoice the transaction is for
         'invoiceid' => ['nullable', 'integer', 'exists:App\Models\Invoice,id'],
         // The unique transaction id for this payment
         'transid' => ['nullable', 'string'],
         // The date of the transaction in your Localisation Format (eg DD/MM/YYYY)
         'date' => ['nullable', 'string'],
         // The currency id for the transaction if not associated with a user
         'currencyid' => ['nullable', 'integer'],
         // The description of the transaction
         'description' => ['nullable', 'string'],
         // The amount received by the payment
         'amountin' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The amount of fee charged on the transaction by the merchant - This can be negative
         'fees' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The amount paid out by the payment
         'amountout' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The exchange rate for the payment based on the default currency
         'rate' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // Should the payment be applied to credit on the client account. Invoice ID must not be provided
         'credit' => ['nullable', 'boolean'],
         // Should an already existing transaction id be allowed. Defaults to false. Example: false
         'allowduplicatetransid' => ['nullable', 'boolean'],
      ];

      $messages = [
         'userid.exists' => "Client ID Not Found",
         'invoiceid.exists' => "Invoice ID Not Found",
         'amountin.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'fees.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'amountout.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'rate.regex' => ':Attribute must be in decimal format: ### or ###.##',
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      $paymentmethod = $this->request->input('paymentmethod');
      $userid = $this->request->input('userid');
      $invoiceid = $this->request->input('invoiceid') ?? 0;
      $transid = $this->request->input('transid') ?? "";
      $date = $this->request->input('date');
      $currencyid = $this->request->input('currencyid');
      $description = $this->request->input('description') ?? "";
      $amountin = $this->request->input('amountin') ?? 0;
      $fees = $this->request->input('fees') ?? 0;
      $amountout = $this->request->input('amountout') ?? 0;
      $rate = $this->request->input('rate');
      $credit = $this->request->input('credit');
      $allowDuplicateTransId = $this->request->input('allowduplicatetransid');

      if ($invoiceid) {
         $result = \App\Models\Invoice::find($invoiceid);
         $invoiceData = $result->toArray();
         $invoiceid = $invoiceData["id"];
         if (!$userid) {
            $userid = $invoiceData["userid"];
         }
      }
      if ($userid) {
         $result = \App\Models\Client::find($userid);
         $clientData = $result->toArray();
         if (!$currencyid) {
            $currencyid = $clientData["currency"];
         }
      }
      if ($userid && $invoiceid && $invoiceData["userid"] != $userid) {
         return ResponseAPI::Error([
            'message' => 'User ID does not own the given Invoice ID',
         ]);
      }
      if ($currencyid) {
         if (!\App\Models\Currency::find($currencyid)) {
            return ResponseAPI::Error([
               'message' => 'Currency ID Not Found',
            ]);
         }
         if ($userid && $currencyid != $clientData["currency"]) {
            return ResponseAPI::Error([
               'message' => 'Currency ID does not match Client currency',
            ]);
         }
      }
      if (!$userid && !$invoiceid) {
         return ResponseAPI::Error([
            'message' => 'A Currency ID is required for non-customer related transactions',
         ]);
      }
      if ($transid && !$allowDuplicateTransId && !\App\Helpers\Invoice::isUniqueTransactionID($transid, $paymentmethod)) {
         return ResponseAPI::Error([
            'message' => 'Transaction ID must be Unique',
         ]);
      }
      if (empty($date)) {
         $date = (new \App\Helpers\Functions())->fromMySQLDate(date("Y-m-d H:i:s"));
      }
      \App\Helpers\Invoice::addTransaction($userid, $currencyid, $description, $amountin, $fees, $amountout, $paymentmethod, $transid, $invoiceid, $date, "", $rate);
      if ($userid && $credit && (!$invoiceid || $invoiceid == 0)) {
         if ($transid) {
            $description .= " (Trans ID: " . $transid . ")";
         }
         \App\Models\Credit::insert(array("clientid" => $userid, "date" => (new \App\Helpers\SystemHelper())->toMySQLDate($date), "description" => $description, "amount" => $amountin));
         \App\Models\Client::where('id', (int) $userid)->increment('credit', $amountin);
      }
      if (0 < $invoiceid) {
         $totalPaid = \App\Models\Account::selectRaw("SUM(amountin)-SUM(amountout) as totalPaid")->where('invoiceid', $invoiceid)->first();
         $totalPaid = $totalPaid->totalPaid;
         $invoiceData = \App\Models\Invoice::find($invoiceid)->toArray();
         $balance = $invoiceData["total"] - $totalPaid;
         if ($balance <= 0 && $invoiceData["status"] == "Unpaid") {
            \App\Helpers\Invoice::processPaidInvoice($invoiceid, "", $date);
         }
      }

      return ResponseAPI::Success();
   }

   /**
    * CreateInvoice
    * 
    * Create an invoice using the provided parameters.
    */
   public function CreateInvoice()
   {
      $auth = Auth::guard('admin')->user();

      $rules = [
         // The ID of the client to create the invoice for.
         'userid' => ['required', 'integer', 'exists:App\Models\Client,id'],
         // The status of the invoice being created. Defaults to unpaid.
         'status' => ['nullable', 'string'],
         // Whether to create the invoice in draft status. (You do not need to pass $status with this.)
         'draft' => ['nullable', 'boolean'],
         // Whether to send the Invoice Created Email to the client. (You can’t use this with $draft.)
         'sendinvoice' => ['nullable', 'boolean'],
         // The payment method of the created invoice, in system format.
         'paymentmethod' => ['nullable', 'string'],
         // The first-level tax rate to apply to the invoice to override the system default.
         'taxrate' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The second-level tax rate to apply to the invoice to override the system default.
         'taxrate2' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The creation date that the invoice should display. Format: YYYY-mm-dd
         'date' => ['nullable', 'date_format:Y-m-d'],
         // The due date of the newly-created invoice. Format: YYYY-mm-dd
         'duedate' => ['nullable', 'date_format:Y-m-d'],
         // The notes to appear on the created invoice.
         'notes' => ['nullable', 'string'],
         
         // The line item’s description. X is an integer to add multiple invoice items.
         'itemdescription' => ['nullable', 'string'],
         
         // The line item’s amount.
         'itemamount' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         
         // The line item’s taxed value.
         'itemtaxed' => ['nullable', 'boolean'],
         
         // Whether to automatically apply credit from the client account to the invoice.
         'autoapplycredit' => ['nullable', 'boolean'],
      ];

      $messages = [
         'userid.exists' => "Client ID Not Found",
         'taxrate.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'taxrate2.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'itemamount.regex' => ':Attribute must be in decimal format: ### or ###.##',
      ];
    
      $validator = Validator::make($this->request->all(), $rules, $messages);
   
      if ($validator->fails()) {
         $error = $validator->errors()->first();
   
         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      DB::beginTransaction();
      try {
         $userid = $this->request->input("userid");
         $sendInvoice = $this->request->input("sendinvoice");
         $paymentMethod = $this->request->input("paymentmethod");
         if (!$paymentMethod) {
            $paymentMethod = NULL;
         }
         $status = $this->request->input("status");
         $createAsDraft = (bool) $this->request->input("draft");
         $invoiceStatuses = \App\Models\Invoice::getInvoiceStatusValues();
         $defaultStatus = "Unpaid";
         $doprocesspaid = false;

         if ($createAsDraft && $sendInvoice) {
            return ResponseAPI::Error([
               'message' => "Cannot create and send a draft invoice in a single API request. Please create and send separately.",
            ]);
         }

         $taxrate = $taxrate2 = NULL;
         if ($this->request->has("taxrate")) {
            $taxrate2 = 0;
            $taxrate = $this->request->input("taxrate");
            if ($this->request->has("taxrate2")) {
               $taxrate2 = $this->request->input("taxrate2");
            }
         }
         if ($createAsDraft) {
            $status = "Draft";
         } else {
            if (!in_array($status, $invoiceStatuses)) {
               $status = $defaultStatus;
            }
         }
         $dateCreated = $this->request->input("date");
         if ($dateCreated) {
            try {
               $format = "Y-m-d";
               if (!stristr($dateCreated, "-")) {
                  $format = "Ymd";
               }
               $dateCreated = \App\Helpers\Carbon::createFromFormat($format, $dateCreated);
            } catch (\Exception $e) {
               $dateCreated = NULL;
            }
         }
         $dueDate = $this->request->input("duedate");
         if ($dueDate) {
            try {
               $format = "Y-m-d";
               if (!stristr($dueDate, "-")) {
                  $format = "Ymd";
               }
               $dueDate = \App\Helpers\Carbon::createFromFormat($format, $dueDate);
            } catch (\Exception $e) {
               $dueDate = NULL;
            }
         }
         $invoice = \App\Models\Invoice::newInvoice($this->request->input("userid"), $paymentMethod, $taxrate, $taxrate2);
         if ($dateCreated) {
            $invoice->dateCreated = $dateCreated;
         }
         if ($dueDate) {
            $invoice->dateDue = $dueDate;
         }
         if ($status != $invoice->status) {
            $invoice->status = $status;
         }
         $invoice->notes = $this->request->input("notes") ?? "";
         $invoice->save();
         $invoiceid = $invoice->id;
         $invoiceArr = array("source" => "api", "user" => $auth ? $auth->id : 0, "invoiceid" => $invoiceid, "status" => $status);
         foreach ($this->request->all() as $k => $v) {
            if (substr($k, 0, 10) == "itemamount") {
               $counter = substr($k, 10);
               $description = $this->request->input("itemdescription" . $counter);
               $amount = $this->request->input("itemamount" . $counter) ?? 0;
               $taxed = $this->request->input("itemtaxed" . $counter) ?? 0;
               if ($description) {
                  \App\Models\Invoiceitem::insert(array("invoiceid" => $invoiceid, "userid" => $userid, "description" => $description, "amount" => $amount, "taxed" => $taxed));
               }
            }
         }
         \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
         \App\Helpers\Hooks::run_hook("InvoiceCreation", $invoiceArr);
         if ($this->request->input('autoapplycredit') && $autoapplycredit) {
            $result = \App\Models\Client::where(array("id" => $userid))->first();
            $data = $result->toArray();
            $credit = $data["credit"];
            $result = \App\Models\Invoice::where(array("id" => $invoiceid))->first();
            $data = $result->toArray();
            $total = $data["total"];
            if (0 < $credit) {
               if ($total <= $credit) {
                  $creditleft = $credit - $total;
                  $credit = $total;
                  $doprocesspaid = true;
               } else {
                  $creditleft = 0;
               }
               LogActivity::Save("Credit Automatically Applied at Invoice Creation - Invoice ID: " . $invoiceid . " - Amount: " . $credit, $userid);
               \App\Models\Client::where('id', $userid)->update(array("credit" => $creditleft));
               \App\Models\Invoice::where('id', $invoiceid)->update(array("credit" => $credit));
               \App\Models\Credit::insert(array("clientid" => $userid, "date" => \Carbon\Carbon::now(), "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $credit * -1));
               \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
            }
         }
         if ($sendInvoice) {
            \App\Helpers\Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
            $where = array("gateway" => $paymentMethod, "setting" => "type");
            $result = \App\Models\Paymentgateway::where($where);
            $data = $result;
            $paymentType = $data->value("value") ?? "";
            $emailTemplate = $paymentType == "CC" || $paymentType == "OfflineCC" ? "Credit Card Invoice Created" : "Invoice Created";
            $template = \App\Models\Emailtemplate::where("name", $emailTemplate)->get()->first();
            \App\Helpers\Functions::sendMessage($template, $invoiceid);
         }
         if ($status != "Draft") {
            \App\Helpers\Hooks::run_hook("InvoiceCreated", $invoiceArr);
         }
         if ($doprocesspaid) {
            \App\Helpers\Invoice::processPaidInvoice($invoiceid);
         }

         DB::commit();
         return ResponseAPI::Success([
            "invoiceid" => $invoiceid,
            "status" => $status,
         ]);
      } catch (\Exception $e) {
         DB::rollback();
         return ResponseAPI::Error([
            'message' => $e->getMessage(),
         ]);
      }
   }



   /**
    * UpdateInvoice
    * 
    * Update an invoice using the provided parameters.
    */
   public function UpdateInvoice()
   {
      $auth = Auth::guard('admin')->user();

      $rules = [
         // The ID of the client to create the invoice for.
         'invoiceid' => ['required', 'integer', 'exists:App\Models\Invoice,id'],
         // The status of the invoice being updated.
         'status' => ['nullable', 'string'],
         // The payment method of the invoice in system format.
         'paymentmethod' => ['nullable', 'string'],
         // The first-level tax rate to apply to the invoice to override the system default.
         'taxrate' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The second-level tax rate to apply to the invoice to override the system default.
         'taxrate2' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // Update the credit applied to the invoice.
         'credit' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // The date that the invoice should show as its creation date. Format: YYYY-mm-dd
         'date' => ['nullable', 'date_format:Y-m-d'],
         // The due date of the invoice. Format: YYYY-mm-dd
         'duedate' => ['nullable', 'date_format:Y-m-d'],
         // The date paid for the invoice. Format: YYYY-mm-dd
         'datepaid' => ['nullable', 'date_format:Y-m-d'],
         // The notes to appear on the invoice.
         'notes' => ['nullable', 'string'],

         // An array of lineItemId => Description of items to change. The lineItemId is the ID of the item from the GetInvoice API command.
         'itemdescription' => ['nullable', 'array'],
         // An array of lineItemId => Description of items to change. The lineItemId is the ID of the item from the GetInvoice API command.
         'itemdescription.*' => ['nullable', 'string', 'distinct'],

         // An array of lineItemId => amount of items to change. Required if itemdescription is provided.
         'itemamount' => ['nullable', 'array'],
         // An array of lineItemId => amount of items to change. Required if itemdescription is provided.
         'itemamount.*' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/', 'distinct'],

         // An array of lineItemId => taxed of items to change Required if itemdescription is provided.
         'itemtaxed' => ['nullable', 'array'],
         // An array of lineItemId => taxed of items to change Required if itemdescription is provided.
         'itemtaxed.*' => ['nullable', 'boolean', 'distinct'],

         // The line items description. This should be a numerically indexed array of new line item descriptions.
         'newitemdescription' => ['nullable', 'array'],
         // The line items description. This should be a numerically indexed array of new line item descriptions.
         'newitemdescription.*' => ['nullable', 'string', 'distinct'],

         // The line items amount. This should be a numerically indexed array of new line item amounts.
         'newitemamount' => ['nullable', 'array'],
         // The line items amount. This should be a numerically indexed array of new line item amounts.
         'newitemamount.*' => ['nullable', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/', 'distinct'],

         // Should the new line items be taxed. This should be a numerically indexed array of new line item taxed values.
         'newitemtaxed' => ['nullable', 'array'],
         // Should the new line items be taxed. This should be a numerically indexed array of new line item taxed values.
         'newitemtaxed.*' => ['nullable', 'boolean', 'distinct'],

         // An array of line item IDs to remove from the invoice. This is the ID of the line item, from GetInvoice API command.
         'deletelineids' => ['nullable', 'string'],
         // An array of line item IDs to remove from the invoice. This is the ID of the line item, from GetInvoice API command.
         'deletelineids.*' => ['nullable', 'integer', 'distinct'],

         // Whether to publish the invoice.
         'publish' => ['nullable', 'boolean'],
         // Whether to publish and email the invoice.
         'publishandsendemail' => ['nullable', 'boolean'],
      ];

      $messages = [
         'invoiceid.exists' => "Invoice ID Not Found",
         'taxrate.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'taxrate2.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'credit.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'itemamount.*.regex' => ':Attribute must be in decimal format: ### or ###.##',
         'newitemamount.*.regex' => ':Attribute must be in decimal format: ### or ###.##',
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      $publish = $this->request->input("publish");
      $publishAndSendEmail = $this->request->input("publishandsendemail");
      $invoiceId = (int) $this->request->input("invoiceid");
      $itemDescription = $this->request->input("itemdescription");
      $itemAmount = $this->request->input("itemamount");
      $itemTaxed = $this->request->input("itemtaxed");
      $newItemDescription = $this->request->input("newitemdescription");
      $newItemAmount = $this->request->input("newitemamount");
      $newItemTaxed = $this->request->input("newitemtaxed");
      $deleteLineIds = $this->request->input("deletelineids");
      $status = $this->request->input("status");

      $invoice = \App\Models\Invoice::findOrFail($invoiceId);
      $userId = $invoice->clientId;

      if (($publish || $publishAndSendEmail) && $invoice->status != "Draft") {
         return ResponseAPI::Error([
            'message' => "Invoice must be in Draft status to be published",
         ]);
      }

      if ($status && !in_array($status, \App\Models\Invoice::getInvoiceStatusValues())) {
         return ResponseAPI::Error([
            'message' => "Invalid status " . $status,
         ]);
      }

      if ($itemDescription) {
         foreach ($itemDescription as $lineid => $description) {
            if (!array_key_exists($lineid, $itemAmount) || !array_key_exists($lineid, $itemTaxed)) {
               return ResponseAPI::Error([
                  'message' => "Missing Variables: itemdescription, itemamount" . " and itemtaxed are required for each item being changed",
               ]);
            }
            $amount = $itemAmount[$lineid];
            $taxed = $itemTaxed[$lineid];
            $update = array("userid" => $userId, "description" => $description, "amount" => $amount, "taxed" => $taxed, "invoiceid" => $invoiceId);
            DB::table("tblinvoiceitems")->where("id", "=", $lineid)->update($update);
         }
      }

      if ($newItemDescription) {
         $inserts = array();
         foreach ($newItemDescription as $k => $v) {
            $description = $v;
            $amount = $newItemAmount[$k];
            $taxed = $newItemTaxed[$k];
            $insert = array("invoiceid" => $invoiceId, "userid" => $userId, "description" => $description, "amount" => $amount, "taxed" => $taxed);
            $inserts[] = $insert;
         }
         if (0 < count($inserts)) {
            DB::table("tblinvoiceitems")->insert($inserts);
         }
      }

      if ($deleteLineIds) {
         DB::table("tblinvoiceitems")->where("invoiceid", "=", $invoiceId)->whereIn("id", $deleteLineIds)->delete();
      }

      $invoiceNum = $this->request->input("invoicenum");
      $date = $this->request->input("date");
      $dueDate = $this->request->input("duedate");
      $datePaid = $this->request->input("datepaid");
      $credit = $this->request->input("credit");
      $taxRate = $this->request->input("taxrate");
      $taxRate2 = $this->request->input("taxrate2");
      $paymentMethod = $this->request->input("paymentmethod");
      $notes = $this->request->input("notes");
      $changes = false;

      if ($invoiceNum) {
         $changes = true;
         $invoice->invoiceNumber = $invoiceNum;
      }
      if ($date) {
         $changes = true;
         $invoice->dateCreated = $date;
      }
      if ($dueDate) {
         $changes = true;
         $invoice->dateDue = $dueDate;
      }
      if ($datePaid) {
         $changes = true;
         $invoice->datePaid = $datePaid;
      }
      if ($credit) {
         $changes = true;
         $invoice->credit = $credit;
      }
      if ($taxRate) {
         $changes = true;
         $invoice->taxRate1 = $taxRate;
      }
      if ($taxRate2) {
         $changes = true;
         $invoice->taxRate2 = $taxRate2;
      }
      if ($status) {
         $changes = true;
         $invoice->status = $status;
      }
      if ($paymentMethod) {
         $changes = true;
         $invoice->paymentGateway = $paymentMethod;
      }
      if ($notes) {
         $changes = true;
         $invoice->adminNotes = $notes;
      }
      if ($changes) {
         $invoice->save();
      }

      \App\Helpers\Invoice::updateInvoiceTotal($invoiceId);
      if ($publish || $publishAndSendEmail) {
         $invoiceArr = array("source" => "api", "user" => $auth ? $auth->id : "system", "invoiceid" => $invoiceId, "status" => "Unpaid");
         $invoice = \App\Models\Invoice::find($invoiceId);
         $invoice->status = "Unpaid";
         $invoice->dateCreated = \Carbon\Carbon::now();
         $invoice->save();
         Hooks::run_hook("InvoiceCreation", $invoiceArr);
         if (!$paymentMethod) {
            $paymentMethod = \App\Helpers\Gateway::getClientsPaymentMethod($userId);
         }
         $paymentType = DB::table("tblpaymentgateways")->where("setting", "type")->where("gateway", $paymentMethod)->value("value");
         \App\Helpers\Invoice::updateInvoiceTotal($invoiceId);
         LogActivity::Save("Modified Invoice Options - Invoice ID: " . $invoiceId, $userId);
         if ($publishAndSendEmail) {
            Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
            $emailName = "Invoice Created";
            if (in_array($paymentType, array("CC", "OfflineCC"))) {
               $emailName = "Credit Card " . $emailName;
            }
            \App\Helpers\Functions::sendMessage($emailName, $invoiceId);
            Hooks::run_hook("InvoiceCreated", $invoiceArr);
         }
      }

      return ResponseAPI::Success([
         "invoiceid" => $invoiceId,
      ]);
   }

   /**
    * DeletePayMethod
    * 
    * Delete a Pay Method.
    */
   public function DeletePayMethod()
   {
      $rules = [
         // The id of the client matching the Pay Method
         'clientid' => ['required', 'integer', 'exists:App\Models\Client,id'],
         // The id of the Pay Method to delete
         'paymethodid' => ['required', 'integer', 'exists:App\Models\Paymethod,id'],
         // Pass as true to return an error if a remote token deletion fails
         'failonremotefailure' => ['nullable', 'boolean'],
      ];

      $messages = [
         'clientid.exists' => "Invalid Client ID",
         'paymethodid.exists' => "Invalid Pay Method ID",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $clientId = $this->request->input('clientid');
      $payMethodId = $this->request->input('paymethodid');

      $payMethod = \App\Models\Paymethod::find($payMethodId);
      if ($payMethod->userid != $clientId) {
         return ResponseAPI::Error([
            'message' => "Pay Method does not belong to passed Client ID",
         ]);
      }
      // next
   }

   /**
    * ApplyCredit
    * 
    * Applies the Client’s Credit to an invoice
    */
   public function ApplyCredit()
   {
      $rules = [
         // The ID of the invoice to apply credit
         'invoiceid' => ['required', 'integer', 'exists:App\Models\Invoice,id'],
         // The amount of credit to apply to the invoice.
         'amount' => ['required', 'regex:/^(?:[1-9]\d+|\d)(?:\.\d\d)?$/'],
         // Set to true to stop the invoice payment email being sent if the invoice becomes paid
         'noemail' => ['nullable', 'boolean'],
      ];

      $messages = [
         'invoiceid.exists' => "Invoice ID Not Found",
         'amount.regex' => "Amount must be in decimal format: ### or ###.##",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      // vars
      $invoiceid = $this->request->input('invoiceid');
      $amount = $this->request->input('amount');
      $noemail = (bool) $this->request->input('noemail');

      $data = \App\Models\Invoice::find($invoiceid);
      $data = $data->toArray();
      $invoiceid = $data["id"];
      $userid = $data["userid"];
      $credit = $data["credit"];
      $total = $data["total"];
      $status = $data["status"];
      $amountpaid = \App\Models\Account::selectRaw("SUM(amountin)-SUM(amountout) as sum")->where("invoiceid", $invoiceid)->value("sum") ?? 0;
      $balance = round($total - $amountpaid, 2);
      $amount = $amount == "full" ? $balance : round($amount, 2);
      $totalcredit = \App\Models\Client::select("credit")->where("id", $userid)->value("credit");
      if ($status != "Unpaid") {
         return ResponseAPI::Error([
            'message' => "Invoice Not in Unpaid Status",
         ]);
      }
      if ($totalcredit < $amount) {
         return ResponseAPI::Error([
            'message' => "Amount exceeds customer credit balance",
         ]);
      }
      if ($balance < $amount) {
         return ResponseAPI::Error([
            'message' => "Amount Exceeds Invoice Balance",
         ]);
      }
      if ($amount == "0.00") {
         return ResponseAPI::Error([
            'message' => "Credit Amount to apply must be greater than zero",
         ]);
      }

      $appliedamount = min($amount, $totalcredit);
      \App\Helpers\Invoice::applyCredit($invoiceid, $userid, $appliedamount, $noemail);

      return ResponseAPI::Success([
         "invoiceid" => $invoiceid,
         "amount" => $appliedamount,
         "invoicepaid" => \App\Models\Invoice::select("status")->where("id", $invoiceid)->value("status") == "Paid" ? "true" : "false",
      ]);
   }

   /**
    * AcceptQuote
    * 
    * Accept a quote using quoteid 
    *
    * @bodyParam quoteid required|integer The quote id to be accepted and converted to an invoice
    * 
    * @return \App\Helpers\ResponseAPI
    */
   public function AcceptQuote()
   {
      $rules = [
         'quoteid' => ['required', 'integer', 'exists:App\Models\Quote,id'],
      ];

      $messages = [
         'quoteid.exists' => "Quote ID Not Found",
      ];

      $validator = Validator::make($this->request->all(), $rules, $messages);

      if ($validator->fails()) {
         $error = $validator->errors()->first();

         return ResponseAPI::Error([
            'message' => $error,
         ]);
      }

      $quote = Quote::find($this->request->input("quoteid"));
      $invoiceid = \App\Helpers\QuoteHelper::convertQuotetoInvoice($quote->id);

      return ResponseAPI::Success([
         'invoiceid' => $invoiceid,
      ]);
   }
}
