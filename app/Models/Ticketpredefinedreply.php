<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Ticketpredefinedreply extends Model
{
	use Filterable;
	protected $table = 'ticketpredefinedreplies';
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
