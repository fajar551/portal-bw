<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
	protected $table = 'bundles';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
