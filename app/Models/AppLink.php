<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AppLink extends AbstractModel
{
    //
    protected $table = 'applinks';
    protected $primaryKey = "id";
    protected $fillable = array("module_type", "module_name");

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function links()
    {
        return $this->hasMany(ApplinksLink::class, "applink_id");
    }

    public function log()
    {
        return $this->hasMany(ApplinksLog::class, "applink_id");
    }
}
