<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
	protected $table = 'servers';
    public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("ordered", function ($builder) {
            $pfx = Database::prefix();
            $builder->orderBy("{$pfx}servers.name");
        });
        static::deleted(function (Server $server) {
            ServersRemote::where("server_id", $server->id)->delete();
        });
    }
    public function scopeEnabled($query)
    {
        return $query->where("disabled", 0);
    }
    public function scopeOfModule($query, $module)
    {
        return $query->where("type", $module);
    }
}
