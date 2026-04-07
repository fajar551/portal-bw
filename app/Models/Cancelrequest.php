<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Cancelrequest extends Model
{
	protected $table = 'cancelrequests';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
