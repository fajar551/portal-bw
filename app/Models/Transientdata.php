<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Transientdata extends Model
{
	protected $table = 'transientdata';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
