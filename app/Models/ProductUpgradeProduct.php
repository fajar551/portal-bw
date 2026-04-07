<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ProductUpgradeProduct extends Model
{
	protected $table = 'product_upgrade_products';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
