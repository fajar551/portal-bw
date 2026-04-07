<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Client;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

// Import Helper Class here
use Cfg, Database;
use App\Helpers\Pwd;

class Vat
{
    const EU_COUNTRIES = array("AT" => 20, "BE" => 21, "BG" => 20, "CY" => 19, "CZ" => 21, "DE" => 19, "DK" => 25, "EE" => 20, "ES" => 21, "FI" => 24, "FR" => 20, "GB" => 20, "GR" => 24, "HR" => 25, "HU" => 27, "IE" => 23, "IT" => 22, "LT" => 21, "LU" => 17, "LV" => 21, "MT" => 18, "NL" => 21, "PL" => 23, "PT" => 23, "RO" => 19, "SE" => 25, "SI" => 22, "SK" => 20);

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
        $this->prefix = Database::prefix();
	}

	/**
	* isUsingNativeField
	* type: @static
	*
	* @param Boolean $contact
	* @return Boolean true or false
	*/
	public static function isUsingNativeField($contact = false)
    {
        return self::isTaxEnabled() && self::isTaxIdEnabled() && self::getFieldName($contact) == "tax_id";
    }
	
	/**
	* getFieldName
	* type: @static
	*
	* @param Boolean $contact
	* @return String $field
	*/
	public static function getFieldName($contact = false)
    {
        $field = "tax_id";
        $customFieldId = (int) Cfg::get("TaxVatCustomFieldId");
        if ($customFieldId && !$contact) {
            $field = "customfield[$customFieldId]";
        }

        return $field;
    }

	/**
	* isTaxIdEnabled
	* type: @static
	* Check whether Tax ID is enabled
	*
	* @return Boolean $isTaxIDDisabled | true or false
	*/
	public static function isTaxIdEnabled()
    {
        $isTaxIDDisabled = Cfg::get("TaxIDDisabled");
        if (is_null($isTaxIDDisabled)) {
            $isTaxIDDisabled = true;
        }

        return !$isTaxIDDisabled;
    }

	/**
	* isTaxIdDisabled
	* type: @static
	* Check whether Tax ID is disabled
	*
	* @return Boolean $isTaxIDDisabled | true or false
	*/
	public static function isTaxIdDisabled()
	{
        $isTaxIDDisabled = Cfg::get("TaxIDDisabled");
        
		if (is_null($isTaxIDDisabled)) {
            $isTaxIDDisabled = true;
        }

        return $isTaxIDDisabled;
    }

	/**
	* isTaxEnabled
	* type: @static
	* Check whether Tax is enabled
	*
	* @return Boolean from Cfg Helper | true or false
	*/
	public static function isTaxEnabled()
    {
        return (bool) Cfg::get("TaxEnabled");
    }

	/**
	* setTaxExempt
	* type: @static
	*
	* @param $client instance of Client Model
	* @return $exempt
	*/
	public static function setTaxExempt(Client &$client)
    {
        $exempt = false;
        $taxId = $client->taxId;
		
        if (self::getFieldName() !== "tax_id") {
            $customFieldId = (int) Cfg::get("TaxVatCustomFieldId");
            $data = $client->customFieldValues()->where("fieldid", $customFieldId)->first();
			$taxId = $data->value;
        }

        if (Cfg::get("TaxEUTaxExempt") && $taxId) {
            $validNumber = self::sendValidateTaxNumber($taxId);

            if ($validNumber && in_array($client->country, array_keys(self::EU_COUNTRIES))) {
                $exempt = true;
                if (Cfg::get("TaxEUHomeCountryNoExempt") && $client->country == Cfg::get("TaxEUHomeCountry")) {
                    $exempt = false;
                }
            }

            $client->taxExempt = $exempt;
            
			self::removeSessionData($taxId);
        }

        return $exempt;
    }

	/**
	* removeSessionData
	* type: @static
	* Remove existing session data related to Tax/Vat 
	*
	* @param int $vatNumber
	* @return @void
	*/
	protected static function removeSessionData($vatNumber)
    {
        $vatNumber = strtoupper($vatNumber);
        $vatNumber = preg_replace("/[^A-Z0-9]/", "", $vatNumber);
        $existingSessionValidation = session("TaxCodeValidation");
        // $existingSessionValidation = \WHMCS\Session::get("TaxCodeValidation");
        
		if ($existingSessionValidation) {
            $existingSessionValidation = json_decode(decrypt($existingSessionValidation), true);
            if (!is_array($existingSessionValidation)) {
                $existingSessionValidation = array();
            }
        }
        
		if (array_key_exists($vatNumber, $existingSessionValidation)) {
            unset($existingSessionValidation[$vatNumber]);
        }

        session(["TaxCodeValidation" => (new Pwd())->encrypt(json_encode($existingSessionValidation))]);
    }

	public static function getLabel($prefix = "tax")
    {
        $key = "taxLabel";
        if (Cfg::getValue("TaxVATEnabled")) {
            $key = "vatLabel";
        }
        if ($prefix) {
            $key = $prefix . "." . $key;
        }
        return $key;
    }

    public static function validateNumber($vatNumber = "")
    {
        if (class_exists("SoapClient") && \App\Helpers\Cfg::getValue("TaxEUTaxValidation") && $vatNumber) {
            return self::sendValidateTaxNumber($vatNumber);
        }
        return true;
    }
    protected static function sendValidateTaxNumber($vatNumber)
    {
        $vatNumber = strtoupper($vatNumber);
        $vatNumber = preg_replace("/[^A-Z0-9]/", "", $vatNumber);
        $existingSessionValidation = \Session::get("TaxCodeValidation");
        $valid = 0;
        if ($existingSessionValidation) {
            $existingSessionValidation = json_decode((new \App\Helpers\Pwd)->decrypt($existingSessionValidation), true);
            if (!is_array($existingSessionValidation)) {
                $existingSessionValidation = array();
            }
        }
        if (!array_key_exists($vatNumber, $existingSessionValidation)) {
            $vat_prefix = substr($vatNumber, 0, 2);
            $vat_num = substr($vatNumber, 2);
            try {
                $taxCheck = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array("connection_timeout" => 5));
                $taxValid = $taxCheck->checkVat(array("countryCode" => $vat_prefix, "vatNumber" => $vat_num));
                $existingSessionValidation[$vatNumber] = $taxValid->valid;
                $valid = $taxValid->valid;
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Tax Code Check Failure - " . $vatNumber . " - " . $e->getMessage());
            }
            // \Session::set("TaxCodeValidation", encrypt(json_encode($existingSessionValidation)));
            session('TaxCodeValidation', (new \App\Helpers\Pwd)->encrypt(json_encode($existingSessionValidation)));
        } else {
            $valid = $existingSessionValidation[$vatNumber];
        }
        return (bool) $valid;
    }
}
