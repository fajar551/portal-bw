<?php 

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class ProductFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];

    public function pid($pid)
    {
        if (is_numeric($pid)) {
            return $this->where('id', $pid);
        } else {
            $pids = array();
            foreach (explode(",", $pid) as $p) {
                $p = (int) trim($p);
                if ($p) {
                    $pids[] = $p;
                }
            }
            if ($pids) {
                return $this->whereIn('id', $pids);
            }
        }
        
    }

    public function gid($gid)
    {
        return $this->where('gid', $gid);
    }

    public function module($module)
    {
        if ($module && preg_match("/^[a-zA-Z0-9_\\.\\-]*\$/", $module)) {
            return $this->where('servertype', $module);
        }
    }
}
