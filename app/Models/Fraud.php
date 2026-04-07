<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Fraud extends Model
{
	protected $table = 'fraud';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
