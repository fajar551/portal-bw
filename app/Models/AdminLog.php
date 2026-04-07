<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    //
    protected $table = 'adminlog';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
