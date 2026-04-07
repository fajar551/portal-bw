<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Database;
use DataTables;
use DB;
use \App\Helpers\Cfg;
use Illuminate\Support\Carbon;
use \App\Helpers\Format;
use App\Helpers\LogActivity;
use Validator;
use \App\Helpers\HelperApi as LocalApi;
use \App\Helpers\Invoice;
use Illuminate\Database\Eloquent\Model;

class BillingController extends Controller
{
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1).'/'.request()->segment(2).'/';
    }

    public function BillableItems()
    {
        return view('pages.billing.billableitems.index');
    }
    public function BillableItems_add()
    {
        return view('pages.billing.billableitems.add');
    }
    public function Quotes()
    {
        return view('pages.billing.quotes.index');
    }
    public function Quotes_add()
    {
        return view('pages.billing.quotes.add');
    }
    public function OfflineCCProcessing()
    {
        return view('pages.billing.offlineccprocessing.index');
    }
    public function GatewayLog()
    {
        return view('pages.billing.gatewaylog.index');
    }
}

