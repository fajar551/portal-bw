<?php
namespace App\Helpers;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Database;
class HelperTickets
{


	public function __construct()
	{


	}

    public static function gettimebetweendates($lastreply, $from = "now"){
        $datetime = strtotime($from);
        $date2 = strtotime($lastreply);
        $holdtotsec = $datetime - $date2;
        $holdtotmin = ($datetime - $date2) / 60;
        $holdtothr = ($datetime - $date2) / 3600;
        $holdtotday = intval(($datetime - $date2) / 86400);
        $holdhr = intval($holdtothr - $holdtotday * 24);
        $holdmr = intval($holdtotmin - ($holdhr * 60 + $holdtotday * 1440));
        $holdsr = intval($holdtotsec - ($holdhr * 3600 + $holdmr * 60 + 86400 * $holdtotday));
        return array("days" => $holdtotday, "hours" => $holdhr, "minutes" => $holdmr, "seconds" => $holdsr);
    }


	public static function getLastReplyTime($lastreply){
        $timeparts = HelperTickets::gettimebetweendates($lastreply);
        $str = "";
        if (0 < $timeparts["days"]) {
            $str .= $timeparts["days"] . " Days ";
        }
        $str .= $timeparts["hours"] . " Hours ";
        $str .= $timeparts["minutes"] . " Minutes ";
        $str .= $timeparts["seconds"] . " Seconds ";
        $str .= "Ago";
        return $str;
    }

    public static function getDeptName($id){
        $id=(int)$id;
        $data=\App\Models\Ticketdepartment::find($id);
        return $data->name;
    }

    public static function getReplayAndNote($id){
        $prefix=Database::prefix();
        $markup=new \App\Helpers\ViewMarkup();
        $smartyvalues=array();
        $replies=array();

        // Tambahkan attachments_removed ke dalam select
        $tiket=DB::table($prefix.'tickets')
        ->select('*', 'attachments_removed')
        ->find($id);

        //$tiket=\App\Models\Ticket::find($id);
        $tiket=DB::table($prefix.'tickets')->find($id);
        $userid     = $tiket->userid;
        $contactid  = $tiket->contactid;
        $name       = $tiket->name;
        $email      = $tiket->email;
        $date       = $tiket->date;
        $datetiket  = $tiket->date;
        $title      = $tiket->title;
        $message    = $tiket->message;
        $admin      = $tiket->admin;
        $attachment = $tiket->attachment;
        $attachmentsRemoved = (bool) (int) $tiket->attachments_removed;
        $friendlydate =substr($date, 0, 10) == date("Y-m-d") ? "" :(substr($date, 0, 4) == date("Y")?\App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date):\App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
        $friendlytime = date("H:i", strtotime($date));
        $date =(new \App\Helpers\Functions())->fromMySQLDate($date, true);
        $markupFormat   = $markup->determineMarkupEditor("ticket_msg",$tiket->editor);
        $message        = $markup->transform($message, $markupFormat);
        if ($userid) {
            $name = \App\Helpers\ClientHelper::outputClientLink($userid,"", "", "", "", true);
        }
        $attachmentType = "ticket";
        if ($attachmentsRemoved) {
            $attachmentType = "removed";
        }
        //$attachment='fc10fcd0-9264-4790-bdd3-2f8bba1f4b75.jpg|2a769930-223f-4468-88cd-7c422b7baabc.jpg';
        $attachments=HelperTickets::getTicketAttachmentsInfo($id, $attachment, $attachmentType);
        //dd($attachments);
        //dd($tiket->date);
        $replies[$datetiket][]=[
                                    "id"            => 0,
                                    "admin"         => $admin,
                                    "userid"        => $userid,
                                    "contactid"     => $contactid,
                                    "clientname"    => $name,
                                    "clientemail"   => $email,
                                    "date"          => $date,
                                    "friendlydate"  => $friendlydate,
                                    "friendlytime"  => $friendlytime,
                                    "message"       => $message,
                                    "attachments"   => $attachments,
                                    "attachments_removed" => $attachmentsRemoved,
                                    "numattachments"=> count($attachments),
                                    'note'          => false
                                 ];

        //dd($replies);

        //$data=\App\Models\Ticketreply::where('tid',$id)->orderBy('date')->get();
        // $data=DB::table($prefix.'ticketreplies')->where('tid',$id)->orderBy('date')->get();
        // Untuk ticketreplies, juga perlu menambahkan attachments_removed
        $data=DB::table($prefix.'ticketreplies')
        ->select('*', 'attachments_removed')
        ->where('tid',$id)
        ->orderBy('date')
        ->get();
        //dd($data);
        $lastReplyId = 0;
        foreach($data as $r){
            $replyid    = $r->id;
            $userid     = $r->userid;
            $contactid  = $r->contactid;
            $name       = $r->name;
            $email      = $r->email;
            $date       = $r->date;
            $message    = $r->message;
            $attachment = $r->attachment;
            $attachmentsRemoved = (bool) (int) $r->attachments_removed;
            $admin      = $r->admin;
            $rating     = $r->rating;
            $friendlydate =substr($date, 0, 10) == date("Y-m-d") ? "" :(substr($date, 0, 4) == date("Y")?\App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date):\App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
            $friendlytime = date("H:i", strtotime($date));

            $date         =(new \App\Helpers\Functions())->fromMySQLDate($date, true);
            $markupFormat = $markup->determineMarkupEditor("ticket_reply", $tiket->editor);
            $message      = $markup->transform($message, $markupFormat);
            if ($userid) {
                $name = \App\Helpers\ClientHelper::outputClientLink($userid,"", "", "", "", true);
            }
            $attachmentType = "reply";
            if ($attachmentsRemoved) {
                $attachmentType = "removed";
            }

            $attachments=HelperTickets::getTicketAttachmentsInfo($id, $attachment, $replyid);
            $ratingstars = array();
            if ($admin && $rating) {
                for ($i = 1; $i <= 5; $i++) {
                    $ratingstars[]=$i <= $rating ?'rating_pos.png':'rating_neg.png';
                }
            }
            $replies[$r->date][] = [
                                    "id"        => $replyid.'',
                                    "admin"     => $admin,
                                    "userid"    => $userid,
                                    "contactid" => $contactid,
                                    "clientname"    => $name,
                                    "clientemail"   => $email,
                                    "date"          => $date,
                                    "friendlydate"  => $friendlydate,
                                    "friendlytime"  => $friendlytime,
                                    "message"       => $message,
                                    "attachments"   => $attachments,
                                    "attachments_removed" => $attachmentsRemoved,
                                    "numattachments"      => count($attachments),
                                    "rating"        => $ratingstars,
                                    'note'          => false
                                    ];

        }
        //dd($replies);
        $noteCollection=\App\Models\Ticketnote::where('ticketid',$id)->orderBy('date')->get();
        $notes = array();
        //dd( $noteCollection);
        foreach($noteCollection as $note){
            $date = $note->date;
            $friendlyDate = substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ?\App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F") : \App\Helpers\Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
            $friendlyTime = date("H:i", strtotime($date));
            $date = (new \App\Helpers\Functions())->fromMySQLDate($date, true);
            $attachmentType = "note";
            $attachmentsRemoved = false;
            if ($note->attachments_removed) {
                $attachmentType = "removed";
                $attachmentsRemoved = true;
            }
            $markupFormat = $markup->determineMarkupEditor("ticket_reply", $tiket->editor);
            $message      = $markup->transform($note->message, $markupFormat);
            //TODO
            //$mentions = WHMCS\Mentions\Mentions::getMentionReplacements($message);
            /* if (0 < count($mentions)) {
                $message = str_replace($mentions["find"], $mentions["replace"], $message);
            } */
            $attachments=HelperTickets::getTicketAttachmentsInfo($id, $note->attachments,'note');
          //print_r($note->attachments);
            $replies[$note->date][]=[
                                        "id" => $note->id,
                                        "admin" => $note->admin,
                                        "userid" => 0,
                                        "contactid" => 0,
                                        "clientname" => "",
                                        "clientemail" => "",
                                        "date" => $date,
                                        "friendlydate" => $friendlyDate,
                                        "friendlytime" => $friendlyTime,
                                        "message" => $message,
                                        "attachments" => $attachments,
                                        "attachments_removed" => $attachmentsRemoved,
                                        "numattachments" => count($attachments),
                                        "rating" => array(),
                                        "note" => true
                                    ];
            $notes[] = [
                        "id" => $note->id,
                        "admin" => $note->admin,
                        "date" => $date,
                        "message" => $message
                    ];

        }
        $smartyvalues["lastReplyId"] = $lastReplyId;
        $smartyvalues["notes"] = $notes;
        $smartyvalues["numnotes"] = count($notes);
        //dd($replies);

        if(\App\Helpers\Cfg::getValue('SupportTicketOrder') == 'DESC'){
            krsort($replies);
        }else{
            ksort($replies);
        }

        $repliesForTemplate = array();
        foreach ($replies as $replyGroup) {
            foreach ($replyGroup as $reply) {
                $repliesForTemplate[] = $reply;
            }
        }
        global  $CONFIG;
        $smartyvalues["replies"] = $repliesForTemplate;
        $smartyvalues["repliescount"] = count($repliesForTemplate);
        $smartyvalues["thumbnails"] = $CONFIG["AttachmentThumbnails"] ? true : false;

        //dd($smartyvalues);
        return $smartyvalues;

    }

    //fc10fcd0-9264-4790-bdd3-2f8bba1f4b75.jpg|2a769930-223f-4468-88cd-7c422b7baabc.jpg
    public static function getTicketAttachmentsInfo($ticketId, $attachment, $type = "ticket", $relatedId = 0){
        $attachments = array();
        if ($attachment) {
            $attachment = explode("|", $attachment);
            //dd( $attachment);
            foreach ($attachment as $num => $filename) {

                $file = substr($filename, 7);
                switch ($type) {
                    case "note":

                        $attachments[] = [
                                            "filename"  => url('/attachments/'.$filename),
                                            "isImage"   => HelperTickets::isAttachmentAnImage($filename),
                                            "removed"   => false,
                                            "dllink"    => "todo ya"
                                        ];
                        break;
                    case "reply":
                        $attachments[] = [
                                            "filename" => url('/attachments/'.$file),
                                            "isImage" => HelperTickets::isAttachmentAnImage($filename),
                                            "removed" => false,
                                            "dllink" => "todo ya"
                                        ];
                        break;
                    case "removed":
                        $attachments[] =[
                                            "filename" => url('/attachments/'.$filename),
                                            "isImage" => false,
                                            "removed" => true,
                                            "dllink" => "",
                                            "deletelink" => ""
                                        ];
                        break;
                    default:
                        $attachments[] = [
                                            "filename" => url('/attachments/'.$filename),
                                            "isImage" => HelperTickets::isAttachmentAnImage($filename),
                                            "removed" => false,
                                            "dllink" => "todo ya"
                                            ];
                }

            }


        }

        return $attachments;
    }

    public static function isAttachmentAnImage($file)
    {
        if (!$file) {
            return false;
        }
        try {
            return (bool) getimagesizefromstring(Storage::disk('attachments')->get($file));
        } catch (\Exception $e) {
            return false;
        }
    }

    }
