<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class DomainLookupConfiguration extends Model
{
	protected $table = 'domain_lookup_configuration';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public function scopeOfRegistrar($query, $registrar)
    {
        return $query->whereRegistrar($registrar);
    }
}
