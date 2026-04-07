<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Networkissue extends Model
{
	protected $table = 'networkissues';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
