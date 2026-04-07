<?php

namespace App\Traits;

/**
 * AbstractModel
 */
trait AbstractModel
{
    // public function attributesToArray()
    // {
    //     $attributes = parent::attributesToArray();
    //     foreach ($this->columnMap as $convention => $actual) {
    //         if (array_key_exists($actual, $attributes)) {
    //             $attributes[$convention] = $attributes[$actual];
    //             unset($attributes[$actual]);
    //         }
    //     }
    //     return $attributes;
    // }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->columnMap)) {
            $key = $this->columnMap[$key];
        }
        return parent::getAttributeValue($key);
    }

    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->columnMap)) {
            $key = $this->columnMap[$key];
        }
        return parent::setAttribute($key, $value);
    }
}
