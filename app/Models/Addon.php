<?php

namespace App\Models;

use App\Helpers\Application;
use Database;
use Illuminate\Database\Eloquent\Model;

class Addon extends AbstractModel
{
    //
    protected $table = 'addons';
    protected $columnMap = array("billingCycle" => "billingcycle", "applyTax" => "tax", "showOnOrderForm" => "showorder", "welcomeEmailTemplateId" => "welcomeemail", "autoLinkCriteria" => "autolinkby", "isHidden" => "hidden", "isRetired" => "retired");
    protected $booleans = array("applyTax", "showOnOrderForm", "suspendProduct", "isHidden", "retired");
    protected $commaSeparated = array("packages", "downloads");

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();
    }

    public function getTableName()
	{
		return $this->table;
	}

    public function scopeMarketConnect($query)
    {
        return $query->where("module", "marketconnect");
    }

    public function getApplyTaxAttribute()
	{
		return $this->attributes['tax'];
	}

    public function moduleConfiguration()
    {
        return $this->hasMany(ModuleConfiguration::class, "entity_id")->where("entity_type", "=", "addon");
    }

    public function customFields()
    {
        return $this->hasMany(Customfield::class, "relid")->where("type", "=", "addon")->orderBy("sortorder");
    }

    public function pricing($currency = NULL)
    {
        if (is_null($this->pricingCache)) {
            $this->pricingCache = new \App\Helpers\ProductPricing($this, $currency);
        }
        return $this->pricingCache;
    }

    public function welcomeEmailTemplate()
    {
        return $this->hasOne(Emailtemplate::class, "id", "welcomeemail");
    }

    public function getAutoActivateAttribute()
	{
		return @$this->attributes['autoactivate'];
	}

    public static function getAddonDropdownValues($currentAddonId = 0)
    {
        $addonCollection = self::all();
        $dropdownOptions = array();
        foreach ($addonCollection as $addon) {
            if ($addon->retired && $currentAddonId != $addon->id) {
                continue;
            }
            $dropdownOptions[$addon->id] = $addon->name;
        }
        return $dropdownOptions;
    }

    public function scopeAvailableOnOrderForm($query, array $addons = array())
    {
        $query->where(function ($query) {
            $query->where("showorder", 1)->where("retired", 0);
            if (Application::isClientAreaRequest()) {
                $query->where("hidden", 0);
            }
        });

        if (0 < count($addons)) {
            $query->orWhere(function ($query) use($addons) {
                $query->where("showorder", 1)->where("retired", 0)->whereIn("id", $addons);
            });
        }

        return $query;
    }
    
}
