<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Announcement extends AbstractModel
{
    //
    protected $table = 'announcements';
    protected $dates = ['date'];
    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}
