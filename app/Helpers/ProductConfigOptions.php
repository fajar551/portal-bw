<?php
namespace App\Helpers;

use DB;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductConfigOptions
{
	protected $cache = array();
    protected function getCurrencyID()
    {
        $currency = (new \App\Helpers\AdminFunctions())->getCurrency();
        return (int) $currency["id"];
    }
    protected function isCached($productID)
    {
        return isset($this->cache[$productID]) && is_array($this->cache[$productID]);
    }
    protected function getFromCache($productID, $optionLabel)
    {
        if ($this->isCached($productID)) {
            return $this->cache[$productID][$optionLabel];
        }
        return array();
    }
    protected function storeToCache($productID, $optionLabel, $optionsData)
    {
        $this->cache[$productID][$optionLabel] = $optionsData;
        return true;
    }
    protected function loadData($productID)
    {
        $ops = array();
        if (!$this->isCached($productID)) {
            $currencyId = $this->getCurrencyID();
            $info = array();
            $query = "SELECT tblproductconfigoptions.id,tblproductconfigoptions.optionname,tblproductconfigoptions.optiontype,tblproductconfigoptions.qtyminimum,tblproductconfigoptions.qtymaximum,(SELECT CONCAT(msetupfee,'|',qsetupfee,'|',ssetupfee,'|',asetupfee,'|',bsetupfee,'|',tsetupfee,'|',monthly,'|',quarterly,'|',semiannually,'|',annually,'|',biennially,'|',triennially) FROM tblpricing WHERE type='configoptions' AND currency=" . (int) $currencyId . " AND relid=(SELECT id FROM tblproductconfigoptionssub WHERE configid=tblproductconfigoptions.id AND hidden=0 ORDER BY sortorder ASC,id ASC LIMIT 1) ) as pricing FROM tblproductconfigoptions INNER JOIN tblproductconfiglinks ON tblproductconfigoptions.gid=tblproductconfiglinks.gid WHERE tblproductconfiglinks.pid=" . (int) $productID . " AND tblproductconfigoptions.hidden=0";
            $result = DB::select(DB::raw($query));
            $result = array_map(function ($value) {
                return (array)$value;
            }, $result);
            foreach ($result as $data) {
                $info[$data['id']] = array("name" => $data['optionname'], "type" => $data['optiontype'], "qtyminimum" => $data['qtyminimum'], "qtymaximum" => $data['qtymaximum']);
                $ops[$data['id']] = explode("|", $data['pricing']);
            }
            $this->storeToCache($productID, "info", $info);
            $this->storeToCache($productID, "pricing" . $currencyId, $ops);
        }
        return $ops;
    }
    public function getBasePrice($productID, $billingCycle)
    {
        $cycles = new \App\Helpers\Cycles();
        if ($cycles->isValidSystemBillingCycle($billingCycle)) {
            $this->loadData($productID);
            $optionsInfo = $this->getFromCache($productID, "info");
            $optionsPricing = $this->getFromCache($productID, "pricing" . $this->getCurrencyID());
            $pricingObj = new \App\Helpers\BillingPricing();
            $cycleindex = array_search($billingCycle, $pricingObj->getDBFields());
            $price = 0;
            foreach ($optionsPricing as $configID => $pricing) {
                if ($optionsInfo[$configID]["type"] == 1 || $optionsInfo[$configID]["type"] == 2) {
                    $price += $pricing[$cycleindex];
                } else {
                    if ($optionsInfo[$configID]["type"] == 3) {
                    } else {
                        if ($optionsInfo[$configID]["type"] == 4) {
                            $minquantity = $optionsInfo[$configID]["qtyminimum"];
                            if (0 < $minquantity) {
                                $price += $minquantity * $pricing[$cycleindex];
                            }
                        }
                    }
                }
            }
            return $price;
        } else {
            return false;
        }
    }
    public function hasConfigOptions($productID)
    {
        $this->loadData($productID);
        $optionsInfo = $this->getFromCache($productID, "info");
        if (0 < count($optionsInfo)) {
            return true;
        }
        return false;
    }
}
