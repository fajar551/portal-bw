<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Cfg;
use App\Helpers\LogActivity;
use App\Helpers\Hooks;

class Message
{
	protected $type = "general";
    protected $templateName = "";
    protected $from = array();
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $subject = "";
    protected $body = "";
    protected $bodyPlainText = "";
    protected $attachments = array();
    protected $mergeData = array();
    const HEADER_MARKER = "<!-- message header end -->";
    const FOOTER_MARKER = "<!-- message footer start -->";

	public function __construct()
	{
		$this->setFromName(Cfg::get("CompanyName"));
        $this->setFromEmail(Cfg::get("Email"));
	}

	public static function createFromTemplate(\App\Models\Emailtemplate $template)
    {
        $message = new self();
        $message->setType($template->type);
        $message->setTemplateName($template->name);
        if ($template->fromname) {
            $message->setFromName($template->fromname);
        }
        if ($template->fromemail) {
            $message->setFromEmail($template->fromemail);
        }
        $message->setSubject($template->subject);
        if ($template->plaintext) {
            $message->setPlainText($template->message);
        } else {
            $message->setBodyAndPlainText($template->message);
        }
        if (is_array($template->copyTo)) {
            foreach ($template->copyTo as $copyto) {
                $message->addRecipient("cc", $copyto);
            }
        }
        if (is_array($template->blindCopyTo)) {
            foreach ($template->blindCopyTo as $bcc) {
                $message->addRecipient("bcc", $bcc);
            }
        }
        if (is_array($template->attachments)) {
            $storage = \Storage::disk('attachments');
            foreach ($template->attachments as $attachment) {
                $displayname = substr($attachment, 7);
                try {
                    $exists = $storage->exists($attachment);
                    if (!$exists) {
                        throw new \League\Flysystem\FileNotFoundException("File not found");
                    }
                    $message->addStringAttachment($displayname, $storage->path($attachment));
                } catch (\League\Flysystem\FileNotFoundException $e) {
                    $message = "Could not access file: " . $attachment;
                    LogActivity::Save("Email Sending Failed - " . $message . " (Subject: " . $template->subject . ")", "none");
                    throw new \App\Exceptions\Mail\InvalidTemplate("Could not access file: " . $attachment);
                }
            }
        }
        return $message;
    }
    public function setMergeData($mergeData)
    {
        $this->mergeData = $mergeData;
    }
    public function getMergeData()
    {
        return $this->mergeData;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function getType()
    {
        return $this->type;
    }
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
    }
    public function getTemplateName()
    {
        return $this->templateName;
    }
    public function addRecipient($kind, $email, $name = "")
    {
        if (in_array($kind, array("to", "cc", "bcc"))) {
            array_push($this->{$kind}, array($email, $name));
        }
        return $this;
    }
    public function clearRecipients($kind)
    {
        if (in_array($kind, array("to", "cc", "bcc"))) {
            $this->{$kind} = array();
        }
        return $this;
    }
    public function setFromName($name)
    {
        $this->from["name"] = $name;
    }
    public function getFromName()
    {
        return $this->from["name"];
    }
    public function setFromEmail($email)
    {
        $this->from["email"] = $email;
    }
    public function getFromEmail()
    {
        return $this->from["email"];
    }
    public function getRecipients($kind)
    {
        if (in_array($kind, array("to", "cc", "bcc"))) {
            return $this->{$kind};
        }
    }
    public function getFormattedRecipients($kind)
    {
        if (in_array($kind, array("to", "cc", "bcc"))) {
            $recipients = array();
            foreach ($this->{$kind} as $recipient) {
                if ($recipient[1]) {
                    $recipients[] = $recipient[1] . " <" . $recipient[0] . ">";
                } else {
                    $recipients[] = $recipient[0];
                }
            }
            return $recipients;
        } else {
            return "";
        }
    }
    public function setSubject($subject)
    {
        $this->subject = \App\Helpers\Sanitize::decode($subject);
    }
    public function getSubject()
    {
        return $this->subject;
    }
    public function setBodyAndPlainText($body)
    {
        $this->setBody($body)->setPlainText($body);
    }
    public function setBody($body)
    {
        if ($this->getType() == "admin") {
            $adminNotification = new \App\Helpers\AdminNotification();
            $body = $adminNotification->getPreparedHtml($this->getSubject(), $body);
        } else {
            $globalHeader = Cfg::get("EmailGlobalHeader");
            $globalFooter = Cfg::get("EmailGlobalFooter");
            $messageHeader = $globalHeader ? \App\Helpers\Sanitize::decode($globalHeader) . "\n" . self::HEADER_MARKER : "";
            $messageFooter = $globalFooter ? self::FOOTER_MARKER . "\n" .\App\Helpers\Sanitize::decode($globalFooter) : "";
            $body = $messageHeader . $body . $messageFooter;
        }
        $this->body = $body;
        return $this;
    }
    public function setBodyFromSmarty($body)
    {
        $this->body = $body;
    }
    public function getBody()
    {
        $body = $this->body;
        if (!$body) {
            return $body;
        }
        $patterns = array();
        $patterns[0] = '/{/';
        $patterns[1] = '/}/';
        $replacements = array();
        $replacements[0] = '{ ';
        $replacements[1] = ' }';
        if (strpos($body, "[EmailCSS]") !== false) {
            if ($this->getType() == "admin") {
                $cssStyling = \App\Helpers\AdminNotification::getCssStyling();
                $style = preg_replace($patterns, $replacements, $cssStyling);
                $body = str_replace("[EmailCSS]", $style, $body);
            } else {
                $cssStyling = Cfg::get("EmailCSS");
                $style = preg_replace($patterns, $replacements, $cssStyling);
                $body = str_replace("[EmailCSS]", $style, $body);
            }
        } else {
            $cssStyling = Cfg::get("EmailCSS");
            $style = preg_replace($patterns, $replacements, $cssStyling);
            $body = "<style>" . PHP_EOL .$style . PHP_EOL . "</style>" . PHP_EOL . $body;
        }
        return $body;
    }
    public function getBodyWithoutCSS()
    {
        return $this->body;
    }
    public function setPlainText($text)
    {
        $text = \App\Helpers\Sanitize::decode($text);
        $text = str_replace(array("\r\n</p>\r\n<p>\r\n", "\n</p>\n<p>\n"), "\n\n", $text);
        $text = str_replace(array("<br />\r\n", "<br />\n", "<br>\r\n", "<br>\n"), "\n", $text);
        $text = str_replace("<p>", "", $text);
        $text = str_replace("</p>", "\n\n", $text);
        $text = str_replace("<br>", "\n", $text);
        $text = str_replace("<br />", "\n", $text);
        $text = $this->replaceLinksWithUrl($text);
        $text = strip_tags($text);
        $this->bodyPlainText = trim($text);
        return $this;
    }
    protected function replaceLinksWithUrl($text)
    {
        return preg_replace("/<a.*?href=([\\\"])(.*?)\\1.*?<\\/a>/", "\$2", $text);
    }
    public function getPlainText()
    {
        return $this->bodyPlainText;
    }
    public function addStringAttachment($filename, $data)
    {
        $this->attachments[] = array("filename" => $filename, "data" => $data);
    }
    public function addFileAttachment($filename, $filepath)
    {
        $this->attachments[] = array("filename" => $filename, "filepath" => $filepath);
    }
    public function getAttachments()
    {
        return $this->attachments;
    }
    public function hasRecipients()
    {
        return 0 < count($this->to) + count($this->cc) + count($this->bcc);
    }
    public function saveToEmailLog($userId)
    {
        $emailData = array(
            "userid" => $userId, 
            "date" => \Carbon\Carbon::now(), 
            "to" => implode(", ", $this->getFormattedRecipients("to")), 
            "cc" => implode(", ", $this->getFormattedRecipients("cc")), 
            "bcc" => implode(", ", $this->getFormattedRecipients("bcc")), 
            "subject" => $this->getSubject(), 
            "message" => $this->getBody() ?: $this->getPlainText()
        );

        $compiled = $this->getCompiledEmail($emailData);
        if ($compiled) {
            $emailData["subject"] = $compiled["subject"];
            $emailData["message"] = $compiled["message"];
        }
        
        $results = Hooks::run_hook("EmailPreLog", $emailData);
        foreach ($results as $hookReturn) {
            if (!is_array($hookReturn)) {
                continue;
            }
            foreach ($hookReturn as $key => $value) {
                if ($key == "abortLogging" && $value === true) {
                    return false;
                }
                if (array_key_exists($key, $emailData)) {
                    $emailData[$key] = $value;
                }
            }
        }
        return \App\Models\Email::insert($emailData);
    }

    private function getCompiledEmail($maildata)
    {
        $mergeData = $this->getMergeData();
        $compiled = [];

        try {
            $subject = $maildata["subject"];
            $message = $maildata["message"];

            $compiled["subject"] = ViewHelper::renderSmarty($subject, $mergeData);
            $compiled["message"] = ViewHelper::renderSmarty($message, $mergeData);
        } catch(\Exception $e) {
            return false;
        }

        return $compiled;
    }

}
