<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ServersRemote extends Model
{
	protected $table = 'servers_remote';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
