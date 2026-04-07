<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use API;
use App\Helpers\Domain as HelpersDomain;
use App\Helpers\DomainFunctions;
use App\Helpers\Orders;
use App\Models\Domain;
use App\Models\Domainpricing;
use App\Models\Ticket;
use App\Models\Ticketstatus;

class TestingController extends Controller
{
    //
    public function index(Request $request)
    {
        //\App\Helpers\Schedule\InvoiceReminders::run();
        //dd($data);    
        //Request::create()
        //global $_LANG;
        //dd($_LANG);
        // phpinfo();exit();
        // \App\Helpers\Schedule\DatabaseBackup::run();
        // $request = [
        //    'currencyid' => 1,
        //    'clientid' => 11, 
        // ];
        // $result = \App\Models\Pricing::where('type', 'domainregister')->where('currency', 1)->where('relid', 3)->first();
        $resultTld = (new DomainFunctions())->getTLDList();

        foreach ($resultTld as $id => $tld) {
           $getTldData = \App\Models\Pricing::where('type', 'domainregister')->where('currency', 1)->where('relid', $id+1)->get();
           dd($getTldData);
        }
        // $getTldData;
        // $result->
        // $result = Orders::getpricinginfo(10);
        // foreach ($result as $data) {
        //     $validtlds[] = $data->extension;
        // }
        // dd($result);
    }
}
