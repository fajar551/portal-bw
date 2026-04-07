<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    //
    protected $table = 'adminroles';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
