<?php

namespace App\Models;

use Database;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    //
    use Filterable;
    protected $table = 'affiliates';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
