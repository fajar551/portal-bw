<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class JobsQueue extends Model
{
	protected $table = 'jobs_queue';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
