<?php

namespace App\Payment\Filter;

interface FilterInterface
{
    public function getFilteredIterator(\Iterator $iterator);
    public function filter(\App\Payment\Adapter\AdapterInterface $adapter);
}

?>
