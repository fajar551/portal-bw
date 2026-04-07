<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Knowledgebase extends Model
{
	protected $table = 'knowledgebase';
	public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	// Relasi ke kategori
	public function categories()
	{
		return $this->belongsToMany(Knowledgebasecat::class, 'knowledgebase_cat_link', 'articleid', 'categoryid');
	}

	// Relasi ke tags
	public function tags()
	{
		return $this->hasMany(KnowledgebaseTag::class, 'articleid');
	}
}