<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Migrasihosting extends Model
{
	protected $table = 'migrasihosting';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
