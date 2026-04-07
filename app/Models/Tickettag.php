<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Tickettag extends Model
{
	protected $table = 'tickettags';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
