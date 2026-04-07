<?php
namespace App\Helpers;

use DB, Auth;

// Import Model Class here

// Import Package Class here
use App\Helpers\Message;
use App\Helpers\Hooks;
use App\Helpers\Cfg;
use App\Helpers\Application;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Emailer
{
	protected $message = NULL;
    protected $entityId = NULL;
    protected $extraParams = NULL;
    protected $isNonClientEmail = false;
    protected $recipientUserId = NULL;
    protected $recipientContactId = NULL;
    protected $mergeData = array();
    protected $emailTemplateNamesToNotLog = array("Automated Password Reset", "Client Email Address Verification", "Password Reset Validation");
    const ENTITY_MAP = [
        "admin" => "Admin", 
        "affiliate" => "Affiliate", 
        "domain" => "Domain", 
        "general" => "General", 
        "invoice" => "Invoice", 
        "notification" => "Notification", 
        "product" => "Product", 
        "support" => "Support"
    ];

    const EMAIL_TYPE_ADMIN = "admin";
    const EMAIL_TYPE_AFFILIATE = "affiliate";
    const EMAIL_TYPE_DOMAIN = "domain";
    const EMAIL_TYPE_GENERAL = "general";
    const EMAIL_TYPE_INVOICE = "invoice";
    const EMAIL_TYPE_NOTIFICATION = "notification";
    const EMAIL_TYPE_PRODUCT = "product";
    const EMAIL_TYPE_SUPPORT = "support";
    const CLIENT_EMAILS = [
        "admin", 
        "affiliate", 
        "domain", 
        "general", 
        "invoice", 
        "notification", 
        "product", 
        "support"
    ];

	public function __construct(Message $message, $entityId, $extraParams = NULL)
	{
		$this->message = $message;
        $this->entityId = $entityId;
        $this->extraParams = $extraParams;
	}
	public static function factory(Message $message, $entityId, $extraParams = NULL)
    {
        if (!$message->getType()) {
            throw new \Exception("A message type is required");
        }
        $entityName = array_key_exists($message->getType(), static::ENTITY_MAP) ? static::ENTITY_MAP[$message->getType()] : ucfirst($message->getType());
        $entityClass = "App\\Mail\\Entity\\" . $entityName;
        return new $entityClass($message, $entityId, $extraParams);
    }
    public static function factoryByTemplate($template, $entityId = 0, $extraParams = NULL)
    {
        if (!$template instanceof \App\Models\Emailtemplate) {
            $template = self::getTemplate($template, $entityId);
        }
        if (!$template instanceof \App\Models\Emailtemplate) {
            throw new \App\Exceptions\Mail\InvalidTemplate("Email Template Not Found");
        }
        if ($template->disabled) {
            throw new \App\Exceptions\Mail\TemplateDisabled("Email Template Disabled");
        }
        $message = Message::createFromTemplate($template);
        $entityName = array_key_exists($message->getType(), static::ENTITY_MAP) ? static::ENTITY_MAP[$message->getType()] : ucfirst($message->getType());
        $entityClass = "App\\Mail\\Entity\\" . $entityName;
        return new $entityClass($message, $entityId, $extraParams);
    }
    public static function getTemplate($templateName, $entityId = 0)
    {
        if ($templateName == "defaultnewacc") {
            $templateId = \App\Models\Product::select("tblproducts.welcomeemail")->where("tblhosting.id", $entityId)->join("tblhosting", "tblhosting.packageid","=","tblproducts.id")->value("tblproducts.welcomeemail");
            return \App\Models\Emailtemplate::find($templateId);
        }
        return \App\Models\Emailtemplate::where("name", "=", $templateName)->where("language", "=", "")->orWhere("language", "=", null)->first();
    }
    protected function getExtra($key)
    {
        if (is_array($this->extraParams) && array_key_exists($key, $this->extraParams)) {
            return $this->extraParams[$key];
        }
        return null;
    }
    protected function getClientMergeData()
    {
        $email_merge_fields = array();
        $userid = $this->recipientUserId;
        $contactid = 0;
        if (in_array($this->message->getTemplateName(), array("Password Reset Validation", "Password Reset Confirmation", "Automated Password Reset")) && $this->getExtra("contactid")) {
            $contactid = $this->getExtra("contactid");
        }
        try {
            if ($contactid) {
                $contact = \App\Models\Contact::with("client")->where("userid", $userid)->where("id", $contactid)->firstOrFail();
                $client = $contact->client;
            } else {
                $client = $contact = \App\Models\Client::findOrFail($userid);
            }
        } catch (\Exception $e) {
            if ($contactid) {
                throw new \Exception("Invalid contact id provided");
            }
            throw new \Exception("Invalid user id provided");
        }
        $firstname = $contact->firstname;
        $email = $contact->email;
        $lastname = $contact->lastname;
        $companyname = $contact->companyname;
        $address1 = $contact->address1;
        $address2 = $contact->address2;
        $city = $contact->city;
        $state = $contact->state;
        $postcode = $contact->postcode;
        $country = $contact->country;
        $phonenumber = $contact->phonenumber;
        $taxId = $contact->tax_id;
        $language = $client->language;
        $credit = $client->credit;
        $status = $client->status;
        $clgroupid = $client->groupid;
        $clgroupname = (string) $client->groupname;
        $gatewayid = $client->paymentGatewayToken;
        $datecreated = (new \App\Helpers\Functions())->fromMySQLDate($client->datecreated, 0, 1);
        $password = "**********";
        $cardtype = $this->getExtra("card_type");
        $cardnum = $this->getExtra("card_last_four");
        $cardexp = $this->getExtra("card_expiry");
        $cardDescription = $this->getExtra("card_description");
        // TODO: if (is_null($cardtype)) {
        // if (is_null($cardtype)) {
        //     $payMethod = null;
        //     if ($this->getExtra("payMethod")) {
        //         $payMethodExtra = $this->getExtra("payMethod");
        //         if (is_numeric($payMethodExtra)) {
        //             $payMethod = \App\Models\Paymethod::find($payMethodExtra);
        //         } else {
        //             if (is_object($payMethodExtra) && $payMethodExtra instanceof \App\Models\Paymethod) {
        //                 $payMethod = $payMethodExtra;
        //             }
        //         }
        //         unset($payMethodExtra);
        //         $cardDetails = getPayMethodCardDetails($payMethod);
        //     } else {
        //         $cardDetails = getClientDefaultCardDetails($userid);
        //     }
        //     $cardtype = $cardDetails["cardtype"];
        //     $cardnum = $cardDetails["cardlastfour"];
        //     $cardexp = $cardDetails["expdate"];
        //     $cardDescription = $cardDetails["card_description"];
        //     $gatewayid = $cardDetails["gatewayid"];
        //     unset($cardDetails);
        // }
        $currency = \App\Helpers\Format::getCurrency($userid);
        $totalInvoices = \App\Models\Invoice::selectRaw("SUM(total) as total")->where("userid", $userid)->where("status", "Unpaid")->value("total");
        $unpaidInvoiceIds = DB::table("tblinvoices")->where("status", "Unpaid")->where("userid", $userid)->pluck("id");
        $paidBalance = 0;
        if ($unpaidInvoiceIds) {
            $paidBalance = \App\Models\Account::selectRaw("SUM(amountin-amountout) as sum")->whereIn("invoiceid", $unpaidInvoiceIds->toArray())->value("sum");
        }
        $balance = floatval($totalInvoices) - floatval($paidBalance);
        $email_merge_fields["client_due_invoices_balance"] = \App\Helpers\Format::formatCurrency($balance);
        if ($this->message->getTemplateName() == "Automated Password Reset") {
            $password = \App\Helpers\Functions::generateFriendlyPassword();
            $hasher = new \App\Helpers\Password();
            $passwordhash = $hasher->hash($password);
            $contact->passwordHash = $passwordhash;
            $contact->save();
            Hooks::run_hook("ClientChangePassword", array("userid" => $userid, "password" => $password));
        }
        $fullName = trim($firstname . " " . $lastname);
        if ($companyname) {
            $fullName .= " (" . $companyname . ")";
        }
        $email = trim($email);
        if (!$email) {
            throw new \Exception("Email address not set for client");
        }
        $this->message->addRecipient("to", $email, $fullName);
        $email_merge_fields["client_id"] = $userid;
        $email_merge_fields["client_name"] = $fullName;
        $email_merge_fields["client_first_name"] = $firstname;
        $email_merge_fields["client_last_name"] = $lastname;
        $email_merge_fields["client_company_name"] = $companyname;
        $email_merge_fields["client_email"] = $email;
        $email_merge_fields["client_address1"] = $address1;
        $email_merge_fields["client_address2"] = $address2;
        $email_merge_fields["client_city"] = $city;
        $email_merge_fields["client_state"] = $state;
        $email_merge_fields["client_postcode"] = $postcode;
        $email_merge_fields["client_country"] = $country;
        $email_merge_fields["client_phonenumber"] = $phonenumber;
        $email_merge_fields["client_tax_id"] = $taxId;
        $email_merge_fields["client_password"] = $password;
        $email_merge_fields["client_signup_date"] = $datecreated;
        $email_merge_fields["client_credit"] = \App\Helpers\Format::formatCurrency($credit);
        $email_merge_fields["client_cc_description"] = (string) $cardDescription;
        $email_merge_fields["client_cc_type"] = (string) $cardtype;
        $email_merge_fields["client_cc_number"] = (string) $cardnum;
        $email_merge_fields["client_cc_expiry"] = (string) $cardexp;
        $email_merge_fields["client_language"] = $language;
        $email_merge_fields["client_status"] = $status;
        $email_merge_fields["client_group_id"] = $clgroupid;
        $email_merge_fields["client_group_name"] = $clgroupname;
        $email_merge_fields["client_gateway_id"] = $gatewayid;
        // TODO: $subscriptionController = new \WHMCS\Marketing\EmailSubscription();
        // $email_merge_fields["email_marketing_optin_url"] = $subscriptionController->generateOptInUrl($userid, $email);
        // $email_merge_fields["email_marketing_optout_url"] = $subscriptionController->generateOptOutUrl($userid, $email);
        $email_merge_fields["email_marketing_optin_url"] = "";
        $email_merge_fields["email_marketing_optout_url"] = "";
        $email_merge_fields["unsubscribe_url"] = $email_merge_fields["email_marketing_optout_url"];
        $customfields = \App\Helpers\Customfield::getCustomFields("client", "", $userid, true, "");
        $email_merge_fields["client_custom_fields"] = array();
        foreach ($customfields as $customfield) {
            $customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield["name"]));
            $email_merge_fields["client_custom_field_" . $customfieldname] = $customfield["value"];
            $email_merge_fields["client_custom_fields"][] = $customfield["value"];
            $email_merge_fields["client_custom_fields_by_name"][] = array("name" => $customfield["name"], "value" => $customfield["value"]);
        }
        $this->massAssign($email_merge_fields);
    }
    protected function getGenericMergeData()
    {
        $sysurl = config('app.url');
        $email_merge_fields = array();
        $email_merge_fields["company_name"] = Cfg::getValue("CompanyName");
        $email_merge_fields["companyname"] = Cfg::getValue("CompanyName");
        $email_merge_fields["company_domain"] = Cfg::getValue("Domain");
        $email_merge_fields["company_logo_url"] = \App\Helpers\Application::getLogoUrlForEmailTemplate();
        $email_merge_fields["company_tax_code"] = Cfg::getValue("TaxCode");
        $email_merge_fields["whmcs_url"] = $sysurl."/";
        $email_merge_fields["whmcs_link"] = "<a href=\"" . $sysurl . "\">" . $sysurl . "</a>";
        $email_merge_fields["signature"] = nl2br(\App\Helpers\Sanitize::decode(Cfg::getValue("Signature")));
        $email_merge_fields["date"] = date("l, jS F Y");
        $email_merge_fields["time"] = date("g:ia");
        $email_merge_fields["charset"] = Cfg::getValue("Charset");
        $this->massAssign($email_merge_fields);
    }
    protected function allowCc()
    {
        $doNotCcList = array("Password Reset Validation", "Password Reset Confirmation", "Automated Password Reset", "Client Email Address Verification");
        return !in_array($this->message->getTemplateName(), $doNotCcList);
    }
    protected function prepare()
    {
        // $originalLanguage = \Lang::self();
        $this->getEntitySpecificMergeData($this->entityId, $this->extraParams);
        if (!$this->isNonClientEmail) {
            $this->getClientMergeData();
        }
        // swapLang($originalLanguage);
        if (is_array($this->extraParams)) {
            $this->massAssign($this->extraParams);
        }
        $this->getGenericMergeData();
        $language = null;
        if (Application::isClientAreaRequest() && \Session::get("Language")) {
            $language = \Session::get("Language");
        } else {
            if (isset($this->mergeData["client_language"]) && $this->mergeData["client_language"]) {
                $language = $this->mergeData["client_language"];
            }
        }
        if (empty($language)) {
            $language = Cfg::getValue("Language");
        }
        $localizedTemplate = \App\Models\Emailtemplate::where("name", "=", $this->message->getTemplateName())->where("language", "=", $language)->first();
        if (isset($localizedTemplate->subject) && substr($this->message->getSubject(), 0, 10) != "[Ticket ID") {
            $this->message->setSubject($localizedTemplate->subject);
        }
        if (isset($localizedTemplate->message)) {
            if ($this->message->getPlainText() && !$this->message->getBody()) {
                $this->message->setPlainText($localizedTemplate->message);
            } else {
                $this->message->setBodyAndPlainText($localizedTemplate->message);
            }
        }
        $hookresults = Hooks::run_hook("EmailPreSend", array("messagename" => $this->message->getTemplateName(), "relid" => $this->entityId, "mergefields" => $this->mergeData));
        foreach ($hookresults as $hookmergefields) {
            foreach ($hookmergefields as $key => $value) {
                if ($key == "abortsend" && $value == true) {
                    throw new \App\Exceptions\Mail\SendHookAbort("Email Send Aborted By Hook");
                }
                $this->assign($key, $value);
            }
        }
        // $smarty = new \WHMCS\Smarty(false, "mail");
        // $smarty->setMailMessage($this->message);
        // $smarty->compile_id = md5($this->message->getSubject() . $this->message->getBody() . (\App::isExecutingViaCron() || \WHMCS\Environment\Php::isCli() ? "cron" : ""));
        // foreach ($this->mergeData as $mergefield => $mergevalue) {
        //     $smarty->assign($mergefield, $mergevalue);
        // }
        $subject = "";
        $message = "";
        $messageText = "";
        // if (!trim($message) && !trim($messageText)) {
        //     throw new \Exception("Email message rendered empty - please check the email message Smarty markup syntax");
        // }
        // $this->message->setSubject($subject);
        // $this->message->setBodyFromSmarty($message);
        // $this->message->setPlainText($messageText);
        if (!$this->isNonClientEmail) {
            if ($this->allowCc()) {
                $recipients = array();
                if ($this->recipientContactId) {
                    $contact = \App\Models\Contact::find($this->recipientContactId);
                    if ($contact->clientId == $this->recipientUserId) {
                        $recipients[] = $contact;
                    }
                } else {
                    $recipients = \App\Models\Contact::where("userid", $this->recipientUserId)->where($this->message->getType() . "emails", "=", "1")->get(array("firstname", "lastname", "email"));
                }
                foreach ($recipients as $recipient) {
                    $this->message->addRecipient("cc", $recipient->email, $recipient->firstName . " " . $recipient->lastName);
                }
                $this->finalizeCopiedRecipients($this->message, $this->entityId);
            } else {
                $this->message->clearRecipients("cc");
                $this->message->clearRecipients("bcc");
            }
        }
    }
    public function finalizeCopiedRecipients(Message $message, $relationalId)
    {
        $allCopiedRecipients = array();
        foreach (array("cc", "bcc") as $type) {
            $allCopiedRecipients[$type] = array();
            foreach ($message->getRecipients($type) as $recipient) {
                $hash = md5($recipient[0] . $recipient[1]);
                $allCopiedRecipients[$type][$hash] = array("email" => $recipient[0], "fullname" => $recipient[1]);
            }
        }
        $message->clearRecipients("cc");
        $message->clearRecipients("bcc");
        $hookresults = Hooks::run_hook("PreEmailSendReduceRecipients", array("messagename" => $message->getTemplateName(), "relid" => $relationalId, "recipients" => $allCopiedRecipients));
        foreach ($hookresults as $hookresult) {
            foreach (array("cc", "bcc") as $type) {
                if (is_array($hookresult) && isset($hookresult[$type]) && is_array($hookresult[$type])) {
                    $hookHashes = array_keys($hookresult[$type]);
                    foreach (array_keys($allCopiedRecipients[$type]) as $hash) {
                        if (!in_array($hash, $hookHashes)) {
                            unset($allCopiedRecipients[$type][$hash]);
                        }
                    }
                }
            }
        }
        foreach (array("cc", "bcc") as $type) {
            foreach ($allCopiedRecipients[$type] as $recipient) {
                $message->addRecipient($type, $recipient["email"], $recipient["fullname"]);
            }
        }
        return $message;
    }
    public function getMergeData()
    {
        return $this->mergeData;
    }
    public function getMergeDataByKey($key)
    {
        return isset($this->mergeData[$key]) ? $this->mergeData[$key] : "";
    }
    public function preview()
    {
        try {
            $this->prepare();
        } catch (\App\Exceptions\Mail\SendHookAbort $e) {
        } catch (\Exception $e) {
            LogActivity::Save("An Error Occurred with the email preview: " . $e->getMessage());
            throw $e;
        }
        return $this->message;
    }
    public function send()
    {
        try {
            $this->prepare();
            if (!$this->message->hasRecipients()) {
                throw new \App\Exceptions\Mail\SendFailure("No recipients provided for message");
            }
            $mail = new \App\Helpers\Mail();
            $mail->sendMessage($this->message, $this->mergeData);
            $userId = $this->recipientUserId;
            $isEmailToNotLog = in_array($this->message->getTemplateName(), $this->emailTemplateNamesToNotLog);
            $ticketReplyEmails = array("Support Ticket Opened by Admin", "Support Ticket Reply");
            $isTicketReplyEmail = in_array($this->message->getTemplateName(), $ticketReplyEmails);
            $ticketEmailLoggingDisabled = Cfg::getValue("DisableSupportTicketReplyEmailsLogging");
            $this->message->setMergeData($this->mergeData);
            if ($userId && !$isEmailToNotLog && !($isTicketReplyEmail && $ticketEmailLoggingDisabled)) {
                $this->message->saveToEmailLog($userId);
            }
            $emailuserlink = 0 < $userId ? " - User ID: " . $userId : "";
            $recipientName = trim($this->getMergeDataByKey("client_first_name") . " " . $this->getMergeDataByKey("client_last_name"));
            if ($recipientName) {
                LogActivity::Save("Email Sent to " . $recipientName . " (" . $mail->Subject . ") " . $emailuserlink, $userId);
            }
            // TODO: $mail->clearAllRecipients();
            return true;
        } catch (\App\Exceptions\Mail\SendHookAbort $e) {
            LogActivity::Save("Email Sending Aborted by Hook (Subject: " . $this->message->getSubject() . ")", "none");
            throw $e;
        } 
        // catch (\PHPMailer\PHPMailer\Exception $e) {
        //     $exceptionMessage = strip_tags($e->getMessage());
        //     logActivity("Email Sending Failed - " . $exceptionMessage . " (Subject: " . $this->message->getSubject() . ")", "none");
        //     throw new \WHMCS\Exception\Mail\SendFailure($exceptionMessage);
        // } 
        catch (\Exception $e) {
            // dd($e);
            LogActivity::Save("Email Sending Failed - " . $e->getMessage() . " (Subject: " . $this->message->getSubject() . ")", "none");
            throw new \App\Exceptions\Mail\SendFailure($e->getMessage());
        }
    }
    protected function setRecipient($userId, $contactId = NULL)
    {
        $this->recipientUserId = (int) $userId;
        $this->recipientContactId = (int) $contactId ?: null;
        global $_LANG;
        global $currency;
        // getUsersLang($userId);
        $currency = \App\Helpers\Format::getCurrency($userId);
        return $this;
    }
    public function assign($key, $value)
    {
        $this->mergeData[$key] = $value;
        return $this;
    }
    public function massAssign($data)
    {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
        return $this;
    }
    public function getMessage()
    {
        return $this->message;
    }
}
