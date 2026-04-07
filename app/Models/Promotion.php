<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
	use Filterable;
	protected $table = 'promotions';
    public $timestamps = false;
    protected $guarded = [];
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
        
	}
}
