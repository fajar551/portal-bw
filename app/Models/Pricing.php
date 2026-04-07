<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    protected $table = 'pricing';
    protected $fillable = ['msetupfee'];
	const UPDATED_AT = null;
	const CREATED_AT = null;
	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

	public function curr()
	{
		return $this->belongsTo(Currency::class, 'currency', 'id')->orderBy('code', 'ASC');
	}
}
