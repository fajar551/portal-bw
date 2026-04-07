<?php

namespace App\Models\Domain;

use Illuminate\Database\Eloquent\Model;

class TopLevel extends \App\Models\AbstractModel
{
    //
    protected $table = "tbltlds";
    public $unique = array("tld");
    protected $fillable = array("tld");
    public function categories()
    {
        // return $this->belongsToMany("App\\Models\\Domain\\TopLevel\\Category", "tbltld_category_pivot", "tld_id")->withTimestamps();
        return $this->belongsToMany(\App\Models\Domain\TopLevel\Category::class, "tbltld_category_pivot", "tld_id")->withTimestamps();
    }
}
