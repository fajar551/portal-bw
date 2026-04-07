<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Ticketmaillog extends Model
{
	protected $table = 'ticketmaillog';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
