<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class KnowledgebaseImage extends Model
{
	protected $table = 'knowledgebase_images';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
}
