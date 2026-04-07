<?php

namespace App\Helpers\Domains\DomainLookup;

class ResultsList extends \ArrayObject
{
    public function toArray()
    {
        $result = array();
        foreach ($this->getArrayCopy() as $key => $data) {
            $result[$key] = $data->toArray();
        }
        return $result;
    }
}

?>
