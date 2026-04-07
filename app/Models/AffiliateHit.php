<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliateHit extends Model
{
    //
    protected $table = 'affiliates_hits';
    public $timestamps = false;
    public $dates = array("created_at");
    protected $fillable = array("affiliate_id", "referrer_id", "created_at");

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }
    
    public function referrer()
    {
        return $this->belongsTo(AffiliateReferrer::class, "referrer_id", "id");
    }
}
