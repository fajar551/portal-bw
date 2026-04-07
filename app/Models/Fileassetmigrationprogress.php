<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Fileassetmigrationprogress extends Model
{
	protected $table = 'fileassetmigrationprogress';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
