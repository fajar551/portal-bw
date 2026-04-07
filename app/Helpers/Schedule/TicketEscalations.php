<?php

namespace App\Helpers\Schedule;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Helpers\LogActivity;
use Database;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Carbon as CarbonX;
use App\Helpers\Cfg;


class TicketEscalations
{
      public static function run(){
         $markup =new \App\Helpers\ViewMarkup();
         $ticketEscalationLastRun=Cfg::get('TicketEscalationLastRun');
         $lastRunTime= $ticketEscalationLastRun ? new CarbonX($ticketEscalationLastRun) : null;
         $thisRunTime = Carbon::now();

         $ticketescalations=\App\Models\Ticketescalation::all();
         foreach($ticketescalations->toArray() as $data ){
            $name = $data["name"];
            $departments = $data["departments"];
            $statusesRaw = $data["statuses"];
            $priorities = $data["priorities"];
            $timeelapsed = $data["timeelapsed"];
            $newdepartment = $data["newdepartment"];
            $newpriority = $data["newpriority"];
            $newstatus = $data["newstatus"];
            $flagto = $data["flagto"];
            $notify = $data["notify"];
            $addreply = $data["addreply"];
            $editor = $data["editor"];
            
            $tiket=\App\Models\Ticket::where('merged_ticket_id',0);

            if($departments) {
               $departments = explode(",", $departments);
               $tiket->whereIn('did',$departments);
            }
            $statuses = json_decode($statusesRaw, true);
            if(!$statuses){
               $tiket->whereIn('status',$statuses);
            }
            if($priorities) {
               $priorities = explode(",", $priorities);
               $tiket->whereIn('urgency',$priorities);
            }
            if ($timeelapsed) {
               $minTime = $lastRunTime ? $lastRunTime->copy()->subMinutes($timeelapsed)->format("Y-m-d H:i:s") : null;
               $maxTime = $thisRunTime->copy()->subMinutes($timeelapsed)->format("Y-m-d H:i:s");
               if ($minTime) {
                  $tiket->where('urgency','>',$minTime);
               }
               $tiket->where('lastreply','=<',$maxTime);
            }
            //dd($tiket->get());
            foreach($tiket->get() as $r){
               $ticketid       = $r->id;
               $tickettid      = $r->tid;
               $ticketsubject  = $r->title;
               $ticketuserid   = $r->userid;
               $ticketdeptid   = $r->did;
               $ticketpriority = $r->urgency;
               $ticketstatus   = $r->status;
               $ticketmsg      = $r->message;
               $ticketFlag     = $r->flag;
               $markupFormat  = $markup->determineMarkupEditor("ticket_msg", $r->editor);
               $ticketmsg     = $markup->transform($ticketmsg, $markupFormat);
               $updateqry = array();
               $changes = array();
               $ticketid=2;
             
               $update=\App\Models\Ticket::find($ticketid);

               if ($newdepartment && $newdepartment != $ticketdeptid) {
                  $update->did =$newdepartment;
                  $changes["Department"] = array("old" => \App\Helpers\Ticket::getDepartmentName($ticketdeptid), "new" => \App\Helpers\Ticket::getDepartmentName($newdepartment));
                  //\WHMCS\Notification\Events::trigger("Ticket", "dept_change", array("ticketid" => $ticketid, "department" => $newdepartment));
               }
               if ($newpriority && $newpriority != $ticketpriority) {
                  $update->urgency =$newpriority;
                  $changes["Priority"] = array("old" => $ticketpriority, "new" => $newpriority);
                  //\WHMCS\Notification\Events::trigger("Ticket", "priority_change", array("ticketid" => $ticketid, "priority" => $newpriority));
               }
               if ($newstatus && $newstatus != $ticketstatus) {
                  $update->status =$newstatus;
                  $changes["Status"] = array("old" => $ticketstatus, "new" => $newstatus);
                  //\WHMCS\Notification\Events::trigger("Ticket", "status_change", array("ticketid" => $ticketid, "status" => $newstatus));
               }
               if ($flagto && $flagto != $ticketFlag) {
                  $update->flag =$flag;
                  $changes["Assigned To"] = array("old" => $ticketFlag ? getAdminName($ticketFlag) : "Unassigned", "oldId" => $ticketFlag ?: 0, "new" => $flagto ? getAdminName($flagto) : "Unassigned", "newId" => $flagto ?: 0);
                  //\WHMCS\Notification\Events::trigger("Ticket", "assigned", array("ticketid" => $ticketid));
               }
                  $update->save();
                  
                  $changes["Who"] = "System";
                  \App\Helpers\Ticket::notifyTicketChanges($ticketid, $changes);
               if($notify){
                  if (!$newstatus) {
                     $newstatus = $ticketstatus;
                  }
                  \App\Helpers\Ticket::AddReply($ticketid, "", "", $addreply, "System", "", "", $newstatus, false, true, $editor == "markdown");
                  //\WHMCS\Notification\Events::trigger("Ticket", "reply_admin", array("ticketid" => $ticketid));
               }

               \App\Helpers\Ticket::addTicketLog($ticketid, "Escalation Rule \"" . $name . "\" applied");
            }
         }

         \App\Models\Configuration::where('setting','TicketEscalationLastRun')->update(["value" => $thisRunTime->format("Y-m-d H:i:s")]);
         return true;
      }

}