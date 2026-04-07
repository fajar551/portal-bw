<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
	use Filterable;
	protected $table = 'quotes';
	public $timestamps = false;

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function items()
	{
		return $this->hasMany(Quoteitem::class, 'quoteid', 'id');
	}

	public function client()
	{
		return $this->belongsTo(Client::class, 'userid', 'id');
	}
}
