<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Registrar extends Model
{
	protected $table = 'registrars';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
