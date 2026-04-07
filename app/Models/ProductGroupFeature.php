<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ProductGroupFeature extends Model
{
	protected $table = 'product_group_features';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
