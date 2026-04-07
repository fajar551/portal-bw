<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    //
    protected $table = 'apilog';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
