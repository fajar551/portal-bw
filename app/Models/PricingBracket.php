<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class PricingBracket extends Model
{
	protected $table = 'pricing_bracket';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
