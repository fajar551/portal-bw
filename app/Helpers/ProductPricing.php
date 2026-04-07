<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductPricing
{
	protected $product = NULL;
    protected $pricing = NULL;
	public function __construct($product, $currency)
    {
        if ($product instanceof \App\Models\Product) {
            $this->product = $product;
            $entityType = "product";
            $paymentTypeKey = "paytype";
        } else {
            if ($product instanceof \App\Models\Addon) {
                $this->product = $product;
                $entityType = "addon";
                $paymentTypeKey = "billingcycle";
            } else {
                throw new \Exception("Product input must be of type Product or Addon");
            }
        }
        switch ($this->product->{$paymentTypeKey}) {
            case "free":
                $this->pricing = array("free" => array("cycle" => "free", "setupfee" => new \App\Helpers\FormatterPrice(0), "price" => new \App\Helpers\FormatterPrice(0)));
                break;
            case "onetime":
                $productPricing = new \App\Helpers\Pricing();
                $productPricing->loadPricing($entityType, $this->product->id, $currency);
                $this->pricing = array("onetime" => $productPricing->getOneTimePricing());
                break;
            case "recurring":
            default:
                $productPricing = new \App\Helpers\Pricing();
                $productPricing->loadPricing($entityType, $this->product->id, $currency);
                $this->pricing = $productPricing->getAllCycleOptionsIndexedByCycle();
                break;
        }
    }
	public function allAvailableCycles()
    {
        $cyclesToReturn = array();
        foreach ($this->pricing as $cycle => $data) {
            $cyclesToReturn[] = new \App\Helpers\Price($data);
        }
        return $cyclesToReturn;
    }
	public function months($months)
    {
        $map = array("onetime", "monthly", 3 => "quarterly", 6 => "semiannual", 12 => "annual", 24 => "biennial", 36 => "triennial", 100 => "free");
        $key = $map[$months];
        return $this->{$key}();
    }
    public function byCycle($cycle)
    {
        $cycle = str_replace("lly", "l", $cycle);
        $cycle = str_replace("-", "", $cycle);
        if (method_exists($this, $cycle)) {
            return $this->{$cycle}();
        }
        return null;
    }
    public function free()
    {
        return null;
    }
    public function onetime()
    {
        return isset($this->pricing["onetime"]) ? new \App\Helpers\Price($this->pricing["onetime"]) : null;
    }
    public function monthly()
    {
        return isset($this->pricing["monthly"]) ? new \App\Helpers\Price($this->pricing["monthly"]) : null;
    }
    public function quarterly()
    {
        return isset($this->pricing["quarterly"]) ? new \App\Helpers\Price($this->pricing["quarterly"]) : null;
    }
    public function semiannual()
    {
        return isset($this->pricing["semiannually"]) ? new \App\Helpers\Price($this->pricing["semiannually"]) : null;
    }
    public function semiannually()
    {
        return $this->semiannual();
    }
    public function annual()
    {
        return isset($this->pricing["annually"]) ? new \App\Helpers\Price($this->pricing["annually"]) : null;
    }
    public function annually()
    {
        return $this->annual();
    }
    public function biennial()
    {
        return isset($this->pricing["biennially"]) ? new \App\Helpers\Price($this->pricing["biennially"]) : null;
    }
    public function biennially()
    {
        return $this->biennial();
    }
    public function triennial()
    {
        return isset($this->pricing["triennially"]) ? new \App\Helpers\Price($this->pricing["triennially"]) : null;
    }
    public function triennially()
    {
        return $this->triennial();
    }
    public function best()
    {
        if (array_key_exists("onetime", $this->pricing)) {
            return $this->onetime();
        }
        $bestPrice = null;
        $bestPriceCycle = null;
        $bestPriceInfo = null;
        foreach ($this->pricing as $cycle => $priceinfo) {
            $monthlyBreakdown = $priceinfo["breakdown"]["monthly"];
            $monthlyBreakdown = $monthlyBreakdown instanceof \App\Helpers\FormatterPrice ? $monthlyBreakdown->toNumeric() : null;
            if (is_null($bestPrice) || !is_null($monthlyBreakdown) && $monthlyBreakdown < $bestPrice) {
                $bestPrice = $monthlyBreakdown;
                $bestPriceInfo = $priceinfo;
            }
            $yearlyBreakdown = $priceinfo["breakdown"]["yearly"];
            $yearlyBreakdown = $yearlyBreakdown instanceof \App\Helpers\FormatterPrice ? $yearlyBreakdown->toNumeric() : null;
            if (is_null($bestPrice) || !is_null($yearlyBreakdown) && $yearlyBreakdown < $bestPrice) {
                $bestPrice = $yearlyBreakdown;
                $bestPriceInfo = $priceinfo;
            }
        }
        if (is_null($bestPriceInfo)) {
            return null;
        }
        return new \App\Helpers\Price($bestPriceInfo);
    }
    public function first()
    {
        return $this->allAvailableCycles()[0];
    }
	// next
}
