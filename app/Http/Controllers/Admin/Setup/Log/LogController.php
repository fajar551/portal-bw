<?php

namespace App\Http\Controllers\Admin\Setup\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;
use Carbon\Carbon;
use Validator;
use DataTables;
use Illuminate\Support\Facades\DB;
use App\Helpers\Database;
use App\Helpers\Cfg;



use App\Traits\DatatableFilter;

class LogController extends Controller
{

   use DatatableFilter;

   protected $outputFormatting = true;

   public function __construct()
    {
        $this->prefix=\Database::prefix();
        $this->adminURL =request()->segment(1).'/';
    }

    public function activitylog(){
       //dd($this->adminURL);

       return view('pages.setup.log.activitylog');
    }

   public function getactivitylog(Request $request){
      //dd($request->all());
      
      $query =\App\Models\ActivityLog::query();
      if(!empty($request->date)){
         $date=Carbon::parse($request->date)->format('Y-m-d');
         $query->whereDate('date',$date);
      }
      if(!empty($request->username)){
         $query->where('user',$request->username);
      }
      if(!empty($request->description)){
         $query->where('description',$request->description);
      }
      if(!empty($request->resipaddressult)){
         $query->where('ipaddr',$request->resipaddressult);
      }
      return datatables()->of($query)
            ->editColumn('id', function($row) {
                return $row->id;
            })
            ->editColumn('userid', function($row) {
                return $row->userid;
            })
            ->editColumn('date', function($row) {
                return (new \App\Helpers\Functions())->fromMySQLDate($row->date, true);
            })
            ->editColumn('description', function($row) {
                $description = \App\Helpers\Sanitize::makeSafeForOutput($row->description);
                if ($this->getOutputFormatting()) {
                    $description = $this->autoLink($description, $row->userid);
                }

                return "<div class=\"card p-3\" style=\"width:450px; overflow: auto\">"
                            . $description
                        ."</div>";
            })
            ->addColumn('username', function($row) {
                return  \App\Helpers\Sanitize::makeSafeForOutput($row->user);
            })
            ->editColumn('ipaddr', function($row) {
                return  \App\Helpers\Sanitize::makeSafeForOutput($row->ipaddr);
            })
            ->addColumn('raw_id', function($row) {
                $route = "javascript:void(0);";

                return "<a href=\"{$route}\">{$row->id}</a>";
            })
            ->orderColumn('raw_id', function($query, $order) {
                $query->orderBy('id', $order);
            })
            ->rawColumns(['raw_id', 'description'])
            ->addIndexColumn()
            ->toJson();
   }

   public function setOutputFormatting($enable)
   {
       $this->outputFormatting = $enable ? true : false;
   }

   public function getOutputFormatting()
   {
       return $this->outputFormatting;
   }

   public function autoLink($description, $userid)
   {
       $patterns = $replacements = array();
       $patterns[] = "/User ID: (.*?) - Contact ID: (.*?) /";
       $patterns[] = "/User ID: (.*?) (?!- Contact)/";
       $patterns[] = "/Service ID: (.*?) /";
       $patterns[] = "/Service Addon ID: (\\d+)(\\D*?)/";
       $patterns[] = "/Domain ID: (.*?) /";
       $patterns[] = "/Invoice ID: (.*?) /";
       $patterns[] = "/Quote ID: (.*?) /";
       $patterns[] = "/Order ID: (.*?) /";
       $patterns[] = "/Transaction ID: (.*?) /";
       $patterns[] = "/Product ID: (\\d+)(\\D*?)/";

       $contactRoute = request()->root() ."/admin/clients/clientsummary?userid=\$1&contactid=\$2";
       $clientSummaryRoute = request()->root() ."/admin/clients/clientsummary?userid=\$1";
       $clientsservicesRoute = request()->root() ."/admin/clients/clientservices?userid=$userid&id=\$1";
       $clientsservicesAidRoute = request()->root() ."/admin/clients/clientservices/edit-addon?userid=$userid&aid=\$1";
       $clientsDomainRoute = request()->root() ."/admin/clients/clientdomain?userid=$userid&domainid=\$1";
       $invoicesRoute = request()->root() ."/admin/billing/invoices/edit/\$1";
       $transactionsRoute = request()->root() ."/admin/billing/transactionlist/edit/\$1";
       $viewOrderRoute = request()->root() ."/admin/orders/view-order/action=view&id=\$1";
       $configProductsRoute = request()->root() ."/admin/setup/productservices/product/edit/\$1";

       // $replacements[] = "<a href=\"clientscontacts.php?userid=\$1&contactid=\$2\">Contact ID: \$2</a> ";
       // $replacements[] = "<a href=\"clientssummary.php?userid=\$1\">User ID: \$1</a> ";
       // $replacements[] = "<a href=\"clientsservices.php?id=\$1\">Service ID: \$1</a> ";
       // $replacements[] = "<a href=\"clientsservices.php?aid=\$1\">Service Addon ID: \$1</a>";
       // $replacements[] = "<a href=\"clientsdomains.php?id=\$1\">Domain ID: \$1</a> ";
       // $replacements[] = "<a href=\"invoices.php?action=edit&id=\$1\">Invoice ID: \$1</a> ";
       // $replacements[] = "<a href=\"quotes.php?action=manage&id=\$1\">Quote ID: \$1</a> ";
       // $replacements[] = "<a href=\"orders.php?action=view&id=\$1\">Order ID: \$1</a> ";
       // $replacements[] = "<a href=\"transactions.php?action=edit&id=\$1\">Transaction ID: \$1</a> ";
       // $replacements[] = "<a href=\"configproducts.php?action=edit&id=\$1\">Product ID: \$1</a>";
       
       $replacements[] = "<a href=\"$contactRoute\">Contact ID: \$2</a> ";
       $replacements[] = "<a href=\"$clientSummaryRoute\">User ID: \$1</a> ";
       $replacements[] = "<a href=\"$clientsservicesRoute\">Service ID: \$1</a> ";
       $replacements[] = "<a href=\"$clientsservicesAidRoute\">Service Addon ID: \$1</a>";
       $replacements[] = "<a href=\"$clientsDomainRoute\">Domain ID: \$1</a> ";
       $replacements[] = "<a href=\"$invoicesRoute\">Invoice ID: \$1</a> ";
       $replacements[] = "<a href=\"#\">Quote ID: \$1</a> ";
       $replacements[] = "<a href=\"$viewOrderRoute\">Order ID: \$1</a> ";
       $replacements[] = "<a href=\"$transactionsRoute\">Transaction ID: \$1</a> ";
       $replacements[] = "<a href=\"$configProductsRoute\">Product ID: \$1</a>";
       
       $description = preg_replace($patterns, $replacements, $description . " ");

       return trim($description);
   }


   public function adminlog(){
      return view('pages.setup.log.adminlog');
   }

   public function getadminlog(Request $request){
      $data=\App\Models\AdminLog::query();

      return datatables()->of($data)
                        ->editColumn('logintime', function($row) {
                           return  Carbon::parse($row->logintime)->format('d/m/Y h:i');
                         })
                        ->editColumn('logouttime', function($row) {
                           return  Carbon::parse($row->logouttime)->format('d/m/Y h:i');
                         })
                        ->editColumn('lastvisit', function($row) {
                           return  Carbon::parse($row->lastvisit)->format('d/m/Y h:i');
                         })
                         ->orderColumn('id', function($query) {
                           $query->orderBy('id','DESC');
                       })
                        ->toJson();
   }

   public function modulelog(){
    return view('pages.setup.log.modulelog');
   }

   public function getmodulelog(Request $request){
        $data=\App\Models\Modulelog::query();
        //$data->where('request','!=','');
        //$data->where('response','!=','');

        return datatables()->of($data)
                        ->editColumn('date', function($row) {
                            return  Carbon::parse($row->date)->format('d/m/Y h:i');
                         })
                        ->editColumn('request', function($row) {
                           return  '  <div class="card">
                                        <div class="card-body">
                                            <textarea class="form-control">'.$row->request.'</textarea>
                                        </div>    
                                       </div> 
                                    ';
                         })
                        ->editColumn('response', function($row) {
                            return  '<div class="card">
                                        <div class="card-body">
                                            <textarea class="form-control">'.$row->response.'</textarea>
                                        </div>
                                    </div>';
                        })
                         ->orderColumn('id', function($query) {
                           $query->orderBy('id','DESC');
                        })
                        ->rawColumns(['request','response'])
                        ->toJson();
    }


    public function emailmessagelog(){
        return view('pages.setup.log.emailmessagelog');
    }

    public function getemailmessagelog(Request $request){
        $data=DB::table($this->prefix.'emails as emails')
                    ->join($this->prefix.'clients as client','emails.userid','=','client.id')
                    ->select('emails.id','emails.date','emails.subject','emails.userid','client.firstname','client.lastname');
                    return datatables()->of($data)
                    ->editColumn('date', function($row) {
                        return  Carbon::parse($row->date)->format('d/m/Y h:i');
                     })
                     ->addColumn('client', function($row) {
                        return $row->firstname.' '.$row->lastname;
                    })
                     ->addColumn('detail', function($row) {
                        return 'aa';
                    })
                     ->orderColumn('id', function($query) {
                       $query->orderBy('id','DESC');
                    })
                    ->rawColumns(['client','response'])
                    ->toJson();
    }

    public function ticketmailimportlog(){
        return view('pages.setup.log.ticketmailimportlog');
    }

    public function getticketmailimportlog(Request $request){
        $data=\App\Models\Ticketmaillog::query();
        return datatables()->of($data)
                            ->editColumn('date', function($row) {
                                return  Carbon::parse($row->date)->format('d/m/Y h:i');
                            })
                            ->editColumn('status', function($row) {
                                switch ($row->status){
                                    case 'Ticket Imported Successfully':
                                         return  "<font color=#669900>Ticket Imported Successfully</font>";
                                    break;
                                    case 'Ticket Reply Imported Successfully':
                                        return  "<font color=#669900>Ticket Reply Imported Successfully</font>";
                                    break;
                                    
                                    default:
                                        return $row->status;
                                }
                            })
                            ->orderColumn('id', function($query) {
                            $query->orderBy('id','DESC');
                            })
                            ->rawColumns(['status'])
                            ->toJson();

    }

    public function whoislookuplog(){
        return view('pages.setup.log.whoislookuplog');
    }


    public function getwhoislookuplog(Request $request){
        $data=\App\Models\Whoislog::query();
        return datatables()->of($data)
                            ->editColumn('date', function($row) {
                                return  Carbon::parse($row->date)->format('d/m/Y h:i');
                            })
                            ->orderColumn('id', function($query) {
                                $query->orderBy('id','DESC');
                            })
                            //->rawColumns(['status'])
                            ->toJson();
    }


}
