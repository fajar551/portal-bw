<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Updatehistory extends Model
{
	protected $table = 'updatehistory';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
