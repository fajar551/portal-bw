<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class AffiliateFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function clientid($value)
    {
        return $this->where('clientid', $value);
    }

    public function visitors($value)
    {
        return $this->where('visitors', $value);
    }

    public function paytype($value)
    {
        return $this->where('paytype', 'LIKE', $value);
    }

    public function payamount($value)
    {
        return $this->where('payamount', 'LIKE', $value);
    }

    public function onetime($value)
    {
        return $this->where('onetime', $value);
    }

    public function balance($value)
    {
        return $this->where('balance', 'LIKE', $value);
    }

    public function withdrawn($value)
    {
        return $this->where('withdrawn', 'LIKE', $value);
    }
}
