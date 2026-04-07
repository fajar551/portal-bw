<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Currency;
use App\Models\Client;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Format
{
	protected $request;
	protected static $currency = NULL;
	protected static $defaultFormat = NULL;
	protected static $defaultCurrencyDescriptor = array("format" => "1", "prefix" => "", "suffix" => "");
	
	const PREFIX = "{PREFIX}";
	const PRICE = "{PRICE}";
	const SUFFIX = "{SUFFIX}";

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Currency formatter
	 * 
	 * @example Format::Currency(1000, NULL, ['prefix' => 'Rp', 'format' => '3'])
	 */
	public static function Currency($amount = 0, $format = NULL, $currency = NULL)
	{
		if (is_null($format)) {
			// $format = self::$defaultFormat;
			$format = self::PREFIX . self::PRICE . self::SUFFIX;
		}
		if (is_null($currency)) {
			$currency = self::$currency;
		}
		if (!is_array($currency)) {
			$currency = self::$defaultCurrencyDescriptor;
		} else {
			foreach (self::$defaultCurrencyDescriptor as $key => $value) {
				if (!isset($currency[$key])) {
					$currency[$key] = $value;
				}
			}
		}

		$format_dm = "2";
		$format_dp = ".";
		$format_ts = "";
		if ($currency["format"] == 2) {
			$format_dm = "2";
			$format_dp = ".";
			$format_ts = ",";
		} else {
			if ($currency["format"] == 3) {
				$format_dm = "2";
				$format_dp = ",";
				$format_ts = ".";
			} else {
				if ($currency["format"] == 4) {
					$format_dm = "0";
					$format_dp = "";
					$format_ts = ",";
				}
			}
		}
		
		$formattedAmount = number_format($amount, $format_dm, $format_dp, $format_ts);
		$format = str_replace(self::PREFIX, $currency["prefix"], $format);
		$format = str_replace(self::PRICE, $formattedAmount, $format);
		$format = str_replace(self::SUFFIX, $currency["suffix"], $format);
		
		return $format;
	}

	/**
	 * Price formatter
	 * 
	 * @example Format::Price(10000)
	 */
	public static function Price($amount = 0)
	{
		// get from active currency
		$currency = Currency::active()->first();
		$prefix = $currency ? $currency->prefix : NULL;
		$suffix = $currency ? $currency->suffix : NULL;
		$format = $currency ? $currency->format : NULL;
		
		$currencyDetails = [
			'prefix' => $prefix,
			'suffix' => $suffix,
			'format' => $format,
		];

		return self::Currency($amount, NULL, $currencyDetails);
	}

	/**
	 * Return as currency
	 * 
	 * @example Format::AsCurrency(10000)
	 */
	public static function AsCurrency($amount)
	{
		if (0 < $amount) {
			$amount += 1.0E-6;
		}
		$amount = round($amount, 2);
		$amount = sprintf("%01.2f", $amount);
		return $amount;
	}

	public static function GetCurrencyOLD($userid = "", $cartcurrency = "")
	{
		static $usercurrencies = array();
		static $currenciesdata = array();
		if ($cartcurrency) {
			$currencyid = $cartcurrency;
		}
		if ($userid) {
			if (isset($usercurrencies[$userid])) {
				$currencyid = $usercurrencies[$userid];
			} else {
				$client = Client::find($userid);
				// $usercurrencies[$userid] = get_query_val("tblclients", "currency", array("id" => $userid));
				$usercurrencies[$userid] = $client->currency;
				$currencyid = $usercurrencies[$userid];
			}
		}
		if (isset($currencyid)) {
			if (isset($currenciesdata[$currencyid])) {
				$data = $currenciesdata[$currencyid];
			} else {
				// $currenciesdata[$currencyid] = $data = get_query_vals("tblcurrencies", "", array("id" => $currencyid));
				$currency = Currency::find($currencyid);
				$currenciesdata[$currencyid] = $data = $currency->toArray();
			}
		} else {
			$currency = Currency::where('default', 1)->first();
			// $data = get_query_vals("tblcurrencies", "", array("`default`" => "1"));
			$data = $currency->toArray();
		}
		$currency_array = array("id" => $data["id"], "code" => $data["code"], "prefix" => $data["prefix"], "suffix" => $data["suffix"], "format" => $data["format"], "rate" => $data["rate"]);
		return $currency_array;
	}
	public static function getCurrency($userid = "", $cartcurrency = "")
    {
        static $usercurrencies = array();
        static $currenciesdata = array();
        if ($cartcurrency) {
            $currencyid = $cartcurrency;
        }
        if ($userid) {
            if (isset($usercurrencies[$userid])) {
                $currencyid = $usercurrencies[$userid];
            } else {
				$usercurrencies[$userid] = \App\Models\Client::where(array("id" => $userid))->value('currency') ?? 1;
                $currencyid = $usercurrencies[$userid];
            }
        }
        if (isset($currencyid)) {
            if (isset($currenciesdata[$currencyid])) {
                $data = $currenciesdata[$currencyid];
            } else {
                // $currenciesdata[$currencyid] = $data = get_query_vals("tblcurrencies", "", array("id" => $currencyid));4
				$c = \App\Models\Currency::where(array("id" => $currencyid))->first();
				$currenciesdata[$currencyid] = $data = $c ? $c->toArray() : [];
            }
        } else {
			$c = \App\Models\Currency::where(array("default" => "1"))->first();
            $data = $c ? $c->toArray() : [];
        }
        $currency_array = array("id" => $data["id"], "code" => $data["code"], "prefix" => $data["prefix"], "suffix" => $data["suffix"], "format" => $data["format"], "rate" => $data["rate"]);
        return $currency_array;
    }

	public static function ConvertCurrencyOLD($amount, $from, $to, $base_currency_exchange_rate = "")
	{
		if (!$base_currency_exchange_rate) {
			$currency = Currency::select('rate')->where('id', $from)->first();
			$base_currency_exchange_rate = $currency->rate;
		}
		
		$currency = Currency::select('rate')->where('id', $to)->first();
		$convertto_currency_exchange_rate = $currency->rate;

		if (!$base_currency_exchange_rate) {
			$base_currency_exchange_rate = 1;
		}
		if (!$convertto_currency_exchange_rate) {
			$convertto_currency_exchange_rate = 1;
		}

		$convertto_amount = self::AsCurrency($amount / $base_currency_exchange_rate * $convertto_currency_exchange_rate);
		return $convertto_amount;
	}
	public static function convertCurrency($amount, $from, $to, $base_currency_exchange_rate = "")
    {
        if (!$base_currency_exchange_rate) {
			$result = Currency::where(array("id" => $from));
            $data = $result;
            $base_currency_exchange_rate = $data->value("rate");
        }
		$result = Currency::where(array("id" => $to));
		$data = $result;
        $convertto_currency_exchange_rate = $data->value("rate");
        if (!$base_currency_exchange_rate) {
            $base_currency_exchange_rate = 1;
        }
        if (!$convertto_currency_exchange_rate) {
            $convertto_currency_exchange_rate = 1;
        }
        $convertto_amount = \App\Helpers\Functions::format_as_currency($amount / $base_currency_exchange_rate * $convertto_currency_exchange_rate);
        return $convertto_amount;
    }

	/**
	 * formatCurrency
	 */
	public static function formatCurrency($amount, $currencyType = false)
	{
		global $currency;
		if ($currencyType === false || !is_numeric($currencyType)) {
			if (is_numeric($currency)) {
				$currencyType = $currency;
			} else {
				if (is_array($currency) && isset($currency["id"]) && is_numeric($currency["id"])) {
					$currencyType = $currency["id"];
				}
			}
		}
		$currencyDetails = array();
		if (is_numeric($currencyType) && 0 < $currencyType) {
			$currencyDetails = self::getCurrency("", $currencyType);
		}
		if (!$currencyDetails || !is_array($currencyDetails) || !isset($currencyDetails["id"])) {
			$currencyDetails = self::getCurrency();
		}
		if (0 < $amount) {
			$amount += 1.0E-6;
		}
		$amount = round($amount, 2);
		return new \App\Helpers\FormatterPrice($amount, $currencyDetails);
	}
}
