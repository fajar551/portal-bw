<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AuthnAccountLink extends Model
{
	protected $table = 'authn_account_links';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
