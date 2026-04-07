<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class EmailImage extends Model
{
	protected $table = 'email_images';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
