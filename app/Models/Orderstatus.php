<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Orderstatus extends Model
{
	protected $table = 'orderstatuses';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
