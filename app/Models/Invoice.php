<?php

namespace App\Models;

use DB;
use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Cfg;
use App\Events\InvoicePaid;

class Invoice extends AbstractModel
{
	use Filterable;
    protected static $invoiceStatusValues = array("Draft", "Unpaid", "Paid", "Cancelled", "Refunded", "Collections", "Payment Pending");
	protected $table = 'invoices';
	protected $appends = ["balance", "paymentGatewayName", "amountPaid"];
	protected $dates = ["date", "dateCreated", "duedate", "dateDue", "datepaid", "datePaid", "lastCaptureAttempt"];

	protected $columnMap = [
		"clientId" => "userid",
		"invoiceNumber" => "invoicenum",
		"dateCreated" => "date",
		"dateDue" => "duedate",
		"datePaid" => "datepaid",
		"tax1" => "tax",
		"taxRate1" => "taxrate",
		"taxRate2" => "taxrate2",
		"paymentGateway" => "paymentmethod",
		"adminNotes" => "notes",
		"lineItems" => "items",
		"dateRefunded" => "date_refunded",
		"dateCancelled" => "date_cancelled"
	];

	public $timestamps = false;

	const STATUS_CANCELLED = "Cancelled";
    const STATUS_COLLECTIONS = "Collections";
	const STATUS_DRAFT = "Draft";
	const STATUS_UNPAID = "Unpaid";
	const STATUS_PAID = "Paid";
	const STATUS_PAYMENT_PENDING = "Payment Pending";
    const STATUS_REFUNDED = "Refunded";

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    // protected static function booted()
    // {
    //     static::updated(function ($invoice) {
    //         if ($invoice->isDirty('status') && $invoice->status === 'Paid') {
    //             event(new InvoicePaid(['invoiceid' => $invoice->id]));
    //         }
    //     });
    // }

	public function client()
	{
		// return $this->belongsTo(Client::class, 'userid', 'id');
        return $this->belongsTo(\App\User\Client::class, "userid");
	}

	public function payMethod()
	{
		return $this->belongsTo(\App\Payment\PayMethod\Model::class, "paymethodid")->withTrashed();
	}

	public function items()
    {
        return $this->hasMany(Invoiceitem::class, "invoiceid");
    }

	public function shouldRenewRun($relatedId, $registrationDate, $type = "Hosting")
    {
        if (!in_array($type, array("Hosting", "Addon"))) {
            throw new \App\Exceptions\Module\NotServicable("Invalid Type for Comparison");
        }
        $table = "tblhosting";
        if ($type == "Addon") {
            $table = "tblhostingaddons";
        }
        $orderInvoice = DB::table($table)->select("tblorders.invoiceid")->where($table . ".id", $relatedId)->join("tblorders", $table . ".orderid", "=", "tblorders.id")->first();
        $runRenew = false;
        if (!is_null($orderInvoice) && $orderInvoice->invoiceid && $this->id != $orderInvoice->invoiceid) {
            $runRenew = true;
        }
        if (!$orderInvoice->invoiceid || $this->id == $orderInvoice->invoiceid) {
            $otherInvoice = Invoiceitem::where("type", $type)->where("relid", $relatedId)->where("invoiceid", "!=", $this->id)->where("invoiceid", "<", $this->id)->first();
            if ($otherInvoice) {
                $runRenew = true;
            }
            if (!$otherInvoice && $this->duedate->toDateString() != $registrationDate) {
                $runRenew = true;
            }
        }
        return $runRenew;
    }

    public static function getInvoiceStatusValues()
    {
        return self::$invoiceStatusValues;
    }

    public static function newInvoice($clientId, $gateway = NULL, $taxRate1 = NULL, $taxRate2 = NULL)
    {
        // if ((!$gateway || is_null($taxRate1) || is_null($taxRate2)) && !function_exists("getClientsPaymentMethod")) {
        //     require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "clientfunctions.php";
        // }
        if (!$gateway) {
            $gateway = \App\Helpers\Gateway::getClientsPaymentMethod($clientId);
        }
        if (is_null($taxRate1) || is_null($taxRate2)) {
            $taxRate1 = 0;
            $taxRate2 = 0;
            if (Cfg::getValue("TaxEnabled")) {
                $clientData = DB::table("tblclients")->where("tblclients.id", $clientId)->first(array("taxexempt", "tblclients.state", "tblclients.country"));
                if (!$clientData->taxexempt) {
                    if (isset($clientData->contact_country) && !is_null($clientData->contact_country)) {
                        $taxCountry = $clientData->contact_country;
                        $taxState = $clientData->contact_state;
                    } else {
                        $taxCountry = $clientData->country;
                        $taxState = $clientData->state;
                    }
                    $taxLevel1 = \App\Helpers\Invoice::getTaxRate(1, $taxState, $taxCountry);
                    $taxRate1 = $taxLevel1["rate"];
                    $taxLevel2 = \App\Helpers\Invoice::getTaxRate(2, $taxState, $taxCountry);
                    $taxRate2 = $taxLevel2["rate"];
                }
            }
        }
        $invoice = new self();
        $invoice->dateCreated = \Carbon\Carbon::now();
        $invoice->dateDue = \Carbon\Carbon::now()->addDays((int) Cfg::getValue("CreateInvoiceDaysBefore"));
        $invoice->clientId = $clientId;
        $invoice->status = self::STATUS_DRAFT;
        $invoice->paymentGateway = $gateway;
        $invoice->taxRate1 = $taxRate1;
        $invoice->taxRate2 = $taxRate2;
        return $invoice;
    }

    public function transactions()
    {
        return $this->hasMany(Account::class, "invoiceid");
    }

    public function getBalanceAttribute()
    {
        $totalDue = $this->total;
        $transactions = $this->transactions();
        if (0 < $transactions->count()) {
            $totalDue = $totalDue - $transactions->sum("amountin") + $transactions->sum("amountout");
        }
        return $totalDue;
    }
    public function getPaymentGatewayNameAttribute()
    {
        $gateway = $this->paymentGateway;
        try {
            $gatewayName = \App\Module\Gateway::factory($gateway)->getDisplayName();
        } catch (\Exception $e) {
            $gatewayName = $gateway;
        }
        return $gatewayName;
    }
    public function getAmountPaidAttribute()
    {
        $amountPaid = 0;
        $transactions = $this->transactions();
        if (0 < $transactions->count()) {
            $amountPaid = $transactions->sum("amountin") - $transactions->sum("amountout");
        }
        return $amountPaid;
    }

    public function addPayment($amount, $transactionId = "", $fees = 0, $gateway = "", $noEmail = false, \App\Helpers\Carbon $date = NULL)
    {
        if (!$amount) {
            throw new \App\Exceptions\Module\NotServicable("Amount is Required");
        }
        if ($amount < 0) {
            throw new \App\Exceptions\Module\NotServicable("Payment Amount Must be Greater than Zero");
        }
        $invoiceId = $this->id;
        if (!$gateway) {
            $gateway = $this->paymentGateway;
        }
        $userId = $this->clientId;
        $status = $this->status;
        if (in_array($status, array("Cancelled", "Draft"))) {
            throw new \App\Exceptions\Module\NotServicable("Payments can only be applied to invoices in Unpaid, Paid, Refunded or Collections statuses");
        }
        if (!$date) {
            $date = \App\Helpers\Carbon::now();
        }
        \App\Helpers\Invoice::addTransaction($userId, 0, "Invoice Payment", $amount, $fees, 0, $gateway, $transactionId, $invoiceId, (new \App\Helpers\Functions())->fromMySQLDate($date->toDateTimeString()));
        $balance = \App\Helpers\Functions::format_as_currency($this->balance);
        \App\Helpers\LogActivity::Save("Added Invoice Payment - Invoice ID: " . $invoiceId, $userId);
        \App\Helpers\Hooks::run_hook("AddInvoicePayment", array("invoiceid" => $invoiceId));
        if ($balance <= 0 && in_array($status, array("Unpaid", "Payment Pending"))) {
            \App\Helpers\Invoice::processPaidInvoice($invoiceId, $noEmail, (new \App\Helpers\Functions())->fromMySQLDate($date));
        } else {
            if (!$noEmail) {
                \App\Helpers\Functions::sendMessage("Invoice Payment Confirmation", $invoiceId);
            }
        }
        if ($balance <= 0) {
            $amountCredited = DB::table("tblcredit")->where("relid", $invoiceId)->sum("amount");
            $balance = $balance + $amountCredited;
            if ($balance < 0) {
                $balance = $balance * -1;
                DB::table("tblcredit")->insert(array("clientid" => $userId, "date" => $date->toDateTimeString(), "description" => "Invoice #" . $invoiceId . " Overpayment", "amount" => $balance, "relid" => $invoiceId));
                $this->client->credit += $balance;
                $this->client->save();
            }
        }
        return true;
    }

    public function setStatusUnpaid()
    {
        $this->status = self::STATUS_UNPAID;
        return $this;
    }

    public function saveRemoteCard($cardLastFour, $cardType, $expiryDate, $remoteToken)
    {
        if (!$remoteToken) {
            return NULL;
        }
        if ($cardLastFour && 4 < strlen($cardLastFour)) {
            $cardLastFour = substr($cardLastFour, -4);
        }
        $payMethod = null;
        if ($this->payMethod && !$this->payMethod->trashed() && $this->payMethod->payment instanceof \App\Payment\PayMethod\Adapter\RemoteCreditCard) {
            $payment = $this->payMethod->payment;
            if ($payment->getLastFour() === $cardLastFour && strcasecmp($payment->getCardType(), $cardType) === 0) {
                $payMethod = $this->payMethod;
            }
        }
        if (!$payMethod) {
            $payMethod = \App\Payment\PayMethod\Adapter\RemoteCreditCard::factoryPayMethod($this->client, $this->client, "New Card");
            if ($this->paymentGateway) {
                $gateway = \App\Module\Gateway::factory($this->paymentGateway);
                if ($gateway) {
                    $payMethod->setGateway($gateway);
                }
            }
            $payMethod->save();
        }
        $payment = $payMethod->payment;
        $payment->setLastFour($cardLastFour);
        if ($cardType) {
            $payment->setCardType($cardType);
        }
        $payment->setExpiryDate(\App\Helpers\Carbon::createFromCcInput($expiryDate));
        $payment->setRemoteToken($remoteToken);
        $payment->save();
        $this->payMethod()->associate($payMethod);
        $this->save();
    }
}
