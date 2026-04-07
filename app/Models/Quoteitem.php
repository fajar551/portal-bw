<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Quoteitem extends Model
{
	protected $table = 'quoteitems';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
