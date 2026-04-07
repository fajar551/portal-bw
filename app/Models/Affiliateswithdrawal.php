<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Affiliateswithdrawal extends Model
{
	protected $table = 'affiliateswithdrawals';
    public $timestamps = false;
    
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
