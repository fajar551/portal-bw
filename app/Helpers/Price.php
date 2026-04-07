<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Price
{
	protected $price = NULL;
    public function __construct($price)
    {
        $this->price = $price;
        if (!isset($price["breakdown"]) && !is_null($price["price"])) {
            $this->price["breakdown"] = array();
            if ($this->isYearly()) {
                $yearlyPrice = $price["price"]->toNumeric() / (int) $this->cycleInYears();
                $this->price["breakdown"]["yearly"] = new \App\Helpers\FormatterPrice($yearlyPrice, $this->price()->getCurrency());
            } else {
                $cycleMonths = $this->cycleInMonths();
                if ($cycleMonths < 1) {
                    $cycleMonths = 1;
                }
                $yearlyPrice = $price["price"]->toNumeric() / (int) $cycleMonths;
                $this->price["breakdown"]["monthly"] = new \App\Helpers\FormatterPrice($yearlyPrice, $this->price()->getCurrency());
            }
        }
    }
	public function cycle()
    {
        return $this->price["cycle"];
    }
    public function isFree()
    {
        return $this->cycle() == "free";
    }
    public function isOneTime()
    {
        return $this->cycle() == "onetime";
    }
    public function isRecurring()
    {
        return in_array($this->cycle(), (new \App\Helpers\Cycles())->getRecurringSystemBillingCycles());
    }
	public function setup()
    {
        return $this->price["setupfee"];
    }
    public function price()
    {
        return $this->price["price"];
    }
    public function breakdown()
    {
        return $this->price["breakdown"];
    }
	public function toPrefixedString()
    {
        $priceString = "";
        $price = $this->price();
        if (!is_null($price)) {
            $priceString .= $price->toPrefixed();
            if ($this->isRecurring()) {
                $priceString .= "/" . $this->getShortCycle();
            }
        }
        $setup = $this->setup();
        if (!is_null($setup) && 0 < $setup->toNumeric()) {
            $priceString .= " + " . $setup->toPrefixed() . " " . \Lang::get("ordersetupfee");
        }
        return $priceString;
    }
	public function toSuffixedString()
    {
        $priceString = "";
        $price = $this->price();
        if (!is_null($price)) {
            $priceString .= $price->toSuffixed();
            if ($this->isRecurring()) {
                $priceString .= "/" . $this->getShortCycle();
            }
        }
        $setup = $this->setup();
        if (!is_null($setup) && 0 < $setup->toNumeric()) {
            $priceString .= " + " . $setup->toSuffixed() . " " . \Lang::get("ordersetupfee");
        }
        return $priceString;
    }
	public function toFullString()
    {
        $priceString = "";
        if ($this->isFree()) {
            return \Lang::get("orderfree");
        }
        $price = $this->price();
        if (!is_null($price)) {
            $priceString .= $price->toFull();
            if ($this->isRecurring()) {
                $priceString .= "/" . $this->getShortCycle();
            } else {
                if ($this->isOneTime()) {
                    $priceString .= " " . \Lang::get("orderpaymenttermonetime");
                }
            }
        }
        $setup = $this->setup();
        if (!is_null($setup) && 0 < $setup->toNumeric()) {
            $priceString .= " + " . $setup->toFull() . " " . \Lang::get("ordersetupfee");
        }
        return $priceString;
    }
	public function getShortCycle()
    {
        switch ($this->cycle()) {
            case "monthly":
                return \Lang::get("pricingCycleShort.monthly");
            case "quarterly":
                return \Lang::get("pricingCycleShort.quarterly");
            case "semiannually":
                return \Lang::get("pricingCycleShort.semiannually");
            case "annually":
                return \Lang::get("pricingCycleShort.annually");
            case "biennially":
                return \Lang::get("pricingCycleShort.biennially");
            case "triennially":
                return \Lang::get("pricingCycleShort.triennially");
        }
    }
	public function isYearly()
    {
        return in_array($this->cycle(), array("annually", "biennially", "triennially"));
    }
	public function cycleInYears()
    {
        switch ($this->cycle()) {
            case "annually":
                return \Lang::get("pricingCycleLong.annually");
            case "biennially":
                return \Lang::get("pricingCycleLong.biennially");
            case "triennially":
                return \Lang::get("pricingCycleLong.triennially");
        }
    }
    public function yearlyPrice()
    {
        return $this->breakdown()["yearly"]->toFull() . "/" . \Lang::get("pricingCycleShort.annually");
    }
    public function cycleInMonths()
    {
        switch ($this->cycle()) {
            case "monthly":
                return \Lang::get("pricingCycleLong.monthly");
            case "quarterly":
                return \Lang::get("pricingCycleLong.quarterly");
            case "semiannually":
                return \Lang::get("pricingCycleLong.semiannually");
        }
    }
    public function monthlyPrice()
    {
        return $this->breakdown()["monthly"]->toFull() . "/" . \Lang::get("pricingCycleShort.monthly");
    }
    public function breakdownPrice()
    {
        if ($this->isYearly()) {
            return $this->yearlyPrice();
        }
        return $this->monthlyPrice();
    }
    public function breakdownPriceNumeric()
    {
        if ($this->isYearly()) {
            return (double) $this->breakdown()["yearly"]->toNumeric();
        }
        return (double) $this->breakdown()["monthly"]->toNumeric();
    }
}
