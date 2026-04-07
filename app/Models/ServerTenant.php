<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ServerTenant extends Model
{
	protected $table = 'server_tenants';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
