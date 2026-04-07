<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class PaymentgatewaysProductMapping extends Model
{
	protected $table = 'paymentgateways_product_mapping';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
