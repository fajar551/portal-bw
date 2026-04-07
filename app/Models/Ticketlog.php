<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Ticketlog extends AbstractModel
{
	protected $table = 'ticketlog';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
