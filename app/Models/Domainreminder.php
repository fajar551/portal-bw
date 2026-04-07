<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Domainreminder extends Model
{
	protected $table = 'domainreminders';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
