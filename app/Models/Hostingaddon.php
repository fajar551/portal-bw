<?php

namespace App\Models;

use App\Helpers\Database;
use Illuminate\Database\Eloquent\Model;

class Hostingaddon extends AbstractModel
{
    protected $table = 'hostingaddons';

    protected $fillable = [
        'hostingid',
        'addonid',
        'userid',
        'name',
        'setupfee',
        'recurring',
        'billingcycle',
        'status',
        'regdate',
        'nextduedate',
        'nextinvoicedate',
        'termination_date',
        'paymentmethod',
        'tax',
        'notes'
    ];

    protected $dates = [
        // "regDate", 
        // "registrationDate", 
        // "nextdueDate", 
        // "nextinvoiceDate", 
        // "terminationDate",
        "regdate",
        "nextduedate",
        "nextinvoicedate",
        "terminationdate",
    ];

    protected $columnMap = [
        "orderId" => "orderid",
        "serviceId" => "hostingid",
        "clientId" => "userid",
        "recurringFee" => "recurring",
        "registrationDate" => "regdate",
        "nextdueDate" => "nextduedate",
        "applyTax" => "tax",
        "terminationDate" => "termination_date",
        "paymentGateway" => "paymentmethod",
        "serverId" => "server",
        "productId" => "addonid",
        "subscriptionId" => "subscriptionid"
    ];

    protected $appends = ["serviceProperties"];
    public static $withoutAppends = false;

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function setPaymentmethodAttribute($value)
    {
        $this->attributes['paymentmethod'] = $value ?? '';
    }

    /*
	public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        foreach ($this->columns as $convention => $actual) {
            if (array_key_exists($actual, $attributes)) {
                $attributes[$convention] = $attributes[$actual];
                unset($attributes[$actual]);
            }
        }
        return $attributes;
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->columns)) {
            $key = $this->columns[$key];
        }
        return parent::getAttributeValue($key);
    }

    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->columns)) {
            $key = $this->columns[$key];
        }
        return parent::setAttribute($key, $value);
    }
	*/

    public static function boot()
    {
        parent::boot();
        self::deleted(function ($addon) {
            Sslorder::where("addon_id", $addon->id)->delete();
        });
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function getNextinvoicedateAttribute()
    {
        if (!isset($this->attributes['nextinvoicedate']) || is_null($this->attributes['nextinvoicedate'])) {
            return null;
        }
        return \Carbon\Carbon::parse($this->attributes['nextinvoicedate'])->format('Y-m-d');
    }

    public function getNextduedateAttribute()
    {
        if (!isset($this->attributes['nextduedate']) || is_null($this->attributes['nextduedate'])) {
            return null;
        }
        return \Carbon\Carbon::parse($this->attributes['nextduedate'])->format('Y-m-d');
    }

    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = $value ?? '';
    }

    public function setSetupfeeAttribute($value)
    {
        $this->attributes['setupfee'] = $value ?? 0;
    }

    public function setRecurringAttribute($value)
    {
        $this->attributes['recurring'] = $value ?? 0;
    }

    /*
	public function getServiceIdAttribute()
	{
		return $this->attributes['hostingid'];
	}
	public function getClientIdAttribute()
	{
		return $this->attributes['userid'];
	}
	public function getRecurringFeeAttribute()
	{
		return $this->attributes['recurring'];
	}
	public function getRegistrationDateAttribute()
	{
		return $this->attributes['regdate'];
	}
	public function getApplyTaxAttribute()
	{
		return $this->attributes['tax'];
	}
	public function gettErminationDateAttribute()
	{
		return $this->attributes['termination_date'];
	}
	public function getPaymentGatewayAttribute()
	{
		return $this->attributes['paymentmethod'];
	}
	public function getServerIdAttribute()
	{
		return $this->attributes['server'];
	}
	public function getProductIdAttribute()
	{
		return $this->attributes['addonid'];
	}
    */
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderid', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'userid', 'id');
    }

    public function hosting()
    {
        return $this->belongsTo(Hosting::class, 'hostingid', 'id');
    }

    public function scopeUserId($query, $userId)
    {
        return $query->where("userid", "=", $userId);
    }

    public function scopeOfService($query, $serviceId)
    {
        return $query->where("hostingid", $serviceId);
    }

    public function scopeActive($query)
    {
        return $query->where("status", Hosting::STATUS_ACTIVE);
    }

    public function scopeMarketConnect($query)
    {
        $marketConnectAddonIds = Addon::marketConnect()->pluck("id");
        return $query->whereIn("addonid", $marketConnectAddonIds);
    }

    public function scopeIsConsideredActive($query)
    {
        return $query->whereIn("status", array(Hosting::STATUS_ACTIVE, Hosting::STATUS_SUSPENDED));
    }

    public function scopeIsNotRecurring($query)
    {
        return $query->whereIn("billingcycle", array("Free", "Free Account", "One Time"));
    }

    public function service()
    {
        return $this->belongsTo(Hosting::class, "hostingid");
    }

    public function productAddon()
    {
        return $this->belongsTo(Addon::class, "addonid");
    }

    public function customFieldValues()
    {
        return $this->hasMany(Customfieldsvalue::class, "relid");
    }

    protected function getCustomFieldType()
    {
        return "addon";
    }

    protected function getCustomFieldRelId()
    {
        return $this->addonId;
    }

    public function getServicePropertiesAttribute()
    {
        return new \App\Helpers\Properties($this);
    }

    public function ssl()
    {
        return $this->hasMany(Sslorder::class);
    }

    public function canBeUpgraded()
    {
        return $this->status == "Active";
    }

    public function isService()
    {
        return false;
    }

    public function isAddon()
    {
        return true;
    }

    public function serverModel()
    {
        return $this->hasOne(Server::class, "id", "server");
    }

    /*
	public function getAddonIdAttribute()
	{
		return $this->attributes['addonid'];
	}
    */

    public function paymentGateway()
    {
        return $this->hasMany(Paymentgateway::class, "gateway", "paymentmethod");
    }

    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;

        return $query;
    }

    protected function getArrayableAppends()
    {
        if (self::$withoutAppends) {
            return [];
        }

        return parent::getArrayableAppends();
    }

    public function legacyProvision()
    {
        try {
            return (new \App\Module\Server())->ModuleCallFunction("Create", $this->serviceId, array(), $this->id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}