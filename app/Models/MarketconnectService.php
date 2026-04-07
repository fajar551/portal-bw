<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class MarketconnectService extends Model
{
	protected $table = 'marketconnect_services';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
