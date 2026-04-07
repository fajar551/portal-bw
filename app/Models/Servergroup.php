<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Servergroup extends Model
{
	protected $table = 'servergroups';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
