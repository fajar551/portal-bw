<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ApplinksLog extends Model
{
	protected $table = 'applinks_log';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
