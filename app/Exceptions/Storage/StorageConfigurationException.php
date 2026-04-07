<?php

namespace App\Exceptions\Storage;

use Exception;

class StorageConfigurationException extends \App\Exceptions\Storage\StorageException
{
    //
    private $fields = array();

    public function __construct(array $fields)
    {
        parent::__construct(join(" ", array_values($fields)));
        $this->fields = $fields;
    }
    
    public function getFields()
    {
        return $this->fields;
    }
}
