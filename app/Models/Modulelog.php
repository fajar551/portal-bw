<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Modulelog extends Model
{
	protected $table = 'modulelog';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
