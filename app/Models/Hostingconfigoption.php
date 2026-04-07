<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Hostingconfigoption extends Model
{
	protected $table = 'hostingconfigoptions';
	protected $guarded = [];
    public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function getTableName()
	{
		return $this->table;
	}
}
