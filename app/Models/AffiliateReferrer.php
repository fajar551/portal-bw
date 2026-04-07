<?php

namespace App\Models;

use Database;
use Illuminate\Database\Eloquent\Model;

class AffiliateReferrer extends Model
{
    //
    protected $table = 'affiliates_referrers';
    protected $fillable = array("affiliate_id", "referrer");

    public function __construct(array $attributes = [])
    {
        $this->table = Database::prefix() . $this->table;
        parent::__construct($attributes);
    }

    public function hits()
    {
        return $this->hasMany(AffiliateHit::class, "referrer_id");
    }
}
