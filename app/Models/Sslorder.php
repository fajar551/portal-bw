<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Sslorder extends Model
{
	protected $table = 'sslorders';
    public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
