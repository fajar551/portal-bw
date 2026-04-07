<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Productconfigoptionssub extends Model
{
	protected $table = 'productconfigoptionssub';
	protected $guarded = [];
	public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getTableName()
	{
		return $this->table;
	}
}
