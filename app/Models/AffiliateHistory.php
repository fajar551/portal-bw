<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliateHistory extends Model
{
    //
    protected $table = 'affiliateshistory';
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
