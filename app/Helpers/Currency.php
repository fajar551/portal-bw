<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Currency
{
	public static function currencyUpdatePricing($currencyid = "")
	{
		$result = \App\Models\Currency::where('default', '1');
		$data = $result;
		$defaultcurrencyid = $data->value("id");
		$where = array();
		if ($currencyid) {
			$where[] = array("id", "=", $currencyid);
		} else {
			$where[] = array("id", "!=", $defaultcurrencyid);
		}
		$currencies = array();
		$result = \App\Models\Currency::where($where)->get();
		foreach ($result->toArray() as $data) {
			$currencies[$data["id"]] = $data["rate"];
		}
		$result = \App\Models\Pricing::where(array("currency" => $defaultcurrencyid))->get();
		foreach ($result->toArray() as $data) {
			$type = $data["type"];
			$relid = $data["relid"];
			$msetupfee = $data["msetupfee"];
			$qsetupfee = $data["qsetupfee"];
			$ssetupfee = $data["ssetupfee"];
			$asetupfee = $data["asetupfee"];
			$bsetupfee = $data["bsetupfee"];
			$tsetupfee = $data["tsetupfee"];
			$monthly = $data["monthly"];
			$quarterly = $data["quarterly"];
			$semiannually = $data["semiannually"];
			$annually = $data["annually"];
			$biennially = $data["biennially"];
			$triennially = $data["triennially"];
			if (in_array($type, array("domainregister", "domaintransfer", "domainrenew"))) {
				$domaintype = true;
			} else {
				$domaintype = false;
			}
			if ($type == "configoptions") {
				$negativePriceAllowed = true;
			} else {
				$negativePriceAllowed = false;
			}
			foreach ($currencies as $id => $rate) {
				if ($rate <= 0) {
					continue;
				}
				if ($domaintype) {
					$result2 = \App\Models\Pricing::where(array("type" => $type, "currency" => $id, "relid" => $relid, "tsetupfee" => $tsetupfee));
				} else {
					$result2 = \App\Models\Pricing::where(array("type" => $type, "currency" => $id, "relid" => $relid));
				}
				$data = $result2;
				$pricing_id = $data->value("id");
				if (!$pricing_id) {
					$pricing_id = \App\Models\Pricing::insertGetId(array("type" => $type, "currency" => $id, "relid" => $relid, "tsetupfee" => $tsetupfee));
				}
				if ($negativePriceAllowed) {
					$update_msetupfee = round($msetupfee * $rate, 2);
					$update_qsetupfee = round($qsetupfee * $rate, 2);
					$update_ssetupfee = round($ssetupfee * $rate, 2);
					$update_asetupfee = round($asetupfee * $rate, 2);
					$update_bsetupfee = round($bsetupfee * $rate, 2);
				} else {
					$update_msetupfee = 0 < $msetupfee ? round($msetupfee * $rate, 2) : $msetupfee;
					$update_qsetupfee = 0 < $qsetupfee ? round($qsetupfee * $rate, 2) : $qsetupfee;
					$update_ssetupfee = 0 < $ssetupfee ? round($ssetupfee * $rate, 2) : $ssetupfee;
					$update_asetupfee = 0 < $asetupfee ? round($asetupfee * $rate, 2) : $asetupfee;
					$update_bsetupfee = 0 < $bsetupfee ? round($bsetupfee * $rate, 2) : $bsetupfee;
				}
				if ($domaintype) {
					$update_tsetupfee = $tsetupfee;
				} else {
					$update_tsetupfee = 0 < $tsetupfee ? round($tsetupfee * $rate, 2) : $tsetupfee;
				}
				if ($negativePriceAllowed) {
					$update_monthly = round($monthly * $rate, 2);
					$update_quarterly = round($quarterly * $rate, 2);
					$update_semiannually = round($semiannually * $rate, 2);
					$update_annually = round($annually * $rate, 2);
					$update_biennially = round($biennially * $rate, 2);
					$update_triennially = round($triennially * $rate, 2);
				} else {
					$update_monthly = 0 < $monthly ? round($monthly * $rate, 2) : $monthly;
					$update_quarterly = 0 < $quarterly ? round($quarterly * $rate, 2) : $quarterly;
					$update_semiannually = 0 < $semiannually ? round($semiannually * $rate, 2) : $semiannually;
					$update_annually = 0 < $annually ? round($annually * $rate, 2) : $annually;
					$update_biennially = 0 < $biennially ? round($biennially * $rate, 2) : $biennially;
					$update_triennially = 0 < $triennially ? round($triennially * $rate, 2) : $triennially;
				}
				if ($domaintype) {
					$updatecriteria = array("type" => $type, "currency" => $id, "relid" => $relid, "tsetupfee" => $tsetupfee);
				} else {
					$updatecriteria = array("type" => $type, "currency" => $id, "relid" => $relid);
				}
				\App\Models\Pricing::where($updatecriteria)->update(array("msetupfee" => $update_msetupfee, "qsetupfee" => $update_qsetupfee, "ssetupfee" => $update_ssetupfee, "asetupfee" => $update_asetupfee, "bsetupfee" => $update_bsetupfee, "tsetupfee" => $update_tsetupfee, "monthly" => $update_monthly, "quarterly" => $update_quarterly, "semiannually" => $update_semiannually, "annually" => $update_annually, "biennially" => $update_biennially, "triennially" => $update_triennially));
			}
		}
	}
}
