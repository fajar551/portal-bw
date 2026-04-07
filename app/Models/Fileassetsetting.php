<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Fileassetsetting extends Model
{
	protected $table = 'fileassetsettings';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
