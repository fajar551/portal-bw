<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\AbstractModel;

class Account extends AbstractModel
{
    //
    use Filterable;
    protected $table = 'accounts';
    public $timestamps = false;
    protected $columnMap = [
        "clientId" => "userid", 
        "currencyId" => "currency", 
        "paymentGateway" => "gateway", 
        "exchangeRate" => "rate", 
        "transactionId" => "transid", 
        "amountIn" => "amountin", 
        "amountOut" => "amountout", 
        "invoiceId" => "invoiceid", 
        "refundId" => "refundid"
    ];


    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, "userid");
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, "invoiceid");
    }

}
