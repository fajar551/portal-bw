<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
	protected $table = 'emails';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
