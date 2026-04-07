<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Upgrade extends Model
{
	protected $table = 'upgrades';
	public $timestamps = false;
	public $currency = NULL;
    public $applyTax = false;
    public $localisedNewCycle = NULL;
    const TYPE_SERVICE = "service";
    const TYPE_ADDON = "addon";
    const TYPE_PACKAGE = "package";
    const TYPE_CONFIGOPTIONS = "configoptions";

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getUserIdAttribute()
	{
		return $this->attributes['userid'];
	}
	public function getOrderIdAttribute()
	{
		return $this->attributes['orderid'];
	}
	public function getEntityIdAttribute()
	{
		return $this->attributes['relid'];
	}
	public function getOriginalValueAttribute()
	{
		return $this->attributes['originalvalue'];
	}
	public function getNewValueAttribute()
	{
		return $this->attributes['newvalue'];
	}
	public function getUpgradeAmountAttribute()
	{
		return $this->attributes['amount'];
	}
	public function getRecurringChangeAttribute()
	{
		return $this->attributes['recurringchange'];
	}

	public function originalProduct()
    {
        return $this->hasOne(Product::class, "id", "originalvalue");
    }
	public function newProduct()
    {
        return $this->hasOne(Product::class, "id", "newvalue");
    }
    public function originalAddon()
    {
        return $this->hasOne(Addon::class, "id", "originalvalue");
    }
    public function newAddon()
    {
        return $this->hasOne(Addon::class, "id", "newvalue");
    }
	public function service()
    {
        return $this->hasOne(Hosting::class, "id", "relid");
    }
    public function addon()
    {
        return $this->hasOne(Hostingaddon::class, "id", "relid");
    }

	public function order()
	{
		return $this->belongsTo(Order::class,
		'orderid',
		'id');
	}

	public function client()
	{
		return $this->belongsTo(Client::class, 'userid', 'id');
	}
}
