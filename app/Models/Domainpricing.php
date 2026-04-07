<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Domainpricing extends Model
{
	protected $table = 'domainpricing';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
