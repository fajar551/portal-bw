<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class OrderFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function id($id)
    {
        return $this->where('id', $id);
    }

    public function ordernum($num)
    {
        return $this->where('ordernum', $num);
    }

    public function userid($id)
    {
        return $this->where('userid', $id);
    }

    public function status($id)
    {
        return $this->where('status', $id);
    }
}
