<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class OauthserverAuthcodeScope extends Model
{
	protected $table = 'oauthserver_authcode_scopes';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
