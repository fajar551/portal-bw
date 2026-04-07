<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Ticketstatus extends Model
{
	use Filterable;
	protected $table = 'ticketstatuses';
    public $timestamps = false;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function scopeActive($query)
	{
		return $query->where('showactive', 1);
	}

	public function scopeAwaiting($query)
	{
		return $query->where('showawaiting', 1);
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class, 'status', 'title');
	}
}
