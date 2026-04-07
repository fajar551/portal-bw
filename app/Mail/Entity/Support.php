<?php

namespace App\Mail\Entity;

use DB;
use App\Helpers\Cfg;

class Support extends \App\Helpers\Emailer
{
    protected function getEntitySpecificMergeData($ticketId, $extra)
    {
        if (substr($this->message->getTemplateName(), strlen("Bounce Message") * -1) == "Bounce Message" && (isset($extra["clientTicket"]) && $extra["clientTicket"] == false || !isset($extra["clientTicket"]))) {
            list($name, $email) = $extra;
            $this->message->addRecipient("to", $email, $name);
            $this->isNonClientEmail = true;
            $email_merge_fields["client_name"] = $name;
            $email_merge_fields["client_first_name"] = $name;
            $email_merge_fields["client_last_name"] = "";
            $email_merge_fields["client_email"] = $email;
        } else {
            $result = \App\Models\Ticket::find($ticketId);
            $data = $result->toArray();
            $id = $data["id"];
            if (!$id) {
                throw new \Exception("Invalid ticket id provided");
            }
            $deptid = $data["did"];
            $tid = $data["tid"];
            $ticketcc = $data["cc"];
            $c = $data["c"];
            $userid = $data["userid"];
            $contactid = $data["contactid"];
            $name = $data["name"];
            $email = $data["email"];
            $date = $data["date"];
            $title = $data["title"];
            $tmessage = $data["message"];
            $status = $data["status"];
            $urgency = $data["urgency"];
            $attachment = $data["attachment"];
            $editor = $data["editor"];
            if ($ticketcc) {
                $ticketcc = explode(",", $ticketcc);
                foreach ($ticketcc as $ccaddress) {
                    $this->message->addRecipient("cc", $ccaddress);
                }
            }
            if ($userid) {
                $this->setRecipient($userid, $contactid);
            } else {
                if ($sessionLanguage = \Session::get("Language")) {
                    // TODO: swapLang($sessionLanguage);
                }
            }
            $urgency = \Lang::get("client.supportticketsticketurgency" . strtolower($urgency));
            $status = \App\Helpers\Ticket::getStatusColour($status);
            $result = \App\Models\Ticketdepartment::find($deptid);
            $data = $result->toArray();
            $this->message->setFromName(Cfg::getValue("CompanyName") . " " . $data["name"]);
            $this->message->setFromEmail($data["email"]);
            $departmentname = $data["name"];
            $contentType = "ticket_msg";
            $replyid = 0;
            if ($extra && is_int($extra)) {
                $result = \App\Models\Ticketreply::where(array("id" => $extra));
                $data = $result;
                $replyid = $data->value("id");
                $tmessage = $data->value("message");
                $attachment = $data->value("attachment");
                $editor = $data->value("editor");
                $contentType = "ticket_reply";
            }
            $markup = new \App\Helpers\ViewMarkup();
            $markupFormat = $markup->determineMarkupEditor($contentType, $editor);
            $includeAttachments = in_array($this->message->getTemplateName(), array("Support Ticket Opened by Admin", "Support Ticket Reply"));
            if ($includeAttachments && $attachment) {
                $storage = \Storage::disk('attachments');
                $attachment = explode("|", $attachment);
                foreach ($attachment as $file) {
                    $this->message->addStringAttachment(substr($file, 7), $storage->read($file));
                    // $this->message->addStringAttachment(substr($file, 7), $storage->path($file));
                }
            }
            $date = (new \App\Helpers\Client())->fromMySQLDate($date, 0, 1);
            if ($this->message->getTemplateName() != "Support Ticket Feedback Request") {
                $this->message->setSubject("[Ticket ID: {\$ticket_id}] {\$ticket_subject}");
            }
            $tmessage = $markup->transform($tmessage, $markupFormat, true);
            $kbarticles = \App\Helpers\Ticket::getKBAutoSuggestions($tmessage);
            $kb_auto_suggestions = "";
            $sysurl = config('app.url');
            foreach ($kbarticles as $kbarticle) {
                $kb_auto_suggestions .= "<a href=\"" . $sysurl . "knowledgebase.php?action=displayarticle&id=" . $kbarticle["id"] . "\" target=\"_blank\">" . $kbarticle["title"] . "</a> - " . $kbarticle["article"] . "...<br />\n";
            }
            $ticket_url = route('pages.support.mytickets.ticketdetails', ['tid' => $tid, 'c' => $c]);
            $email_merge_fields = array();
            $email_merge_fields["ticket_id"] = $tid;
            $email_merge_fields["ticket_reply_id"] = $replyid;
            $email_merge_fields["ticket_department"] = $departmentname;
            $email_merge_fields["ticket_date_opened"] = $date;
            $email_merge_fields["ticket_subject"] = $title;
            $email_merge_fields["ticket_message"] = $tmessage;
            $email_merge_fields["ticket_status"] = $status;
            $email_merge_fields["ticket_priority"] = $urgency;
            $email_merge_fields["ticket_url"] = $ticket_url;
            $email_merge_fields["ticket_link"] = "<a href=\"" . $ticket_url . "\">" . $ticket_url . "</a>";
            $email_merge_fields["ticket_auto_close_time"] = Cfg::getValue("CloseInactiveTickets");
            $email_merge_fields["ticket_kb_auto_suggestions"] = $kb_auto_suggestions;
            if ($userid == "0") {
                $this->isNonClientEmail = true;
                $this->message->addRecipient("to", $email, $name);
                $email_merge_fields["client_name"] = $name;
                $email_merge_fields["client_first_name"] = $name;
                $email_merge_fields["client_last_name"] = "";
                $email_merge_fields["client_email"] = $email;
            }
        }
        $this->massAssign($email_merge_fields);
    }
}

?>
