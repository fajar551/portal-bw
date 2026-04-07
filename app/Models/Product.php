<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Product extends AbstractModel
{
	use Filterable;
	protected $columnMap = array("productGroupId" => "gid", "isHidden" => "hidden", "welcomeEmailTemplateId" => "welcomeemail", "stockControlEnabled" => "stockcontrol", "quantityInStock" => "qty", "proRataChargeDayOfCurrentMonth" => "proratadate", "proRataChargeNextMonthAfterDay" => "proratachargenextmonth", "paymentType" => "paytype", "allowMultipleQuantities" => "allowqty", "freeSubDomains" => "subdomain", "module" => "servertype", "serverGroupId" => "servergroup", "moduleConfigOption1" => "configoption1", "moduleConfigOption2" => "configoption2", "moduleConfigOption3" => "configoption3", "moduleConfigOption4" => "configoption4", "moduleConfigOption5" => "configoption5", "moduleConfigOption6" => "configoption6", "moduleConfigOption7" => "configoption7", "moduleConfigOption8" => "configoption8", "moduleConfigOption9" => "configoption9", "moduleConfigOption10" => "configoption10", "moduleConfigOption11" => "configoption11", "moduleConfigOption12" => "configoption12", "moduleConfigOption13" => "configoption13", "moduleConfigOption14" => "configoption14", "moduleConfigOption15" => "configoption15", "moduleConfigOption16" => "configoption16", "moduleConfigOption17" => "configoption17", "moduleConfigOption18" => "configoption18", "moduleConfigOption19" => "configoption19", "moduleConfigOption20" => "configoption20", "moduleConfigOption21" => "configoption21", "moduleConfigOption22" => "configoption22", "moduleConfigOption23" => "configoption23", "moduleConfigOption24" => "configoption24", "recurringCycleLimit" => "recurringcycles", "daysAfterSignUpUntilAutoTermination" => "autoterminatedays", "autoTerminationEmailTemplateId" => "autoterminateemail", "allowConfigOptionUpgradeDowngrade" => "configoptionsupgrade", "upgradeEmailTemplateId" => "upgradeemail", "enableOverageBillingAndUnits" => "overagesenabled", "overageDiskLimit" => "overagesdisklimit", "overageBandwidthLimit" => "overagesbwlimit", "overageDiskPrice" => "overagesdiskprice", "overageBandwidthPrice" => "overagesbwprice", "applyTax" => "tax", "affiliatePayoutOnceOnly" => "affiliateonetime", "affiliatePaymentType" => "affiliatepaytype", "affiliatePaymentAmount" => "affiliatepayamount", "isRetired" => "retired", "displayOrder" => "order");
	protected $table = 'products';
	protected $guarded = [];  
    protected $booleans = array("isHidden", "showDomainOptions", "stockControlEnabled", "proRataBilling", "allowConfigOptionUpgradeDowngrade", "applyTax", "affiliatePayoutOnceOnly", "isRetired", "isFeatured");
    protected $strings = array("description", "autoSetup", "module", "moduleConfigOption1", "moduleConfigOption2", "moduleConfigOption3", "moduleConfigOption4", "moduleConfigOption5", "moduleConfigOption6", "moduleConfigOption7", "moduleConfigOption8", "moduleConfigOption9", "moduleConfigOption10", "moduleConfigOption11", "moduleConfigOption12", "moduleConfigOption13", "moduleConfigOption14", "moduleConfigOption15", "moduleConfigOption16", "moduleConfigOption17", "moduleConfigOption18", "moduleConfigOption19", "moduleConfigOption20", "moduleConfigOption21", "moduleConfigOption22", "moduleConfigOption23", "moduleConfigOption24");
    protected $ints = array("welcomeEmailTemplateId", "quantityInStock", "proRataChargeDayOfCurrentMonth", "proRataChargeNextMonthAfterDay", "serverGroupId", "displayOrder");
    protected $commaSeparated = array("freeSubDomains", "freeDomainPaymentTerms", "freeDomainTlds", "enableOverageBillingAndUnits");
    protected $appends = array("formattedProductFeatures");
	const TYPE_SHARED = "hostingaccount";
	const TYPE_RESELLER = "reselleraccount";
	const TYPE_SERVERS = "server";
	const TYPE_OTHER = "other";
	const PAYMENT_FREE = "free";
	const PAYMENT_ONETIME = "onetime";
	const PAYMENT_RECURRING = "recurring";
	
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getTableName()
	{
		return $this->table;
	}

	public function productGroup()
	{
		return $this->belongsTo(Productgroup::class, "gid");
	}

	public function group()
	{
		return $this->belongsTo(Productgroup::class, 'gid', 'id');
	}

	public function pricings()
	{
		return $this->hasMany(Pricing::class, 'relid', 'id')->where('type', 'product');
	}

	public function customfields()
	{
		return $this->hasMany(Customfield::class, 'relid', 'id')->where('type', 'product')->where('showorder', 'on');
	}

	public function customfieldsArray()
	{
		$customfields = $this->customfields;
		$data = [];
		foreach ($customfields as $customfield) {
			$fieldname = $customfield->fieldname;
			if (strpos($fieldname, "|")) {
				$fieldname = explode("|", $fieldname);
				$fieldname = trim($fieldname[1]);
			}

			$required = $customfield->required;

			if ($required == "on") {
				$required = "*";
			}

			$data[] = [
				"id" => $customfield->id,
				"name" => $fieldname,
				"description" => $customfield->description,
				"required" => $required,
			];
		}

		return $data;
	}

	public function getModuleAttribute()
	{
		return $this->attributes['servertype'];
	}

	public function getApplyTaxAttribute()
	{
		return $this->attributes['tax'];
	}

	public static function getProductDescription($productId, $fallback = "", $language = NULL)
	{
		$description = \Lang::get("product." . $productId . ".description", array(), "dynamicMessages", $language);
		if ($description == "product." . $productId . ".description") {
			if ($fallback) {
				return $fallback;
			}
			return self::find($productId, array("description"))->description;
		}
		return $description;
	}

	public static function getProductName($productId, $fallback = "", $language = NULL)
	{
		$name = \Lang::get("product." . $productId . ".name", array(), "dynamicMessages", $language);
		if ($name == "product." . $productId . ".name") {
			if ($fallback) {
				return $fallback;
			}
			$p = self::find($productId, array("name"));
			return $p ? $p->name : "";
		}
		return $name;
	}

	public function pricing($currency = NULL)
    {
        if (is_null($this->pricingCache)) {
            $this->pricingCache = new \App\Helpers\ProductPricing($this, $currency);
        }
        return $this->pricingCache;
    }

	public function upgradeProducts()
    {
        $pfx = Database::prefix();
        return $this->belongsToMany(Product::class, "{$pfx}product_upgrade_products", "product_id", "upgrade_product_id");
    }

	public function getAvailableBillingCycles()
    {
        switch ($this->paymentType) {
            case "free":
                return array("free");
            case "onetime":
                return array("onetime");
            case "recurring":
                $validCycles = array();
                $productPricing = new \App\Helpers\Pricing();
                $productPricing->loadPricing("product", $this->id);
                return $productPricing->getAvailableBillingCycles();
        }
        return array();
    }

    public function getDownloadIds()
    {
        return array_map(function ($download) {
            return $download["id"];
        }, $this->productDownloads->toArray());
    }
    
    public function productDownloads()
    {
        $pfx = Database::prefix();
        return $this->belongsToMany(Download::class, "{$pfx}product_downloads");
    }

    public function getUpgradeProductIds()
    {
        return array_map(function ($product) {
            return $product["id"];
        }, $this->upgradeProducts->toArray());
    }

	public function getFormattedProductFeaturesAttribute()
    {
        $features = array();
        $featuresDescription = "";
        $descriptionLines = explode("\n", $this->description);
        foreach ($descriptionLines as $line) {
            if (strpos($line, ":")) {
                $line = explode(":", $line, 2);
                $features[trim($line[0])] = trim($line[1]);
            } else {
                if (trim($line)) {
                    $featuresDescription .= $line . "\n";
                }
            }
        }
        return array("original" => nl2br($this->description), "features" => $features, "featuresDescription" => nl2br($featuresDescription));
    }
}
