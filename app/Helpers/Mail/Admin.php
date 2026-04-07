<?php
namespace App\Helpers\Mail;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\Cfg;


class Admin extends \App\Helpers\Emailer
{
    protected $isNonClientEmail = true;
    public function __construct($message, $entityId, $extraParams = NULL)
    {
        parent::__construct($message, $entityId, $extraParams);
        $this->message->setFromName(\WHMCS\Config\Setting::getValue("SystemEmailsFromName"));
        $this->message->setFromEmail(\WHMCS\Config\Setting::getValue("SystemEmailsFromEmail"));
    }

}
