<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\AbstractModel;

class ForwardDomain extends AbstractModel
{
    //
    use Filterable;
    protected $fillable = ['id', 'client_id', 'domain', 'target', 'isMasked', 'status', 'active_later'];
    protected $guarded = [];

    protected $table = 'forward_domain';
    public $timestamps = false;
    protected $columnMap = [
        "id" => "id", 
        "client_id" => "client_id", 
        "domain" => "domain", 
        "target" => "target", 
        "isMasked" => "isMasked", 
        "status" => "status", 
        "active_later" => "active_later", 
    ];
    
}
