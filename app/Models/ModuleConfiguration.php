<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ModuleConfiguration extends AbstractModel
{
	protected $table = 'module_configuration';
	protected $fillable = array("entity_type", "setting_name", "friendly_name", "value");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function productAddon()
	{
		return $this->belongsTo(Addon::class, "entity_id");
	}

	public function product()
	{
		return $this->belongsTo(Product::class, "entity_id");
	}
}
