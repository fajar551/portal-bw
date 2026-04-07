<?php

namespace App\Models;

use App\Helpers\Database;
use App\Helpers\DomainStatus as Status;
use App\Helpers\Cfg;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Domain extends AbstractModel
{
    const PENDING = "Pending";
    const PENDING_REGISTRATION = "Pending Registration";
    const PENDING_TRANSFER = "Pending Transfer";
    const ACTIVE = "Active";
    const GRACE = "Grace";
    const REDEMPTION = "Redemption";
    const EXPIRED = "Expired";
    const TRANSFERRED_AWAY = "Transferred Away";
    const CANCELLED = "Cancelled";
    const FRAUD = "Fraud";

    protected $table = 'domains';
    protected $dates = ["registrationdate", "nextduedate", "nextinvoicedate"];
    protected $appends = ["tld", "extension", "gracePeriod", "gracePeriodFee", "redemptionGracePeriod", "redemptionGracePeriodFee"];
    protected $columnMap = [
        "clientId" => "userid",
        "registrarModuleName" => "registrar",
        "promotionId" => "promoid",
        "paymentGateway" => "paymentmethod",
        "hasDnsManagement" => "dnsmanagement",
        "hasEmailForwarding" => "emailforwarding",
        "hasIdProtection" => "idprotection",
        "hasAutoInvoiceOnNextDueDisabled" => "donotrenew",
        "isSyncedWithRegistrar" => "synced",
        "isPremium" => "is_premium"
    ];
    protected $booleans = [
        "hasDnsManagement",
        "hasEmailForwarding",
        "hasIdProtection",
        "isPremium",
        "hasAutoInvoiceOnNextDueDisabled",
        "isSyncedWithRegistrar"
    ];
    protected $characterSeparated = ["|" => ["reminders"]];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function getRegistrationdateAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['registrationdate'])->format('Y-m-d');
    }

    public function getNextinvoicedateAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['nextinvoicedate'])->format('Y-m-d');
    }

    public function getNextduedateAttribute()
    {
        return \Carbon\Carbon::parse($this->attributes['nextduedate'])->format('Y-m-d');
    }

    public function scopeOfClient($query, $clientId)
    {
        return $query->where("userid", $clientId);
    }

    public function scopeNextDueBefore($query, \Carbon\Carbon $date)
    {
        return $query->whereStatus("Active")->where("nextduedate", "<=", $date);
    }

    public function scopeIsConsideredActive($query)
    {
        return $query->whereIn("status", [Status::ACTIVE, Status::PENDING_TRANSFER, Status::GRACE]);
    }

    public function getTldAttribute()
    {
        $domainParts = explode(".", $this->domain, 2);
        return isset($domainParts[1]) ? $domainParts[1] : "";
    }

    public function client()
    {
        return $this->belongsTo(Client::class, "userid");
    }

    public function additionalFields()
    {
        return $this->hasMany(Domainsadditionalfield::class, "domainid");
    }

    public function extra()
    {
        return $this->hasMany(DomainsExtra::class, "domain_id");
    }

    public function order()
    {
        return $this->belongsTo(Order::class, "orderid");
    }

    public function invoiceItems()
    {
        return $this->hasMany(Invoiceitem::class, "relid")->whereIn("type", [
            "DomainRegister",
            "DomainTransfer",
            "Domain",
            "DomainAddonDNS",
            "DomainAddonEMF",
            "DomainAddonIDP",
            "DomainGraceFee",
            "DomainRedemptionFee"
        ]);
    }

    public function setRemindersAttribute($reminders)
    {
        $remindersArray = $this->asArrayFromCharacterSeparatedValue($reminders, "|");
        if (count($remindersArray) > 5) {
            throw new \Exception("You may only store the past 5 domain reminders.");
        }
        foreach ($remindersArray as $reminder) {
            if (!is_numeric($reminder)) {
                throw new \Exception("Domain reminders must be numeric.");
            }
        }
        $this->attributes["reminders"] = $reminders;
    }

    public function failedActions()
    {
        return $this->hasMany(Modulequeue::class, "service_id")->where("service_type", "=", "domain");
    }

    public function isConfiguredTld()
    {
        $tld = $this->getTldAttribute();
        return DB::table("tbldomainpricing")->where("extension", "." . $tld)->exists();
    }

    public function getAdditionalFields()
    {
        return (new \App\Helpers\AdditionalFields())->setDomainType($this->type)->setDomain($this->domain);
    }

    public function getExtensionAttribute()
    {
        $tld = $this->getTldAttribute();
        static $data = [];
        if ($tld && !array_key_exists($tld, $data)) {
            $data[$tld] = Domainpricing::where("extension", "." . $tld)->first();
        }
        return $data[$tld];
    }

    public function getGracePeriodAttribute()
    {
        if (Cfg::get("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        static $renewalGracePeriod = [];
        if (!array_key_exists($this->tld, $renewalGracePeriod)) {
            $domainExtensionConfiguration = $this->extension;
            if ($domainExtensionConfiguration) {
                $renewalGracePeriod[$this->tld] = $domainExtensionConfiguration->gracePeriod;
                if ($renewalGracePeriod[$this->tld] == -1) {
                    $renewalGracePeriod[$this->tld] = $domainExtensionConfiguration->defaultGracePeriod;
                }
            } else {
                $renewalGracePeriod[$this->tld] = \App\Helpers\GracePeriod::getForTld($this->getTldAttribute());
            }
        }
        return $renewalGracePeriod[$this->tld];
    }

    public function getGracePeriodFeeAttribute()
    {
        if (Cfg::get("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        static $gracePeriodFee = [];
        if (!array_key_exists($this->tld, $gracePeriodFee)) {
            $domainExtensionConfiguration = $this->extension;
            $gracePeriodFee[$this->tld] = -1;
            if ($domainExtensionConfiguration) {
                if ($domainExtensionConfiguration->gracePeriodFee >= 0) {
                    $gracePeriodFee[$this->tld] = $domainExtensionConfiguration->gracePeriodFee;
                }
            }
        }
        return $gracePeriodFee[$this->tld];
    }

    public function getRedemptionGracePeriodAttribute()
    {
        if (Cfg::get("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        static $redemptionGracePeriod = [];
        if (!array_key_exists($this->tld, $redemptionGracePeriod)) {
            $domainExtensionConfiguration = $this->extension;
            if ($domainExtensionConfiguration) {
                $redemptionGracePeriod[$this->tld] = $domainExtensionConfiguration->redemptionGracePeriod;
                if ($redemptionGracePeriod[$this->tld] == -1) {
                    $redemptionGracePeriod[$this->tld] = $domainExtensionConfiguration->defaultRedemptionGracePeriod;
                }
            } else {
                $redemptionGracePeriod[$this->tld] = \App\Helpers\RedemptionGracePeriod::getForTld($this->tld);
            }
        }
        return $redemptionGracePeriod[$this->tld];
    }

    public function getRedemptionGracePeriodFeeAttribute()
    {
        if (Cfg::get("DisableDomainGraceAndRedemptionFees")) {
            return -1;
        }
        static $redemptionGracePeriodFee = [];
        if (!array_key_exists($this->tld, $redemptionGracePeriodFee)) {
            $domainExtensionConfiguration = $this->extension;
            $redemptionGracePeriodFee[$this->tld] = -1;
            if ($domainExtensionConfiguration) {
                if ($domainExtensionConfiguration->redemptionGracePeriodFee >= 0) {
                    $redemptionGracePeriodFee[$this->tld] = $domainExtensionConfiguration->redemptionGracePeriodFee;
                }
            }
        }
        return $redemptionGracePeriodFee[$this->tld];
    }

    public function getClientIdAttribute()
    {
        return $this->attributes['userid'];
    }

    public function getRegistrarModuleNameAttribute()
    {
        return $this->attributes['registrar'];
    }

    public function getPromotionIdAttribute()
    {
        return $this->attributes['promoid'];
    }

    public function getPaymentGatewayAttribute()
    {
        return $this->attributes['paymentmethod'];
    }

    public function getHasDnsManagementAttribute()
    {
        return $this->attributes['dnsmanagement'];
    }

    public function getHasEmailForwardingAttribute()
    {
        return $this->attributes['emailforwarding'];
    }

    public function getHasIdProtectionAttribute()
    {
        return $this->attributes['idprotection'];
    }

    public function getHasAutoInvoiceOnNextDueDisabledAttribute()
    {
        return $this->attributes['donotrenew'];
    }

    public function getIsSyncedWithRegistrarAttribute()
    {
        return $this->attributes['synced'];
    }

    public function getIsPremiumAttribute()
    {
        return $this->attributes['is_premium'];
    }

    public function getRawAttribute($key = null, $default = null)
    {
        return \Illuminate\Support\Arr::get($this->attributes, $key, $default);
    }

    public function paymentGateway()
    {
        return $this->hasMany(Paymentgateway::class, "gateway", "paymentmethod");
    }
}