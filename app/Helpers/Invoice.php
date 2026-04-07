<?php
namespace App\Helpers;

use DB, Auth;
use LogActivity, Cfg;

// Import Model Class here
use App\Models\Invoice as InvoiceModel;
use App\Models\Invoiceitem;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Currency;
use App\Models\Account;
use App\Models\Domain;
use App\Models\Pricing;
use App\Models\Hosting;
use App\Models\Order;
use App\Models\Product;

// Import Package Class here
use App\Helpers\Hooks;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

// Import Helpers Class here
use App\Helpers\AdminFunctions;
use App\Helpers\SystemHelper;
use Carbon\Carbon;

class Invoice
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * RefundCreditOnStatusChange
	 *
	 * @param $invoiceId
	 * @param $status default to Fraud
	 *
	 */
	public static function RefundCreditOnStatusChange($invoiceId, $status = "Fraud")
	{
		$invoice = InvoiceModel::find($invoiceId);
		$creditAmount = $invoice->credit;
    	$userId = $invoice->userid;

		if (0 < $creditAmount) {
			$invoice->credit = 0;
			$invoice->save();
			self::UpdateInvoiceTotal($invoiceId);

			$client = Client::find($userId);
			// $client->credit += $creditAmount;
			$client->increment('credit', $creditAmount);
			// $client->save();

			$credit = new Credit;
			$credit->clientid = $userId;
			$credit->date = \Carbon\Carbon::now()->format("Y-m-d");
			$credit->description = "Credit Removed from Invoice #" . $invoiceId . " due to Order Status being changed to " . $status;
			$credit->amount = $creditAmount;
			$credit->save();

			LogActivity::Save("Credit Removed from Invoice ID: " . $invoiceId . " due to Order Status being changed to " . $status . " - Amount: " . $creditAmount, $userId);
		}
	}

	/**
	 * UpdateInvoiceTotal
	 *
	 * @param $id invoice id
	 */
	public static function UpdateInvoiceTotal($id)
	{
		$taxsubtotal = 0;
    	$nontaxsubtotal = 0;

		$invoice = InvoiceModel::find($id);
		$userid = $invoice->userid;
		$credit = $invoice->credit;
		$taxrate = $invoice->taxrate;
		$taxrate2 = $invoice->taxrate2;

		$client = new \App\Helpers\Client;
		$clientsdetails = $client->GetClientsDetails(['clientid' => $userid]);

		$taxCalculator = new \App\Helpers\Tax();
		$taxCalculator->setIsInclusive(Cfg::get("TaxType") == "Inclusive")->setIsCompound(Cfg::get("TaxL2Compound"));
		if (is_numeric($taxrate)) {
				$taxCalculator->setLevel1Percentage($taxrate);
		}
		if (is_numeric($taxrate2)) {
				$taxCalculator->setLevel2Percentage($taxrate2);
		}
		$tax = $tax2 = 0;

		$result = Invoiceitem::where('invoiceid', $id)->get();
		foreach ($result->toArray() as $data) {
			if ($data["taxed"] == "1" && Cfg::get("TaxEnabled") == "on" && !$clientsdetails["taxexempt"]) {
				if (Cfg::get("TaxPerLineItem")) {
					$taxCalculator->setTaxBase($data["amount"]);
					$tax += $taxCalculator->getLevel1TaxTotal();
					$tax2 += $taxCalculator->getLevel2TaxTotal();
					$taxsubtotal += $taxCalculator->getTotalBeforeTaxes();
				} else {
					$taxsubtotal += $data["amount"];
				}
			} else {
				$nontaxsubtotal += $data["amount"];
			}
		}

		if (!Cfg::get("TaxPerLineItem")) {
			$taxCalculator->setTaxBase($taxsubtotal);
			$tax = $taxCalculator->getLevel1TaxTotal();
			$tax2 = $taxCalculator->getLevel2TaxTotal();
			$taxsubtotal = $taxCalculator->getTotalBeforeTaxes();
		}
		$subtotal = $nontaxsubtotal + $taxsubtotal;
		$total = $subtotal + $tax + $tax2;
		if (0 < $credit) {
			if ($total < $credit) {
				$total = 0;
				$remainingcredit = $total - $credit;
			} else {
				$total -= $credit;
			}
		}

		$invoice->subtotal = $subtotal;
		$invoice->tax = $tax;
		$invoice->tax2 = $tax2;
		$invoice->total = $total;
		$invoice->save();

		\App\Helpers\Hooks::run_hook("UpdateInvoiceTotal", array("invoiceid" => $id));
	}

	/**
	 * addInvoicePayment
	 */
	public static function addInvoicePayment($invoiceId, $transactionId, $amount, $fees, $gateway, $noEmail = false, $date = NULL)
	{
		try {
			$invoice = InvoiceModel::findOrFail($invoiceId);
			if (!$amount) {
				$amount = $invoice->balance;
				if ($amount <= 0) {
					throw new \App\Exceptions\Module\NotServicable("Invoice Amount Invalid");
				}
			}
			if ($date && !$date instanceof \App\Helpers\Carbon) {
				$date = \App\Helpers\Carbon::createFromFormat("Y-m-d", (new \App\Helpers\SystemHelper())->toMySQLDate($date));
			}
			if (!$date instanceof \App\Helpers\Carbon) {
				$date = NULL;
			}
			return $invoice->addPayment($amount, $transactionId, $fees, $gateway, (bool) $noEmail, $date);
		} catch (\Exception $e) {
			// return $e->getMessage();
			return false;
		}
	}

	public static function AddPayment($amount, $transactionId, $fees, $gateway, $noEmail, $date, \App\Models\Invoice $invoice)
	{
		$pfx = \Database::prefix();

		if (!$amount) {
            throw new \Exception("Amount is Required");
        }

        if ($amount < 0) {
            throw new \Exception("Payment Amount Must be Greater than Zero");
        }

		$invoiceId = $invoice->id;
        if (!$gateway) {
            $gateway = $invoice->paymentmethod;
        }

		$userId = $invoice->userid;
        $status = $invoice->status;

		if (in_array($status, ["Cancelled", "Draft"])) {
            throw new \Exception("Payments can only be applied to invoices in Unpaid, Paid, Refunded or Collections statuses");
        }

		if (!$date) {
            $date = \Carbon\Carbon::now();
        }

        self::addTransaction($userId, 0, "Invoice Payment", $amount, $fees, 0, $gateway, $transactionId, $invoiceId, (new \App\Helpers\Client())->fromMySQLDate($date->toDateTimeString()));
		$balance = \App\Helpers\Functions::format_as_currency($invoice->balance);

		\LogActivity::save("Added Invoice Payment - Invoice ID: " . $invoiceId, $userId);
        Hooks::run_hook("AddInvoicePayment", ["invoiceid" => $invoiceId]);

		if ($balance <= 0 && in_array($status, ["Unpaid", "Payment Pending"])) {
            self::processPaidInvoice($invoiceId, $noEmail, (new \App\Helpers\Client())->fromMySQLDate($date));
        } else {
            if (!$noEmail) {
                Functions::sendMessage("Invoice Payment Confirmation", $invoiceId);
            }
        }

		if ($balance <= 0) {
            $amountCredited = \DB::table("{$pfx}credit")->where("relid", $invoiceId)->sum("amount");
            $balance = $balance + $amountCredited;
            if ($balance < 0) {
                $balance = $balance * -1;
                \DB::table("{$pfx}credit")->insert([
					"clientid" => $userId,
					"date" => $date->toDateTimeString(),
					"description" => "Invoice #" . $invoiceId . " Overpayment",
					"amount" => $balance,
					"relid" => $invoiceId
				]);

                $invoice->client->credit += $balance;
                $invoice->client->save();
            }
        }

        return true;
	}

	/**
	 * GetBillingCycleMonths
	 */
	public static function getBillingCycleMonths($billingcycle)
	{
		try {
			$months = (new \App\Helpers\Cycles)->getNumberOfMonths($billingcycle);
		} catch (\Exception $e) {
			$months = 1;
		}
		return $months;
	}

	public static function getTaxRate($level, $state, $country)
	{
		$result = \App\Models\Tax::where(array("level" => $level, "state" => $state, "country" => $country));
		$data = $result;
		$taxname = $data->value("name");
		$taxrate = $data->value("taxrate");
		if (is_null($taxrate)) {
			$result = \App\Models\Tax::where(array("level" => $level, "state" => "", "country" => $country));
			$data = $result;
			$taxname = $data->value("name");
			$taxrate = $data->value("taxrate");
		}
		if (is_null($taxrate)) {
			$result = \App\Models\Tax::where(array("level" => $level, "state" => "", "country" => ""));
			$data = $result;
			$taxname = $data->value("name");
			$taxrate = $data->value("taxrate");
		}
		if (is_null($taxrate)) {
			$taxname = "";
			$taxrate = 0;
		} else {
			if (!$taxname) {
				$taxname = \Lang::get("client.invoicestax");
			}
		}
		return array("name" => $taxname, "rate" => $taxrate);
	}

	/**
	 * getTaxRate
	 *
	 * @param String $level
	 * @param String $state
	 * @param String $country
	 *
	 * @return Array [
	 * 	'name' => string,
	 * 	'rate' => string,
	 * ]
	 */
	public static function getTaxRateOLD($level, $state, $country)
	{
		$result = \App\Models\Tax::where('level', $level)->where('state', $state)->where('country', $country)->first();
		$data["name"] = "";
		$data["taxrate"] = 0;
		if ($result) {
			$data = $result->toArray();
		}
		$taxname = $data["name"];
		$taxrate = $data["taxrate"];
		if (is_null($taxrate)) {
			$result = \App\Models\Tax::where('level', $level)->where('state', "")->where('country', $country)->first();
			$data = $result->toArray();
			$taxname = $data["name"];
			$taxrate = $data["taxrate"];
		}
		if (is_null($taxrate)) {
			$result = \App\Models\Tax::where('level', $level)->where('state', "")->where('country', "")->first();
			$data = $result->toArray();
			$taxname = $data["name"];
			$taxrate = $data["taxrate"];
		}
		if (is_null($taxrate)) {
			$taxname = "";
			$taxrate = 0;
		} else {
			if (!$taxname) {
				$taxname = \Lang::get("client.invoicestax");
			}
		}
		return array("name" => $taxname, "rate" => $taxrate);
	}

	/**
	 * getProrataValues
	 */
	public static function getProrataValuesOLD($billingcycle, $amount, $proratadate, $proratachargenextmonth, $day, $month, $year, $userid)
	{
		global $CONFIG;
		if ($CONFIG["ProrataClientsAnniversaryDate"]) {
			$result = \App\Models\Client::find($userid);
			if (!$result) {
				throw new \Exception("Client ID not found");
			}
			$data = $result;
			$clientregdate = $data->datecreated->format('Y-m-d');
			$clientregdate = explode("-", $clientregdate);
			$proratadate = $clientregdate[2];
			if ($proratadate <= 0) {
				$proratadate = date("d");
			}
		}
		$billingcycle = str_replace("-", "", strtolower($billingcycle));
		$proratamonths = self::GetBillingCycleMonths($billingcycle);
		if ($billingcycle != "monthly") {
			$proratachargenextmonth = 0;
		}
		if ($billingcycle == "monthly") {
			if ($day < $proratadate) {
				$proratamonth = $month;
			} else {
				$proratamonth = $month + 1;
			}
		} else {
			$proratamonth = $month + $proratamonths;
		}
		$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
		$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
		$monthnumdays = array("31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31");
		if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
			$monthnumdays[1] = 29;
		}
		$totaldays = $extraamount = 0;
		if ($billingcycle == "monthly") {
			if ($proratachargenextmonth < $proratadate && $day < $proratadate && $proratachargenextmonth <= $day || $proratadate <= $proratachargenextmonth && $proratadate <= $day && $proratachargenextmonth <= $day) {
				$proratamonth++;
				$extraamount = $amount;
			}
			$totaldays += $monthnumdays[$month - 1];
			$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
			$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
			$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
		} else {
			for ($counter = $month; $counter <= $month + $proratamonths - 1; $counter++) {
				$month2 = round($counter);
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				$totaldays += $monthnumdays[$month2 - 1];
			}
			$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
		}
		$prorataamount = round($amount * $days / $totaldays, 2) + $extraamount;
		$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
		return array("amount" => $prorataamount, "date" => $proratadateuntil, "invoicedate" => $proratainvoicedate, "days" => $days);
	}
	public static function getProrataValues($billingcycle, $amount, $proratadate, $proratachargenextmonth, $day, $month, $year, $userid)
	{
		global $CONFIG;
		if ($CONFIG["ProrataClientsAnniversaryDate"]) {
			$proratadate = 0;
			$result = \App\Models\Client::where("id", $userid)->first();
			if ($result) {
				$data = $result;
				$clientregdate = $data->datecreated->format('Y-m-d');
				$clientregdate = explode("-", $clientregdate);
				$proratadate = $clientregdate[2];
			}
			if ($proratadate <= 0) {
				$proratadate = date("d");
			}
		}
		$billingcycle = str_replace("-", "", strtolower($billingcycle));
		$proratamonths = self::getBillingCycleMonths($billingcycle);
		if ($billingcycle != "monthly") {
			$proratachargenextmonth = 0;
		}
		if ($billingcycle == "monthly") {
			if ($day < $proratadate) {
				$proratamonth = $month;
			} else {
				$proratamonth = $month + 1;
			}
		} else {
			$proratamonth = $month + $proratamonths;
		}
		$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
		$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
		$monthnumdays = array("31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31");
		if ($year % 4 == 0 && $year % 100 != 0 || $year % 400 == 0) {
			$monthnumdays[1] = 29;
		}
		$totaldays = $extraamount = 0;
		if ($billingcycle == "monthly") {
			if ($proratachargenextmonth < $proratadate && $day < $proratadate && $proratachargenextmonth <= $day || $proratadate <= $proratachargenextmonth && $proratadate <= $day && $proratachargenextmonth <= $day) {
				$proratamonth++;
				$extraamount = $amount;
			}
			$totaldays += $monthnumdays[$month - 1];
			$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
			$proratadateuntil = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate, $year));
			$proratainvoicedate = date("Y-m-d", mktime(0, 0, 0, $proratamonth, $proratadate - 1, $year));
		} else {
			for ($counter = $month; $counter <= $month + $proratamonths - 1; $counter++) {
				$month2 = round($counter);
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				if (12 < $month2) {
					$month2 = $month2 - 12;
				}
				$totaldays += $monthnumdays[$month2 - 1];
			}
			$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
		}
		$prorataamount = round($amount * $days / $totaldays, 2) + $extraamount;
		$days = ceil((strtotime($proratadateuntil) - strtotime((string) $year . "-" . $month . "-" . $day)) / (60 * 60 * 24));
		return array("amount" => $prorataamount, "date" => $proratadateuntil, "invoicedate" => $proratainvoicedate, "days" => $days);
	}

	/**
	 * getInvoicePayUntilDate
	 */
	public static function getInvoicePayUntilDate($nextduedate, $billingcycle, $fulldate = "")
	{
		$year = substr($nextduedate, 0, 4);
		$month = substr($nextduedate, 5, 2);
		$day = substr($nextduedate, 8, 2);
		$daysadjust = $months = 0;
		$months = is_numeric($billingcycle) ? $billingcycle * 12 : self::GetBillingCycleMonths($billingcycle);
		if (!$fulldate) {
			$daysadjust = 1;
		}
		$new_time = mktime(0, 0, 0, (int) $month + (int) $months, $day - $daysadjust, $year);
		$invoicepayuntildate = $billingcycle != "One Time" ? date("Y-m-d", $new_time) : "";
		return $invoicepayuntildate;
	}
	public static function getInvoicePayUntilDateEndOfMonth($nextduedate, $billingcycle, $fulldate = "")
	{
		if ($billingcycle != "One Time" && $nextduedate) {
			$months = is_numeric($billingcycle) ? $billingcycle * 12 : self::GetBillingCycleMonths($billingcycle);
			$newnextduedate = \Carbon\Carbon::parse($nextduedate);
			$newnextduedate = $newnextduedate->endOfMonth()->toDateString();
			return $newnextduedate;
		}
		return "";
	}

	/**
	 * isUniqueTransactionID
	 */
	public static function isUniqueTransactionID($transactionID, $gateway)
	{
		$transactionID = \App\Models\Account::where('transid', $transactionID)->where('gateway', $gateway)->first();
		if ($transactionID) {
			return false;
		}
		return true;
	}

	/**
	 * addTransaction
	 *
	 * @param $userid integer|required
	 * @param $currencyid integer|required
	 * @param $description string|optional
	 * @param $amountin float|optional,
	 * @param $fees float|optional
	 * @param $amountout float|optional
	 * @param $gateway string|optional
	 * @param $transid string|optional
	 * @param $invoiceid integer|optinal
	 * @param $date date|optional
	 * @param $refundid integer|optional
	 * @param $rate flaot|optional
	 *
	 * @return void
	 *
	 */
	public static function addTransaction($userid, $currencyid, $description, $amountin, $fees, $amountout, $gateway = "", $transid = "", $invoiceid = "", $date = "", $refundid = "", $rate = "")
	{
        $date = $date ? (new \App\Helpers\SystemHelper())->toMySQLDate($date) . date(" H:i:s") : date('Y-m-d H:i:s');
		if ($userid) {
			$currency = (new \App\Helpers\AdminFunctions())->getCurrency($userid);
			$currencyid = $currency["id"];
		}
		if (!is_numeric($rate)) {
			if (empty($currencyid)) {
				$currency = (new \App\Helpers\AdminFunctions())->getCurrency();
				$currencyid = $currency["id"];
			}
			$result = \App\Models\Currency::find($currencyid);
			$data = $result->toArray();
			$rate = $data["rate"];
		}
		if ($userid) {
			$currencyid = 0;
		}
		$array = array("userid" => $userid, "currency" => $currencyid, "gateway" => $gateway, "date" => $date, "description" => $description, "amountin" => $amountin, "fees" => $fees, "amountout" => $amountout, "rate" => $rate, "transid" => $transid, "invoiceid" => $invoiceid, "refundid" => $refundid);
		$saveid = \App\Models\Account::insert($array);
		LogActivity::Save("Added Transaction - Transaction ID: " . $saveid, $userid);
		$array["id"] = $saveid;
		\App\Helpers\Hooks::run_hook("AddTransaction", $array);
	}

	/**
	 * isSequentialPaidInvoiceNumberingEnabled
	 */
	public static function isSequentialPaidInvoiceNumberingEnabled()
	{
		return Cfg::get('SequentialInvoiceNumbering') ? true : false;
	}

	/**
	 * processPaidInvoice
	 */
	public static function processPaidInvoice($invoiceid, $noemail = "", $date = "")
	{
		try {
			$invoice = \App\Models\Invoice::findOrFail($invoiceid);
			$invoiceid = $invoice->id;
			$userid = $invoice->userid;
			$invoicestatus = $invoice->status;
			$invoicenum = $invoice->invoicenum;
			if (!in_array($invoicestatus, array("Unpaid", "Payment Pending"))) {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
		$date = $date ? (new \App\Helpers\SystemHelper())->toMySQLDate($date) . date(" H:i:s") : \Carbon\Carbon::now();
		$invoice->status = "Paid";
		$invoice->datepaid = $date;
		$invoice->save();
		LogActivity::Save("Invoice Marked Paid - Invoice ID: " . $invoiceid, $userid);
		if (\App\Helpers\Invoices::isSequentialPaidInvoiceNumberingEnabled()) {
			$euVATAddonCustomInvoiceNumbersEnabled = Cfg::get("TaxNextCustomInvoiceNumber");
			if (!$invoicenum || $euVATAddonCustomInvoiceNumbersEnabled) {
				\App\Models\Invoice::where('id', $invoiceid)->update(array("invoicenum" => \App\Helpers\Invoices::getNextSequentialPaidInvoiceNumber()));
			}
		}
		Hooks::run_hook("InvoicePaidPreEmail", array("invoiceid" => $invoiceid));
		if (!$noemail) {
			\App\Helpers\Functions::sendMessage("Invoice Payment Confirmation", $invoiceid);
		}
		$orderId = \App\Models\Order::where('invoiceid', $invoiceid)->value("id");
		if ($orderId) {
			Hooks::run_hook("OrderPaid", array("orderId" => $orderId, "userId" => $userid, "invoiceId" => $invoiceid));
		}
		$items = $invoice->items()->where("type", "!=", "")->orderBy("id", "asc")->get();
		foreach ($items as $item) {
			$userid = $item->userid;
			$type = $item->type;
			$relid = $item->relid;
			$amount = $item->amount;
			if ($type == "Hosting") {
				self::makeHostingPayment($relid, $invoice);
			} else {
				if ($type == "DomainRegister" || $type == "DomainTransfer" || $type == "Domain") {
					self::makeDomainPayment($relid, $type);
				} else {
					if ($type == "DomainAddonDNS") {
						$enabledcheck = \App\Models\Domain::find($relid);
						$enabledcheck = $enabledcheck ? $enabledcheck->dnsmanagement : 0;
						if (!$enabledcheck) {
							$currency = (new \App\Helpers\AdminFunctions())->getCurrency($userid);
							$dnscost = \App\Models\Pricing::where('type', 'domainaddons')->where('currency', $currency["id"])->where('relid', 0)->get()->pluck('msetupfee')->toArray();
							$dnscost = $dnscost[0] ?? 0;
							$d = \App\Models\Domain::find($relid);
							$d->dnsmanagement = 1;
							$d->save();
							$d->increment('recurringamount', $dnscost);
						}
					} else {
						if ($type == "DomainAddonEMF") {
							$enabledcheck = \App\Models\Domain::find($relid);
							$enabledcheck = $enabledcheck ? $enabledcheck->emailforwarding : 0;
							if (!$enabledcheck) {
								$currency = (new \App\Helpers\AdminFunctions())->getCurrency($userid);
								$emfcost = \App\Models\Pricing::where('type', 'domainaddons')->where('currency', $currency["id"])->where('relid', 0)->get()->pluck('qsetupfee')->toArray();
								$emfcost = $emfcost[0] ?? 0;
								$d = \App\Models\Domain::find($relid);
								$d->emailforwarding = 1;
								$d->save();
								$d->increment('recurringamount', $emfcost);
							}
						} else {
							if ($type == "DomainAddonIDP") {
								$enabledcheck = \App\Models\Domain::find($relid);
								$enabledcheck = $enabledcheck ? $enabledcheck->idprotection : 0;
								if (!$enabledcheck) {
									$currency = (new \App\Helpers\AdminFunctions())->getCurrency($userid);
									$idpcost = \App\Models\Pricing::where('type', 'domainaddons')->where('currency', $currency["id"])->where('relid', 0)->get()->pluck('ssetupfee')->toArray();
									$idpcost = $idpcost[0] ?? 0;
									$d = \App\Models\Domain::find($relid);
									$d->idprotection = 1;
									$d->save();
									$d->increment('recurringamount', $idpcost);
									$data = $d->toArray();
									$domainparts = explode(".", $data["domain"], 2);
									$params = array();
									$params["domainid"] = $relid;
									list($params["sld"], $params["tld"]) = $domainparts;
									$params["regperiod"] = $data["registrationperiod"];
									$params["registrar"] = $data["registrar"];
									$params["regtype"] = $data["type"];
									$values = (new \App\Module\Registrar())->RegIDProtectToggle($params);
									if ($values["error"]) {
										LogActivity::Save("ID Protection Enabling Failed - Error: " . $values["error"] . " - Domain ID: " . $relid, $userid);
									} else {
										LogActivity::Save("ID Protection Enabled Successfully - Domain ID: " . $relid, $userid);
									}
								}
							} else {
								if ($type == "Addon") {
									self::makeAddonPayment($relid, $invoice);
								} else {
									if ($type == "Upgrade") {
										\App\Helpers\Upgrade::processUpgradePayment($relid, "", "", "true");
									} else {
										if ($type == "AddFunds") {
											\App\Models\Credit::insert(array("clientid" => $userid, "date" => \Carbon\Carbon::now(), "description" => "Add Funds Invoice #" . $invoiceid, "amount" => $amount));
											$c = \App\Models\Client::find((int) $userid);
											$c->increment('credit', $amount);
										} else {
											if ($type == "Invoice") {
												\App\Models\Credit::insert(array("clientid" => $userid, "date" => \Carbon\Carbon::now(), "description" => "Mass Invoice Payment Credit for Invoice #" . $relid, "amount" => $amount));
												$c = \App\Models\Client::find((int) $userid);
												$c->increment('credit', $amount);
												self::applyCredit($relid, $userid, $amount);
											} else {
												if (substr($type, 0, 14) == "ProrataProduct") {
													$newduedate = substr($type, 14);
													\App\Models\Hosting::where('id', $relid)->update(array("nextduedate" => $newduedate, "nextinvoicedate" => $newduedate));
												} else {
													if (substr($type, 0, 12) == "ProrataAddon") {
														$newduedate = substr($type, 12);
														\App\Models\Hostingaddon::where('id', $relid)->update(array("nextduedate" => $newduedate, "nextinvoicedate" => $newduedate));
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		Hooks::run_hook("InvoicePaid", array("invoiceid" => $invoiceid));
	}

	/**
	 * applyCredit
	 */
	public static function applyCredit($invoiceid, $userid, $amount, $noemail = "")
	{
		$amount = round($amount, 2);
		$i = \App\Models\Invoice::find((int) $invoiceid);
		$i->increment('credit', $amount);

		$c = \App\Models\Client::find((int) $userid);
		$c->decrement('credit', $amount);

		\App\Models\Credit::insert(array("clientid" => $userid, "date" => \Carbon\Carbon::now(), "description" => "Credit Applied to Invoice #" . $invoiceid, "amount" => $amount * -1));

		LogActivity::Save("Credit Applied - Amount: " . $amount . " - Invoice ID: " . $invoiceid, $userid);
		self::UpdateInvoiceTotal($invoiceid);
		$result = \App\Models\Invoice::find($invoiceid);
		$data = $result->toArray();
		$total = $data["total"];
		$result = \App\Models\Account::select(DB::raw("SUM(amountin)-SUM(amountout) as sumtotal"))->where('invoiceid', $invoiceid)->first();
		$data = $result->toArray();
		$amountpaid = $data['sumtotal'];
		$balance = $total - $amountpaid;
		if ($balance <= 0) {
			self::processpaidinvoice($invoiceid, $noemail);
		}
	}

	/**
	 * makeAddonPayment
	 */
	public static function makeAddonPayment($func_addonid, $invoice)
	{
		try {
			$configuration = \Config::get('portal');
			$disable_to_do_list_entries = false;
			if (array_key_exists("disable_to_do_list_entries", $configuration)) {
				$disable_to_do_list_entries = (bool) $configuration["disable_to_do_list_entries"];
			}
			$addon = \App\Models\Hostingaddon::with("productAddon", "productAddon.welcomeEmailTemplate", "service", "service.product")->findOrFail($func_addonid);
			$id = $addon->id;
			$serviceId = $addon->serviceId;
			$addonId = $addon->addonId;
			$billingCycle = $addon->billingCycle;
			$status = $addon->status;
			$nextDueDate = $addon->nextDueDate;
			$userId = $addon->clientId;
			$nextDueDate = self::getInvoicePayUntilDate($nextDueDate, $billingCycle, true);
			$name = $addon->name ?: $addon->productAddon->name;
			$addon->nextDueDate = $nextDueDate;
			$addon->save();
			if ($status == "Pending") {
				$autoActivate = "";
				$welcomeEmail = 0;
				if ($addonId) {
					$autoActivate = $addon->productAddon->autoActivate;
					$welcomeEmail = $addon->productAddon->welcomeEmailTemplate;
				}
				if ($autoActivate && $autoActivate == "payment") {
					switch ($addon->productAddon->module) {
						case "":
							$addon->status = "Active";
							$addon->save();
							$automationResult = "";
							$noModule = true;
							break;
						default:
							$automation = \App\Helpers\AddonAutomation::factory($addon);
							$automationResult = $automation->runAction("CreateAccount");
							$noModule = false;
					}
					if ($noModule || $automationResult) {
						if ($welcomeEmail) {
							\App\Helpers\Functions::sendMessage($welcomeEmail, $serviceId, array("addon_id" => $id, "addon_service_id" => $serviceId, "addon_addonid" => $addonId, "addon_billing_cycle" => $billingCycle, "addon_status" => $status, "addon_nextduedate" => $nextDueDate, "addon_name" => $name));
						}
						if ($noModule) {
							Hooks::run_hook("AddonActivation", array("id" => $addon->id, "userid" => $userId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
						}
					}
				}
			} else {
				if ($status == "Suspended") {
					if ($addonId && $addon->productAddon->module) {
						$automation = \App\Helpers\AddonAutomation::factory($addon);
						$automationResult = $automation->runAction("UnsuspendAccount");
						$noModule = false;
					} else {
						$automationResult = "";
						$addon->status = "Active";
						$addon->save();
						$noModule = true;
						Hooks::run_hook("AddonUnsuspended", array("id" => $addon->id, "userid" => $userId, "serviceid" => $serviceId, "addonid" => $addonId));
					}
					if (($automationResult || $noModule) && $addon->productAddon->suspendProduct && $addon->service->domainStatus == "Suspended" && $addon->service->product->module) {
						LogActivity::Save("Unsuspending Parent Service for Addon Payment - Service ID: " . $serviceId, $userId);
						(new \App\Module\Server())->ServerUnsuspendAccount($serviceId);
					}
				} else {
					if ($status == "Active") {
						$noModule = true;
						if ($addonId) {
							switch ($addon->productAddon->module) {
								case '':
									break;
								default:
									$registrationDate = $addon->registrationDate;
									if ($registrationDate instanceof \Carbon\Carbon) {
										$registrationDate = $registrationDate->toDateString();
									}
									$runRenew = $invoice->shouldRenewRun($func_addonid, $registrationDate, "Addon");
									if ($runRenew) {
										$automation = \App\Helpers\AddonAutomation::factory($addon);
										$success = $automation->runAction("Renew");
										if (!$success && $automation->getError() != "notsupported") {
											$addonName = $addon->name;
											if (!$addonName && $addon->addonId) {
												$addonName = $addon->productAddon->name;
											}
											\App\Helpers\Functions::sendAdminMessage("Service Renewal Failed", array("client_id" => $userId, "service_id" => $addon->serviceId, "service_product" => $addon->service->product->name, "service_domain" => $addon->service->domain, "addon_id" => $addon->id, "addon_name" => $addonName, "error_msg" => $automation->getError()), "account");
											if (!$disable_to_do_list_entries) {
												$domain = $addon->serviceProperties->get("Domain Name");
												if (!$domain) {
													$domain = $addon->service->product->name;
												}
												$productName = $addon->service->product->name;
												$description = "The order placed for " . $domain . " has received its" . " next payment and the automatic renewal has failed<br>" . "Client ID: " . $userId . "<br>Product/Service: " . $productName . "<br>" . "Domain: " . $domain . "<br>Addon: " . $addonName;
												$date = \Carbon\Carbon::now();
												DB::table("tbltodolist")->insert(array("date" => $date->toDateString(), "title" => "Manual Renewal Required", "description" => $description, "admin" => "", "status" => "Pending", "duedate" => $date->toDateTimeString()));
											}
										}
										$noModule = false;
									}
									break;
							}
						}
						if ($noModule) {
							Hooks::run_hook("AddonRenewal", array("id" => $addon->id, "userid" => $userId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
						}
					}
				}
			}
		} catch (\Exception $e) {}
	}

	/**
	 * makeDomainPayment
	 */
	public static function makeDomainPayment($func_domainid, $type = "")
	{
		$result = \App\Models\Domain::find($func_domainid);
		$data = $result->toArray();
		$userid = $data["userid"];
		$orderid = $data["orderid"];
		$registrationperiod = $data["registrationperiod"];
		$registrationdate = $data["registrationdate"];
		$nextduedate = $data["nextduedate"];
		$recurringamount = $data["recurringamount"];
		$domain = $data["domain"];
		$paymentmethod = $data["paymentmethod"];
		$registrar = $data["registrar"];
		$status = $data["status"];
		$year = substr($nextduedate, 0, 4);
		$month = substr($nextduedate, 5, 2);
		$day = substr($nextduedate, 8, 2);
		$newnextduedate = date("Y-m-d", mktime(0, 0, 0, $month, $day, $year + $registrationperiod));
		$result->nextduedate = $newnextduedate;
		$result->save();
		$domaintype = substr($type, 6);
		$domainparts = explode(".", $domain, 2);
		list($sld, $tld) = $domainparts;
		$params = array();
		$params["domainid"] = $func_domainid;
		$params["sld"] = $sld;
		$params["tld"] = $tld;
		if ($domaintype == "Register" || $domaintype == "Transfer") {
			$result = \App\Models\Domainpricing::where('extension', ".".$tld)->first();
			$data = $result->toArray();
			$autoreg = $data['autoreg'];
			if ($status == "Pending") {
				if (self::getNewClientAutoProvisionStatus($userid)) {
					if ($autoreg) {
						\App\Models\Domain::where('id', $func_domainid)->update(array("registrar" => $autoreg));
						$params["registrar"] = $autoreg;
						if ($domaintype == "Register") {
							LogActivity::Save("Running Automatic Domain Registration on Payment", $userid);
							$result = (new \App\Module\Registrar())->RegRegisterDomain($params);
							$emailmessage = "Domain Registration Confirmation";
						} else {
							if ($domaintype == "Transfer") {
								LogActivity::Save("Running Automatic Domain Transfer on Payment", $userid);
								$result = (new \App\Module\Registrar())->RegTransferDomain($params);
								$emailmessage = "Domain Transfer Initiated";
							}
						}
						$result = $result["error"];
						if ($result) {
							\App\Helpers\Functions::sendAdminMessage("Automatic Setup Failed", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => $result), "account");
							if (Cfg::get("DomainToDoListEntries")) {
								if ($domaintype == "Register") {
									\App\Helpers\Functions::addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain . " and the automated registration attempt has failed with the following error: " . $result);
								} else {
									if ($domaintype == "Transfer") {
										\App\Helpers\Functions::addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain . " and the automated transfer attempt has failed with the following error: " . $result);
									}
								}
							}
						} else {
							\App\Helpers\Functions::sendMessage($emailmessage, $func_domainid);
							\App\Helpers\Functions::sendAdminMessage("Automatic Setup Successful", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_type" => $domaintype, "domain_name" => $domain, "error_msg" => ""), "account");
						}
					} else {
						if (Cfg::get("DomainToDoListEntries")) {
							if ($domaintype == "Register") {
								\App\Helpers\Functions::addToDoItem("Manual Domain Registration", "Client ID " . $userid . " has paid for the registration of domain " . $domain);
							} else {
								if ($domaintype == "Transfer") {
									\App\Helpers\Functions::addToDoItem("Manual Domain Transfer", "Client ID " . $userid . " has paid for the transfer of domain " . $domain);
								}
							}
						}
					}
				} else {
					LogActivity::Save("Automatic Domain Registration on Payment Suppressed for New Client", $userid);
				}
			} else {
				if ($autoreg) {
					LogActivity::Save("Automatic Domain Registration Suppressed as Domain Is Already Active", $userid);
				}
			}
		} else {
			if ($status != "Pending" && $status != "Cancelled" && $status != "Fraud") {
				if (Cfg::get("AutoRenewDomainsonPayment") && $registrar) {
					$val = \App\Models\Hosting::where('userid', $userid)->where('domain', $domain)->where('domainstatus', 'Active')->count();
					if (Cfg::get("FreeDomainAutoRenewRequiresProduct") && $recurringamount <= 0 && !$val) {
						LogActivity::Save("Suppressed Automatic Domain Renewal on Payment Due to Domain Being Free and having No Active Associated Product", $userid);
						\App\Helpers\Functions::sendAdminNotification("account", "Free Domain Renewal Manual Action Required", "The domain " . $domain . " (ID: " . $func_domainid . ") was just invoiced for renewal and automatically marked paid due to it being free, but because no active Product/Service matching the domain was found in order to qualify for the free domain offer, the renewal has not been automatically submitted to the registrar.  You must login to review & process this renewal manually should it be desired.");
					} else {
						LogActivity::Save("Running Automatic Domain Renewal on Payment", $userid);
						$params["registrar"] = $registrar;
						$result = (new \App\Module\Registrar())->RegRenewDomain($params);
						$result = $result["error"];
						if ($result) {
							\App\Helpers\Functions::sendAdminMessage("Domain Renewal Failed", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => $result), "account");
							if (Cfg::get("DomainToDoListEntries")) {
								\App\Helpers\Functions::addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain . " and the automated renewal attempt has failed with the following error: " . $result);
							}
						} else {
							\App\Helpers\Functions::sendMessage("Domain Renewal Confirmation", $func_domainid);
							\App\Helpers\Functions::sendAdminMessage("Domain Renewal Successful", array("client_id" => $userid, "domain_id" => $func_domainid, "domain_name" => $domain, "error_msg" => ""), "account");
						}
					}
				} else {
					if (Cfg::get("DomainToDoListEntries")) {
						\App\Helpers\Functions::addToDoItem("Manual Domain Renewal", "Client ID " . $userid . " has paid for the renewal of domain " . $domain);
					}
				}
			}
		}
	}

	/**
	 * makeHostingPayment
	 */
	public static function makeHostingPayment($func_domainid, \App\Models\Invoice $invoice)
	{
		global $CONFIG;
		global $disable_to_do_list_entries;
		$result = \App\Models\Hosting::find($func_domainid);
		$data = $result->toArray();
		$userid = $data["userid"];
		$orderId = $data["orderid"];
		$billingcycle = $data["billingcycle"];
		$domain = $data["domain"];
		$packageid = $data["packageid"];
		$regdate = $data["regdate"];
		$nextduedate = $data["nextduedate"];
		$status = $data["domainstatus"];
		$server = $data["server"];
		$paymentmethod = $data["paymentmethod"];
		$suspendreason = $data["suspendreason"];
		$result = \App\Models\Product::find($packageid);
		$data = $result->toArray();
		$producttype = $data["type"];
		$productname = $data["name"];
		$module = $data["servertype"];
		$proratabilling = $data["proratabilling"];
		$proratadate = $data["proratadate"];
		$proratachargenextmonth = $data["proratachargenextmonth"];
		$autosetup = $data["autosetup"];
		if ($regdate == $nextduedate && $proratabilling) {
			$orderyear = substr($regdate, 0, 4);
			$ordermonth = substr($regdate, 5, 2);
			$orderday = substr($regdate, 8, 2);
			$proratavalues = self::getProrataValues($billingcycle, $product_onetime, $proratadate, $proratachargenextmonth, $orderday, $ordermonth, $orderyear, $userid);
			$nextduedate = $proratavalues["date"];
		} else {
			$nextduedate = self::getInvoicePayUntilDate($nextduedate, $billingcycle, true);
		}
		\App\Models\Hosting::where('id', $func_domainid)->update(array("nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate));
		if ($status == "Pending" && $autosetup == "payment" && $module) {
			if (self::getNewClientAutoProvisionStatus($userid)) {
				LogActivity::Save("Running Module Create on Payment", $userid);
				$result = (new \App\Module\Server())->ServerCreateAccount($func_domainid);
				if ($result == "success") {
					if ($module != "marketconnect") {
						\App\Helpers\Functions::sendMessage("defaultnewacc", $func_domainid);
					}
					\App\Helpers\Functions::sendAdminMessage("Automatic Setup Successful", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""), "account");
				} else {
					\App\Helpers\Functions::sendAdminMessage("Automatic Setup Failed", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $result), "account");
				}
			} else {
				LogActivity::Save("Module Create on Payment Suppressed for New Client", $userid);
			}
		}
		$suspenddate = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $CONFIG["AutoSuspensionDays"], date("Y")));
		if ($status == "Suspended" && $CONFIG["AutoUnsuspend"] == "on" && $module && !$suspendreason && $suspenddate <= str_replace("-", "", $nextduedate)) {
			LogActivity::Save("Running Auto Unsuspend on Payment", $userid);
			$moduleresult = (new \App\Module\Server())->ServerUnsuspendAccount($func_domainid);
			if ($moduleresult == "success") {
				\App\Helpers\Functions::sendMessage("Service Unsuspension Notification", $func_domainid);
				\App\Helpers\Functions::sendAdminMessage("Service Unsuspension Successful", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => ""), "account");
			} else {
				\App\Helpers\Functions::sendAdminMessage("Service Unsuspension Failed", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "error_msg" => $moduleresult), "account");
				if (!$disable_to_do_list_entries) {
					\App\Models\Todolist::insert(array("date" => \Carbon\Carbon::now()->toDateString(), "title" => "Manual Unsuspend Required", "description" => "The order placed for " . $domain . " has received its next payment and the automatic unsuspend has failed<br />Client ID: " . $userid . "<br>Product/Service: " . $productname . "<br>Domain: " . $domain, "admin" => "", "status" => "Pending", "duedate" => date("Y-m-d")));
				}
			}
		}
		if ($status != "Pending" && $module) {
			$runRenew = $invoice->shouldRenewRun($func_domainid, $regdate);
			if ($runRenew) {
				$moduleResult = (new \App\Module\Server())->ServerRenew($func_domainid);
				if ($moduleResult != "success" && $moduleResult != "notsupported") {
					\App\Helpers\Functions::sendAdminMessage("Service Renewal Failed", array("client_id" => $userid, "service_id" => $func_domainid, "service_product" => $productname, "service_domain" => $domain, "addon_id" => 0, "addon_name" => "", "error_msg" => $moduleResult), "account");
					if (!$disable_to_do_list_entries) {
						$description = "The order placed for " . $domain . " has received its next payment and the" . " automatic renewal has failed<br>Client ID: " . $userid . "<br>" . "Product/Service: " . $productname . "<br>Domain: " . $domain;
						$date = \Carbon\Carbon::now();
						DB::table("tbltodolist")->insert(array("date" => $date->toDateString(), "title" => "Manual Renewal Required", "description" => $description, "admin" => "", "status" => "Pending", "duedate" => $date->toDateTimeString()));
					}
				}
			}
		}
		\App\Helpers\Functions::AffiliatePayment("", $func_domainid);
		$freeAddons = \App\Models\Hostingaddon::with("productAddon", "productAddon.welcomeEmailTemplate")->whereIn("billingcycle", array("Free", "Free Account"))->where("addonid", ">", 0)->where("status", "Pending")->where("hostingid", $func_domainid)->get();
		foreach ($freeAddons as $freeAddon) {
			$aId = $freeAddon->id;
			$addonId = $freeAddon->addonid;
			$autoActivate = $freeAddon->productAddon->autoactivate;
			$welcomeEmail = $freeAddon->productAddon->welcomeEmailTemplate;
			if ($autoActivate && $autoActivate == "payment") {
				switch ($freeAddon->productAddon->module) {
					case "":
						$freeAddon->status = "Active";
						$freeAddon->save();
						$automationResult = "";
						$noModule = true;
						break;
					default:
						$automation = \App\Helpers\AddonAutomation::factory($freeAddon);
						$automationResult = $automation->runAction("CreateAccount");
						$noModule = false;
				}
				if ($noModule || $automationResult) {
					if ($welcomeEmail) {
						\App\Helpers\Functions::sendMessage($welcomeEmail, $func_domainid, array("addon_id" => $aId, "addon_service_id" => $func_domainid, "addon_addonid" => $addonId, "addon_billing_cycle" => $freeAddon->billingCycle, "addon_status" => "Active", "addon_nextduedate" => "0000-00-00", "addon_name" => $name = $freeAddon->name ?: $freeAddon->productAddon->name));
					}
					if ($noModule) {
						Hooks::run_hook("AddonActivation", array("id" => $freeAddon->id, "userid" => $freeAddon->userid, "serviceid" => $func_domainid, "addonid" => $freeAddon->addonid));
					}
				}
			}
		}
	}

	/**
	 * getNewClientAutoProvisionStatus
	 *
	 * @param $userid integer|required
	 *
	 * @return boolean true|false
	 *
	 */
	public static function getNewClientAutoProvisionStatus($userid)
	{
		global $CONFIG;
		if ($CONFIG["AutoProvisionExistingOnly"]) {
			$result = \App\Models\Hosting::where('userid', $userid)->where('domainstatus', 'Active')->count();
			$data = ($result);
			$result = \App\Models\Domain::where('userid', $userid)->where('status', 'Active')->count();
			$data2 = ($result);
			if ($data + $data2) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * getBillingCycleDays
	 */
	public static function getBillingCycleDays($billingcycle)
	{
		$totaldays = 0;
		if ($billingcycle == "Monthly") {
			$totaldays = 30;
		} else {
			if ($billingcycle == "Quarterly") {
				$totaldays = 90;
			} else {
				if ($billingcycle == "Semi-Annually") {
					$totaldays = 180;
				} else {
					if ($billingcycle == "Annually") {
						$totaldays = 365;
					} else {
						if ($billingcycle == "Biennially") {
							$totaldays = 730;
						} else {
							if ($billingcycle == "Triennially") {
								$totaldays = 1095;
							}
						}
					}
				}
			}
		}
		return $totaldays;
	}

	/**
	 * AffiliatePayment
	 *
	 * @param $affaccid integer|required
	 * @param $hostingid integer|required
	 *
	 * @return boolean true|false
	 *
	 */
	public static function AffiliatePayment($affaccid, $hostingid)
    {
        global $CONFIG;

		$prefix = \Database::prefix();
        $payout = false;

		if ($affaccid) {
            $result = \App\Models\AffiliateAccount::find($affaccid);
        } else {
            $result = \App\Models\AffiliateAccount::firstWhere("relid", $hostingid);
        }

		// Uncomment this in case no AffiliateAccount data retrieved
		// if (!$result) return null;

        $data = $result->toArray();

        $affaccid = $data["id"];
        $affid = $data["affiliateid"];
        $lastpaid = $data["lastpaid"];
        $relid = $data["relid"];

        $commission = self::calculateAffiliateCommission($affid, $relid, $lastpaid);

		$result = Product::select("{$prefix}products.id", "{$prefix}products.affiliateonetime", "{$prefix}hosting.id", "{$prefix}hosting.packageid")
							->join("{$prefix}hosting", "{$prefix}hosting.packageid", "{$prefix}products.id")
							->where("{$prefix}hosting.id", $relid)
							->first();

        $data = $result->toArray();
		$affiliateonetime = $data["affiliateonetime"];

		if ($affiliateonetime) {
            if ($lastpaid == "0000-00-00") {
                $payout = true;
            } else {
                $error = "This product is setup for a one time affiliate payment only and the commission has already been paid";
            }
        } else {
            $payout = true;
        }

        $result = \App\Models\Affiliate::select("id", "onetime")->find($affid);
        $data = $result->toArray();
        $onetime = $data["onetime"];

        if ($onetime && $lastpaid != "0000-00-00") {
            $payout = false;
            $error = "This affiliate is setup for a one time commission only on all products and that has already been paid";
        }

        if ($affaccid) {
            $commissionDelayed = false;
            if ($CONFIG["AffiliatesDelayCommission"]) {
                $commissionDelayed = true;
                $clearingDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["AffiliatesDelayCommission"], date("Y")));
            }

            $responses = Hooks::run_hook("AffiliateCommission", [
				"affiliateId" => $affid,
				"referralId" => $affaccid,
				"serviceId" => $relid,
				"commissionAmount" => $commission,
				"commissionDelayed" => $commissionDelayed,
				"clearingDate" => $clearingDate,
				"payout" => $payout,
				"message" => $error
			]);

			$skipCommission = false;
            foreach ($responses as $response) {
                if (array_key_exists("skipCommission", $response) && $response["skipCommission"]) {
                    $skipCommission = true;
                } else {
                    if (array_key_exists("payout", $response) && $response["payout"]) {
                        $payout = true;
                    }
                }
            }

            if ($payout && !$skipCommission) {
                if ($commissionDelayed) {
					$affPending = new \App\Models\AffiliatePending();
					$affPending->affaccid = $affaccid;
					$affPending->amount = $commission;
					$affPending->clearingdate = $clearingDate;
					$affPending->save();
                } else {
					$affUpdate = \App\Models\Affiliate::find((int) $affid);
					$affUpdate->balance += $commission;
					$affUpdate->save();

					$affHistory = new \App\Models\AffiliateHistory();
					$affHistory->affiliateid = $affid;
					$affHistory->date = date("Y-m-d H:i:s");
					$affHistory->affaccid = $affaccid;
					$affHistory->amount = $commission;
					$affHistory->save();
                }

				$affAccount = \App\Models\AffiliateAccount::find($affaccid);
				$affAccount->lastpaid = date("Y-m-d H:i:s");
				$affAccount->save();
            }
        }

        return $error;
    }

	/**
	 * calculateAffiliateCommission
	 *
	 * @param $affid integer|required
	 * @param $relid integer|required
	 * @param $lastpaid string|optional
	 *
	 * @return $commission
	 *
	 */
	public static function calculateAffiliateCommission($affid, $relid, $lastpaid = "")
    {
        global $CONFIG;
        static $AffCommAffiliatesData = [];
        $percentage = $fixedamount = "";
		$prefix = \Database::prefix();

       	$result = Product::select("{$prefix}products.id", "{$prefix}products.affiliateonetime", "{$prefix}products.affiliatepaytype", "{$prefix}products.affiliatepayamount", "{$prefix}hosting.id", "{$prefix}hosting.packageid", "{$prefix}hosting.amount", "{$prefix}hosting.firstpaymentamount", "{$prefix}hosting.billingcycle", "{$prefix}hosting.userid", "{$prefix}clients.id", "{$prefix}clients.currency")
							->join("{$prefix}hosting", "{$prefix}hosting.packageid", "{$prefix}products.id")
							->join("{$prefix}clients", "{$prefix}clients.id", "{$prefix}hosting.userid")
							->where("{$prefix}hosting.id", $relid)
							->first();

		$data = $result->toArray();

		$userid = $data["userid"];
        $billingcycle = $data["billingcycle"];
        $affiliateonetime = $data["affiliateonetime"];
        $affiliatepaytype = $data["affiliatepaytype"];
        $affiliatepayamount = $data["affiliatepayamount"];
        $clientscurrency = $data["currency"];
        $amount = $lastpaid == "0000-00-00" || $billingcycle == "One Time" || $affiliateonetime ? $data["firstpaymentamount"] : $data["amount"];

		if ($affiliatepaytype == "none") {
            return "0.00";
        }

        if ($affiliatepaytype) {
            if ($affiliatepaytype == "percentage") {
                $percentage = $affiliatepayamount;
            } else {
                $fixedamount = $affiliatepayamount;
            }
        }
        if (isset($AffCommAffiliatesData[$affid])) {
            $data = $AffCommAffiliatesData[$affid];
        } else {
            $result = \App\Models\Affiliate::select(\DB::raw("id, clientid, paytype, payamount, (SELECT currency FROM {$prefix}clients WHERE {$prefix}clients.id = clientid) AS currency"))
												->firstWhere("id", $affid);
			$data = $result->toArray();
            $AffCommAffiliatesData[$affid] = $data;
        }

        $affuserid = $data["clientid"];
        $paytype = $data["paytype"];
        $payamount = $data["payamount"];
        $affcurrency = $data["currency"];

        if ($paytype) {
            $percentage = $fixedamount = "";
            if ($paytype == "percentage") {
                $percentage = $payamount;
            } else {
                $fixedamount = $payamount;
            }
        }

        if (!$fixedamount && !$percentage) {
            $percentage = $CONFIG["AffiliateEarningPercent"];
        }

        $commission = $fixedamount ? self::convertCurrency($fixedamount, 1, $affcurrency) : self::convertCurrency($amount, $clientscurrency, $affcurrency) * $percentage / 100;
        $commission = \App\Helpers\Functions::format_as_currency($commission);

		Hooks::run_hook("CalcAffiliateCommission", [
			"affid" => $affid,
			"relid" => $relid,
			"amount" => $amount,
			"commission" => $commission
		]);

        return $commission;
    }

	public static function convertCurrency($amount, $from, $to, $base_currency_exchange_rate = "")
    {
        if (!$base_currency_exchange_rate) {
            $result = Currency::select("id", "rate")->find($from);
            $data = $result->toArray();

            $base_currency_exchange_rate = $data["rate"];
        }

        $result = Currency::select("id", "rate")->find($to);
        $data = $result->toArray();
        $convertto_currency_exchange_rate = $data["rate"];

		if (!$base_currency_exchange_rate) $base_currency_exchange_rate = 1;
        if (!$convertto_currency_exchange_rate) $convertto_currency_exchange_rate = 1;

        $convertto_amount = \App\Helpers\Functions::format_as_currency($amount / $base_currency_exchange_rate * $convertto_currency_exchange_rate);

		return $convertto_amount;
    }

 	public static function getInvoiceStatusColour($status, $clientarea = true)
	{
		if (!$clientarea) {
			switch ($status) {
				case "Draft":
					return "<span class=\"textgrey\">" . __("admin.statusdraft") . "</span>";
				case "Unpaid":
					return "<span class=\"textred\">" . __("admin.statusunpaid") . "</span>";
				case "Paid":
					return "<span class=\"textgreen\">" . __("admin.statuspaid") . "</span>";
				case "Cancelled":
					return "<span class=\"textgrey\">" . __("admin.statuscancelled") . "</span>";
				case "Refunded":
					return "<span class=\"textblack\">" . __("admin.statusrefunded") . "</span>";
				case "Collections":
					return "<span class=\"textgold\">" . __("admin.statuscollections") . "</span>";
				case "Payment Pending":
					return "<span class=\"textgreen\">" .__("admin.statuspaymentpending") . "</span>";
				default:
					return "N/A";
			}
		} else {
			switch ($status) {
				case "Unpaid":
					return "<span class=\"textred\">" . __("client.invoicesunpaid") . "</span>";
				case "Paid":
					return "<span class=\"textgreen\">" . __("client.invoicespaid") . "</span>";
				case "Cancelled":
					return "<span class=\"textgrey\">" . __("client.invoicescancelled") . "</span>";
				case "Refunded":
					return "<span class=\"textblack\">" . __("client.invoicesrefunded") . "</span>";
				case "Collections":
					return "<span class=\"textgold\">" . __("client.invoicescollections") . "</span>";
				case "Payment Pending":
					return "<span class=\"textgreen\">" .__("client.invoicesPaymentPending") . "</span>";
				default:
					return "N/A";
			}
		}

		return $status;
	}

	public static function removeCreditOnInvoiceDelete($invoiceID)
	{
		$invoiceData = \App\Models\Invoice::select("userid", "credit")->find($invoiceID);
		$creditAmount = $invoiceData->credit;
		$userID = $invoiceData->userid;

		if (0 < $creditAmount) {
			\App\Models\Invoice::where("id", $invoiceID)->update(["credit" => 0]);

			self::updateinvoicetotal($invoiceID);
			$client = \App\Models\Client::find($userID);
			$client->credit += $creditAmount;
			$client->save();

			$credit = new \App\Models\Credit();
			$credit->clientid = $userID;
			$credit->date = date("Y-m-d");
			$credit->description = "Credit Removed on deletion of Invoice #$invoiceID";
			$credit->amount = $creditAmount;
			$credit->save();

		 	\LogActivity::save("Credit Removed on Invoice Deletion - Amount: $creditAmount - Invoice ID: $invoiceID", $userID);
		}
	}

	public static function duplicate($invoiceid)
    {
        $existingInvoice = InvoiceModel::with("items")->find($invoiceid);
        $newInvoice = $existingInvoice->replicate(["invoicenum"]);
        $newInvoice->status = "Draft";
        $newInvoice->save();

		$userid = $newInvoice->userid;
        $newid = $newInvoice->id;
        $newItems = [];

		foreach ($existingInvoice->items as $invoiceItem) {
            $newItems[] = $invoiceItem->replicate();
        }

		$newInvoice->items()->saveMany($newItems);
        \LogActivity::Save("Duplicated Invoice - Existing Invoice ID: $invoiceid - New Invoice ID: $newid", $userid);

		return true;
    }

	/**
	 * adjustIncrementForNextInvoice
	 */
	public static function adjustIncrementForNextInvoice($lastInvoiceId)
    {
        $incrementValue = (int) Cfg::getValue("InvoiceIncrement");
        if (1 < $incrementValue) {
            $incrementedId = $lastInvoiceId + $incrementValue - 1;
			\App\Models\Invoice::insert(array("id" => $incrementedId));
			\App\Models\Invoice::where(array("id" => $incrementedId))->delete();
        }
    }

	public static function paymentReversed($reverseTransactionId, $originalTransactionId, $invoiceId = 0, $gateway = NULL)
	{
		$transaction = \App\Models\Account::with("client")->where("transid", "=", $originalTransactionId);
		if ($invoiceId) {
			$transaction = $transaction->where("invoiceid", "=", $invoiceId);
		}
		if ($gateway) {
			$transaction = $transaction->where("gateway", "=", $gateway);
		}
		if (1 < $transaction->count()) {
			throw new \Exception("Multiple Original Transaction matches - Reversal not Available");
		}
		$transaction = $transaction->first();
		if (!$transaction) {
			throw new \Exception("Original Transaction Not Found");
		}
		$existingRefundTransaction = \App\Models\Account::where("refundid", "=", $transaction->id)->first();
		$reverseTransactionWithSameId = \App\Models\Account::where("transid", "=", $reverseTransactionId)->first();
		if ($existingRefundTransaction || $reverseTransactionWithSameId) {
			throw new \Exception("Transaction Already Reversed");
		}
		$invoice = $transaction->invoice;
		$reversedTransaction = new \App\Models\Account();
		$reversedTransaction->amountOut = $transaction->amountIn;
		$reversedTransaction->refundId = $transaction->id;
		$reversedTransaction->transactionId = $reverseTransactionId;
		$reversedTransaction->invoiceId = $transaction->invoiceId;
		$reversedTransaction->exchangeRate = $transaction->exchangeRate;
		$reversedTransaction->fees = $transaction->fees * -1;
		$reversedTransaction->clientId = $transaction->clientId;
		$reversedTransaction->description = "Reversed Transaction ID: " . $transaction->transactionId;
		$reversedTransaction->paymentGateway = $transaction->paymentGateway;
		$reversedTransaction->date = \Carbon\Carbon::now();
		$reversedTransaction->save();
		if ($invoice) {
			self::reversePaymentActions($transaction, $reverseTransactionId, $originalTransactionId);
		}
		$gateway = $transaction->paymentGateway;
		$paymentGateway = "No Gateway";
		if ($gateway) {
			try {
				$paymentGateway = \App\Module\Gateway::factory($gateway)->getDisplayName();
			} catch (\Exception $e) {
				$paymentGateway = $gateway;
			}
		}
		\App\Helpers\Functions::sendAdminMessage("Payment Reversed Notification", array("invoice_id" => $invoice->id, "transaction_id" => $originalTransactionId, "transaction_date" => (new \App\Helpers\Functions)->fromMySQLDate($transaction->date), "transaction_amount" => new \App\Helpers\FormatterPrice($transaction->amountIn, \App\Helpers\Format::getCurrency($transaction->clientId)), "payment_method" => $paymentGateway), "account");
	}
	public static function reversePaymentActions(\App\Models\Account $transaction, $reverseTransactionId, $originalTransactionId)
	{
		$invoice = $transaction->invoice;
		$doChangeInvoiceStatus = (bool) \App\Helpers\Cfg::getValue("ReversalChangeInvoiceStatus");
		$doChangeDueDates = (bool) \App\Helpers\Cfg::getValue("ReversalChangeDueDates");
		if ($doChangeInvoiceStatus) {
			$invoice->status = "Collections";
			$invoice->save();
			\App\Helpers\LogActivity::Save("Payment Reversal - Invoice Status set to Collections - Invoice ID: " . $invoice->id, $invoice->clientId);
		}
		foreach ($invoice->items as $item) {
			switch ($item->type) {
				case "Addon":
				case "Hosting":
					if ($doChangeDueDates) {
						if ($item->type == "Addon") {
							$model = \App\Models\Hostingaddon::find($item->relatedEntityId);
							$activityLogEntry = "Payment Reversal - Modified Service Addon - Next Due Date changed from ";
							$activityLogSuffix = " - Service ID: " . $model->serviceId . " - Addon ID: " . $model->id;
						} else {
							$model = \App\Models\Hosting::find($item->relatedEntityId);
							$activityLogEntry = "Payment Reversal - Modified Product/Service - Next Due Date changed from ";
							$activityLogSuffix = " - Service ID: " . $model->id;
						}
						$defaultNextDueDate = $model->registrationDate;
						$nextDueDate = $model->nextDueDate;
						if (!$nextDueDate instanceof \Carbon\Carbon && $nextDueDate != "0000-00-00" && $nextDueDate != "1970-01-01") {
							$nextDueDate = \Carbon\Carbon::createFromFormat("Y-m-d", $nextDueDate);
						}
						if ($nextDueDate instanceof \Carbon\Carbon) {
							$activityLogEntry .= (string) $nextDueDate->toDateString() . " to";
							$nextDueDate = $nextDueDate->subMonths(self::getbillingcyclemonths($model->billingCycle));
							$activityLogEntry .= " " . $nextDueDate->toDateString();
						} else {
							$activityLogEntry .= (string) $nextDueDate . " to " . $defaultNextDueDate;
						}
						$activityLogEntry .= " - User ID: " . $model->clientId;
						$model->nextDueDate = $nextDueDate;
						$model->save();
						\App\Helpers\LogActivity::Save($activityLogEntry . $activityLogSuffix, $model->clientId);
					}
					break;
				case "Upgrade":
					$upgrade = DB::table("tblupgrades")->find($item->relatedEntityId);
					$service = \App\Models\Hosting::find($upgrade->relid);
					if ($service->serverId) {
						$server = new \App\Module\Server();
						$server->loadByServiceID($service->id);
						if ($server->functionExists("SuspendAccount")) {
							$server->call("SuspendAccount");
						}
					}
					break;
				case "AddFunds":
					DB::table("tblcredit")->insert(array("clientid" => $item->userId, "date" => \Carbon\Carbon::now()->toDateString(), "description" => "Reversed Transaction ID: " . $originalTransactionId, "amount" => $transaction->amountIn * -1));
					$transaction->client->credit -= $transaction->amountIn;
					$transaction->client->save();
					\App\Helpers\LogActivity::Save("Payment Reversal - Removed Credit - User ID: " . $item->userId . " - Amount: " . \App\Helpers\Format::formatCurrency($transaction->amountIn), $item->userId);
					break;
				case "Invoice":
					$reversedTransaction = new \App\Models\Account();
					$reversedTransaction->amountOut = $item->amount;
					$reversedTransaction->refundId = $transaction->id;
					$reversedTransaction->transactionId = $reverseTransactionId;
					$reversedTransaction->invoiceId = $item->relatedEntityId;
					$reversedTransaction->exchangeRate = $transaction->exchangeRate;
					$reversedTransaction->fees = 0;
					$reversedTransaction->clientId = $item->userId;
					$reversedTransaction->description = "Invoice Payment Reversal: Invoice ID: #" . $item->invoiceId;
					$reversedTransaction->paymentGateway = $transaction->paymentGateway;
					$reversedTransaction->date = \Carbon\Carbon::now();
					$reversedTransaction->save();
					if ($doChangeInvoiceStatus) {
						$reversedTransaction->invoice->status = "Collections";
						$reversedTransaction->invoice->save();
						\App\Helpers\LogActivity::Save("Payment Reversal - Invoice Status set to Collections - Invoice ID: " . $reversedTransaction->invoice->id, $item->userId);
					}
					break;
				case "DomainRegister":
				case "DomainRenew":
				case "DomainTransfer":
				case "DomainAddonDNS":
				case "DomainAddonEMF":
				case "DomainAddonIDP":
					break;
				default:
					if ($doChangeDueDates) {
						$model = NULL;
						$previousInvoiceItem = NULL;
						$activityLogEntry = "";
						$activityLogSuffix = "";
						if (substr($item->type, 0, 14) == "ProrataProduct") {
							$model = \App\Models\Hosting::find($item->relatedEntityId);
							$previousInvoiceItem = \App\Models\Invoiceitem::where("relid", "=", $item->relatedEntityId)->where("type", "=", "Service")->orderBy("id", "DESC")->first();
							$activityLogEntry = "Payment Reversal - Modified Product/Service - Next Due Date changed from ";
							$activityLogSuffix = " - Service ID: " . $model->id;
						} else {
							if (substr($item->type, 0, 12) == "ProrataAddon") {
								$model = \App\Models\Hostingaddon::find($item->relatedEntityId);
								$previousInvoiceItem = \App\Models\Invoiceitem::where("relid", "=", $item->relatedEntityId)->where("type", "=", "Addon")->orderBy("id", "DESC")->first();
								$activityLogEntry = "Payment Reversal - Modified Service Addon - Next Due Date changed from ";
								$activityLogSuffix = " - Service ID: " . $model->serviceId . " - Addon ID: " . $model->id;
							}
						}
						if ($model && $previousInvoiceItem) {
							$activityLogEntry .= (string) $model->nextDueDate . " to " . $previousInvoiceItem->dueDate . " - User ID: " . $model->clientId;
							$model->nextDueDate = $previousInvoiceItem->dueDate;
							$model->save();
							\App\Helpers\LogActivity::Save($activityLogEntry . $activityLogSuffix, $model->clientId);
						}
					}
			}
		}
	}

	 /*
	 		refundInvoicePayment

	 */

	 public static function refundInvoicePayment($transid, $amount, $sendtogateway, $addascredit = "", $sendemail = true, $refundtransid = "", $reverse = false){
			try{
				$transaction =\App\Models\Account::findOrFail($transid);
				//dd($transaction );
				$transid = $transaction->id;
				$invoiceid = $transaction->invoiceid;
				$gateway = $transaction->paymentGateway;
				$fullamount = $transaction->amountin;
				$fees = $transaction->fees;
				$gatewaytransid = $transaction->transid;
				$rate = $transaction->rate;
				$userid = $transaction->userid;

			}catch (Exception $e) {
					return "amounterror";
	 		}
			if (!$userid && $transaction->userid) {
					$userid = \App\Models\Invoice::find($transaction->invoiceid)->userid;
		  	}

			//$gatewayDATA=\App\Models\Paymentgateway::distinct('gateway')->get();
		//	$this->makeName($gateway);
		$gatewayDATA=\App\Models\Paymentgateway::selectRaw('DISTINCT gateway')->get();
		$gateways = array();
		foreach($gatewayDATA as $r){
			if(Invoice::isNameValid($r->gateway)){
				$gateways[] = $r->gateway;
			}
		}

		$gateway=in_array($gateway, $gateways) ? $gateway : "";


		$alreadyrefunded=\App\Models\Account::where('refundid', $transid)->sum('amountin');
		//dd($alreadyrefunded);
		$alreadyrefundedfees=\App\Models\Account::where('refundid', $transid)->sum('amountout');
		$fullamount -= $alreadyrefunded;
		$fees -= $alreadyrefundedfees * -1;
		if ($fees <= 0) {
			$fees = 0;
	  	}


		$invoicetotalpaid=\App\Models\Account::where('invoiceid', $invoiceid)->sum('amountin');
		$invoicetotalrefunded=\App\Models\Account::where('invoiceid', $invoiceid)->sum('amountout');
		if (!$amount) {
			$amount = $fullamount;
	 	}
		if (!$amount || $fullamount < $amount) {
			return "amounterror";
	   }
		$amount =\App\Helpers\Functions::format_as_currency($amount);
		if ($addascredit) {
			Invoice::addTransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid . " to Credit Balance", 0, $fees * -1, $amount, "", "", $invoiceid, "", $transid, $rate);
			Invoice::addtransaction($userid, 0, "Credit from Refund of Invoice ID " . $invoiceid, $amount, $fees, 0, "", "", "", "", "", "");
			\App\Helpers\LogActivity::Save("Refunded Invoice Payment to Credit Balance - Invoice ID: ".$invoiceid,$userid);

			$credit=new \App\Models\Credit();
			$credit->clientid =$userid;
			$credit->date = Carbon::now()->format("Y-m-d");
			$credit->description = "Credit from Refund of Invoice ID ".$invoiceid;
			$credit->amount = $amount;
			$credit->save();

			$client=\App\Models\Client::find($userid);
			$client->credit = $client->credit + $amount;
			$client->save();

			if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
				$tblinvoices=\App\Models\Invoice::find($invoiceid);
				$tblinvoices->status = 'Refunded';
				$tblinvoices->date_refunded = Carbon::now();
				$tblinvoices->save();
				\App\Helpers\Hooks::run_hook("InvoiceRefunded",['invoiceid' => (int) $invoiceid]);
			}
			if ($sendemail) {
           // sendMessage("Invoice Refund Confirmation", $invoiceid, array("invoice_refund_type" => "credit"));
        	}

		}

		$convertto =\App\Models\Paymentgateway::select('value')->where('gateway',$gateway)->where('setting','convertto')->first()->value;
		//dd($convertto);
		$client = \App\Models\Client::findOrFail($userid);

		if ($convertto) {
			$convertedamount = \App\Helpers\Format::convertCurrency($amount, $client->currencyId, $convertto, $rate);
			$refundCurrencyId = $convertto;
	  } else {
			$convertedamount = NULL;
			$refundCurrencyId = $client->currencyId;
	  }
	  $params = array();
	  //todo
	  //$module = \Module::find($gateway);
	  if ($sendtogateway) {
			$module = \Module::find($gateway);
			if ($module) {
				$params["amount"] = $convertedamount ? $convertedamount : $amount;
            $params["transid"] = $gatewaytransid;
            $params["paymentmethod"] = $gateway;
				if ($refundCurrencyId) {
					 $refundCurrency=\App\Models\Currency::find($refundCurrencyId);
					 if ($refundCurrency) {
						$params["currency"] = $refundCurrency->code;
				 	 }
				}
				if (!isset($params["currency"])) {
					$params["currency"] = "";
			 	}
				try {

					$className = "\\Modules\\Gateways\\{$gateway}\\Http\\Controllers\\{$gateway}Controller";
					$object = new $className();
					$gatewayresult=$object->refund($params);
					if (is_array($gatewayresult)) {
						$refundtransid = $gatewayresult["transid"];
						$rawdata = $gatewayresult["rawdata"];
						if (isset($gatewayresult["fees"])) {
							 $fees = $gatewayresult["fees"];
						}
						$gatewayresult = $gatewayresult["status"];
					}else{
						$gatewayresult = "error";
						$rawdata = "Returned false";
					}
					\App\Helpers\Gateway::logTransaction($gateway, $rawdata, "Refund " . ucfirst($gatewayresult));
				} catch (\Exception $e) {
					throw new \App\Exceptions\Module\NotServicable("Module not in serviceable");
				}
			}else{
				$gatewayresult = "manual";
				\App\Helpers\Hooks::run_hook("ManualRefund",['transid' => (int) $transid,'amount' => $amount ]);
			}

	  }else{
			$gatewayresult = "manual";
			\App\Helpers\Hooks::run_hook("ManualRefund",['transid' => (int) $transid,'amount' => $amount ]);
	  }

	  if ($gatewayresult == "success" || $gatewayresult == "manual") {
		  	if($gatewayresult == "manual"){
				$refundtransid=0;
			  }

			Invoice::addTransaction($userid, 0, "Refund of Transaction ID " . $gatewaytransid, 0, $fees * -1, $amount, $gateway, $refundtransid, $invoiceid, "", $transid, $rate);
			\App\Helpers\LogActivity::Save("Refunded Invoice Payment - Invoice ID: " . $invoiceid . " - Transaction ID: " . $transid, $userid);
			$invoicetotal=\App\Models\Invoice::find($invoiceid)->total;
			if ($invoicetotalpaid - $invoicetotalrefunded - $amount <= 0) {
				$inv=\App\Models\Invoice::find($invoiceid);
				$inv->status = 'Refunded';
				$inv->date_refunded = Carbon::now();
				$inv->save();
				\App\Helpers\Hooks::run_hook("InvoiceRefunded",['invoiceid' => (int) $invoiceid]);
				\App\Helpers\Functions::sendMessage("Invoice Refund Confirmation", $invoiceid, array("invoice_refund_type" => "gateway"));
			}
	  }


	  return $gatewayresult;

	 }

	 public static function isNameValid($gateway)
    {
        if (!is_string($gateway) || empty($gateway)) {
            return false;
        }
        if (!ctype_alnum(str_replace(array("_", "-"), "", $gateway))) {
            return false;
        }
        return true;
    }

	 public static function getInvoiceTotals(){
		$invoicesummary = array();
		$pfx = \Database::prefix();
		$paid=DB::table("{$pfx}invoices as invoices")
				->join("{$pfx}clients as clients",'invoices.userid','=','clients.id')
				->where('invoices.status','Paid')
				->select('currency')
				->selectRaw('COUNT(invoices.id) as id')
				->selectRaw('SUM(total) as total ')
				->groupBy('clients.currency')
				->first();
        if ($paid) {
            $invoicesummary[$paid->currency]["paid"] = $paid->total;
        }

		/* unpaid */
		$Unpaid=DB::table("{$pfx}invoices as invoices")
				->join("{$pfx}clients as clients",'invoices.userid','=','clients.id')
				->where('invoices.status','Unpaid')
				->where('invoices.duedate','>=',Carbon::now()->format('Y-m-d'))
				->select('currency')
				->selectRaw('COUNT(invoices.id) as id')
				->selectRaw('(SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM '.$pfx.'accounts WHERE '.$pfx.'accounts.invoiceid=invoices.id)),0)) as total ')
				->groupBy('clients.currency')
				->first();
        if ($Unpaid) {
            $invoicesummary[$Unpaid->currency]["unpaid"] = $Unpaid->total;
        }
		/*overdue*/
		$overdue=DB::table("{$pfx}invoices as invoices")
				->join("{$pfx}clients as clients",'invoices.userid','=','clients.id')
				->where('invoices.status','Unpaid')
				->where('invoices.duedate','<',Carbon::now()->format('Y-m-d'))
				->select('currency')
				->selectRaw('COUNT(invoices.id) as id')
				->selectRaw('(SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM '.$pfx.'accounts WHERE '.$pfx.'accounts.invoiceid=invoices.id)),0)) as total ')
				->groupBy('clients.currency')
				->first();
        if ($overdue) {
            $invoicesummary[$overdue->currency]["overdue"] = $overdue->total;
        }

		$totals = array();
		$helper=new \App\Helpers\AdminFunctions();

		foreach($invoicesummary  as $currency => $vals){
			$currency =$helper->getCurrency('', $currency);
			if (!isset($vals["paid"])) {
				$vals["paid"] = 0;
		  }
		  if (!isset($vals["unpaid"])) {
				$vals["unpaid"] = 0;
		  }
		  if (!isset($vals["overdue"])) {
				$vals["overdue"] = 0;
		  }

		  $paid =\App\Helpers\Format::formatCurrency($vals["paid"]);
		  $unpaid =\App\Helpers\Format::formatCurrency($vals["unpaid"]);
		  $overdue =\App\Helpers\Format::formatCurrency($vals["overdue"]);
		  $totals[] = ["currencycode" => $currency["code"], "paid" => $paid, "unpaid" => $unpaid, "overdue" => $overdue];
		}

		return $totals;

	 }

	public static function pdfInvoice($invoiceid)
  {
    global $CONFIG;
    global $_LANG;
    global $currency;
    $invoice = new \App\Helpers\InvoiceClass();
    $invoice->pdfCreate();
    $invoice->pdfInvoicePage($invoiceid);
    $pdfdata = $invoice->pdfOutput();
    return $pdfdata;
  }

}
