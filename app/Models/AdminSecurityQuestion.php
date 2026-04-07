<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AdminSecurityQuestion extends Model
{
    //
    protected $table = 'adminsecurityquestions';

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
