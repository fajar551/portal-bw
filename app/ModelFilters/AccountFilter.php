<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class AccountFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function userid($value)
    {
        return $this->where('userid', $value);
    }

    public function invoiceid($value)
    {
        return $this->where('invoiceid', $value);
    }

    public function transid($value)
    {
        return $this->where('transid', $value);
    }
}
