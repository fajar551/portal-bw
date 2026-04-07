<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
	protected $table = 'sessions';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
