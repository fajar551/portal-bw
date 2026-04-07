<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OperatingSystem
{
	public static function isWindows($phpOs = PHP_OS)
    {
        return in_array($phpOs, array("Windows", "WIN32", "WINNT"));
    }
    public function isOwnedByMe($path)
    {
        return fileowner($path) == Php::getUserRunningPhp();
    }
}
