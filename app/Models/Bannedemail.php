<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Bannedemail extends Model
{
	protected $table = 'bannedemails';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
