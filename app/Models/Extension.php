<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\DomainPricing;
use App\Helpers\Domain\Domain;

class Extension extends AbstractModel
{
    protected $table = "tbldomainpricing";
    protected $columnMap = array("supportsDnsManagement" => "dnsmanagement", "supportsEmailForwarding" => "emailforwarding", "supportsIdProtection" => "idprotection", "requiresEppCode" => "eppcode", "autoRegistrationRegistrar" => "autoreg", "gracePeriod" => "grace_period", "gracePeriodFee" => "grace_period_fee", "redemptionGracePeriod" => "redemption_grace_period", "redemptionGracePeriodFee" => "redemption_grace_period_fee", "topLevelId" => "top_level_id");
    protected $appends = array("defaultGracePeriod", "defaultRedemptionGracePeriod", "pricing");
    protected $casts = array("grace_period_fee" => "float", "gracePeriodFee" => "float", "redemption_grace_period_fee" => "float", "redemptionGracePeriodFee" => "float");
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("order", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy("tbldomainpricing.order")->orderBy("tbldomainpricing.id");
        });
    }
    public function getTopLevel()
    {
        $extension = $this->extension;
        return \App\Models\Domain\TopLevel::firstOrCreate(array("tld" => ltrim($extension, ".")));
    }
    public function getDefaultGracePeriodAttribute()
    {
        $tld = ltrim($this->getRawAttribute("extension"), ".");
        return \App\Helpers\GracePeriod::getForTld($tld);
    }
    public function getDefaultRedemptionGracePeriodAttribute()
    {
        $tld = ltrim($this->getRawAttribute("extension"), ".");
        return \App\Helpers\RedemptionGracePeriod::getForTld($tld);
    }
    public function getPricingAttribute()
    {
        return (new DomainPricing(new Domain("sample" . $this->extension)))->toArray();
    }
    public function getGracePeriodFeeAttribute()
    {
        if (\App\Helpers\Cfg::getValue("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        return $this->attributes["grace_period_fee"];
    }
    public function setGracePeriodFeeAttribute($value)
    {
        $this->attributes["grace_period_fee"] = $value;
    }
    public function getRedemptionGracePeriodFeeAttribute()
    {
        if (\App\Helpers\Cfg::getValue("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        return $this->attributes["redemption_grace_period_fee"];
    }
    public function setRedemptionGracePeriodFeeAttribute($value)
    {
        $this->attributes["redemption_grace_period_fee"] = $value;
    }
}
