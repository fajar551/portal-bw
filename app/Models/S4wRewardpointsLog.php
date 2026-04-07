<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class S4wRewardpointsLog extends Model
{
	protected $table = 's4w_rewardpoints_log';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
