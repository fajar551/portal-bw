<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Currency extends AbstractModel
{
	protected $table = 'currencies';
	protected $guarded = array("id");
	public $timestamps = false;
	const DEFAULT_CURRENCY_ID = 1;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function scopeActive($query)
	{
		return $query->where('default', 1);
	}

	public function scopeDefaultCurrency($query)
    {
        return $query->where("default", 1);
    }
	
    public function scopeDefaultSorting($query)
    {
        return $query->orderBy("default", "desc")->orderBy("code");
    }
}
