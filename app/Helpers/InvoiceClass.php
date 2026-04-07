<?php

namespace App\Helpers;

use DB, Auth, App;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Format;
use \Carbon\Carbon;

class InvoiceClass
{
   protected $pdf = NULL;
   protected $invoiceId = 0;
   protected $data = array();
   protected $output = array();
   protected $totalBalance = 0;
   protected $gateway = NULL;
   protected $gatewayModulesWhereCallbacksMightBeDelayed = array("paypal");
   public function __construct($invoiceId = 0)
   {
      if ($invoiceId) {
         $this->setID($invoiceId);
      }
      //$this->pdf = App::make('dompdf.wrapper');
   }
   public function setID($invoiceId)
   {
      $this->invoiceId = $invoiceId;
      $loaded = $this->loadData();
      return $loaded;
   }
   public function getID()
   {
      return $this->invoiceId;
   }
   // private function parseDateToStr(){

   // }
   protected function loadData($force = true)
   {
      if (!$force && count($this->data)) {
         return false;
      }
      try {
         $invoiceModel = \App\Models\Invoice::findOrFail($this->invoiceId);
         $this->invoiceId = $invoiceModel->id;
         $invoiceData = $invoiceModel->toArray();
         // dd($invoiceModel->duedate);
         // dd($invoiceData['duedate']);
         // dd(\Carbon\Carbon::parse($invoiceData['duedate'])->format('dd-m-Y'));
         $invoiceData["model"] = $invoiceModel;
         $invoiceData["invoiceid"] = $invoiceData["id"];
         $invoiceData["invoicenumorig"] = $invoiceData["invoicenum"];
         if (!$invoiceData["invoicenum"]) {
            $invoiceData["invoicenum"] = $invoiceData["id"];
         }
         $invoiceData["paymentmodule"] = $invoiceData["paymentmethod"];
         $invoiceData["paymentmethod"] = $invoiceData["paymentGatewayName"];
         $invoiceData["rawDueDate"] = $invoiceData["duedate"];
         $invoiceData["payMethod"] = $invoiceModel->payMethod;
         $payMethodDisplayName = "";
         if ($invoiceModel->payMethod) {
            $payment = $invoiceModel->payMethod->payment;
            // TODO: $payment instanceof Payment\Contracts\PayMethodAdapterInterface
            // if ($payment instanceof Payment\Contracts\PayMethodAdapterInterface) {
            //     $payMethodDisplayName = $payment->getDisplayName();
            // }
         }
         $invoiceData["paymethoddisplayname"] = $payMethodDisplayName;
         $invoiceData["amountpaid"] = $invoiceData["amountPaid"];
         $invoiceData["balance"] = sprintf("%01.2f", $invoiceData["balance"]);
         $this->data = $invoiceData;
         return true;
      } catch (\Exception $e) {
         $this->invoiceId = 0;
         throw new \App\Exceptions\Module\NotServicable("Invalid invoice id provided");
      }
   }
   public function getData($var = "")
   {
      $this->loadData(false);
      return isset($this->data[$var]) ? $this->data[$var] : $this->data;
   }
   public function getStatuses()
   {
      return array("Draft", "Unpaid", "Paid", "Cancelled", "Refunded", "Collections", "Payment Pending");
   }
   public function getModel()
   {
      $model = $this->getData("model");
      if ($model instanceof \App\Models\Invoice) {
         return $model;
      }
      return null;
   }
   public function isAllowed($uid = 0)
   {
      $this->loadData(false);
      if (!$uid) {
         $auth = Auth::guard('web')->user();
         $uid = $auth->id;
      }
      if (!$uid || $this->data["status"] == "Draft" || isset($this->data["userid"]) && $this->data["userid"] != $uid) {
         return false;
      }
      return true;
   }
   protected function formatForOutput()
   {
      global $currency;
      $this->output = $this->data;
      // $this->output["taxrate"] = substr_replace($this->output["taxrate"], "", -1);
      // $this->output["taxrate2"] = substr_replace($this->output["taxrate2"], "", -1);
      $array = array("date", "duedate", "datepaid");
      foreach ($array as $v) {
         $this->output[$v] = substr($this->output[$v], 0, 10) != "0000-00-00" ? (new \App\Helpers\Functions())->fromMySQLDate($this->output[$v], $v == "datepaid" ? "1" : "0", 1) : "";
      }
      $this->output["datecreated"] = $this->output["date"];
      $this->output["datedue"] = $this->output["duedate"];
      $currency = \App\Helpers\Format::getCurrency($this->getData("userid"));
      $array = array("subtotal", "credit", "tax", "tax2", "total", "balance", "amountpaid");
      foreach ($array as $v) {
         $this->output[$v] = \App\Helpers\Format::formatCurrency($this->output[$v]);
      }
      if ($snapshotData = $this->getClientSnapshotData()) {
         $clientsdetails = $snapshotData["clientsdetails"];
         $customfields = array();
         foreach ($snapshotData["customfields"] as $data) {
            $data["fieldname"] = \App\Models\CustomField::getFieldName($data["id"], $data["fieldname"], $clientsdetails["language"]);
            $customfields[] = $data;
         }
      } else {
         $clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($this->getData("userid"), "billing");
         $customfields = array();
         $result = \App\Models\Customfield::select("tblcustomfields.id", "tblcustomfields.fieldname", DB::raw("(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.fieldid=tblcustomfields.id AND tblcustomfieldsvalues.relid=" . (int) $this->getData("userid") . ") AS value"))
            ->where("type", "client")
            ->where("showinvoice", "on")
            ->get();
         foreach ($result->toArray() as $data) {
            if ($data["value"]) {
               $data["fieldname"] = \App\Models\CustomField::getFieldName($data["id"], $data["fieldname"], $clientsdetails["language"]);
               $customfields[] = $data;
            }
         }
      }
      $clientsdetails["country"] = $clientsdetails["countryname"];
      if (\App\Helpers\Vat::isTaxIdDisabled()) {
         $clientsdetails["tax_id"] = "";
      }
      $this->output["clientsdetails"] = $clientsdetails;
      $this->output["customfields"] = $customfields;
      $taxData1 = \App\Helpers\Invoice::getTaxRate(1, $clientsdetails["state"], $clientsdetails["countrycode"]);
      $taxData2 = \App\Helpers\Invoice::getTaxRate(2, $clientsdetails["state"], $clientsdetails["countrycode"]);
      $taxName1 = $taxData1["name"];
      $taxName2 = $taxData2["name"];
      if ($taxName1 != "") {
         $this->output["taxname"] = $taxName1;
      } else {
         $this->output["taxname"] = "";
         $this->output["taxrate"] = "0";
      }
      if ($taxName2 != "") {
         $this->output["taxname2"] = $taxName2;
      } else {
         $this->output["taxname2"] = "";
         $this->output["taxrate2"] = "0";
      }
      $this->output["taxIdLabel"] = \Lang::get(\App\Helpers\Vat::getLabel());
      $this->output["statuslocale"] = \Lang::get("client.invoices" . strtolower($this->output["status"]));
      if ($this->output["status"] == "Payment Pending") {
         $this->output["statuslocale"] = \Lang::get("client.invoicesPaymentPending");
      }
      if ($this->isProformaInvoice()) {
         $this->output["pagetitle"] = \Lang::get("client.proformainvoicenumber") . $this->getData("invoicenum");
      } else {
         $this->output["pagetitle"] = \Lang::get("client.invoicenumber") . $this->getData("invoicenum");
      }
      $this->output["payto"] = nl2br(Cfg::getValue("InvoicePayTo"));
      $this->output["notes"] = nl2br($this->output["notes"]);
      $i_subscriptionid = \App\Models\Invoiceitem::select("tblhosting.subscriptionid")->where("tblinvoiceitems.type", "Hosting")->where("tblinvoiceitems.invoiceid", $this->getData("id"))->where("tblhosting.subscriptionid", "!=", "")->orderBy("tblhosting.id", "ASC")->join("tblhosting", "tblhosting.id", "=", "tblinvoiceitems.relid")->value("tblhosting.subscriptionid");
      $this->output["subscrid"] = $i_subscriptionid;
      $clienttotalsQuery = \App\Models\Invoice::selectRaw("SUM(credit) as credit, SUM(total) as total")->where("userid", 11)->where("status", "Unpaid");
      $credits = $clienttotalsQuery->value("credit");
      $totals = $clienttotalsQuery->value("total");
      $clienttotals[] = $credits;
      $clienttotals[] = $totals;
      $unpaidInvoiceIds = DB::table("tblinvoices")->where("status", "Unpaid")->where("userid", (int) $this->getData("userid"))->pluck("id");
      $alldueinvoicespayments = 0;
      if ($unpaidInvoiceIds) {
         $alldueinvoicespayments = \App\Models\Account::selectRaw("SUM(amountin-amountout) as sum")->whereIn("tblaccounts.invoiceid", $unpaidInvoiceIds)->value('sum');
      }
      $this->output["clientdepositbalance"] = \App\Helpers\Format::formatCurrency($clientsdetails['credit'], $clientsdetails['currency']);
      $this->output["clienttotaldue"] = \App\Helpers\Format::formatCurrency($clienttotals[0] + $clienttotals[1]);
      $this->output["clientpreviousbalance"] = \App\Helpers\Format::formatCurrency($clienttotals[1] - $this->getData("total"));
      $this->output["clientbalancedue"] = \App\Helpers\Format::formatCurrency($clienttotals[1] - $alldueinvoicespayments);
      $lastpaymentQuery = \App\Models\Account::selectRaw("(amountin-amountout) as sum, transid")->where("invoiceid", $this->getData("id"))->orderBy("id", "DESC");
      $lastpayment[] = $lastpaymentQuery->value("sum");
      $lastpayment[] = $lastpaymentQuery->value("transid");
      $this->output["lastpaymentamount"] = \App\Helpers\Format::formatCurrency($lastpayment[0]);
      $this->output["lastpaymenttransid"] = $lastpayment[1];
      $this->output["taxCode"] = Cfg::getValue("TaxCode");
   }
   public function getOutput($pdf = false)
   {
      $this->loadData(false);
      // TODO: $existingLanguage = getUsersLang($this->data["userid"]);
      $this->formatForOutput();
      // if ($existingLanguage) {
      //     swapLang($existingLanguage);
      // }
      if ($pdf) {
         $this->makePDFFriendly();
      }
      return $this->output;
   }
   public function initialiseGatewayAndParams($passedInGatewayModuleName = "")
   {
      $this->gateway = new \App\Module\Gateway();
      if ($passedInGatewayModuleName) {
         $gatewaymodule = $passedInGatewayModuleName;
      } else {
         $gatewaymodule = $this->getData("paymentmodule");
      }
      $gatewaymodule = strtolower($gatewaymodule);
      if (!$this->gateway->isActiveGateway($gatewaymodule)) {
         if ($passedInGatewayModuleName) {
            throw new \App\Exceptions\Module\NotActivated("Gateway Module '" . \App\Helpers\Sanitize::makeSafeForOutput($gatewaymodule) . "' Not Activated");
         }
         $gatewaymodule = $this->gateway->getFirstAvailableGateway();
         if (!$gatewaymodule) {
            throw new \App\Exceptions\Information("No Gateway Modules are Currently Active");
         }
         \App\Models\Invoice::where("id", $this->getID())->update(array("paymentmethod" => $gatewaymodule));
      }
      if (!$this->gateway->load($gatewaymodule)) {
         LogActivity::Save("Gateway Module '" . $gatewaymodule . "' is Missing");
         throw new \App\Exceptions\Module\NotServicable("Gateway Module '" . \App\Helpers\Sanitize::makeSafeForOutput($gatewaymodule) . "' is Missing or Invalid");
      }
      $params = $this->gateway->loadSettings();
      if (!$params) {
         throw new \App\Exceptions\Module\InvalidConfiguration("No Gateway Settings Found");
      }
      $params["companyname"] = Cfg::getValue("CompanyName");
      $params["systemurl"] = config('app.url');
      $params["langpaynow"] = \Lang::get("client.invoicespaynow");
      return $params;
   }
   public function getGatewayInvoiceParams(array $params = array())
   {
      if (count($params) < 1) {
         try {
            $params = $this->initialiseGatewayAndParams();
         } catch (\Exception $e) {
            LogActivity::Save("Failed to initialise payment gateway module: " . $e->getMessage());
            throw new \App\Exceptions\Fatal("Could not initialise payment gateway. Please contact support.");
         }
      }
      $invoiceid = $this->getID();
      $userid = $this->getData("userid");
      $invoicenum = $this->getData("invoicenum");
      $balance = $this->getData("balance");
      $invoiceModel = \App\Models\Invoice::find($invoiceid);
      $result = \App\Models\Client::select("tblclients.currency", "tblcurrencies.code")->where("tblclients.id", $userid)->join("tblcurrencies", "tblcurrencies.id", "=", "tblclients.currency")->first();
      $data = $result->toArray();
      $invoice_currency_id = $data["currency"];
      $invoice_currency_code = $data["code"];
      $params["invoiceid"] = $invoiceid;
      $params["invoicenum"] = $invoicenum;
      $params["amount"] = $balance;
      $params["description"] = $params["companyname"] . " - " . \Lang::get("client.invoicenumber") . ($invoicenum ? $invoicenum : $invoiceid);
      // TODO: $params["returnurl"] = $params["systemurl"] . "viewinvoice.php?id=" . $invoiceid;
      $params["returnurl"] = route('pages.services.mydomains.viewinvoiceweb', ['id' => $invoiceid]);
      $params["dueDate"] = $this->getData("duedate");
      $client = new \App\Helpers\ClientClass($userid);
      $billingContactId = null;
      if (!$invoiceModel && $invoiceModel->payMethod) {
         $billingContactId = $invoiceModel->payMethod->getContactId();
      }
      if (is_null($billingContactId) && isset($params["billingcontactid"])) {
         $billingContactId = $params["billingcontactid"];
      }
      if (is_null($billingContactId)) {
         $billingContactId = "billing";
      }
      $clientsdetails = $client->getDetails($billingContactId);
      $clientsdetails["state"] = $clientsdetails["statecode"];
      if (!strlen($clientsdetails["gatewayid"])) {
         $relevantPayMethods = $payMethod = \App\Models\Paymethod::where("userid", $client->getID())->where("gateway_name", $params["paymentmethod"])->get();
         $payMethod = null;
         if ($relevantPayMethods->count()) {
            if (\Session::get("cartccdetail")) {
               $cartCcDetail = unserialize(base64_decode((new \App\Helpers\Pwd())->decrypt(\Session::get("cartccdetail"))));
               $ccInfo = $cartCcDetail[9];
               if (is_numeric($ccInfo)) {
                  $payMethod = $relevantPayMethods->find($ccInfo);
                  if ($payMethod && $invoiceModel) {
                     $invoiceModel->payMethod()->associate($payMethod);
                     $invoiceModel->save();
                  }
               }
            }
            if (!$payMethod && $invoiceModel->payMethod) {
               $payMethod = $invoiceModel->payMethod;
            }
            if (!$payMethod) {
               $payMethod = $relevantPayMethods->first();
            }
         }
         if ($payMethod) {
            $payment = $payMethod->payment;
            if ($payment instanceof \App\Payment\Contracts\RemoteTokenDetailsInterface) {
                $clientsdetails["gatewayid"] = $payment->getRemoteToken();
            }
         }
      }
      $params["clientdetails"] = $clientsdetails;
      $params["gatewayid"] = $clientsdetails["gatewayid"];
      if (isset($params["convertto"]) && $params["convertto"]) {
         $result = \App\Models\Currency::select('code')->where('id', (int) $params["convertto"])->first();
         $data = $result->toArray();
         $converto_currency_code = $data["code"];
         $converto_amount = \App\Helpers\Format::convertCurrency($balance, $invoice_currency_id, $params["convertto"]);
         $params["amount"] = \App\Helpers\Functions::format_as_currency($converto_amount);
         $params["currency"] = $converto_currency_code;
         $params["currencyId"] = (int) $params["convertto"];
         $params["basecurrencyamount"] = \App\Helpers\Functions::format_as_currency($balance);
         $params["basecurrency"] = $invoice_currency_code;
         $params["baseCurrencyId"] = $invoice_currency_id;
      }
      if (!isset($params["currency"]) || !$params["currency"]) {
         $params["amount"] = \App\Helpers\Functions::format_as_currency($balance);
         $params["currency"] = $invoice_currency_code;
         $params["currencyId"] = $invoice_currency_id;
      }
      return $params;
   }
   public function getPaymentLink()
   {
      try {
         $params = $this->initialiseGatewayAndParams();
      } catch (\Exception $e) {
         LogActivity::Save("Failed to initialise payment gateway module: " . $e->getMessage());
         return false;
      }
      $params = $this->getGatewayInvoiceParams($params);
      if (!$this->gateway->functionExists("link")) {
         // TODO: eval("function " . $this->gateway->getLoadedModule() . "_link(\$params) { return '<form method=\"get\" action=\"'.\$params['systemurl'].'creditcard.php\" name=\"paymentfrm\"><input type=\"hidden\" name=\"invoiceid\" value=\"'.\$params['invoiceid'].'\"><button type=\"submit\" class=\"btn btn-success btn-sm\" id=\"btnPayNow\"><i class=\"fas fa-credit-card\"></i>&nbsp; ' . \$params['langpaynow'] . '</button></form>'; }");
        //  $paymentbutton = "";
        return "<form method=\"get\" action=\"{$params['systemurl']}/creditcard.php\" name=\"paymentfrm\"><input type=\"hidden\" name=\"invoiceid\" value=\"{$params['invoiceid']}\"><button type=\"submit\" class=\"btn btn-success btn-sm\" id=\"btnPayNow\"><i class=\"fas fa-credit-card\"></i>&nbsp; {$params['langpaynow']}</button></form>";
      }
      $paymentbutton = $this->gateway->call("link", $params);
      return $paymentbutton;
   }
   public function getLineItems($entityDecode = false)
   {
      // TODO: getUsersLang($this->getData("userid"));
      $invoiceid = $this->getID();
      $invoiceitems = array();
      if (Cfg::getValue("GroupSimilarLineItems")) {
         $result = \App\Models\Invoiceitem::select(DB::raw("COUNT(*) as qty"), "id", "type", "relid", "description", "amount", "taxed")->where("invoiceid", (int) $invoiceid)->groupByRaw('description, amount')->orderBy("id", "ASC")->get();
      } else {
         $result = \App\Models\Invoiceitem::select(DB::raw("0 as qty"), "id", "type", "relid", "description", "amount", "taxed")->where("invoiceid", $invoiceid)->orderBy("id", "ASC")->get();
      }
      foreach ($result->toArray() as $data) {
         $qty = $data["qty"];
         $description = $data["description"];
         $amount = $data["amount"];
         $taxed = $data["taxed"] ? true : false;
         if (1 < $qty) {
            $description = $qty . " x " . $description . " @ " . $amount . \Lang::get("client.invoiceqtyeach");
            $amount *= $qty;
         }
         if ($entityDecode) {
            $description = htmlspecialchars(\App\Helpers\Sanitize::decode($description));
         } else {
            $description = nl2br($description);
         }

         if ($taxed) {
            $getTaxData = $this->getData();
            $taxAmount = $getTaxData['tax'];
            $taxrate = $getTaxData['taxrate'];
         }
         $invoiceitems[] = array(
            "id" => (int) $data["id"],
            "type" => $data["type"],
            "relid" => (int) $data["relid"],
            "description" => $description,
            "rawamount" => $amount,
            "amount" => \App\Helpers\Format::formatCurrency($amount),
            "taxed" => $taxed ?? "",
            "taxamount" => $taxAmount ?? "",
            "taxrate" => $taxrate ?? "",
         );
      }
      return $invoiceitems;
   }
   public function getTransactions()
   {
      $invoiceid = $this->invoiceId;
      $transactions = array();
      $result = \App\Models\Account::select("id", "date", "transid", "amountin", "amountout", DB::raw("(SELECT value FROM tblpaymentgateways WHERE gateway=tblaccounts.gateway AND setting='name' LIMIT 1) AS gateway"))->where("invoiceid", $invoiceid)->orderBy("date", "ASC")->orderBy("id", "ASC")->get();
      foreach ($result->toArray() as $data) {
         $tid = $data["id"];
         $date = $data["date"];
         $gateway = $data["gateway"];
         $amountin = $data["amountin"];
         $amountout = $data["amountout"];
         $transid = $data["transid"];
         $date = (new \App\Helpers\Functions())->fromMySQLDate($date, 0, 1);
         if (!$gateway) {
            $gateway = "-";
         }
         $transactions[] = array("id" => $tid, "date" => $date, "gateway" => $gateway, "transid" => $transid, "amount" => \App\Helpers\Format::formatCurrency($amountin - $amountout));
      }
      return $transactions;
   }
   public function pdfCreate()
   {
      $this->pdf = App::make('dompdf.wrapper');
      return $this->pdf;
   }
   protected function makePDFFriendly()
   {
      $this->output["companyname"] = Cfg::getValue("CompanyName");
      $this->output["companyurl"] = Cfg::getValue("Domain");
      $companyAddress = Cfg::getValue("InvoicePayTo");
      $this->output["companyaddress"] = explode("\n", $companyAddress);
      if (trim($this->output["notes"])) {
         $this->output["notes"] = str_replace("<br />", "", $this->output["notes"]) . "\n";
      }
      $this->output = \App\Helpers\Sanitize::decode($this->output);
      return true;
   }
   public function pdfInvoicePage($invoiceId = 0)
   {
      if ($invoiceId) {
         try {
            $this->setID($invoiceId);
         } catch (\Exception $e) {
            return false;
         }
      }
      $tplvars = $this->getOutput(true);
      //   \Log::debug('==TPLVARS==');
      //   \Log::debug($tplvars);
      $tplvars["invoiceitems"] = $this->getLineItems(true);
      $tplvars["transactions"] = $this->getTransactions();
      // $tplvars["imgpath"] = $assetHelper->getFilesystemImgPath();
      $tplvars["pdfFont"] = Cfg::getValue("TCPDFFont");
      $this->pdfAddPage("pdf.invoice", $tplvars);
      return true;
   }
   public function pdfAddPage($tplfile, array $tplvars)
   {
      global $_LANG;
      $tplvars['pdf'] = $this->pdf;
      $tplvars['credit'] = Format::Currency($tplvars['credit']->toNumeric(), null, ['prefix' => $tplvars['credit']->getCurrency()['prefix'] . ' ', 'format' => $tplvars['credit']->getCurrency()['format']]);
      $tplvars['total'] = Format::Currency($tplvars['total']->toNumeric(), null, ['prefix' => $tplvars['total']->getCurrency()['prefix'] . ' ', 'format' => $tplvars['total']->getCurrency()['format']]);
      $tplvars['logo'] = \App\Helpers\Cfg::get('LogoURL');
      $this->pdf->loadView($tplfile, $tplvars);
      $this->pdf->stream();
      return true;
   }
   public function pdfOutput()
   {
      // return $this->pdf->stream();
      return $this->pdf->output();
   }

   public function addStringAttachment()
   {
      // return $this->pdf->stream();
      return $this->pdf->output();
   }
   public function getInvoices($status = "", $userid = 0, $orderby = "id", $sort = "DESC", $limit = "", $excludeDraftInvoices = true)
   {
      $where = array();
      if ($status) {
         $where[] = "status = '" . \App\Helpers\Database::db_escape_string($status) . "'";
      }
      if ($userid) {
         $where[] = "userid = " . (int) $userid;
      }
      if ($excludeDraftInvoices) {
         $where[] = "status != 'Draft'";
      }
      $where[] = "(select count(id) from tblinvoiceitems where invoiceid=tblinvoices.id and type='Invoice')<=0";
      $invoices = array();
      $result = \App\Models\Invoice::query();
      $result->selectRaw("tblinvoices.*, total-IFNULL((SELECT SUM(amountin-amountout) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id),0) AS balance");
      $result->whereRaw(implode(" AND ", $where));
      $result->orderBy($orderby, $sort);
      if ($limit) {
         $result->limit($limit);
      }
      $result->get();
      foreach ($result->toArray() as $data) {
         $id = $data["id"];
         $invoicenum = $data["invoicenum"];
         $date = $data["date"];
         $normalisedDate = $date;
         $duedate = $data["duedate"];
         $normalisedDueDate = $duedate;
         $credit = $data["credit"];
         $total = $data["total"];
         $balance = $data["balance"];
         $status = $data["status"];
         if ($status == "Unpaid") {
            $this->totalBalance += $balance;
         }
         $date = (new \App\Helpers\Functions())->fromMySQLDate($date, 0, 1);
         $duedate = (new \App\Helpers\Functions())->fromMySQLDate($duedate, 0, 1);
         $rawstatus = strtolower($status);
         if (!$invoicenum) {
            $invoicenum = $id;
         }
         $totalnum = $credit + $total;
         $statusText = \Lang::get("invoices" . $rawstatus);
         if ($rawstatus == "payment pending") {
            $statusText = \Lang::get("invoicesPayment Pending");
         }
         $invoices[] = array("id" => $id, "invoicenum" => $invoicenum, "datecreated" => $date, "normalisedDateCreated" => $normalisedDate, "datedue" => $duedate, "normalisedDateDue" => $normalisedDueDate, "totalnum" => $totalnum, "total" => \App\Helpers\Format::formatCurrency($totalnum), "balance" => \App\Helpers\Format::formatCurrency($balance), "status" => \App\Helpers\Invoice::getInvoiceStatusColour($status), "statusClass" => \App\Helpers\ViewHelper::generateCssFriendlyClassName($status), "rawstatus" => $rawstatus, "statustext" => $statusText);
      }
      return $invoices;
   }
   public function getTotalBalance()
   {
      return $this->totalBalance;
   }
   public function getTotalBalanceFormatted()
   {
      return \App\Helpers\Format::formatCurrency($this->getTotalBalance());
   }
   public function getEmailTemplates()
   {
      $names = array("Invoice Created", "Credit Card Invoice Created", "Invoice Payment Reminder", "First Invoice Overdue Notice", "Second Invoice Overdue Notice", "Third Invoice Overdue Notice", "Credit Card Payment Due", "Credit Card Payment Failed", "Invoice Payment Confirmation", "Credit Card Payment Confirmation", "Invoice Refund Confirmation");
      switch ($this->getData("status")) {
         case "Paid":
            $extraNames = array("Invoice Payment Confirmation", "Credit Card Payment Confirmation");
            break;
         case "Refunded":
            $extraNames = array("Invoice Refund Confirmation");
            break;
         default:
            $extraNames = array();
            break;
      }
      $sortedTemplates = array();
      $names = array_merge($extraNames, $names);
      $templates = \App\Models\Emailtemplate::where("type", "=", "invoice")->where("language", "=", "")->whereIn("name", $names)->get();
      foreach ($names as $name) {
         foreach ($templates as $i => $template) {
            if ($template->name == $name) {
               $sortedTemplates[] = $template;
               unset($templates[$i]);
               continue;
            }
         }
      }
      return $sortedTemplates;
   }
   public function isAddFundsInvoice()
   {
      $numaddfunditems = \App\Models\Invoiceitem::where("invoiceid", $this->getID())->where("type", "AddFunds")->count();
      $numtotalitems = \App\Models\Invoiceitem::where("invoiceid", $this->getID())->count();
      return $numaddfunditems == $numtotalitems ? true : false;
   }
   public static function isValidCustomInvoiceNumberFormat($format)
   {
      $replaceValues = array("{YEAR}", "{MONTH}", "{DAY}", "{NUMBER}");
      $replaceData = array(date("Y"), date("m"), date("d"), "1");
      $format = str_replace($replaceValues, $replaceData, $format);
      $cleanedPopulatedFormat = preg_replace("/[^[:word:] {}!@€#£\$&()-=+\\[\\]]/", "", $format);
      if ($cleanedPopulatedFormat == $format) {
         return true;
      }
      return false;
   }
   public function isProformaInvoice()
   {
      if (Cfg::getValue("EnableProformaInvoicing") && $this->getData("status") != "Paid") {
         return true;
      }
      return false;
   }
   public static function saveClientSnapshotData($invoiceId)
   {
      if (!Cfg::getValue("StoreClientDataSnapshotOnInvoiceCreation")) {
         return false;
      }
      try {
         $invoice = \App\Models\Invoice::findOrFail($invoiceId);
      } catch (\Exception $e) {
         \Log::debug("Invoice Save Client Data Snapshot: Got invalid invoice id or client missing");
         return false;
      }
      $client = new \App\Helpers\ClientClass($invoice->client);
      $clientsDetails = $client->getDetails("billing");
      unset($clientsDetails["model"]);
      $customFields = array();
      $result = \App\Models\Customfield::selectRaw("tblcustomfields.id,tblcustomfields.fieldname,(SELECT value FROM tblcustomfieldsvalues WHERE tblcustomfieldsvalues.fieldid=tblcustomfields.id AND tblcustomfieldsvalues.relid=" . (int) $invoice->userId . ") AS value")->where("type", "client")->where("showinvoice", "on")->get();
      foreach ($result->toArray() as $data) {
         if ($data["value"]) {
            $customFields[] = $data;
         }
      }
      \App\Models\Billing\Invoice\Snapshot::firstOrCreate(array("invoiceid" => $invoiceId, "clientsdetails" => $clientsDetails, "customfields" => $customFields));
      return true;
   }
   protected function getClientSnapshotData()
   {
      if (!Cfg::getValue("StoreClientDataSnapshotOnInvoiceCreation")) {
         return null;
      }
      try {
         $snapshotData = \App\Models\Billing\Invoice\Snapshot::findOrFail($this->getID());
         return array("clientsdetails" => $snapshotData->clientsDetails, "customfields" => $snapshotData->customFields);
      } catch (\Exception $e) {
         return null;
      }
   }
   public static function getUserIdByInvoiceId($invoiceId)
   {
      return DB::table("tblinvoices")->where("id", "=", (int) $invoiceId)->value("userid");
   }
   public function isAssignedGatewayWithDelayedCallbacks()
   {
      return in_array($this->getData("paymentmodule"), $this->gatewayModulesWhereCallbacksMightBeDelayed);
   }
   public function showPaymentSuccessAwaitingNotificationMsg($paymentSuccessful = false)
   {
      return $paymentSuccessful == true && $this->getData("status") == "Unpaid" && $this->isAssignedGatewayWithDelayedCallbacks();
   }
}
