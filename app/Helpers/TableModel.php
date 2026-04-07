<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Cfg;

abstract class TableModel extends TableQuery
{
	protected $pageObj = NULL;
    protected $queryObj = NULL;
    public function __construct(Pagination $obj = NULL)
    {
        $this->pageObj = $obj;
        $numrecords = Cfg::get("NumRecordstoDisplay");
        $this->setRecordLimit($numrecords);
        return $this;
    }
    public abstract function _execute($implementationData);
    public function setPageObj(Pagination $pageObj)
    {
        $this->pageObj = $pageObj;
    }
    public function getPageObj()
    {
        return $this->pageObj;
    }
    public function execute($implementationData = NULL)
    {
        $results = $this->_execute($implementationData);
        $this->getPageObj()->setData($results);
        return $this;
    }
}
