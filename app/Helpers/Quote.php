<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\LogActivity;
use App\Helpers\Cfg;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Quote
{
	/**
	 * saveQuote
	 */
	public static function saveQuote($id = 0, $subject = "", $stage = "", $datecreated = "", $validuntil = "", $clienttype = "", $userid = 0, $firstname = "", $lastname = "", $companyname = "", $email = "", $address1 = "", $address2 = "", $city = "", $state = "", $postcode = "", $country = "", $phonenumber = "", $currency = 0, array $lineitems = array(), $proposal = "", $customernotes = "", $adminnotes = "", $updatepriceonly = false, $taxId = "")
	{
		global $CONFIG;
		if (!$id) {
			$q = \App\Models\Quote::insert(array("subject" => $subject, "stage" => $stage, "datecreated" => (new \App\Helpers\SystemHelper())->toMySQLDate($datecreated), "validuntil" => (new \App\Helpers\SystemHelper())->toMySQLDate($validuntil), "lastmodified" => \Carbon\Carbon::now()));
			$id = $q->id;
			$newQuote = true;
		} else {
			$newQuote = false;
		}
		if ($clienttype == "new") {
			$userid = 0;
			$fortax_state = $state;
			$fortax_country = $country;
			$isClientTaxExempt = false;
			if ($taxId) {
				$isClientTaxExempt = WHMCS\Billing\Tax\Vat::validateNumber($taxId) && \App\Helpers\Cfg::getValue("TaxEUTaxExempt");
			}
		} else {
			$clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($userid);
			$fortax_state = $clientsdetails["state"];
			$fortax_country = $clientsdetails["country"];
			$isClientTaxExempt = $clientsdetails["taxexempt"];
		}
		$taxlevel1 = \App\Helpers\Invoice::getTaxRate(1, $fortax_state, $fortax_country);
		$taxlevel2 = \App\Helpers\Invoice::getTaxRate(2, $fortax_state, $fortax_country);
		$subtotal = 0;
		$taxableamount = 0;
		$tax1 = 0;
		$tax2 = 0;
		if ($lineitems) {
			foreach ($lineitems as $linedata) {
				$line_id = $linedata["id"];
				$line_desc = $linedata["desc"];
				$line_qty = $linedata["qty"];
				$line_up = $linedata["up"];
				$line_discount = $linedata["discount"];
				$line_taxable = $linedata["taxable"];
				if ($line_id) {
					\App\Models\Quoteitem::where('id', $line_id)->update(array("description" => $line_desc, "quantity" => $line_qty, "unitprice" => $line_up, "discount" => $line_discount, "taxable" => $line_taxable));
				} else {
					\App\Models\Quoteitem::insert(array("quoteid" => $id, "description" => $line_desc, "quantity" => $line_qty, "unitprice" => $line_up, "discount" => $line_discount, "taxable" => $line_taxable));
				}
				$lineitemamount = $line_qty * $line_up * (1 - $line_discount / 100);
				$subtotal += $lineitemamount;
				if ($line_taxable) {
					$taxableamount += $lineitemamount;
				}
			}
		} else {
			$result = \App\Models\Quoteitem::where('quoteid', $id)->orderBy('id', 'ASC')->get();
			foreach ($result->toArray() as $data) {
				$line_qty = $data["quantity"];
				$line_unitprice = $data["unitprice"];
				$line_discount = $data["discount"];
				$line_taxable = $data["taxable"];
				$lineitemamount = round($line_qty * $line_unitprice * (1 - $line_discount / 100), 2);
				$subtotal += $lineitemamount;
				if ($line_taxable) {
					$taxableamount += $lineitemamount;
				}
			}
		}
		if (Cfg::getValue("TaxEnabled")) {
			if (0 < $taxlevel1["rate"] && !$isClientTaxExempt) {
				if ($CONFIG["TaxType"] == "Inclusive") {
					$tax1 = \App\Helpers\Functions::format_as_currency($taxableamount / (100 + $taxlevel1["rate"]) * $taxlevel1["rate"]);
				} else {
					$tax1 = \App\Helpers\Functions::format_as_currency($taxableamount * $taxlevel1["rate"] / 100);
				}
			}
			if (0 < $taxlevel2["rate"] && !$isClientTaxExempt) {
				if ($CONFIG["TaxType"] == "Inclusive") {
					$tax2 = \App\Helpers\Functions::format_as_currency($taxableamount / (100 + $taxlevel2["rate"]) * $taxlevel2["rate"]);
				} else {
					if ($CONFIG["TaxL2Compound"]) {
						$tax2 = \App\Helpers\Functions::format_as_currency(($taxableamount + $tax1) * $taxlevel2["rate"] / 100);
					} else {
						$tax2 = \App\Helpers\Functions::format_as_currency($taxableamount * $taxlevel2["rate"] / 100);
					}
				}
			}
		}
		if ($CONFIG["TaxType"] == "Inclusive") {
			$total = $subtotal;
			$subtotal = $subtotal - $tax1 - $tax2;
		} else {
			$total = $subtotal + $tax1 + $tax2;
		}
		if ($updatepriceonly) {
			\App\Models\Quote::where('id', $id)->update(array("subtotal" => $subtotal, "tax1" => $tax1, "tax2" => $tax2, "total" => $total));
		} else {
			\App\Models\Quote::where('id', $id)->update(array("subject" => $subject, "stage" => $stage, "datecreated" => (new \App\Helpers\SystemHelper())->toMySQLDate($datecreated), "validuntil" => (new \App\Helpers\SystemHelper())->toMySQLDate($validuntil), "lastmodified" => "now()", "userid" => $userid, "firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "tax_id" => $taxId, "currency" => $currency, "subtotal" => $subtotal, "tax1" => $tax1, "tax2" => $tax2, "total" => $total, "proposal" => $proposal, "customernotes" => $customernotes, "adminnotes" => $adminnotes));
		}
		if ($newQuote) {
			Hooks::run_hook("QuoteCreated", array("quoteid" => $id, "status" => $stage));
		} else {
			Hooks::run_hook("QuoteStatusChange", array("quoteid" => $id, "status" => $stage));
		}
		return $id;
	}

	public static function genQuotePDF($id)
	{
		global $CONFIG;
		global $_LANG;
		global $currency;
		$companyname = $CONFIG["CompanyName"];
		$companyurl = $CONFIG["Domain"];
		$companyaddress = $CONFIG["InvoicePayTo"];
		$companyaddress = explode("\n", $companyaddress);
		$quotenumber = $id;
		$result = \App\Models\Quote::where(array("id" => $id));
		$data = $result;
		$subject = $data->value("subject");
		$stage = $data->value("stage");
		$datecreated = (new \App\Helpers\Functions)->fromMySQLDate($data->value("datecreated"));
		$validuntil = (new \App\Helpers\Functions)->fromMySQLDate($data->value("validuntil"));
		$userid = $data->value("userid");
		$proposal = $data->value("proposal") ? $data->value("proposal") . "\n" : "";
		$notes = $data->value("customernotes") ? $data->value("customernotes") . "\n" : "";
		$currency = \App\Helpers\Format::getCurrency($userid, $data->value("currency"));
		if ($userid) {
			// TODO: getUsersLang($userid);
			$stage = self::getQuoteStageLang($stage);
			$clientsdetails = \App\Helpers\ClientHelper::getClientsDetails($userid);
		} else {
			$clientsdetails["firstname"] = $data->value("firstname");
			$clientsdetails["lastname"] = $data->value("lastname");
			$clientsdetails["companyname"] = $data->value("companyname");
			$clientsdetails["email"] = $data->value("email");
			$clientsdetails["address1"] = $data->value("address1");
			$clientsdetails["address2"] = $data->value("address2");
			$clientsdetails["city"] = $data->value("city");
			$clientsdetails["state"] = $data->value("state");
			$clientsdetails["postcode"] = $data->value("postcode");
			$clientsdetails["country"] = $data->value("country");
			$clientsdetails["phonenumber"] = $data->value("phonenumber");
		}
		$taxlevel1 = \App\Helpers\Invoice::getTaxRate(1, $clientsdetails["state"], $clientsdetails["country"]);
		$taxlevel2 = \App\Helpers\Invoice::getTaxRate(2, $clientsdetails["state"], $clientsdetails["country"]);
		$countries = new \App\Helpers\Country();
		$clientsdetails["country"] = $countries->getName($clientsdetails["country"]);
		$subtotal = \App\Helpers\Format::formatCurrency($data->value("subtotal"));
		$tax1 = \App\Helpers\Format::formatCurrency($data->value("tax1"));
		$tax2 = \App\Helpers\Format::formatCurrency($data->value("tax2"));
		$total = \App\Helpers\Format::formatCurrency($data->value("total"));
		$lineitems = array();
		$result = \App\Models\Quoteitem::where(array("quoteid" => $id))->orderBy("id", "ASC")->get();
		foreach ($result->toArray() as $data) {
			$line_id = $data["id"];
			$line_desc = $data["description"];
			$line_qty = $data["quantity"];
			$line_unitprice = $data["unitprice"];
			$line_discount = $data["discount"];
			$line_taxable = $data["taxable"];
			$line_total = \App\Helpers\Functions::format_as_currency($line_qty * $line_unitprice * (1 - $line_discount / 100));
			$lineitems[] = array("id" => $line_id, "description" => htmlspecialchars(\App\Helpers\Sanitize::decode($line_desc)), "qty" => $line_qty, "unitprice" => $line_unitprice, "discount" => $line_discount, "taxable" => $line_taxable, "total" => \App\Helpers\Format::formatCurrency($line_total));
		}
		$tplvars = array();
		$tplvars["companyname"] = $companyname;
		$tplvars["companyurl"] = $companyurl;
		$tplvars["companyaddress"] = $companyaddress;
		$tplvars["paymentmethod"] = $paymentmethod;
		$tplvars["quotenumber"] = $quotenumber;
		$tplvars["subject"] = $subject;
		$tplvars["stage"] = $stage;
		$tplvars["datecreated"] = $datecreated;
		$tplvars["validuntil"] = $validuntil;
		$tplvars["userid"] = $userid;
		$tplvars["clientsdetails"] = $clientsdetails;
		$tplvars["proposal"] = $proposal;
		$tplvars["notes"] = $notes;
		$tplvars["taxlevel1"] = $taxlevel1;
		$tplvars["taxlevel2"] = $taxlevel2;
		$tplvars["subtotal"] = $subtotal;
		$tplvars["tax1"] = $tax1;
		$tplvars["tax2"] = $tax2;
		$tplvars["total"] = $total;
		$tplvars = \App\Helpers\Sanitize::decode($tplvars);
		$tplvars["lineitems"] = $lineitems;
		$tplvars["pdfFont"] = \App\Helpers\Cfg::getValue("TCPDFFont");
		$invoice = new \App\Helpers\InvoiceClass();
		$invoice->pdfCreate($_LANG["quotenumber"] . $id);
		$invoice->pdfAddPage("pdf.quotepdf", $tplvars);
		$pdfdata = $invoice->pdfOutput();
		return $pdfdata;
	}

	public static function getQuoteStageLang($stage)
	{
		global $_LANG;
		$translation = $_LANG["quotestage" . strtolower(str_replace(" ", "", $stage))];
		if (!$translation) {
			$translation = $stage;
		}
		return $translation;
	}
}
