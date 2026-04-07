<?php

namespace App\Models;

use Database;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\DB as Database;

class Hosting extends Model
{
    protected $table = 'hosting';

    const STATUS_PENDING = "Pending";
    const STATUS_ACTIVE = "Active";
    const STATUS_SUSPENDED = "Suspended";

    protected $fillable = [
        'username',
        'domain',
        'password',
        'packagename',
        'domainstatus',
        'userid',
        'packageid',
        'server',
        'regdate',
        'paymentmethod',
        'promoid',
        'overideautosuspend',
        'overidesuspenduntil',
        'bwusage',
        'bwlimit',
        'lastupdate',
        'firstpaymentamount',
        'amount'
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'orderid', 'id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'userid', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'packageid', 'id');
    }

    public function server()
    {
        return $this->belongsTo(Server::class, 'server', 'id');
    }

    public function paymentGateway()
    {
        return $this->hasMany(Paymentgateway::class, "gateway", "paymentmethod");
    }

    public function promotion()
    {
        return $this->hasMany(Promotion::class, "id", "promoid");
    }

    public function customFieldValues()
    {
        return $this->hasMany(Customfieldsvalue::class, "relid");
    }

    public function addons()
    {
        return $this->hasMany(Hostingaddon::class, "hostingid");
    }

    // Attribute Getters
    public function getClientIdAttribute()
    {
        return $this->attributes['userid'];
    }

    public function getProductIdAttribute()
    {
        return $this->attributes['packageid'];
    }

    public function getServerIdAttribute()
    {
        return $this->attributes['server'];
    }

    public function getRegistrationDateAttribute()
    {
        return $this->attributes['regdate'];
    }

    public function getPaymentGatewayAttribute()
    {
        return $this->attributes['paymentmethod'];
    }

    public function getStatusAttribute()
    {
        return $this->attributes['domainstatus'];
    }

    public function getPromotionIdAttribute()
    {
        return $this->attributes['promoid'];
    }

    public function getOverrideAutoSuspendAttribute()
    {
        return $this->attributes['overideautosuspend'];
    }

    public function getOverrideSuspendUntilDateAttribute()
    {
        return $this->attributes['overidesuspenduntil'];
    }

    public function getBandwidthUsageAttribute()
    {
        return $this->attributes['bwusage'];
    }

    public function getBandwidthLimitAttribute()
    {
        return $this->attributes['bwlimit'];
    }

    public function getLastUpdateDateAttribute()
    {
        return $this->attributes['lastupdate'];
    }

    public function getFirstPaymentAmountAttribute()
    {
        return $this->attributes['firstpaymentamount'];
    }

    public function getRecurringAmountAttribute()
    {
        return $this->attributes['amount'];
    }

    public function getRecurringFeeAttribute()
    {
        return $this->attributes['amount'];
    }

    public function getPackageIdAttribute()
    {
        return $this->attributes['packageid'];
    }

    public function getDomainStatusAttribute()
    {
        return $this->attributes['domainstatus'];
    }

    // Utility Methods
    public function getTableName()
    {
        return $this->table;
    }

    public function legacyProvision()
    {
        try {
            Log::info('Starting legacyProvision for hosting', [
                'hosting_id' => $this->id,
                'username' => $this->username,
                'domain' => $this->domain,
            ]);

            $response = (new \App\Module\Server())->ModuleCallFunction("Create", $this->id);
            Log::info('Response from ModuleCallFunction:', ['response' => $response]);
            return $response;
        } catch (\Exception $e) {
            Log::error('Error during legacyProvision: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    public function updatePackage($newPackage)
    {
        try {
            Log::info('Updating hosting package', [
                'hosting_id' => $this->id,
                'old_package' => $this->package,
                'new_package' => $newPackage
            ]);

            $this->package = $newPackage;
            $this->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating hosting package', [
                'error' => $e->getMessage(),
                'hosting_id' => $this->id
            ]);
            return false;
        }
    }
}