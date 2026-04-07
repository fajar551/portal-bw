<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AuthnConfig extends Model
{
	protected $table = 'authn_config';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
