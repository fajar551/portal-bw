<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class RegistrarSetting extends AbstractModel
{
    protected $table = "registrars";
    public $timestamps = false;
    protected $fillable = array("registrar");
    public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
    public function scopeRegistrar($query, $registrarName)
    {
        return $query->where("registrar", "=", $registrarName);
    }
    public function scopeSetting($query, $registrarSettingName)
    {
        return $query->where("setting", "=", $registrarSettingName);
    }
    public function getValueAttribute($value)
    {
        if (!empty($value)) {
            $value = $this->decrypt($value);
        }
        return $value;
    }
    public function setValueAttribute($value)
    {
        $this->attributes["value"] = $this->encrypt($value);
    }
}
