<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Creditcard extends Model
{
	protected $table = 'creditcards';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
