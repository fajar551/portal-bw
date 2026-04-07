<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliatesVoucher extends Model
{
	protected $table = '_affiliates_voucher';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
