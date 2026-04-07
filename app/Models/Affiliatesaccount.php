<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Affiliatesaccount extends AbstractModel
{
	protected $table = 'affiliatesaccounts';
    public $timestamps = false;
    
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
