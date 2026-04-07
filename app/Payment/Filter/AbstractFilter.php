<?php

namespace App\Payment\Filter;

abstract class AbstractFilter implements FilterInterface
{
    public function getFilteredIterator(\Iterator $iterator)
    {
        return new Iterator\CallbackIterator($iterator, array($this, "filter"));
    }
    public abstract function filter(\App\Payment\Adapter\AdapterInterface $adapter);
}

?>
