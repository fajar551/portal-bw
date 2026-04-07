<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class QuoteFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function id($value)
    {
        return $this->where('id', $value);
    }

    public function userid($value)
    {
        return $this->where('userid', $value);
    }

    public function subject($value)
    {
        return $this->where('subject', $value);
    }

    public function stage($value)
    {
        return $this->where('stage', $value);
    }

    public function datecreated($value)
    {
        return $this->where('datecreated', $value);
    }

    public function lastmodified($value)
    {
        return $this->where('lastmodified', $value);
    }

    public function validuntil($value)
    {
        return $this->where('validuntil', $value);
    }
}
