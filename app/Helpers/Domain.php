<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Domainpricing;
use App\Models\Client;
use App\Models\Pricing;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Database;

// Import Helpers Class here
use App\Helpers\AdminFunctions;
use App\Helpers\Format;

class Domain {

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->prefix=Database::prefix();
	}

	public static function GetTLDPriceList($tld, $display = false, $renewpricing = "", $userid = 0, $useCache = true) {
		global $currency;

		if (!$currency || !is_array($currency)) {
            $currency = \App\Helpers\Format::getCurrency(\Auth::user()->id, session("currency"));
        }

		if (!$userid && \Auth::guard('web')->user()) {
            $userid = \Auth::guard('web')->user()->id;
        }

		if (ltrim($tld, ".") == $tld) {
            $tld = "." .$tld;
        }

        static $pricingCache = NULL;
        $cacheKey = NULL;

		if (!$pricingCache) {
            $pricingCache = array();
        } else {
            foreach ($pricingCache as $key => $pricing) {
                if ($pricing["tld"] == $tld && $pricing["display"] == $display && $pricing["renewpricing"] == $renewpricing && $pricing["userid"] == $userid) {
                    if ($useCache) {
                        return $pricing["pricing"];
                    }

                    $cacheKey = $key;
                    break;
                }
            }
        }

		if (is_null($cacheKey)) {
            $pricing = array("tld" => $tld, "display" => $display, "renewpricing" => $renewpricing, "userid" => $userid);
            $cacheKey = count($pricingCache);
            $pricingCache[$cacheKey] = $pricing;
        }

		if ($renewpricing == "renew") {
            $renewpricing = true;
        }

        $currency_id = $currency["id"];
        
		try {
			$extensionData = Domainpricing::where("extension", $tld)->firstOrFail();
            $id = $extensionData->id;
        } catch (\Exception $e) {
            return array();
        }

		$clientgroupid = $userid ? \App\Models\Client::select("groupid")->where("id", $userid)->value("groupid") : "0";
        $checkfields = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially");
        $pricingData = \DB::table("tblpricing")->whereIn("type", array("domainregister", "domaintransfer", "domainrenew"))->where("currency", "=", $currency_id)->where("relid", "=", $id)->orderBy("tsetupfee", "desc")->get();
        $sortedData = array("domainregister" => array(), "domaintransfer" => array(), "domainrenew" => array());
        foreach ($pricingData as $entry) {
            $entryPricingGroupId = (int) $entry->tsetupfee;
            if ($entryPricingGroupId == 0 || $entryPricingGroupId == $clientgroupid) {
                $type = $entry->type;
                if (empty($sortedData[$type])) {
                    $sortedData[$type] = (array) $entry;
                }
            }
        }

		if (!$renewpricing || $renewpricing === "transfer") {
            $data = $sortedData["domainregister"];
            foreach ($checkfields as $k => $v) {
                $register[$k + 1] = $data[$v] ?? -1;
            }

            $data = $sortedData["domaintransfer"];
            foreach ($checkfields as $k => $v) {
                $transfer[$k + 1] = $data[$v] ?? -1;
            }
        }

		if (!$renewpricing || $renewpricing !== "transfer") {
            $data = $sortedData["domainrenew"];
            foreach ($checkfields as $k => $v) {
                $renew[$k + 1] = $data[$v] ?? -1;
            }
        }
        
		$tldpricing = array();
        $years = 1;

		while ($years <= 10) {
            if ($renewpricing === "transfer") {
                if (0 <= $register[$years] && 0 <= $transfer[$years]) {
                    if ($display) {
                        $transfer[$years] = Format::formatCurrency($transfer[$years]);
                    }
                    $tldpricing[$years]["transfer"] = $transfer[$years];
                }
            } else {
                if ($renewpricing) {
                    if (0 < $renew[$years]) {
                        if ($display) {
                            $renew[$years] = Format::formatCurrency($renew[$years]);
                        }
                        $tldpricing[$years]["renew"] = $renew[$years];
                    }
                } else {
                    if (0 <= $register[$years]) {
                        if ($display) {
                            $register[$years] = Format::formatCurrency($register[$years]);
                        }
                        $tldpricing[$years]["register"] = $register[$years];
                        if (0 <= $transfer[$years]) {
                            if ($display) {
                                $transfer[$years] = Format::formatCurrency($transfer[$years]);
                            }
                            $tldpricing[$years]["transfer"] = $transfer[$years];
                        }
                        if (0 < $renew[$years]) {
                            if ($display) {
                                $renew[$years] = Format::formatCurrency($renew[$years]);
                            }
                            $tldpricing[$years]["renew"] = $renew[$years];
                        }
                    }
                }
            }

            $years += 1;
        }

		$pricingCache[$cacheKey]["pricing"] = $tldpricing;
        
		return $tldpricing;

	}

    public static function getTLDPriceList2($tld, $display = false, $renewpricing = "", $userid = 0, $useCache = true)
    {
        global $currency;
        $auth = \Auth::guard('web')->user();
        $uid = $auth ? $auth->id : 0;
        if (!$currency || !is_array($currency)) {
            $currency = (new \App\Helpers\AdminFunctions)->getCurrency($uid, \Session::get("currency"));
        }
        if (!$userid && $uid) {
            $userid = $uid;
        }
        if (ltrim($tld, ".") == $tld) {
            $tld = "." . $tld;
        }
        static $pricingCache = NULL;
        $cacheKey = NULL;
        if (!$pricingCache) {
            $pricingCache = array();
        } else {
            foreach ($pricingCache as $key => $pricing) {
                if ($pricing["tld"] == $tld && $pricing["display"] == $display && $pricing["renewpricing"] == $renewpricing && $pricing["userid"] == $userid) {
                    if ($useCache) {
                        return $pricing["pricing"];
                    }
                    $cacheKey = $key;
                    break;
                }
            }
        }
        if (is_null($cacheKey)) {
            $pricing = array("tld" => $tld, "display" => $display, "renewpricing" => $renewpricing, "userid" => $userid);
            $cacheKey = count($pricingCache);
            $pricingCache[$cacheKey] = $pricing;
        }
        if ($renewpricing == "renew") {
            $renewpricing = true;
        }
        $currency_id = $currency["id"];
        try {
            $extensionData = \App\Models\Extension::where("extension", $tld)->firstOrFail(array("id"));
            $id = $extensionData->id;
        } catch (\Exception $e) {
            return array();
        }
        $clientgroupid = $userid ? \App\Models\Client::where(array("id" => $userid))->value('groupid') : "0";
        $checkfields = array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially");
        $pricingData = \DB::table("tblpricing")->whereIn("type", array("domainregister", "domaintransfer", "domainrenew"))->where("currency", "=", $currency_id)->where("relid", "=", $id)->orderBy("tsetupfee", "desc")->get();
        $sortedData = array("domainregister" => array(), "domaintransfer" => array(), "domainrenew" => array());
        foreach ($pricingData as $entry) {
            $entryPricingGroupId = (int) $entry->tsetupfee;
            if ($entryPricingGroupId == 0 || $entryPricingGroupId == $clientgroupid) {
                $type = $entry->type;
                if (empty($sortedData[$type])) {
                    $sortedData[$type] = (array) $entry;
                }
            }
        }
        if (!$renewpricing || $renewpricing === "transfer") {
            $data = $sortedData["domainregister"];
            foreach ($checkfields as $k => $v) {
                $register[$k + 1] = $data[$v] ?: -1;
            }
            $data = $sortedData["domaintransfer"];
            foreach ($checkfields as $k => $v) {
                $transfer[$k + 1] = $data[$v] ?: -1;
            }
        }
        if (!$renewpricing || $renewpricing !== "transfer") {
            $data = $sortedData["domainrenew"];
            foreach ($checkfields as $k => $v) {
                $renew[$k + 1] = $data[$v] ?: -1;
            }
        }
        $tldpricing = array();
        $years = 1;
        while ($years <= 10) {
            if ($renewpricing === "transfer") {
                if (0 <= $register[$years] && 0 <= $transfer[$years]) {
                    if ($display) {
                        $transfer[$years] = \App\Helpers\Format::formatCurrency($transfer[$years]);
                    }
                    $tldpricing[$years]["transfer"] = $transfer[$years];
                }
            } else {
                if ($renewpricing) {
                    if (0 < $renew[$years]) {
                        if ($display) {
                            $renew[$years] = \App\Helpers\Format::formatCurrency($renew[$years]);
                        }
                        $tldpricing[$years]["renew"] = $renew[$years];
                    }
                } else {
                    if (0 <= $register[$years]) {
                        if ($display) {
                            $register[$years] = \App\Helpers\Format::formatCurrency($register[$years]);
                        }
                        $tldpricing[$years]["register"] = $register[$years];
                        if (0 <= $transfer[$years]) {
                            if ($display) {
                                $transfer[$years] = \App\Helpers\Format::formatCurrency($transfer[$years]);
                            }
                            $tldpricing[$years]["transfer"] = $transfer[$years];
                        }
                        if (0 < $renew[$years]) {
                            if ($display) {
                                $renew[$years] = \App\Helpers\Format::formatCurrency($renew[$years]);
                            }
                            $tldpricing[$years]["renew"] = $renew[$years];
                        }
                    }
                }
            }
            $years += 1;
        }
        $pricingCache[$cacheKey]["pricing"] = $tldpricing;
        return $tldpricing;
    }

    public static function disableAutoRenew($domainid)
    {
        $authadmin = Auth::guard('admin')->user();
        $auth = Auth::guard('web')->user();
        $uid = $auth ? $auth->id : 0;
        $data = \App\Models\Domain::selectRaw("id,domain,nextduedate,userid")->where(array("id" => $domainid));
        $domainid = $data->value("id") ?? 0;
        $domainname = $data->value("domain") ?? "";
        $nextduedate = $data->value("nextduedate") ?? "";
        $userId = $data->value("userid") ?? 0;
        if (!$domainid) {
            return false;
        }
        \App\Models\Domain::where(array("id" => $domainid))->update(array("nextinvoicedate" => $nextduedate, "donotrenew" => "1"));
        $who = "Client";
        if ($authadmin) {
            $who = "Admin";
        }
        \App\Helpers\LogActivity::Save((string) $who . " Disabled Domain Auto Renew - Domain ID: " . $domainid . " - Domain: " . $domainname, $userId);
        $result = \App\Models\Invoiceitem::where(array("type" => "Domain", "relid" => $domainid, "status" => "Unpaid", "tblinvoices.userid" => $uid))
        ->join("tblinvoices", "tblinvoices.id","=","tblinvoiceitems.invoiceid")
        ->get();
        foreach ($result->toArray() as $data) {
            $itemid = $data["id"];
            $invoiceid = $data["invoiceid"];
            $result2 = \App\Models\Invoiceitem::where(array("invoiceid" => $invoiceid))->count();
            $data = $result2;
            $itemcount = $data;
            $otheritemcount = 0;
            if (1 < $itemcount) {
                $otheritemcount = \App\Models\Invoiceitem::whereRaw("invoiceid=" . (int) $invoiceid . " AND id!=" . (int) $itemid . " AND type NOT IN ('PromoHosting','PromoDomain','GroupDiscount')")->count();
            }
            if ($itemcount == 1 || $otheritemcount == 0) {
                \App\Models\Invoiceitem::where(array("id" => $itemid))->update(array("type" => "", "relid" => "0"));
                \App\Models\Invoice::where(array("id" => $invoiceid))->update(array("status" => "Cancelled"));
                \App\Helpers\LogActivity::Save("Cancelled Previous Domain Renewal Invoice - Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $userId);
                \App\Helpers\Hooks::run_hook("InvoiceCancelled", array("invoiceid" => $invoiceid));
            } else {
                \App\Models\Invoiceitem::where(array("id" => $itemid))->delete();
                \App\Helpers\Invoice::updateInvoiceTotal($invoiceid);
                \App\Helpers\LogActivity::Save("Removed Previous Domain Renewal Line Item - Invoice ID: " . $invoiceid . " - Domain: " . $domainname, $userId);
            }
        }
    }
}
