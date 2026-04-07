<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\AbstractModel;

class ForwardEmail extends AbstractModel
{
    //
    use Filterable;
    protected $fillable = ['id', 'uid', 'domain', 'alias', 'email'];
    protected $guarded = [];

    protected $table = 'forward_email';
    public $timestamps = false;
    protected $columnMap = [
        "id" => "id", 
        "uid" => "uid", 
        "domain" => "domain", 
        "alias" => "alias", 
        "email" => "email"
    ];
    
}
