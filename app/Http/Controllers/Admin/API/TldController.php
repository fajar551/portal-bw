<?php

namespace App\Http\Controllers\Admin\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseAPI;
use DB;

class TldController extends Controller
{
    private $domainPricingTypes = array("register" => "domainregister", "renew" => "domainrenew", "transfer" => "domaintransfer");
    private $currencies = NULL;
    private $registerPricing = array();
    private $renewPricing = array();
    private $transferPricing = array();
    private $copyToYears = false;
    private $graceFee = NULL;
    private $graceDuration = NULL;
    private $redemptionFee = NULL;
    private $redemptionDuration = NULL;
    private $tldIds = array();

    public function massConfiguration(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->tldIds = $request->input("tldIds") ?? [];
            $pricing = $request->input("pricing") ?? [];
            $this->registerPricing[\App\Models\Currency::DEFAULT_CURRENCY_ID] = $pricing["register"];
            $this->renewPricing[\App\Models\Currency::DEFAULT_CURRENCY_ID] = $pricing["renew"];
            $this->transferPricing[\App\Models\Currency::DEFAULT_CURRENCY_ID] = $pricing["transfer"];
            $this->copyToYears = $pricing["copyToYears"] != "false";
            $this->graceFee = $pricing["grace"]["fee"];
            $this->graceDuration = $pricing["grace"]["duration"];
            $this->redemptionFee = $pricing["redemption"]["fee"];
            $this->redemptionDuration = $pricing["redemption"]["duration"];
            $this->currencies = \App\Models\Currency::all();
            $this->buildPricingArraysUsingCurrencyConversion();
            $this->savePricing($this->getUpdateArraysFromBuiltPricing());
            $this->conditionallySaveGraceAndRedemptionData();

            DB::commit();
            return ResponseAPI::Success([
                "success" => true,
                'message' => "Your changes have been saved.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseAPI::Error(['message' => $e->getMessage()]);
        }
    }

    protected function conditionallySaveGraceAndRedemptionData()
    {
        foreach ($this->tldIds as $tldId) {
            if ($this->graceDuration || $this->graceDuration === "0" || $this->graceFee || $this->graceFee === "0" || $this->redemptionDuration || $this->redemptionDuration === "0" || $this->redemptionFee || $this->redemptionFee === "0") {
                $extensionToUpdate = \App\Models\Extension::find($tldId);
                if ($this->graceDuration || $this->graceDuration === "0") {
                    $extensionToUpdate->gracePeriod = (int) $this->graceDuration;
                }
                if ($this->graceFee || $this->graceFee === "0") {
                    $extensionToUpdate->gracePeriodFee = (double) $this->graceFee;
                }
                if ($this->redemptionDuration || $this->redemptionDuration === "0") {
                    $extensionToUpdate->redemptionGracePeriod = (int) $this->redemptionDuration;
                }
                if ($this->redemptionFee || $this->redemptionFee === "0") {
                    $extensionToUpdate->redemptionGracePeriodFee = (double) $this->redemptionFee;
                }
                $extensionToUpdate->save();
            }
        }
    }

    protected function getUpdateArraysFromBuiltPricing()
    {
        $updatePricing = array();
        $multipliers = array("qsetupfee" => 2, "ssetupfee" => 3, "asetupfee" => 4, "bsetupfee" => 5, "monthly" => 6, "quarterly" => 7, "semiannually" => 8, "annually" => 9, "biennially" => 10);
        foreach ($this->domainPricingTypes as $pricingType => $databaseField) {
            $varName = $pricingType . "Pricing";
            $value = $this->{$varName};
            foreach ($this->currencies as $currency) {
                $valueToUse = $value[$currency->id];
                if (!$valueToUse) {
                    continue;
                }
                $updatePricing[$databaseField][$currency->id]["msetupfee"] = $valueToUse;
                if ($pricingType != "transfer" && $this->copyToYears) {
                    foreach ($multipliers as $field => $multiplier) {
                        $updatePricing[$databaseField][$currency->id][$field] = $valueToUse * $multiplier;
                    }
                } else {
                    if ($this->copyToYears) {
                        foreach (array_keys($multipliers) as $field) {
                            $updatePricing[$databaseField][$currency->id][$field] = -1;
                        }
                    }
                }
            }
        }
        return $updatePricing;
    }

    protected function savePricing(array $updatePricing)
    {
        if ($updatePricing) {
            foreach ($updatePricing as $pricingType => $currencyBasedValues) {
                foreach ($currencyBasedValues as $currencyId => $pricingValues) {
                    foreach ($this->tldIds as $tldId) {
                        \App\Models\Pricing::where("currency", $currencyId)->where("relid", $tldId)->where("type", $pricingType)->where("tsetupfee", 0)->update($pricingValues);
                    }
                }
            }
        }
    }

    protected function buildPricingArraysUsingCurrencyConversion()
    {
        foreach ($this->currencies as $currency) {
            $this->registerPricing = $this->buildPricingForCurrency($this->registerPricing, $currency);
            $this->renewPricing = $this->buildPricingForCurrency($this->renewPricing, $currency);
            $this->transferPricing = $this->buildPricingForCurrency($this->transferPricing, $currency);
        }
    }

    protected function buildPricingForCurrency(array $pricingData, \App\Models\Currency $currency)
    {
        if ($pricingData[\App\Models\Currency::DEFAULT_CURRENCY_ID] || $pricingData[\App\Models\Currency::DEFAULT_CURRENCY_ID] === "0") {
            if ($currency->id == \App\Models\Currency::DEFAULT_CURRENCY_ID) {
                if ($pricingData[\App\Models\Currency::DEFAULT_CURRENCY_ID] < 0) {
                    $pricingData[\App\Models\Currency::DEFAULT_CURRENCY_ID] = -1;
                }
            } else {
                $value = $pricingData[\App\Models\Currency::DEFAULT_CURRENCY_ID];
                if (0 < $value) {
                    $value = \App\Helpers\Invoice::convertCurrency($value, \App\Models\Currency::DEFAULT_CURRENCY_ID, $currency->id);
                }
                $pricingData[$currency->id] = $value;
            }
        }
        return $pricingData;
    }
}
