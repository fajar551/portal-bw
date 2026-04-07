<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Updatelog extends Model
{
	protected $table = 'updatelog';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
