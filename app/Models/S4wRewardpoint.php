<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class S4wRewardpoint extends Model
{
	protected $table = 's4w_rewardpoints';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
