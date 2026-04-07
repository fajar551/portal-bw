<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class TenantStat extends Model
{
	protected $table = 'tenant_stats';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
