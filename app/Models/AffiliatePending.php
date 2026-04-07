<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliatePending extends Model
{
    //
    protected $table = 'affiliatespending';
    public $timestamps = false;
    
    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
