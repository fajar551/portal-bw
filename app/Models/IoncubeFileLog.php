<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class IoncubeFileLog extends Model
{
	protected $table = 'ioncube_file_log';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
