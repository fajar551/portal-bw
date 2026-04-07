<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ProductDownload extends Model
{
	protected $table = 'product_downloads';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
