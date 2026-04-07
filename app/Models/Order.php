<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

$prefix = Database::prefix();

class Order extends AbstractModel
{
    //
    use Filterable;
    public $timestamps = false;
    protected $table = 'orders';
    protected $dates = ["date"];
    protected $guarded = [];

    protected $columnMap = [
        "clientId" => "userid", 
        "orderNumber" => "ordernum",
    ];

    protected $appends = ["isPaid"];

    const PENDING = "Pending";
    const REFUNDED = "Refunded";
    const CANCELLED = "Cancelled";
    const FRAUD = "Fraud";

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    // public function attributesToArray()
    // {
    //     $attributes = parent::attributesToArray();
    //     foreach ($this->columns as $convention => $actual) {
    //         if (array_key_exists($actual, $attributes)) {
    //             $attributes[$convention] = $attributes[$actual];
    //             unset($attributes[$actual]);
    //         }
    //     }
    //     return $attributes;
    // }

    // public function getAttribute($key)
    // {
    //     if (array_key_exists($key, $this->columns)) {
    //         $key = $this->columns[$key];
    //     }
    //     return parent::getAttributeValue($key);
    // }

    // public function setAttribute($key, $value)
    // {
    //     if (array_key_exists($key, $this->columns)) {
    //         $key = $this->columns[$key];
    //     }
    //     return parent::setAttribute($key, $value);
    // }

    public function getIsPaidAttribute()
    {
        if (0 < $this->invoiceid) {
            // return $this->invoice()->first()->status == "Paid";
            if ($this->invoice()->first()) {
                return $this->invoice()->first()->status == "Paid";
            }
        }

        return false;
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'userid', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, "id", "invoiceid");
    }

}
