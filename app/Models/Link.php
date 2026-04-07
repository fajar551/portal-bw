<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
	protected $table = 'links';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
