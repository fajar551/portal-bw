<?php
namespace App\Helpers;

use DB;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderForm
{
	private $pid = "";
    private $productinfo = array();
    private $validbillingcycles = array("free", "onetime", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
    public function getCartData()
    {
        return (array) session("cart");
    }
    public function getCartDataByKey($key, $keyNotFoundValue = "")
    {
        $cartSession = $this->getCartData();
        return array_key_exists($key, $cartSession) ? $cartSession[$key] : $keyNotFoundValue;
    }
    // public function getProductGroups($asCollection = false)
    // {
    //     if ($asCollection) {
    //         return \App\Models\Productgroup::where("hidden", false)->orderBy("order")->get();
    //     }
    //     $groups = array();
    //     $groupIds = \App\Models\Productgroup::where("hidden", "=", false)->orderBy("order")->pluck("name", "id");
    //     foreach ($groupIds as $id => $name) {
    //         $groups[] = array("gid" => $id, "name" => $name);
    //     }
    //     return $groups;
    // }
    public function getProductGroups($asCollection = false)
{
    try {
        $query = \App\Models\Productgroup::where("hidden", false)->orderBy("order");
        
        if ($asCollection) {
            return $query->get()->map(function($group) {
                return [
                    'gid' => $group->id,
                    'id' => $group->id, // tambahkan id untuk kompatibilitas
                    'name' => $group->name
                ];
            });
        }
        
        $groups = [];
        $groupIds = $query->pluck("name", "id");
        foreach ($groupIds as $id => $name) {
            $groups[] = [
                "gid" => $id, 
                "id" => $id, // tambahkan id untuk kompatibilitas
                "name" => $name
            ];
        }
        return $groups;
    } catch (\Exception $e) {
        \Log::error('getProductGroups error: ' . $e->getMessage());
        return $asCollection ? collect([]) : [];
    }
}
    public function getProducts($productGroup, $includeConfigOptions = false, $includeBundles = false)
    {
        global $currency;
        $products = array();
        $unsortedProducts = array();
        $pricing = new \App\Helpers\Pricing();
        try {
            if (!$productGroup instanceof \App\Models\Productgroup) {
                $productGroup = \App\Models\Productgroup::findOrFail($productGroup);
            }
            if (!$productGroup instanceof \App\Models\Productgroup) {
                $productGroup = \App\Models\Productgroup::orderBy("order")->where("hidden", false)->firstOrFail();
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \Exception("NoProductGroup");
        }
        $productsCollection = $productGroup->products()->where("hidden", false)->where("retired", false)->orderBy("order")->orderBy("name")->get();
        if (!$productsCollection) {
            $productsCollection = array();
        }
        foreach ($productsCollection as $product) {
            $pricingInfo = \App\Helpers\Orders::getPricingInfo($product->id, $includeConfigOptions);
            $pricing->loadPricing("product", $product->id);
            $description = $this->formatProductDescription(\App\Models\Product::getProductDescription($product->id, $product->description));
            if ($pricing->hasBillingCyclesAvailable() || $product->paytype == "free") {
                $unsortedProducts[$product->order][] = array("pid" => $product->id, "bid" => 0, "type" => $product->type, "name" => \App\Models\Product::getProductName($product->id, $product->name), "description" => $description["original"], "features" => $description["features"], "featuresdesc" => $description["featuresdesc"], "paytype" => $product->paytype, "pricing" => $pricingInfo, "freedomain" => $product->freedomain, "freedomainpaymentterms" => $product->freedomainpaymentterms, "qty" => $product->stockcontrol ? $product->qty : "", "isFeatured" => $product->is_featured);
            }
        }
        if ($includeBundles) {
            foreach (DB::table("tblbundles")->where("showgroup", "1")->where("gid", $productGroup->id)->get() as $bundle) {
                $description = $this->formatProductDescription($bundle->description);
                $convertedCurrency = \App\Helpers\Format::ConvertCurrency($bundle->displayprice, 1, $currency["id"]);
                $price = new \App\Helpers\FormatterPrice($convertedCurrency, $currency);
                $displayPrice = 0 < $bundle->displayprice ? $price : "";
                $displayPriceSimple = 0 < $bundle->displayprice ? $price->toPrefixed() : "";
                $unsortedProducts[$bundle->sortorder][] = array("bid" => $bundle->id, "name" => $bundle->name, "description" => $description["original"], "features" => $description["features"], "featuresdesc" => $description["featuresdesc"], "displayprice" => $displayPrice, "displayPriceSimple" => $displayPriceSimple, "isFeatured" => (bool) $bundle->is_featured);
            }
        }
        if (empty($unsortedProducts)) {
            throw new \Exception("NoProducts");
        }
        ksort($unsortedProducts);
        foreach ($unsortedProducts as $items) {
            foreach ($items as $item) {
                $products[] = $item;
            }
        }
        return $products;
    }
    public function formatProductDescription($desc)
    {
        $features = array();
        $featuresdesc = "";
        $descriptionlines = explode("\n", $desc);
        foreach ($descriptionlines as $line) {
            if (strpos($line, ":")) {
                $line = explode(":", $line, 2);
                $features[trim($line[0])] = trim($line[1]);
            } else {
                if (trim($line)) {
                    $featuresdesc .= $line . "\n";
                }
            }
        }
        return array("original" => nl2br($desc), "features" => $features, "featuresdesc" => nl2br($featuresdesc));
    }
    public function getProductGroupInfo($gid)
    {
        $result = \App\Models\Productgroup::find($gid);
        if (!$result) {
            return false;
        }
        $data = $result->toArray();
        return $data;
    }
    public function setPid($pid)
    {
        $this->pid = $pid;
        $product = \App\Models\Product::with("productGroup")->where("id", $pid)->where("retired", false)->first();
        if (!$product) {
            return array();
        }
        $data = array("pid" => $product->id, "gid" => $product->productGroupId, "type" => $product->type, "name" => $product->name, "group_name" => $product->productGroup->name, "description" => $this->formatProductDescription($product->description)["original"], "showdomainoptions" => $product->showDomainOptions, "freedomain" => $product->freeDomain, "freedomainpaymentterms" => $product->freeDomainPaymentTerms, "freedomaintlds" => $product->freeDomainTlds, "subdomain" => $product->freeSubDomains, "stockcontrol" => $product->stockControlEnabled, "qty" => $product->quantityInStock, "allowqty" => $product->allowMultipleQuantities, "paytype" => $product->paymentType, "orderfrmtpl" => $product->productGroup->orderFormTemplate, "module" => $product->module);
        if (!$data["stockcontrol"]) {
            $data["qty"] = 0;
        }
        $this->productinfo = $data;
        return $this->productinfo;
    }
    public function getProductInfo($var = "")
    {
        return $var ? $this->productinfo[$var] : $this->productinfo;
    }
    public function validateBillingCycle($billingcycle)
    {
        global $currency;
        if (empty($currency)) {
            $currency = getCurrency();
        }
        if ($billingcycle && in_array($billingcycle, $this->validbillingcycles)) {
            return $billingcycle;
        }
        $paytype = $this->productinfo["paytype"];
        $result = \App\Models\Pricing::where("type", "product")->where("currency", $currency["id"])->where("relid", $this->productinfo["pid"]);
        $data = $result;
        $monthly = $data->value("monthly") ?? 0;
        $quarterly = $data->value("quarterly") ?? 0;
        $semiannually = $data->value("semiannually") ?? 0;
        $annually = $data->value("annually") ?? 0;
        $biennially = $data->value("biennially") ?? 0;
        $triennially = $data->value("triennially") ?? 0;
        if ($paytype == "free") {
            $billingcycle = "free";
        } else {
            if ($paytype == "onetime") {
                $billingcycle = "onetime";
            } else {
                if ($paytype == "recurring") {
                    if (0 <= $monthly) {
                        $billingcycle = "monthly";
                    } else {
                        if (0 <= $quarterly) {
                            $billingcycle = "quarterly";
                        } else {
                            if (0 <= $semiannually) {
                                $billingcycle = "semiannually";
                            } else {
                                if (0 <= $annually) {
                                    $billingcycle = "annually";
                                } else {
                                    if (0 <= $biennially) {
                                        $billingcycle = "biennially";
                                    } else {
                                        if (0 <= $triennially) {
                                            $billingcycle = "triennially";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $billingcycle;
    }
    public function getNumItemsInCart()
    {
        $client = \App\Models\Client::loggedIn()->first();
        $products = $this->getCartDataByKey("products", array());
        foreach ($products as $key => $product) {
            if (isset($product["noconfig"]) && $product["noconfig"] === true) {
                unset($products[$key]);
            }
        }
        $domains = $this->getCartDataByKey("domains", array());
        $numAddons = $numDomainRenewals = $numUpgrades = 0;
        if (!is_null($client)) {
            $serviceIds = null;
            $cartAddons = $this->getCartDataByKey("addons", array());
            if (0 < count($cartAddons)) {
                $serviceIds = $client->services()->pluck("id");
                foreach ($cartAddons as $addon) {
                    if ($serviceIds->contains($addon["productid"])) {
                        $numAddons++;
                    }
                }
            }
            $renewals = $this->getCartDataByKey("renewals", array());
            if (0 < count($renewals)) {
                $domainIds = $client->domains()->pluck("id");
                foreach ($renewals as $renewalId => $regPeriod) {
                    if ($domainIds->contains($renewalId)) {
                        $numDomainRenewals++;
                    }
                }
            }
            $upgrades = $this->getCartDataByKey("upgrades", array());
            if (0 < count($upgrades)) {
                if (is_null($serviceIds)) {
                    $serviceIds = $client->services()->pluck("id");
                }
                $addonIds = $client->addons()->pluck("id");
                foreach ($upgrades as $upgrade) {
                    $entityType = $upgrade["upgrade_entity_type"];
                    $entityId = $upgrade["upgrade_entity_id"];
                    if ($entityType == "service" && $serviceIds->contains($entityId) || $entityType == "addon" && $addonIds->contains($entityId)) {
                        $numUpgrades++;
                    }
                }
            }
        }
        return count($products) + count($domains) + $numAddons + $numDomainRenewals + $numUpgrades;
    }
    public static function addToCart($type, $parameters)
    {
        if (!in_array($type, array("product", "addon", "upgrade"))) {
            throw new \Exception("Invalid product type.");
        }
        $cart = new self();
        $cartData = $cart->getCartData();
        $cartData[$type . "s"][] = $parameters;
        // \Session::set("cart", $cartData);
        session()->put("cart", $cartData);
    }
    public static function addProductToCart($productId, $billingCycle, $domain, array $extra = array())
    {
        $cartData = array_merge(array("pid" => $productId, "billingcycle" => $billingCycle, "domain" => $domain), $extra);
        self::addToCart("product", $cartData);
    }
    public static function addAddonToCart($addonId, $serviceId, $billingCycle, array $extra = array())
    {
        $cartData = array_merge(array("id" => $addonId, "productid" => $serviceId, "billingcycle" => $billingCycle), $extra);
        self::addToCart("addon", $cartData);
    }
    public static function addUpgradeToCart($upgradeEntityType, $upgradeEntityId, $targetEntityId, $billingCycle)
    {
        self::addToCart("upgrade", array("upgrade_entity_type" => $upgradeEntityType, "upgrade_entity_id" => $upgradeEntityId, "target_entity_id" => $targetEntityId, "billing_cycle" => $billingCycle));
    }
}