<?php

namespace App\Models\Domain\TopLevel;

use Illuminate\Database\Eloquent\Model;

class Category extends \App\Models\AbstractModel
{
    protected $table = "tbltld_categories";
    public $unique = array("category");
    protected $booleans = array("isPrimary");
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("order", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy("tbltld_categories.display_order")->orderBy("tbltld_categories.id");
        });
    }
    public function topLevelDomains()
    {
        // return $this->belongsToMany("App\\Domain\\TopLevel", "tbltld_category_pivot", "category_id", "tld_id")->withTimestamps();
        return $this->belongsToMany(\App\Models\Domain\TopLevel::class, "tbltld_category_pivot", "category_id", "tld_id")->withTimestamps();
    }
    public function scopeTldsIn($query, array $tlds = array())
    {
        return $query->whereHas("topLevelDomains", function ($subQuery) use($tlds) {
            $subQuery->whereIn("tld", $tlds);
        });
    }
}
