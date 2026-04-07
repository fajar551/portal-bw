<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Gatewaylog extends Model
{
	protected $table = 'gatewaylog';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
