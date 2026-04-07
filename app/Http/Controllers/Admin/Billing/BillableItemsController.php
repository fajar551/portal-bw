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
class BillableItemsController extends Controller
{
   const invoiceAction = ["Don't Invoice","Next Cron Run","User's Next Invoice","Invoice for Due Date","Recurring Cycle"];
   public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix=Database::prefix();
        $this->adminURL=request()->segment(1).'/'.request()->segment(2).'/';     
    }

    public function index(){
      /* if (!auth()->user()->checkPermissionTo("Manage Invoice")) {
         return back()->with('error', '<b>Oh No!</b> You don\'t have permission to access the action.');
      }  */
      $param=['invoiceAction' => self::invoiceAction];

       return view('pages.billing.billableitems.index',$param);
    }

    public function getData(Request $request){
         //dd($request->all());
         $data=DB::table("{$this->prefix}billableitems as  billableitems")->join("{$this->prefix}clients as clients", "clients.id", "=", "billableitems.userid")->select("billableitems.*", "clients.firstname", "clients.lastname", "clients.companyname", "clients.groupid", "clients.currency");
         if($request->client){
            $data->where("billableitems.userid", "=", (int)$request->client);
         }
         if($request->description){
            $data->where("billableitems.description",'LIKE',"%" . $request->description . "%" );
         }
         if($request->amount){
            $data->where("billableitems.amount",'=',$request->amount);
         }
         if($request->status && $request->status !='Any'){
            $status=$request->status;
            if ($status == "Invoiced") {
               $data->where("billableitems.invoicecount", ">", "0");
           } else {
               if ($status == "Recurring") {
                   $data->where("billableitems.invoiceaction", "=", "4");
               } else {
                   if ($status == "Active Recurring") {
                       $data->where("billableitems.invoiceaction", "=", "4")->whereRaw("billableitems.invoicecount < billableitems.recurfor");
                   } else {
                       if ($status == "Completed Recurring") {
                           $data->where("billableitems.invoiceaction", "=", "4")->whereRaw("billableitems.invoicecount >= billableitems.recurfor");
                       }
                   }
               }
           }

         }



         return Datatables::of($data)
                  ->addColumn('client', function($data) {
                     return $data->firstname.' '.$data->lastname;
                  })
                  ->editColumn('invoiceaction', function($data) {

                      return self::invoiceAction[$data->invoiceaction];
                  })
                  ->editColumn('amount', function($data) {
                     /* $getAdmin=new \App\Helpers\AdminFunctions();
		               $currency =$getAdmin->getCurrency($data->userid); */
                     if($data->hours != 0 ){
                        return \App\Helpers\Format::formatCurrency($data->amount / $data->hours);
                     }else{
                        return \App\Helpers\Format::formatCurrency($data->amount);
                     }
                  })
                  ->addColumn('invoiced', function($data) {
                     return ($data->invoiceaction == 0)?'Don\'t Invoice':'Next Cron Run';
                  })
                  ->addColumn('action', function($data) {

                     $description= $data->description;
                     return '
                             <form id="fd'.$data->id.'" action="'.url($this->adminURL.'billableitemlist/destroy/').'" method="POST">
                                 <input type="hidden" name="_token" value="'.csrf_token().'" />
                                 <input type="hidden" name="_method" value="DELETE">
                                 <input type="hidden" name="id" value="'.$data->id.'">
                                 <a href="'.url($this->adminURL.'billableitemlist/edit/'.$data->id).'" class="btn btn-info btn-xs"><i class="far fa-edit"></i></a>
                                 <button  type="button" data-id="'.$data->id.'"  data-title="'.$description.'" class="delete btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>
                             </form>   
                                 ';
                  })
                  ->rawColumns(['client','action'])
                  ->toJson();
    }




}