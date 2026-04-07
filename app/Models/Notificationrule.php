<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Notificationrule extends Model
{
	protected $table = 'notificationrules';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
