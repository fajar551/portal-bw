<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Configuration extends AbstractModel
{
	protected $table = 'configuration';
	protected $fillable = ['setting', 'value'];
    protected $dates = ['created_at', 'updated_at'];

	public $incrementing = false;
    protected $primaryKey = "setting";
    public $unique = array("setting");
    public $guardedForUpdate = array("setting");
    // protected $fillable = array("value");
    protected $booleanValues = array("EnableProformaInvoicing");
    protected $nonEmptyValues = array();
    // protected $commaSeparatedValues = array("BulkCheckTLDs");
    protected static $defaultKeyValuePairs = array();

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public static function getValue($setting)
    {
        global $CONFIG;
        if (isset($CONFIG[$setting])) {
            return $CONFIG[$setting];
        }
        $setting = self::find($setting);
        if (is_null($setting)) {
            return null;
        }
        $CONFIG[$setting->setting] = $setting->value;
        return $setting->value;
    }
    public static function setValue($key, $value)
    {
        $value = trim($value);
        $setting = self::findOrNew($key);
        $setting->setting = $key;
        $setting->value = $value;
        $setting->save();
        return $setting;
    }
}
