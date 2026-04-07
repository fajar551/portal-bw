<?php

namespace App\Http\Controllers\Admin\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Carbon;
use DataTables;
use App\Helpers\Cfg;
use Illuminate\Support\Facades\DB;
use Database;
use Validator;
use App\Models\Ticket;
use App\Models\Ticketreply;
use API;
use Ticket as TicketHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Helpers\LogActivity;
use PhpParser\Node\Expr\FuncCall;

class SupportoverviewController extends Controller
{
    public function __construct()
    {
        $this->prefix=Database::prefix();
        $this->adminURL =request()->segment(1);
    }

    public function index()
    {
        return view ('pages.support.supportoverview.index');
    }

    public function SupportOverviewPost(Request $request){
        $error=true;
        $data =[];
        if($request->display == 'today' ){
           $date=Carbon::now()->format('Y-m-d');
        }

        //$dataTiket=\App\Models\Ticket::select('id','date',DB::raw("(SELECT date FROM tblticketreplies WHERE tblticketreplies.tid=tbltickets.id AND admin!='' LIMIT 1) as datefirstreply") );
        $period = $request->display;
        if ($period == "today"){
            $newTiket =\App\Models\Ticket::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->count();
            $clientReplies =\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->where('admin','=','')->count();
            $staffReplies = \App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->where('admin','!=','')->count();
            $ticketsWithoutReply=0;
            $AverageFirstResponse=0;
        }else{
            if ($period == "ThisWeek") {
                $last_monday =  Carbon::now()->modify('last monday')->format('Y-m-d');
                $next_sunday  =  Carbon::now()->modify('next sunday')->format('Y-m-d');
                $newTiket=\App\Models\Ticket::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->count();
                $clientReplies=\App\Models\Ticketreply::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->where('admin','=','')->count();
                $staffReplies=\App\Models\Ticketreply::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->where('admin','!=','')->count();
                $ticketsWithoutReply=0;
            $AverageFirstResponse=0;
            }else{
                if ($period == "LastMonth") {
                    $newTiket=\App\Models\Ticket::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->count();
                    $clientReplies=\App\Models\Ticketreply::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->where('admin','=','')->count();
                    $staffReplies=\App\Models\Ticketreply::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->where('admin','!=','')->count();
                    $ticketsWithoutReply=0;
                    $AverageFirstResponse=0;
                } else {
                    //yesterday
                    $newTiket=\App\Models\Ticket::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->count();
                    $clientReplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->where('admin','=','')->count();
                    $staffReplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->where('admin','!=','')->count();
                    $ticketsWithoutReply=0;
                    $AverageFirstResponse=0;
                }

            }

        }
        
        $params=[
                    'newTiket'              => $newTiket,
                    'clientReplies'         =>  $clientReplies,
                    'staffReplies'          =>  $staffReplies,
                    'ticketsWithoutReply'   =>  $ticketsWithoutReply,
                    'AverageFirstResponse'   =>  $AverageFirstResponse
        ];
        return json_encode($params);
        
    }

    public function SupportOverviewPie(Request $request){
        // dd($request->all());
         $data=[];
 
 
         $dataTiket=\App\Models\Ticket::select('id','date',DB::raw("(SELECT date FROM tblticketreplies WHERE tblticketreplies.tid=tbltickets.id AND admin!='' LIMIT 1) as datefirstreply") );
         
         //$request->display='LastMonth';
         $period = $request->display;
         if ($period == "today") {
             $newtickets=\App\Models\Ticket::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->count();
             $clientreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->where('admin','=','')->count();
             $staffreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%')->where('admin','!=','')->count();
 
             $dataTiket->where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%');
 
 
         } else {
             if ($period == "ThisWeek") {
                 $last_monday =  Carbon::now()->modify('last monday')->format('Y-m-d');
                 $next_sunday  =  Carbon::now()->modify('next sunday')->format('Y-m-d');
         
                 $newtickets=\App\Models\Ticket::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->count();
                 $clientreplies=\App\Models\Ticketreply::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->where('admin','=','')->count();
                 $staffreplies=\App\Models\Ticketreply::whereBetween(DB::raw('DATE(date)'),[$last_monday,$next_sunday])->where('admin','!=','')->count();
 
                 $dataTiket->where('date','LIKE','%'.Carbon::now()->format('Y-m-d').'%');
                 
 
             } else {
                 if ($period == "ThisMonth") {
                     $newtickets=\App\Models\Ticket::where('date','LIKE','%'.Carbon::now()->format('Y-m-').'%')->count();
                     $clientreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-').'%')->where('admin','=','')->count();
                     $staffreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->format('Y-m-').'%')->where('admin','!=','')->count();
 
                     $dataTiket->where('date','LIKE','%'.Carbon::now()->format('Y-m-').'%');
 
 
                 } else {
                     if ($period == "LastMonth") {
                         $newtickets=\App\Models\Ticket::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->count();
                         $clientreplies=\App\Models\Ticketreply::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->where('admin','=','')->count();
                         $staffreplies=\App\Models\Ticketreply::where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%')->where('admin','!=','')->count();
                    
                         $dataTiket->where('date','LIKE','%'. Carbon::now()->modify('last month')->format('Y-m-').'%');
                    
                     } else {
                         //yesterday
                         $newtickets=\App\Models\Ticket::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->count();
                         $clientreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->where('admin','=','')->count();
                         $staffreplies=\App\Models\Ticketreply::where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%')->where('admin','!=','')->count();
                    
                         $dataTiket->where('date','LIKE','%'.Carbon::now()->modify('yesterday')->format('Y-m-d').'%');
                     }
                 }
             }
         }
 
         
        //dd($dataTiket);
        $hours = array();
        $maxHour = !$period || $period == "today" ? date("H") : 23;
         for ($hour = 0; $hour <= $maxHour; $hour++) {
             $hours[str_pad($hour, 2, 0, STR_PAD_LEFT)] = 0;
         }
         $replytimes = [1 => "0", 2 => 0, 4 => "0", 8 => "0", 16 => "0", 24 => "0"];
         $avefirstresponse = "0";
         $avefirstresponsecount = "0";
         $opennoreply = "0";
         //DB::enableQueryLog();
         $dataTiket=$dataTiket->get();
         //dd($dataTiket);
         //dd(DB::getQueryLog());
         foreach($dataTiket as $result){
             $ticketid = $result->id;
             $dateopened  = $result->date;
             $datefirstreply =$result->date;
 
             $datehour = substr($dateopened, 11, 2);
             $hours[$datehour]++;
 
             if (!$datefirstreply) {
                 $opennoreply++;
             } else {
                     $timetofirstreply = strtotime($datefirstreply) - strtotime($dateopened);
                     $timetofirstreply = round($timetofirstreply / (60 * 60), 2);
                     $avefirstresponse += $timetofirstreply;
                     $avefirstresponsecount++;
                     if ($timetofirstreply <= 1) {
                         $replytimes[1]++;
                     } else {
                         if (1 < $timetofirstreply && $timetofirstreply <= 4) {
                             $replytimes[2]++;
                         } else {
                             if (4 < $timetofirstreply && $timetofirstreply <= 8) {
                                 $replytimes[4]++;
                             } else {
                                 if (8 < $timetofirstreply && $timetofirstreply <= 16) {
                                     $replytimes[8]++;
                                 } else {
                                     if (16 < $timetofirstreply && $timetofirstreply <= 24) {
                                         $replytimes[16]++;
                                     } else {
                                         $replytimes[24]++;
                                     }
                                 }
                             }
                         }
                     }
                 }
             }
 
             $avefirstresponse = 0 < $avefirstresponsecount ? round($avefirstresponse / $avefirstresponsecount, 2) : "-";
            // dd( $replytimes);
             //$avereplieschartdata = array();
             $respone=[
                         []
                     ];
             //$avereplieschartdata["cols"][] = array("label" => AdminLang::trans("support.timeframe"), "type" => "string");
             //$avereplieschartdata["cols"][] = array("label" => AdminLang::trans("support.numberOfTickets"), "type" => "number");
             if (0 < $replytimes[1]) {
                 $respone[]=[ 
                                 'label' => '0-1 Hours',
                                 'data' => $replytimes[1]
                             ];
             }
             if (0 < $replytimes[2]) {
                 $respone[]=[ 
                                 'label' => '1-4 Hours',
                                 'data' => $replytimes[2]
                             ];
                         
             }
             if (0 < $replytimes[4]) {
                 $respone[]=[ 
                             'label' => '4-8 Hours',
                             'data' => $replytimes[4]
                         ];
             
             }
             if (0 < $replytimes[8]) {
                 $respone[]=[ 
                     'label' => '4-8 Hours',
                     'data' => $replytimes[8]
                 ];
             }
             if (0 < $replytimes[16]) {
                 $respone[]=[ 
                     'label' => '4-8 Hours',
                     'data' => $replytimes[16]
                 ];
             }
             if (0 < $replytimes[24]) {
                 $respone[]=[ 
                     'label' => '4-8 Hours',
                     'data' => $replytimes[24]
                 ];
             }
 
           /*  foreach ($hours as $hour => $count) {
                 $hourschartdata["rows"][] = array("c" => array(array("v" => $hour), array("v" => $count, "f" => $count)));
             }  */
             
             $hourschartdata=array();
             foreach($hours as $k=>$v){
                 $hourschartdata[]=[(int)$k,$v];
             }
             //dd($hourschartdata);
 
             $data=[
                         'pie' => $respone,
                         'line' => $hourschartdata
             ];
            // dd($respone);
             return json_encode($data);
 
     }



}