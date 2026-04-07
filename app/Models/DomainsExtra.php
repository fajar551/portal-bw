<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class DomainsExtra extends Model
{
	protected $table = 'domains_extra';
	protected $fillable = array("domain_id", "name");

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function domain()
	{
		return $this->belongsTo(Domain::class, "domain_id");
	}
}
