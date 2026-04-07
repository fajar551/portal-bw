<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class OauthserverScope extends Model
{
	protected $table = 'oauthserver_scopes';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
