<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class InvoiceFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function userid($id)
    {
        $this->where('userid', $id);
    }

    public function status($status)
    {
        if ($status == 'Overdue') {
            $this->where('status', 'Unpaid')->where('duedate', '<', \Carbon\Carbon::today()->toDateString());
        } else {
            $this->where('status', $status);
        }
    }
}
