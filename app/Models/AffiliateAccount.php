<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliateAccount extends Model
{
    //
    protected $table = 'affiliatesaccounts';
    public $timestamps = false;
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
