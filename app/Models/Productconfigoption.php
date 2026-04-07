<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Productconfigoption extends Model
{
	protected $table = 'productconfigoptions';
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
