<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Ticketbreakline extends Model
{
	protected $table = 'ticketbreaklines';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
