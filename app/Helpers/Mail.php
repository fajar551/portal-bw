<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here
use App\Helpers\ViewHelper;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as MailClass;
use Illuminate\Support\Facades\View;

// class Mail extends \Illuminate\Support\Facades\Mail
class Mail
{
	public $Subject = "";
	public $SubjectParsed = "";
	public $From = "";
	public $FromName = "";
	public $Sender = "";
	public $AltBody = "";
	public $AltBodyParsed = "";
	public $Body = "";
	public $BodyParsed = "";
	public $To = [];
	public $Cc = [];
	public $BCc = [];
    public $ErrorInfo = "";
	protected $mergeData = [];
	protected $mailClass = NULL;
	protected $message = NULL;
	protected $decodeAltBodyOnSend = true;
    protected static $validEncodings = array("8bit", "7bit", "binary", "base64", "quoted-printable");
    public function __construct($name = "", $email = "")
    {
        // parent::__construct(true);
		$this->mailClass = new MailClass();
        $this->setSenderNameAndEmail($name, $email);
    }
	public function setSenderNameAndEmail($name, $email)
    {
        if (!$name) {
            $name = Cfg::getValue("CompanyName");
        }
        if (!$email) {
            $email = Cfg::getValue("Email");
        }
		$this->From = $email;
        $this->FromName = \App\Helpers\Sanitize::decode($name);
        $this->Sender = $email;
        
        return $this;
    }
	public function send()
    {
        $this->Subject = \App\Helpers\Sanitize::decode($this->Subject);
        if ($this->decodeAltBodyOnSend) {
            $this->AltBody = \App\Helpers\Sanitize::decode($this->AltBody);
        }
        
        // return parent::send();
        try {
            MailClass::send([], [], function ($message) {
                foreach ($this->To as $key => $value) {
                    if ($this->filterEmail($value[0])) {
                        $message->to($value[0], $value[1] ?? null);
                    }
                }
                foreach ($this->Cc as $key => $value) {
                    if ($this->filterEmail($value[0])) {
                        $message->cc($value[0], $value[1] ?? null);
                    }
                }
                foreach ($this->BCc as $key => $value) {
                    if ($this->filterEmail($value[0])) {
                        $message->bcc($value[0], $value[1] ?? null);
                    }
                }
                $message->subject($this->SubjectParsed);
                $message->from($this->From, $this->FromName);
                $message->setBody($this->BodyParsed, 'text/html');
                foreach ($this->message->getAttachments() as $attachment) {
                    if (array_key_exists("data", $attachment)) {
                        $attachment["filename"] = preg_replace("|[\\\\/]+|", "-", $attachment["filename"]);
                        $message->attachData($attachment["data"], $attachment["filename"], [
                            // 'mime' => 'application/pdf',
                        ]);
                    } else {
                        $message->attach($attachment["filepath"], [
                            'as' => $attachment["filename"],
                            // 'mime' => 'application/pdf',
                        ]);
                    }
                }
            });
            \Log::info("app\Helpers\Mail MailClass::send success: ".$this->SubjectParsed);
        } catch (\Exception $e) {
            \Log::debug("app\Helpers\Mail MailClass::send error: ".$e->getMessage());
            $this->ErrorInfo = $e->getMessage();
        }
    }
    public function filterEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function addRecipient($kind, $email, $name = "")
    {
        if (in_array($kind, array("To", "Cc", "BCc"))) {
            array_push($this->{$kind}, array($email, $name));
            // $this->{$kind}[$email] = $name;
        }
        return $this;
    }
    public function AddAddress($email, $name = "")
    {
        $this->addRecipient("To", $email, $name);
    }
    public function AddCC($email, $name = "")
    {
        $this->addRecipient("Cc", $email, $name);
    }
    public function AddBCC($email, $name = "")
    {
        $this->addRecipient("BCc", $email, $name);
    }
	public function sendMessage(\App\Helpers\Message $message, Array $merge_data = [])
    {
        foreach ($message->getRecipients("to") as $to) {
            $this->AddAddress($to[0], $to[1]);
        }
        foreach ($message->getRecipients("cc") as $to) {
            if (Cfg::getValue("MailType") == "mail") {
                $this->AddAddress($to[0], $to[1]);
            } else {
                $this->AddCC($to[0], $to[1]);
            }
        }
        foreach ($message->getRecipients("bcc") as $to) {
            $this->AddBCC($to[0], $to[1]);
        }
        if (Cfg::getValue("BCCMessages")) {
            $bcc = Cfg::getValue("BCCMessages");
            $bcc = explode(",", $bcc);
            foreach ($bcc as $value) {
                if (trim($value)) {
                    $this->AddBCC($value);
                }
            }
        }
        $this->setSenderNameAndEmail($message->getFromName(), $message->getFromEmail());
        $this->Subject = $message->getSubject();
        $body = $message->getBody();
        $plainText = $message->getPlainText();
        if ($body) {
            $this->Body = $body;
            $this->AltBody = $plainText;
            if (!empty($this->Body) && empty($this->AltBody)) {
                $this->AltBody = " ";
            }
        } else {
            $this->Body = $plainText;
        }

        $this->SubjectParsed = ViewHelper::renderSmarty($this->Subject, $merge_data);
        $this->BodyParsed = ViewHelper::renderSmarty($this->Body, $merge_data);
        $this->AltBodyParsed = ViewHelper::renderSmarty($this->AltBody, $merge_data);
        $this->mergeData = $merge_data;
        $this->message = $message;
		return $this->send();
    }
}
