<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
	protected $table = 'tax';
    public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
