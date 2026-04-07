<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
	protected $table = 'credit';
	public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
