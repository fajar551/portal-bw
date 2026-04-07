<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Servergroupsrel extends Model
{
	public $timestamps = false;
	protected $fillable = ['groupid', 'serverid'];
	protected $table = 'servergroupsrel';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
