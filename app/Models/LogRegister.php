<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class LogRegister extends Model
{
	protected $table = 'log_register';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
