<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TableQuery
{
	protected $recordOffset = 0;
    protected $recordLimit = 25;
    protected $data = array();
    public function getData()
    {
        return $this->data;
    }
    public function getOne()
    {
        return isset($this->data[0]) ? $this->data[0] : null;
    }
    public function setRecordLimit($limit)
    {
        $this->recordLimit = $limit;
        return $this;
    }
    public function getRecordLimit()
    {
        return $this->recordLimit;
    }
    public function getRecordOffset()
    {
        $page = $this->getPageObj()->getPage();
        $offset = ($page - 1) * $this->getRecordLimit();
        return $offset;
    }
    public function getQueryLimit()
    {
        return $this->getRecordOffset() . "," . $this->getRecordLimit();
    }
    public function setData($data = array())
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException("Dataset must be an array");
        }
        $this->data = $data;
        return $this;
    }
}
