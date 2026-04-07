<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Knowledgebasecat extends Model
{
	protected $table = 'knowledgebasecats';
	public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
