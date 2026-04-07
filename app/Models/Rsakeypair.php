<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Rsakeypair extends Model
{
	protected $table = 'rsakeypairs';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
