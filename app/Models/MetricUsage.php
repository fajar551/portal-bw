<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class MetricUsage extends Model
{
	protected $table = 'metric_usage';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
