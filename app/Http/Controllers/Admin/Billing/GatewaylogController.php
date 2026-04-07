<?php

namespace App\Http\Controllers\Admin\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Database;
use DataTables;
use DB;
use App;
use \App\Helpers\Cfg;
use Illuminate\Support\Carbon;
use \App\Helpers\Format;
use App\Helpers\LogActivity;
use Validator;
use \App\Helpers\HelperApi as LocalApi;
use App\Helpers\HelperApi;
use \App\Helpers\Invoice;
use Illuminate\Database\Eloquent\Model;
class GatewaylogController extends Controller
{
   public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix=Database::prefix();
    }

    public function index(){
        $gateway=\App\Helpers\Gateway::GetGatewaysArray();
        $result=\App\Models\Gatewaylog::select('result')->where('result','!=','')->groupBy('result')->get();
       
        return view('pages.billing.gatewaylog.index',['payment' => $gateway, 'result' => $result ]);
    }

    public function GetData(Request $request){
        //dd($request->all());
         $data=\App\Models\Gatewaylog::distinct();
        if($request->date){
            $date=explode('|',$request->date);
            $startDate= Carbon::parse(trim($date[0]))->format('Y-m-d');
            $endDate=Carbon::parse(trim($date[1]))->format('Y-m-d');
            $data->whereBetween(DB::raw('DATE(date)'),[$startDate,$endDate]);
        }

        if($request->debugdata){
            $data->where('data','LIKE','%'.$request->debugdata.'%');
        }
        if($request->gateway){
            $data->where('gateway','LIKE','%'.$request->gateway.'%');
        }
        if($request->result){
            $data->where('result','LIKE','%'.$request->result.'%');
        }

         return Datatables::of($data)
                  ->editColumn('date', function($data) {
                        return  Carbon::parse($data->date)->isoFormat(Cfg::get('DateFormat'));
                  })
                  ->editColumn('data', function($data) {
                       // return  \App\Helpers\Sanitize::makeSafeForOutput($data->data);
                        return  html_entity_decode($data->data);
                  })
                  ->toJson();
    }




}