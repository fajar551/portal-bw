<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class OauthserverAccessToken extends Model
{
	protected $table = 'oauthserver_access_tokens';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
