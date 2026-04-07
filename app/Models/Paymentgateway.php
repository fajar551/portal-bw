<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Paymentgateway extends Model
{
	protected $table = 'paymentgateways';
    public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function scopeName($query)
    {
        return $query->where("setting", "name");
    }
}
