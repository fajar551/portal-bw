<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
	protected $table = 'notes';
	const CREATED_AT = 'created';
	const UPDATED_AT = 'modified';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
