<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Quote;
use App\Models\Quoteitem;
use App\Models\Invoice as MInvoice;
use App\Models\Invoiceitem;
use App\Models\Client as MClient;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Import Helper Class here
use App\Helpers\Client;
use App\Helpers\SystemHelper;
use App\Helpers\Hooks;
use App\Helpers\Invoice;
use App\Helpers\Cfg;
use App\Models\Tax;
use App\Helpers\Gateway;
use App\Helpers\Functions;

class QuoteHelper {

	protected $request;

	public function __construct(Request $request) {
		$this->request = $request;
		$this->prefix = Database::prefix();
	}

	/**
	 * convertQuotetoInvoice
	 *
	 * @param $id integer|required the quote id
	 * @param $invoicetype string|null
	 * @param $invoiceduedate date|null
	 * @param $depositpercent double|0
	 * @param $depositduedate date|null, 
	 * @param $finalduedate date|null, 
	 * @param $sendemail boolean|false
	 * 
	 * @return Integer $invoiceid;
	*/
	public static function convertQuotetoInvoice($id, $invoicetype = NULL, $invoiceduedate = NULL, $depositpercent = 0, $depositduedate = NULL, $finalduedate = NULL, $sendemail = false) {
		global $CONFIG;
		global $_LANG;

		$clientHelper = new Client();
		$sysHelper = new SystemHelper();

		$data = Quote::find($id)->toArray();

		$userid = $data["userid"];
		$firstname = $data["firstname"];
		$lastname = $data["lastname"];
		$companyname = $data["companyname"];
		$email = $data["email"];
		$address1 = $data["address1"];
		$address2 = $data["address2"];
		$city = $data["city"];
		$state = $data["state"];
		$postcode = $data["postcode"];
		$country = $data["country"];
		$phonenumber = $data["phonenumber"];
		$taxId = $data["tax_id"];
		$currency = $data["currency"];

		if ($userid) {
			// TODO: @getUsersLang. getUsersLang not assign in any variable, should we removed it ?  
			// self::getUsersLang($userid);
			$clientsdetails = $clientHelper->DataClientsDetails($userid);
		} else {
			session(['currency' => $currency]);
			
			$userid = $clientHelper->AddClient2(
							$firstname, 
							$lastname, 
							$companyname, 
							$email, 
							$address1, 
							$address2, 
							$city, 
							$state, 
							$postcode, 
							$country, 
							$phonenumber, 
							substr(md5($id), 0, 10), 
							0, 
							"", 
							"on", 
							["tax_id" => $taxId]
						);
			
			// self::getUsersLang($userid);
			$clientsdetails = $clientHelper->DataClientsDetails($userid);
		}

		$taxExempt = $clientsdetails["taxexempt"];
		$taxRate = $taxRate2 = NULL;

		if ($taxExempt) {
			$taxRate = $taxRate2 = 0;
		}

		$subtotal = $data["subtotal"];
		$tax1 = $data["tax1"];
		$tax2 = $data["tax2"];
		$total = $data["total"];
		$duedate = $finaldate = "";
		if ($invoicetype == "deposit") {
			if ($depositduedate) {
				$duedate = $sysHelper->toMySQLDate($depositduedate);
			}

			$finaldate = $finalduedate ? $sysHelper->toMySQLDate($finalduedate) : date("Y-m-d");
		} else {
			if ($invoiceduedate) {
				$duedate = $sysHelper->toMySQLDate($invoiceduedate);
			}
		}

		$finalinvoiceid = 0;
		$invoice = self::newInvoice($userid, NULL, $taxRate, $taxRate2);
		
		if ($duedate) {
			$invoice->dateDue = $duedate;
		}
		$invoice->status = "Unpaid";
		$invoice->tax = $tax1;
		$invoice->tax2 = $tax2;
		$invoice->subtotal = $subtotal;
		$invoice->total = $total;
		$invoice->notes = \Lang::get('admin.quoteref') .$id; 
		$invoice->save();
		$invoiceid = $invoice->id;

		if ($finaldate) {
			$finalInvoice = self::newInvoice($userid, NULL, $taxRate, $taxRate2);

			if ($finaldate) {
				$finalInvoice->dateDue = $finaldate;
			}

			$finalInvoice->status = "Unpaid";
			$finalInvoice->tax1 = $tax1;
			$finalInvoice->tax2 = $tax2;
			$finalInvoice->subtotal = $subtotal;
			$finalInvoice->total = $total;
			$finalInvoice->notes = \Lang::get("admin.quoteref") .$id;
			$finalInvoice->save();
			$finalinvoiceid = $finalInvoice->id;
		}

		$result = Quoteitem::where("quoteid", $id)->orderBy("id", "ASC")->get()->toArray();

		foreach ($result as $data) {
			$line_id = $data["id"];
			$line_desc = $data["description"];
			$line_qty = $data["quantity"];
			$line_unitprice = $data["unitprice"];
			$line_discount = $data["discount"];
			$line_taxable = $data["taxable"];
			$line_total = Functions::format_as_currency($line_qty * $line_unitprice * (1 - $line_discount / 100));
			$lineitemdesc = (string) "$line_qty x $line_desc @ $line_unitprice";
			
			if (0 < $line_discount) {
				$lineitemdesc .= " - $line_discount% " .($_LANG["orderdiscount"] ?? "");
			}

			if ($finalinvoiceid) {
				$originalamount = $line_total;
				$line_total = $originalamount * $depositpercent / 100;
				$final_amount = $originalamount - $line_total;
				
				$invItems = new Invoiceitem();
				$invItems->invoiceid = $finalinvoiceid;
				$invItems->userid = $userid;
				$invItems->description = "$lineitemdesc (" .(100 - $depositpercent) ."% " .($_LANG["quotefinalpayment"] ?? "") .")";
				$invItems->amount = $final_amount;
				$invItems->taxed = $line_taxable;
				$invItems->save();

				$lineitemdesc .= " ($depositpercent% " .($_LANG["quotedeposit"] ?? "") .")";
			}

			$invItems = new Invoiceitem();
			$invItems->invoiceid = $finalinvoiceid;
			$invItems->userid = $userid;
			$invItems->description = $lineitemdesc;
			$invItems->amount = $line_total;
			$invItems->taxed = $line_taxable;
			$invItems->save();
		}

		Invoice::UpdateInvoiceTotal($invoiceid);

		if ($finalinvoiceid) {
			Invoice::UpdateInvoiceTotal($finalinvoiceid);
		}

		if (defined("APICALL")) {
			$source = "api";
			$user = auth()->guard('admin')->user()->id;
		} else {
			if (defined("ADMINAREA")) {	
				$source = "adminarea";
				$user = auth()->guard('admin')->user()->id;
			} else {
				$source = "clientarea";
				$user = auth()->user()->id ?? 0;
			}
		}

		$invoiceArr = [
			"source" => $source, 
			"user" => $user, 
			"invoiceid" => $invoiceid, 
			"status" => "Unpaid"
		];

    	Hooks::run_hook("InvoiceCreation", $invoiceArr);
		if ($sendemail) {
			Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
			
			// TODO: sendMessage
			// sendMessage("Invoice Created", $invoiceid);
		}
		
		Hooks::run_hook("InvoiceCreated", $invoiceArr);
		if ($finalinvoiceid) {
			$invoiceArr = [
				"source" => $source, 
				"user" => $user, 
				"invoiceid" => $finalinvoiceid, 
				"status" => "Unpaid"
			];

			Hooks::run_hook("InvoiceCreation", $invoiceArr);

			if ($sendemail) {
				Hooks::run_hook("InvoiceCreationPreEmail", $invoiceArr);
				
				// TODO: sendMessage
				// sendMessage("Invoice Created", $finalinvoiceid);
			}

			Hooks::run_hook("InvoiceCreated", $invoiceArr);
		}

		$quote = Quote::find($id);
		$quote->userid = $userid;
		$quote->stage = "Accepted";
		$quote->dateaccepted = Carbon::now()->toDateString();
		$quote->save();

		return $invoiceid;
	}

	/**
	 * convertQuotetoInvoice
	 *
	 * @param $clientId integer|required the existing client id
	 * @param $gateway string|null
	 * @param $taxRate1 double|null
	 * @param $taxRate2 double|null
	 * 
	 * @return App\Models\Invoice $invoice 
	*/
	public static function newInvoice($clientId, $gateway = NULL, $taxRate1 = NULL, $taxRate2 = NULL) {
        
		if (!$gateway) $gateway = Gateway::getClientsPaymentMethod($clientId);

        if (is_null($taxRate1) || is_null($taxRate2)) {
            $taxRate1 = 0;
            $taxRate2 = 0;

            if (Cfg::get("TaxEnabled")) {
                $clientData = MClient::select("id", "taxexempt", "state", "country")->where("id", $clientId)->first();

				if (!$clientData->taxexempt) {
                    
					if (!is_null($clientData->contact_country)) {
                        $taxCountry = $clientData->contact_country;
                        $taxState = $clientData->contact_state;
                    } else {
                        $taxCountry = $clientData->country;
                        $taxState = $clientData->state;
                    }

                    $taxLevel1 = Invoice::getTaxRate(1, $taxState, $taxCountry);
					$taxRate1 = $taxLevel1["rate"] ?? 0;

                    $taxLevel2 = Invoice::getTaxRate(2, $taxState, $taxCountry);
                    $taxRate2 = $taxLevel2["rate"] ?? 0;

                }
            }
        }

        $invoice = new MInvoice();
		$invoice->date = \Carbon\Carbon::now();
        $invoice->duedate = \Carbon\Carbon::now()->addDays((int) Cfg::get("CreateInvoiceDaysBefore"));
        $invoice->userid = $clientId;
        $invoice->status = "Draft";
        $invoice->paymentmethod = $gateway;
        $invoice->taxrate = $taxRate1;
        $invoice->taxRate2 = $taxRate2;

        return $invoice;

    }

	public static function getUsersLang($userId) {
        $client = MClient::find($userId);

		$existingLanguage = NULL;
		$languageName =  $client->language;

        if (empty($languageName)) {
            $languageName = Cfg::get('Language');
        }
        
		// TODO: What is existingLanguage for ?  
		// $existingLanguage = swapLang($languageName);
        // return $existingLanguage;

		return $languageName;
    }

	// TODO
    public static function swapLang($desiredLanguage) {
        global $_LANG;
        $existingLanguage = Lang::self();
        if ($desiredLanguage instanceof WHMCS\Language\ClientLanguage) {
            $languageName = $desiredLanguage->getName();
        } else {
            $languageName = $desiredLanguage;
        }
        if ($languageName != $existingLanguage->getName()) {
            if (!$desiredLanguage instanceof WHMCS\Language\ClientLanguage) {
                $desiredLanguage = WHMCS\Language\ClientLanguage::factory($languageName);
            }
            Lang::swap($desiredLanguage);
            $_LANG = $desiredLanguage->toArray();
        } else {
            $existingLanguage = NULL;
        }
        return $existingLanguage;
    }
}
