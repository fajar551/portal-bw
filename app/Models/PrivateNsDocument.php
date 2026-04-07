<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
// use App\Traits\AbstractModel;

class PrivateNsDocument extends AbstractModel
{
    //
    use Filterable;
    protected $fillable = ['id', 'userid', 'syarat', 'file_meta', 'domain'];

    protected $table = 'privatensdocument';
    public $timestamps = false;
    protected $columnMap = [
        "id" => "id", 
        "userid" => "userid", 
        "domain" => "domain", 
        "syarat" => "syarat", 
        "file_meta" => "file_meta", 
    ];


    public function client()
    {
        return $this->belongsTo(Client::class, "userid");
    }
    

}