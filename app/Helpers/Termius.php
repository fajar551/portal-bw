<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Termius
{
	private static $instance = NULL;
    protected static function setInstance(Termius $terminus)
    {
        self::$instance = $terminus;
        return $terminus;
    }
    protected static function destroyInstance()
    {
        self::$instance = null;
    }
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::setInstance(new Termius());
        }
        return self::$instance;
    }
    public function doExit($status = 0)
    {
        $status = (int) $status;
        exit($status);
    }
    public function doDie($msg = "")
    {
        if (!headers_sent()) {
            header("HTTP/1.1 500 Internal Server Error");
        }
        if (is_string($msg)) {
            exit($msg);
        }
        exit;
    }
}
