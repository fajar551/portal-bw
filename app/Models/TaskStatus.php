<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
	protected $table = 'task_status';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
