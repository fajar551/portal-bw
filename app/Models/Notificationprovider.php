<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Notificationprovider extends Model
{
	protected $table = 'notificationproviders';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
