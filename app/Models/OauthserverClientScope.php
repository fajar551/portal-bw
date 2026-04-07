<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class OauthserverClientScope extends Model
{
	protected $table = 'oauthserver_client_scopes';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
