<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class EmailmarketerRelatedPivot extends Model
{
	protected $table = 'emailmarketer_related_pivot';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
