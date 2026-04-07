<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Storageconfiguration extends Model
{
	protected $table = 'storageconfigurations';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
