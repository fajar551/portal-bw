<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DomainCheckerController extends Controller
{
    //
    public function index(Request $request)
    {
        (new \App\Helpers\Domain\Checker())->ajaxCheck();
        \App\Helpers\Terminus::getInstance()->doExit();
    }
}
