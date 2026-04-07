<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AdminPerm extends Model
{
    //
    protected $table = 'adminperms';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
