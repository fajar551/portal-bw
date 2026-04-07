<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class OauthserverClientFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function grantType($value)
    {
        $this->whereLike("grant_types", "%" . $grantType . "%");
    }
}
