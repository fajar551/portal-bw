<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ApplinksLink extends Model
{
	protected $table = 'applinks_links';

	public function __construct(array $attributes = [])
	{
		$this->table = Database::prefix() . $this->table;
		parent::__construct($attributes);
	}

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("order", function ($builder) {
            $pfx = Database::prefix();
            $builder->orderBy("{$pfx}applinks_links.order")->orderBy("{$pfx}applinks_links.id");
        });
    }
}
