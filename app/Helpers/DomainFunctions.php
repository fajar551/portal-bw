<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Auth, DB;
use App\Helpers\Cfg;

class DomainFunctions
{
	public static function getTLDList($type = "register")
	{
		global $currency;
		$auth = Auth::guard('web')->user();
		$currency_id = $currency["id"];
		$userId = (int) \Session::get("uid");
		$userId = $auth ? $auth->id : 0;
		if (!$currency_id) {
			$currency_id = \Session::get('currency') ? \Session::get('currency') : "";
			$currency = \App\Helpers\Format::getCurrency($userId, $currency_id);
			$currency_id = $currency["id"];
		}
		$clientgroupid = $auth ? \App\Models\Client::where(array("id" => $userId))->value("groupid") : "0";
		if (!$clientgroupid) {
			$clientgroupid = 0;
		}
		$isReg = strcasecmp($type, "register") == 0;
		$checkfields = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially");
		$query = "SELECT DISTINCT tbldomainpricing.extension";
		$query .= " FROM tbldomainpricing";
		$query .= " JOIN tblpricing ON tblpricing.relid=tbldomainpricing.id";
		if (!$isReg) {
			$query .= " JOIN tblpricing AS regcheck ON regcheck.relid=tbldomainpricing.id";
		}
		$query .= " WHERE";
		$query .= " tblpricing.type=?";
		$query .= " AND tblpricing.currency=?";
		$query .= " AND (tblpricing.tsetupfee=? OR tblpricing.tsetupfee=0)";
		if (!$isReg) {
			$query .= " AND regcheck.type=\"domainregister\"";
			$query .= " AND regcheck.currency=tblpricing.currency";
			$query .= " AND regcheck.tsetupfee=tblpricing.tsetupfee";
		}
		$extraConds = array();
		foreach ($checkfields as $field) {
			$cond = "(tblpricing." . $field . " >= 0 ";
			if (!$isReg) {
				$cond .= " AND regcheck." . $field . " >= 0";
			}
			$cond .= ")";
			$extraConds[] = $cond;
		}
		$query .= " AND (" . implode(" OR ", $extraConds) . ")";
		$query .= " ORDER BY tbldomainpricing.order ASC";
		$bindings = array("domain" . $type, $currency_id, $clientgroupid);
		$result = DB::connection()->select($query, $bindings);
		$extensions = array_map(function ($item) {
			return $item->extension;
		}, $result);
		return $extensions;
	}

	public static function getSpotlightTldsWithPricing()
	{
		return self::multipletldpricelistings(self::getspotlighttlds());
	}

	public static function getSpotlightTlds()
	{
		return array_filter(explode(",", CFG::getValue("SpotlightTLDs")), function ($item) {
			return $item;
		});
	}

	public static function multipleTldPriceListings(array $tlds)
	{
		$auth = Auth::guard('web')->user();
		$tldPriceListings = array();
		static $groups = NULL;
		if (is_null($groups)) {
			$groups = DB::table("tbldomainpricing")->pluck("group", "extension");
		}
		foreach ($tlds as $tld) {
			$tldPricing = \App\Helpers\Domain::gettldpricelist($tld, true, "", $auth ? $auth->id : 0);
			$firstOption = current($tldPricing);
			$year = key($tldPricing);
			$saleGroup = isset($groups[$tld]) && strtolower($groups[$tld]) != "none" ? strtolower($groups[$tld]) : "";
			$tldPriceListings[] = array("tld" => $tld, "tldNoDots" => str_replace(".", "", $tld), "period" => $year, "register" => isset($firstOption["register"]) ? $firstOption["register"] : "", "transfer" => isset($firstOption["transfer"]) ? $firstOption["transfer"] : "", "renew" => isset($firstOption["renew"]) ? $firstOption["renew"] : "", "group" => $saleGroup, "groupDisplayName" => $saleGroup ? \Lang::get("client.domainCheckerSalesGroup." . $saleGroup) : "");
		}
		return $tldPriceListings;
	}

	public static function cleanDomainInput($val)
	{
		global $CONFIG;
		$val = trim($val);
		if (!$CONFIG["AllowIDNDomains"]) {
			$val = strtolower($val);
		}
		return $val;
	}
}
