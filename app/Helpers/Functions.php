<?php
namespace App\Helpers;

use DB;

// Import Model Class here
use App\Models\Emailtemplate;

// Import Package Class here
use Mail;
use App\Helpers\Hooks;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;
use App\Helpers\Application;

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Functions
{
	public static function generateUniqueID($type = "")
    {
        $z = 0;
        if ($type == "") {
            $length = 10;
        } else {
            $length = 6;
        }
        while ($z <= 0) {
            $seedsfirst = "123456789";
            $seeds = "0123456789";
            $str = NULL;
            $seeds_count = strlen($seeds) - 1;
            for ($i = 0; $i < $length; $i++) {
                if ($i == 0) {
                    $str .= $seedsfirst[rand(0, $seeds_count - 1)];
                } else {
                    $str .= $seeds[rand(0, $seeds_count)];
                }
            }
            if ($type == "") {
                $result = \App\Models\Order::where('ordernum', $str)->first();
                $data = $result ? $result->toArray() : ['id' => ""];
                $id = $data["id"];
                if ($id == "") {
                    $z = 1;
                }
            } else {
                if ($type == "tickets") {
                    $result = \App\Models\Ticket::where('tid', $str)->first();
                    $data = $result ? $result->toArray() : ['id' => ""];
                    $id = $data["id"];
                    if ($id == "") {
                        $z = 1;
                    }
                }
            }
        }
        return $str;
    }

    public static function format_as_currency($amount)
    {
        if (0 < $amount) {
            $amount += 1.0E-6;
        }
        $amount = round($amount, 2);
        $amount = sprintf("%01.2f", $amount);
        return $amount;
    }

    /**
     * sendMessage
     */
    public static function sendMessage($template, $func_id, $extra = "", $displayresult = "", $attachments = "")
    {
        try {
            $defaultTheme = \App\Helpers\Cfg::get('Template');
            \Hexadog\ThemesManager\Facades\ThemesManager::set($defaultTheme);

            $emailer = \App\Helpers\Emailer::factoryByTemplate($template, $func_id, $extra);
            if (is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    $emailer->getMessage()->addFileAttachment($attachment["displayname"], $attachment["path"]);
                }
            }
            $emailer->send();
            if ($displayresult) {
                return "<p>Email Sent Successfully to <a href=\"clientssummary.php?userid=" . $emailer->getMergeDataByKey("client_id") . "\">" . \App\Helpers\Sanitize::makeSafeForOutput($emailer->getMergeDataByKey("client_first_name")) . " " . \App\Helpers\Sanitize::makeSafeForOutput($emailer->getMergeDataByKey("client_last_name")) . "</a></p>";
            }
        } catch (\App\Exceptions\Mail\SendHookAbort $e) {
            if ($displayresult) {
                return "<p>" . $e->getMessage() . "</p>";
            }
            if (Application::isApiRequest()) {
                return false;
            }
            return $e->getMessage();
        } catch (\App\Exceptions\Mail\SendFailure $e) {
            if ($displayresult) {
                return "<p>Email Sending Failed - " . $e->getMessage() . "</p>";
            }
            if (Application::isApiRequest()) {
                return false;
            }
            return $e->getMessage();
        } catch (\App\Exceptions\Mail\InvalidTemplate $e) {
            if ($displayresult) {
                return "<p>Email Sending Failed - " . $e->getMessage() . "</p>";
            }
            if (Application::isApiRequest()) {
                return false;
            }
            return "Email Sending Failed - " . $e->getMessage();
        } catch (\App\Exceptions\Mail\TemplateDisabled $e) {
            if ($displayresult) {
                return "<p>Email Sending Failed - " . $e->getMessage() . "</p>";
            }
            if (Application::isApiRequest()) {
                return false;
            }
            return "Email Sending Failed - " . $e->getMessage();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * sendAdminMessage
     */
    public static function sendAdminMessage($template, $email_merge_fields = array(), $to = "system", $deptid = 0, $adminid = 0, $ticketnotify = "")
    {
        try {
            $emailer = \App\Helpers\Emailer::factoryByTemplate($template, "");
            $subject = $emailer->getMessage()->getSubject();
            $type = $emailer->getMessage()->getType();
            if ($type != "admin") {
                throw new \Exception("Email template provided is not an admin email template");
            }
            $emailer->massAssign($email_merge_fields);
            $emailer->determineAdminRecipientsAndSender($to, $deptid, $adminid, $ticketnotify);
            $emailer->send();
            return true;
        } catch (\App\Exceptions\Mail\SendHookAbort $e) {
            $logSubject = isset($subject) ? " (Subject: " . $subject . ")" : "";
            LogActivity::Save("Admin Email Message Sending Aborted by Hook" . $logSubject, "none");
        } catch (\Exceptions $e) {
            $logSubject = isset($subject) ? " (Subject: " . $subject . ")" : "";
            LogActivity::Save("Admin Email Message Sending Failed - " . $e->getMessage() . $logSubject, "none");
        }
        return false;
    }

    /**
     * getUsersLang
     */
    public static function getUsersLang($userId)
    {
        $existingLanguage = NULL;
        $languageName = DB::table("tblclients")->where("id", "=", (int) $userId)->value("language");
        if (empty($languageName)) {
            $languageName = \App\Helpers\Cfg::get("Language");
        }
        // TODO: $existingLanguage = swapLang($languageName);
        return $existingLanguage;
    }

    /**
     * AffiliatePayment
     */
    public static function AffiliatePayment($affaccid, $hostingid)
    {
        global $CONFIG;
        try {
            $payout = false;
            $error = "";
            if ($affaccid) {
                $result = \App\Models\Affiliatesaccount::findOrFail($affaccid);
            } else {
                $result = \App\Models\Affiliatesaccount::where('relid', $hostingid)->firstOrFail();
            }
            $data = $result->toArray();
            $affaccid = $data["id"];
            $affid = $data["affiliateid"];
            $lastpaid = $data["lastpaid"];
            $relid = $data["relid"];
            $commission = self::calculateAffiliateCommission($affid, $relid, $lastpaid);
            $result = \App\Models\Product::select("tblproducts.affiliateonetime")
                ->where("tblhosting.id", $relid)
                ->join("tblhosting", "tblhosting.packageid", "=", "tblproducts.id")
                ->first();
            $data = $result->toArray();
            $affiliateonetime = $data["affiliateonetime"];
            if ($affiliateonetime) {
                if ($lastpaid == "0000-00-00") {
                    $payout = true;
                } else {
                    $error = "This product is setup for a one time affiliate payment only and the commission has already been paid";
                }
            } else {
                $payout = true;
            }
            $result = \App\Models\Affiliate::find($affid);
            $data = $result->toArray();
            $onetime = $data["onetime"];
            if ($onetime && $lastpaid != "0000-00-00") {
                $payout = false;
                $error = "This affiliate is setup for a one time commission only on all products and that has already been paid";
            }
            if ($affaccid) {
                $commissionDelayed = false;
                $clearingDate = date("Y-m-d");
                if ($CONFIG["AffiliatesDelayCommission"]) {
                    $commissionDelayed = true;
                    $clearingDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $CONFIG["AffiliatesDelayCommission"], date("Y")));
                }
                $responses = Hooks::run_hook("AffiliateCommission", array("affiliateId" => $affid, "referralId" => $affaccid, "serviceId" => $relid, "commissionAmount" => $commission, "commissionDelayed" => $commissionDelayed, "clearingDate" => $clearingDate, "payout" => $payout, "message" => $error));
                $skipCommission = false;
                foreach ($responses as $response) {
                    if (array_key_exists("skipCommission", $response) && $response["skipCommission"]) {
                        $skipCommission = true;
                    } else {
                        if (array_key_exists("payout", $response) && $response["payout"]) {
                            $payout = true;
                        }
                    }
                }
                if ($payout && !$skipCommission) {
                    if ($commissionDelayed) {
                        \App\Models\Affiliatespending::insert(array("affaccid" => $affaccid, "amount" => $commission, "clearingdate" => $clearingDate));
                    } else {
                        \App\Models\Affiliate::where('id', (int) $affid)->increment('balance', $commission);
                        \App\Models\Affiliateshistory::insert(array("affiliateid" => $affid, "date" => \Carbon\Carbon::now(), "affaccid" => $affaccid, "amount" => $commission));
                    }
                    \App\Models\Affiliatesaccount::where('id', $affaccid)->update(array("lastpaid" => \Carbon\Carbon::now()));
                }
            }
            return $error; 
        } catch (\Exception $e) {
            return $e->getMessage();
        } 
    }

    /**
     * calculateAffiliateCommission
     */
    public static function calculateAffiliateCommission($affid, $relid, $lastpaid = "")
    {
        global $CONFIG;
        static $AffCommAffiliatesData = array();
        $percentage = $fixedamount = "";
        $result = \App\Models\Product::select("tblproducts.affiliateonetime", "tblproducts.affiliatepaytype", "tblproducts.affiliatepayamount", "tblhosting.amount", "tblhosting.firstpaymentamount", "tblhosting.billingcycle", "tblhosting.userid", "tblclients.currency")
            ->where("tblhosting.id", $relid)
            ->join("tblhosting", "tblhosting.packageid", "=", "tblproducts.id")
            ->join("tblclients", "tblclients.id", "=", "tblhosting.userid")
            ->first();
        $data = $result->toArray();
        $userid = $data["userid"];
        $billingcycle = $data["billingcycle"];
        $affiliateonetime = $data["affiliateonetime"];
        $affiliatepaytype = $data["affiliatepaytype"];
        $affiliatepayamount = $data["affiliatepayamount"];
        $clientscurrency = $data["currency"];
        $amount = $lastpaid == "0000-00-00" || $billingcycle == "One Time" || $affiliateonetime ? $data["firstpaymentamount"] : $data["amount"];
        if ($affiliatepaytype == "none") {
            return "0.00";
        }
        if ($affiliatepaytype) {
            if ($affiliatepaytype == "percentage") {
                $percentage = $affiliatepayamount;
            } else {
                $fixedamount = $affiliatepayamount;
            }
        }
        if (isset($AffCommAffiliatesData[$affid])) {
            $data = $AffCommAffiliatesData[$affid];
        } else {
            $result = \App\Models\Affiliate::select("clientid", "paytype", "payamount", DB::raw("(SELECT currency FROM tblclients WHERE id=clientid) AS currency"))
                ->where('id', $affid)
                ->first();
            $data = $result->toArray();
            $AffCommAffiliatesData[$affid] = $data;
        }
        $affuserid = $data["clientid"];
        $paytype = $data["paytype"];
        $payamount = $data["payamount"];
        $affcurrency = $data["currency"];
        if ($paytype) {
            $percentage = $fixedamount = "";
            if ($paytype == "percentage") {
                $percentage = $payamount;
            } else {
                $fixedamount = $payamount;
            }
        }
        if (!$fixedamount && !$percentage) {
            $percentage = $CONFIG["AffiliateEarningPercent"];
        }
        $commission = $fixedamount ? \App\Helpers\Format::ConvertCurrency($fixedamount, 1, $affcurrency) : \App\Helpers\Format::ConvertCurrency($amount, $clientscurrency, $affcurrency) * $percentage / 100;
        Hooks::run_hook("CalcAffiliateCommission", array("affid" => $affid, "relid" => $relid, "amount" => $amount, "commission" => $commission));
        $commission = self::format_as_currency($commission);
        return $commission;
    }

    /**
     * addToDoItem
     */
    public static function addToDoItem($title, $description, $duedate = "", $status = "", $admin = "")
    {
        if (!$status) {
            $status = "Pending";
        }
        if (!$duedate) {
            $duedate = date("Y-m-d");
        }
        \App\Models\Todolist::insert(array("date" => \Carbon\Carbon::now(), "title" => $title, "description" => $description, "admin" => $admin, "status" => $status, "duedate" => $duedate));
    }

    /**
     * getTodaysDate
     */
    public static function getTodaysDate($client = "")
    {
        return (new \App\Helpers\Client())->fromMySQLDate(date("Y-m-d"), 0, $client);
    }

    /**
     * generateFriendlyPassword
     */
    public static function generateFriendlyPassword($length = 12)
    {
        $password = str_replace(array("=", "+", "/", "."), "", base64_encode(Str::random($length * 2)));
        if (strlen($password) < $length) {
            $password .= self::generateFriendlyPassword($length - strlen($password));
        }
        return substr($password, 0, $length);
    }

    /**
     * fromMySQLDate
     */
    public function fromMySQLDate($date, $time = false, $client = false, $zerodateval = false)
    {
        if (strpos(substr($date, 0, 11), "-00") !== false) {
            $date = "0000-00-00";
        }
        if (substr($date, 0, 11) == "-0001-11-30") {
            $date = "0000-00-00";
        }

        if ($date instanceof \App\Helpers\Carbon || $date instanceof \Carbon\Carbon) {
            $date = (string) $date;
            if ((string) $date === (string) \App\Helpers\Carbon::createFromTimestamp(0, "UTC")) {
                $date = "0000-00-00";
            }
        }
        $isZeroDate = substr($date, 0, 10) == "0000-00-00";
        if ($isZeroDate) {
            if ($zerodateval) {
                return $zerodateval;
            }
            $dateFormat = \App\Helpers\Carbon::now()->getAdminDateFormat();
            // return str_replace(array("d", "m", "Y"), array("00", "00", "0000"), $dateFormat);
            return str_replace(array("d", "m", "Y", "H:i"), array("00", "00", "0000", ($time ? "00:00" : "")), $dateFormat);
        }
        try {
            $date = \App\Helpers\Carbon::parse($date);
        } catch (\Exception $e) {
            throw new \App\Exceptions\Fatal("Invalid date format provided: " . $date);
        }
        if ($client && $time) {
            return $date->toClientDateTimeFormat();
        }
        if ($client) {
            return $date->toClientDateFormat();
        }
        if ($time) {
            return $date->toAdminDateTimeFormat();
        }
        return $date->toAdminDateFormat();
    }

    /**
     * saveSingleCustomField
     */
    public static function saveSingleCustomField($fieldId, $relId, $value)
    {
        $customField = DB::table("tblcustomfields")->find($fieldId);
        if (!$customField) {
            return false;
        }
        $fieldSaveHooks = Hooks::run_hook("CustomFieldSave", array("fieldid" => $fieldId, "relid" => $relId, "value" => $value));
        if (0 < count($fieldSaveHooks)) {
            $fieldSaveHooksLast = array_pop($fieldSaveHooks);
            if (array_key_exists("value", $fieldSaveHooksLast)) {
                $value = $fieldSaveHooksLast["value"];
            }
        }
        $customFieldValue = \App\Models\Customfieldsvalue::firstOrNew(array("fieldid" => $fieldId, "relid" => $relId));
        $customFieldValue->value = $value ?? "";
        return $customFieldValue->save();
    }

    /**
     * sendAdminNotification
     */
    public static function sendAdminNotification($to = "system", $subject, $messageBody, $deptid = 0, $appendAdminLink = true)
    {
        $sendNow = true;
        // if (!class_exists("\\DI")) {
        //     $sendNow = false;
        // } else {
        //     if (!DI::has("app")) {
        //         $sendNow = false;
        //     } else {
        //         $app = DI::make("app");
        //         if (!$app instanceof WHMCS\Application) {
        //             $sendNow = false;
        //         }
        //     }
        // }
        if ($sendNow) {
            return self::sendAdminNotificationNow($to, $subject, $messageBody, $deptid, $appendAdminLink);
        }
        // TODO: return WHMCS\Scheduling\Jobs\Queue::add(WHMCS\Mail\Job\AdminNotification::JOB_NAME_GENERIC, "WHMCS\\Mail\\Job\\AdminNotification", "send", array($to, $subject, $messageBody, $deptid, $appendAdminLink), 0, false);
    }

    /**
     * sendAdminNotificationNow
     */
    public static function sendAdminNotificationNow($to = "system", $subject, $messageBody, $deptid = 0, $appendAdminLink = true)
    {
        global $smtp_debug;
        // $whmcs = App::self();
        // $whmcsAppConfig = $whmcs->getApplicationConfig();
        if (!trim($messageBody)) {
            return false;
        }
        $messageBody = "<p>" . $messageBody . "</p>";
        if ($appendAdminLink) {
            $adminurl = config('app.url') . "/" . env('ADMIN_ROUTE_PREFIX') . "/";
            $messageBody .= "\n<p><a href=\"" . $adminurl . "\">" . $adminurl . "</a></p>";
        }
        $message = new \App\Helpers\Message();
        $message->setType("admin");
        $message->setSubject($subject);
        $message->setBodyAndPlainText($messageBody);
        if ($deptid) {
            $data = \App\Models\Ticketdepartment::selectRaw('name,email')->where('id', $deptid)->first();
            $data = $data->toArray();
            $message->setFromName(Cfg::getValue("CompanyName") . " " . $data["name"]);
            $message->setFromEmail($data["email"]);
        } else {
            $message->setFromName(Cfg::getValue("SystemEmailsFromName"));
            $message->setFromEmail(Cfg::getValue("SystemEmailsFromEmail"));
        }
        // $where = "tbladmins.disabled=0 AND tbladminroles." . \App\Helpers\Database::db_escape_string($to) . "emails='1'";
        $where = "tbladmins.disabled=0";
        if ($deptid) {
            $where .= " AND tbladmins.ticketnotifications!=''";
        }
        // $result = select_query("tbladmins", "", $where, "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");
        $result = \App\Models\Admin::selectRaw("firstname,lastname,email,ticketnotifications")->whereRaw($where)->get();
        foreach ($result->toArray() as $data) {
            if ($data["email"]) {
                $adminsend = true;
                if ($deptid) {
                    $ticketnotifications = explode(",", $data["ticketnotifications"]);
                    if (!in_array($deptid, $ticketnotifications)) {
                        $adminsend = false;
                    }
                }
                if ($adminsend) {
                    $message->addRecipient("to", trim($data["email"]), $data["firstname"] . " " . $data["lastname"]);
                }
            }
        }
        if (!$message->getRecipients("to")) {
            return false;
        }
        try {
            $mail = new \App\Helpers\Mail();
            if (!$mail->sendMessage($message)) {
                LogActivity::Save("Admin Email Notification Sending Failed - " . $mail->ErrorInfo . " (Subject: " . $subject . ")", "none");
            }
        } 
        // catch (PHPMailer\PHPMailer\Exception $e) {
        //     logActivity("Admin Email Notification Sending Failed - PHPMailer Exception - " . $e->getMessage() . " (Subject: " . $subject . ")", "none");
        // } 
        catch (\Exception $e) {
            LogActivity::Save("Admin Email Notification Sending Failed - " . $e->getMessage() . " (Subject: " . $subject . ")", "none");
        }
    }
    
    public static function generateCssFriendlyClassName($value)
    {
        return preg_replace("/[^a-z0-9_-]/", "-", strtolower(trim(strip_tags($value))));
    }

    /**
     * ensurePaymentMethodIsSet
     */
    public static function ensurePaymentMethodIsSet($userId, $id, $table = "tblhosting")
    {
        $userId = (int) $userId;
        $id = (int) $id;
        if (!is_int($userId) || $userId < 1) {
            return "";
        }
        if (!is_int($id) || $id < 1) {
            return "";
        }
        $validTables = array("tblhosting", "tbldomains", "tblhostingaddons", "tblinvoiceitems", "tblinvoices");
        if (!in_array($table, $validTables)) {
            return "";
        }
        $paymentMethod = \App\Helpers\Gateway::getClientsPaymentMethod($userId);
        DB::table($table)->where('id', $id)->update(array("paymentmethod" => $paymentMethod));
        return $paymentMethod;
    }

    public static function validateDateInput($date)
    {
        $sqldate = (new \App\Helpers\SystemHelper())->toMySQLDate($date);
        $dateonly = explode(" ", $sqldate);
        $dateparts = explode("-", $dateonly[0]);
        list($year, $month, $day) = $dateparts;
        if (is_numeric($day) && is_numeric($month) && is_numeric($year)) {
            return checkdate($month, $day, $year);
        }
        return false;
    }
    
    /**
     * defineGatewayFieldStorage
     */
    public static function defineGatewayFieldStorage($clear = false, $gatewayName = NULL, $data = array())
    {
        static $gatewayFields = NULL;
        if (!is_null($gatewayName)) {
            $gatewayFields[$gatewayName] = $data;
        }
        $gatewayFieldsToReturn = $gatewayFields;
        if ($clear) {
            $gatewayFields = array();
        }
        return $gatewayFieldsToReturn;
    }

    /**
     * build_query_string
     */
    public static function build_query_string($data, $encoding = PHP_QUERY_RFC1738)
    {
        if ($encoding == PHP_QUERY_RFC1738 || $encoding == PHP_QUERY_RFC3986) {
            return http_build_query($data, "", "&", $encoding);
        }
        if (empty($data)) {
            return "";
        }
        $query = "";
        foreach ($data as $key => $value) {
            $query .= $key . "=" . $value . "&";
        }
        return substr($query, 0, -1);
    }

    /**
     * logModuleCall
     */
    public static function logModuleCall($module, $action, $request, $response, $data = "", $variablesToMask = array())
    {
        if (!Cfg::getValue("ModuleDebugMode")) {
            return false;
        }
        if (!$module) {
            return false;
        }
        if (!$action) {
            $action = "Unknown";
        }
        if (is_array($request) || is_object($request)) {
            $request = print_r($request, true);
        }
        if (is_array($response) || is_object($response)) {
            $response = print_r($response, true);
        }
        if (is_array($data)) {
            $data = print_r($data, true);
        }
        foreach ($variablesToMask as $variable) {
            $variableMask = str_repeat("*", strlen($variable));
            $request = str_replace($variable, $variableMask, $request);
            $response = str_replace($variable, $variableMask, $response);
            $data = str_replace($variable, $variableMask, $data);
        }
        \App\Models\Modulelog::insert(array("date" => \Carbon\Carbon::now(), "module" => strtolower($module), "action" => strtolower($action), "request" => $request, "response" => $response, "arrdata" => $data));
    }

    /**
     * curlCall
     */
    public static function curlCall($url, $postData, $options = array(), $returnUnexecutedHandle = false, $throwOnCurlError = false)
    {
        $appConfig = collect(\Config::get("portal"))->toJson();
        $appConfig = json_decode($appConfig);
        $isSSL = strpos($url, "https") === 0 ? true : false;
        $sanitizedOptions = array();
        foreach ($options as $curlOptName => $value) {
            if ($curlOptName == "HEADER") {
                $sanitizedOptions["CURLOPT_HTTPHEADER"] = $value;
            } else {
                if ($curlOptName == "CURLOPT_URL") {
                    continue;
                }
                if (strpos($curlOptName, "CURLOPT_") === 0 && defined($curlOptName)) {
                    if (strpos($curlOptName, "CURLOPT_SSL") === 0) {
                        if ($isSSL) {
                            $sanitizedOptions[$curlOptName] = $value;
                        }
                    } else {
                        $sanitizedOptions[$curlOptName] = $value;
                    }
                }
            }
        }
        $options = $sanitizedOptions;
        unset($sanitizedOptions);
        $defaultOptions = array("CURLOPT_HEADER" => 0, "CURLOPT_TIMEOUT" => 100, "CURLOPT_RETURNTRANSFER" => 1);
        $options = array_merge($defaultOptions, $options);
        if (!array_key_exists("CURLOPT_PROXY", $options)) {
            $outboundProxies = $appConfig->outbound_http_proxy;
            $proxy = "";
            if (!empty($outboundProxies)) {
                if (is_array($outboundProxies)) {
                    if ($isSSL && !empty($outboundProxies["https"])) {
                        $proxy = $outboundProxies["https"];
                    } else {
                        if (!empty($outboundProxies["http"])) {
                            $proxy = $outboundProxies["http"];
                        }
                    }
                } else {
                    $proxy = $outboundProxies;
                }
            }
            if ($proxy) {
                $options["CURLOPT_PROXY"] = $proxy;
            }
        }
        if ($isSSL) {
            if (!array_key_exists("CURLOPT_SSL_VERIFYHOST", $options)) {
                if ($appConfig->outbound_http_ssl_verifyhost) {
                    $options["CURLOPT_SSL_VERIFYHOST"] = 2;
                } else {
                    $options["CURLOPT_SSL_VERIFYHOST"] = 0;
                }
            }
            if (!array_key_exists("CURLOPT_SSL_VERIFYPEER", $options)) {
                if ($appConfig->outbound_http_ssl_verifypeer) {
                    $options["CURLOPT_SSL_VERIFYPEER"] = 1;
                } else {
                    $options["CURLOPT_SSL_VERIFYPEER"] = 0;
                }
            }
        }
        if ($postData || !empty($options["CURLOPT_POST"])) {
            if (!is_string($postData)) {
                $postData = http_build_query($postData);
            }
            $options["CURLOPT_POSTFIELDS"] = (string) $postData;
            $options["CURLOPT_POST"] = 1;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        foreach ($options as $curlOptName => $value) {
            curl_setopt($ch, constant($curlOptName), $value);
        }
        if ($returnUnexecutedHandle) {
            return $ch;
        }
        $retval = curl_exec($ch);
        if (curl_errno($ch)) {
            if ($throwOnCurlError) {
                throw new \App\Exceptions\Http\ConnectionError(curl_error($ch), curl_errno($ch));
            }
            $retval = "CURL Error: " . curl_errno($ch) . " - " . curl_error($ch);
        }
        curl_close($ch);
        return $retval;
    }

    public static function autoHyperLink($message)
    {
        $regex = "/((http(s?):\\/\\/)|(www\\.))([\\w\\.]+)([a-zA-Z0-9?&%#~.;:\\/=+_-]+)/i";
        return preg_replace_callback($regex, function (array $matches) {
            list($url, , , $optionalS, $subDomain, $domain, $pathAndQuery) = $matches;
            $displayUrl = $url;
            $pathAndQuery = trim($pathAndQuery);
            $characterMatches = array();
            if (preg_match("%(&quot;)|(&#039;)\$%", trim($pathAndQuery), $characterMatches)) {
                $pathAndQuery = preg_replace("/" . preg_quote($characterMatches[0]) . "\$/", "", $pathAndQuery);
                $displayUrl = preg_replace("/" . preg_quote($characterMatches[0]) . "\$/", "", $displayUrl);
            } else {
                $characterMatches[0] = "";
            }
            $fullUrl = "http" . $optionalS . "://" . $subDomain . $domain . $pathAndQuery;
            return "<a href=\"" . $fullUrl . "\" target=\"_blank\" class=\"autoLinked\">" . $displayUrl . "</a>" . $characterMatches[0];
        }, $message);
    }

    public static function foreignChrReplace($arr)
    {
        global $CONFIG;
        $cleandata = array();
        if (is_array($arr)) {
            foreach ($arr as $key => $val) {
                if (is_array($val)) {
                    $cleandata[$key] = self::foreignChrReplace($val);
                } else {
                    if (!is_object($val)) {
                        if (function_exists("hook_transliterate")) {
                            $cleandata[$key] = hook_transliterate($val);
                        } else {
                            $cleandata[$key] = self::foreignChrReplace2($val);
                        }
                    }
                }
            }
        } else {
            if (!is_object($arr)) {
                if (function_exists("hook_transliterate")) {
                    $cleandata = hook_transliterate($arr);
                } else {
                    $cleandata = self::foreignChrReplace2($arr);
                }
            }
        }
        return $cleandata;
    }
    public static function foreignChrReplace2($string)
    {
        if (is_null($string) || !(is_numeric($string) || is_string($string))) {
            return $string;
        }
        $accents = "/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig|tilde|ring|slash|zlig|elig|quest|caron);/";
        $string = htmlentities($string, ENT_NOQUOTES, \App\Helpers\Cfg::getValue("Charset"));
        $string = preg_replace($accents, "\$1", $string);
        $string = html_entity_decode($string, ENT_NOQUOTES, \App\Helpers\Cfg::getValue("Charset"));
        if (function_exists("mb_internal_encoding") && function_exists("mb_regex_encoding") && function_exists("mb_ereg_replace")) {
            mb_internal_encoding("UTF-8");
            mb_regex_encoding("UTF-8");
            $changeKey = array("g" => "g", "ü" => "u", "s" => "s", "ö" => "o", "i" => "i", "ç" => "c", "G" => "G", "Ü" => "U", "S" => "S", "Ö" => "O", "I" => "I", "Ç" => "C");
            foreach ($changeKey as $i => $u) {
                $string = mb_ereg_replace($i, $u, $string);
            }
        }
        return $string;
    }

    public static function isValidforPath($name)
    {
        if (!is_string($name) || empty($name)) {
            return false;
        }
        if (!ctype_alnum(str_replace(array("_", "-"), "", $name))) {
            return false;
        }
        return true;
    }
}
