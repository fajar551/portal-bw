<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Bannedip extends Model
{
	protected $table = 'bannedips';
	protected $guarded = [];
	const UPDATED_AT = null;
	const CREATED_AT = null;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
