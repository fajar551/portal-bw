<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Example
{
  protected $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  /***
   * Accepts a pending order
   */
  public static function GetUsers($params = [])
  {
    $userid = $params['userid'] ?? 0;
  }
}
