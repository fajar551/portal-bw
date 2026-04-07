<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class UsageItem extends Model
{
	protected $table = 'usage_items';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
