<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Domainsadditionalfield extends Model
{
	protected $table = 'domainsadditionalfields';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}
	public function domain()
    {
        return $this->belongsTo(Domain::class, "domainid");
    }
}
