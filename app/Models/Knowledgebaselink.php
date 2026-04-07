<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class Knowledgebaselink extends Model
{
    protected $table = 'knowledgebaselinks';
    public $timestamps = false;
    
    protected $fillable = [
        'articleid',
        'categoryid'
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
}