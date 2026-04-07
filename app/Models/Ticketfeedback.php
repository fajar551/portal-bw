<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Ticketfeedback extends Model
{
	protected $table = 'ticketfeedback';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
