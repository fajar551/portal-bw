<?php

namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use App\Http\Controllers\HomeController;
use App\Module\Registrar;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Cfg;

class CloseInactiveTickets
{
   public static function run(){
      if(!Cfg::get('CloseInactiveTickets')){

      }
      $departmentresponders = array();
      $dep=\App\Models\Ticketdepartment::select('id','noautoresponder')->get();
      foreach($dep as $data){
         $id = $data->id;
         $noautoresponder = $data->noautoresponder;
         $departmentresponders[$id] = $noautoresponder;
      }
      $closetitles = array();
      $status=\App\Models\Ticketstatus::select('title')->where('autoclose',1)->get();
      foreach($status as $r){
         $closetitles[] = $r->title;
      }
      if($closetitles){
         $ticketCloseCutoff = Carbon::now()->subHours(Cfg::get("CloseInactiveTickets"));
         $ticketIdsToClose=\App\Models\Ticket::whereIn('status', $closetitles)->where('lastreply','<=',$ticketCloseCutoff)->pluck('id');
         foreach ($ticketIdsToClose as $ticketId) {
            $ticket =\App\Models\Ticket::find($ticketId);
            if (!$ticket) {
               continue;
            }
            if (!in_array($ticket->status, $closetitles)) {
               continue;
            }
            if ($ticket->lastReply->gt($ticketCloseCutoff)) {
               continue;
            }

            \App\Helpers\Ticket::CloseTicket($ticket->id);
            
            if(!$departmentresponders[$ticket->departmentId] && ! Cfg::get("TicketFeedback")){
               \App\Helpers\Functions::sendMessage("Support Ticket Auto Close Notification", $ticket->id);
            }
            //$this->addSuccess(array("ticket", $ticket->id, ""));
         }
      }
     /*  $this->output("closed")->write(count($this->getSuccesses()));
      $this->output("action.detail")->write(json_encode($this->getDetail()));
      return $this; */
   }

}