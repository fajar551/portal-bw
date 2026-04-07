<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Productconfiggroup extends Model
{
	protected $table = 'productconfiggroups';
	public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
