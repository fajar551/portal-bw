<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Bankacct extends Model
{
	protected $table = 'bankaccts';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
