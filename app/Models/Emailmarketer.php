<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Emailmarketer extends Model
{
	protected $table = 'emailmarketer';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public function setSettingsAttribute($settings)
    {
        if (is_array($settings)) {
            $settings = json_encode($settings);
        }
        if (!is_string($settings) || substr($settings, 0, 1) !== "{") {
            $settings = json_encode(array());
        }
        $this->attributes["settings"] = $settings;
    }
    public function getSettingsAttribute($settings)
    {
        return json_decode($this->attributes["settings"]);
    }
}
