<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Serversssoperm extends Model
{
	protected $table = 'serversssoperms';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
