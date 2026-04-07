<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Modbox extends AbstractModel
{
    use Filterable;
    protected $fillable = ['id', 'userid', 'comid', 'file', 'type', 'meta', 'set_all'];
    protected $guarded = [];

    protected $table = 'mod_box';
    public $timestamps = false;
    protected $columnMap = [
        "id" => "id", 
        "userid" => "userid", 
        "comid" => "comid", 
        "type" => "type", 
        "file" => "file", 
        "meta" => "meta", 
        "set_all" => "set_all", 
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, "userid");
    }
}
