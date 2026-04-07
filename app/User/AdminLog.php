<?php

namespace App\User;

class AdminLog extends \App\Models\AbstractModel
{
    protected $table = "tbladminlog";
    protected $columnMap = array("username" => "adminusername");
    public $timestamps = false;
    public $unique = array("sessionid");
    public function admin()
    {
        return $this->belongsTo(Admin::class, "adminusername", "username");
    }
    public function scopeOnline($query)
    {
        return $query->where("lastvisit", ">", \Carbon\Carbon::now()->subMinutes(15))->groupBy("adminusername")->orderBy("lastvisit");
    }
}

?>