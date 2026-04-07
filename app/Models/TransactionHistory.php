<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
	protected $table = 'transaction_history';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
