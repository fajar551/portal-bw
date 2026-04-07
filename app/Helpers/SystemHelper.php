<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Bannedip;
use App\Helpers\Pwd as Password;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Helpers\Database;
use App\Helpers\Permission;
use App\Models\AdminPerm;
use App\Models\Admin;
use App\Models\LogRegister;
use App\Helpers\Cfg;
use App\Models\Configuration;
use App\Models\Currency;
use App\Models\Emailtemplate;
use App\Models\Paymentgateway;
use App\Helpers\AdminFunctions;
use App\Models\Order;
use App\Models\AdminLog;
use App\Models\Orderstatus;
use App\Models\Account;
use App\Models\Ticket;
use App\Models\Todolist;
use Illuminate\Support\Carbon;
use App\Events\AnnouncementEdit;
use App\Helpers\Mail;

class SystemHelper
{
    protected $Password;
    protected $admin;
    protected $prefix;

    public function __construct()
    {
        $this->Password = new Password();
        $this->admin = new AdminFunctions();
        $this->prefix = Database::prefix();
    }

    public function AddBannedIp(array $params)
    {
        if (!@$params['expires']) {
            $params['expires'] = '';
        }
        extract($params);
        if (!$days) {
            $days = 7;
        }
        if (!$expires) {
            $expires = date("YmdHis", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $days, date("Y")));
        }
        $banned = new Bannedip;
        $banned->ip = $ip;
        $banned->reason = $reason;
        $banned->expires = $expires;
        $banned->save();
        $banid = $banned->id;
        return ["result" => "success", "banid" => $banid];
    }

    public function DecryptPassword(string $password)
    {
        $dec = $this->Password->decrypt($password);
        return ["result" => "success", "password" => $dec];
    }

    public function EncryptPassword(string $password)
    {
        $dec = $this->Password->encrypt($password);
        return ["result" => "success", "password" => $dec];
    }

    public function GetActivityLog(array $params)
    {
        extract($params);
        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $db = DB::table(Database::prefix() . 'activitylog');
        if ($userid) {
            $db->where('userid', $userid);
        }

        if ($date) {
            $db->whereDate('date', $date);
        }

        if ($user) {
            $db->where('user', $user);
        }

        if ($description) {
            $db->where('description', 'like', '%' . $description . '%');
        }

        if ($ipaddress) {
            $db->where('ipaddr', $ipaddress);
        }
        $db->select('id', 'userid', 'date', 'description', 'user as username', 'ipaddr as ipaddress');
        $db->orderBy('id', 'DESC');
        $db->offset($limitstart);
        $db->limit($limitnum);
        $activity = $db->get();
        return [
            'result' => 'success',
            'totalresults' => count($activity),
            'startnumber' => $limitstart,
            'activity' => [
                'entry' => $activity
            ]
        ];
    }

    public function GetAdminDetails(array $params)
    {
        extract($params);
        @$admin = Admin::find($adminid)->id;
        if (is_null($admin)) {
            return ["result" => "error", "message" => "You must be authenticated as an admin user to perform this action"];
        }
        $admin = Admin::find($adminid);

        $apiresults = ["result" => "success", "adminid" => $admin->id, "name" => $admin->firstName . " " . $admin->lastName, "notes" => $admin->notes, "signature" => $admin->signature];

        $adminPermissionsArray = $this->getAdminPermsArray();
        $adminPermissions = AdminPerm::where('roleid', $admin->roleid)->get();
        $apiresults["allowedpermissions"] = "";
        foreach ($adminPermissions as $r) {
            $apiresults["allowedpermissions"] .= $adminPermissionsArray[$r->permid] . ",";
        }
        $apiresults["departments"] = $admin->supportdepts;
        $apiresults["whmcs"] = [];
        return $apiresults;
    }

    private function getAdminPermsArray()
    {
        return Permission::all();
    }

    public function GetAdminUsers(array $params)
    {
        extract($params);
        $admin = Admin::orderBy("firstname")->orderBy("lastname");
        if ($roleid) {
            $admin->where('roleid', $roleid);
        }

        if (!empty($email)) {
            $admin->where("email", "LIKE", "%" . $email . "%");
        }
        if (!$include_disabled) {
            $admin->where("disabled", 0);
        }
        $data = $admin->get();
        $apiresults["count"] = 0;
        foreach ($data as $r) {
            $supportDepartment = explode(',', $r->supportdepts);
            $supportDepartmentIds = [];
            foreach ($supportDepartment as $k) {
                if (!empty($k)) {
                    $supportDepartmentIds[] = $k;
                }
            }

            $receivesTicket = explode(',', $r->ticketnotifications);
            $receivesTicketNotifications = [];
            foreach ($receivesTicket as $y) {
                if (!empty($y)) {
                    $receivesTicketNotifications[] = $y;
                }
            }

            $adminData = [
                'id' => $r->id,
                'uuid' => $r->uuid,
                'roleId' => $r->roleid,
                'username' => $r->username,
                'twoFactorAuthModule' => $r->authmodule,
                'firstname' => $r->firstname,
                'lastname' => $r->lastname,
                'email' => $r->email,
                'signature' => $r->signature,
                'notes' => $r->notes,
                'template' => $r->template,
                'language' => $r->language,
                'isDisabled' => $r->disabled,
                'loginAttempts' => $r->loginattempts,
                'supportDepartmentIds' => $supportDepartmentIds,
                'receivesTicketNotifications' => $receivesTicketNotifications,
                'homepageWidgetsConfig' => $r->widget_order,
                'hiddenHomepageWidgets' => $r->homewidgets,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
                'fullName' => $r->firstname . ' ' . $r->lastname,
                'gravatarHash' => md5(strtolower(trim($r->email))),
            ];

            $apiresults["admin_users"][] = $adminData;
        }
        $apiresults["count"] = count($apiresults["admin_users"]);
        return $apiresults;
    }

    public function GetAutomationLog(array $params)
    {
        extract($params);
        if (!$startdate) {
            $startdate = date("Y-m-d");
        }
        if (!$enddate) {
            $enddate = date("Y-m-d");
        }
        $namespace = trim($namespace);
        $query = DB::table(Database::prefix() . 'log_register')
            ->select(DB::raw("date_format(created_at, '%Y-%m-%d') AS date, name, namespace, IF((namespace_value REGEXP '^[[:digit:]]+\$'), SUM(namespace_value), namespace_value) AS total_count"));
        if ($namespace) {
            $query->where("namespace", "LIKE", $namespace . "%");
        } else {
            $query->where("namespace", "!=", "cron.dailyreport");
        }
        $tempStats = [];
        $entries = $query->where("created_at", ">=", $startdate . " 00:00:00")->where("created_at", "<=", $enddate . " 23:59:59")->groupBy("name", "namespace", DB::raw("date_format(created_at, '%Y-%m-%d')"))->get();

        if (!empty($entries)) {
            foreach ($entries as $data) {
                $key = [
                    'date' => $data->date,
                    'name' => $data->name,
                    'namespace' => $data->namespace,
                    'total_count' => $data->total_count,
                ];

                @$tempStats[$data->namespace][$data->date] = $key;
            }
        }
        $statistics = [];
        foreach ($tempStats as $namespace => $stats) {
            for ($i = 0; $i <= 90; $i++) {
                $date = date("Y-m-d", strtotime($startdate) + $i * 24 * 60 * 60);
                $namespaceParts = explode(".", $namespace, 2);
                $statistics[$date][$namespaceParts[0]][$namespaceParts[1]] = isset($stats[$date]["total_count"]) ? $stats[$date]["total_count"] : 0;
                if ($date == $enddate) {
                    break;
                }
            }
        }
        $apiresults = ["result" => "success", "currentDatetime" => date("Y-m-d H:i:s"), "lastDailyCronInvocationTime" => Cfg::get("lastDailyCronInvocationTime"), "startdate" => $startdate . " 00:00:00", "enddate" => $enddate . " 23:59:59", "statistics" => $statistics];

        return $apiresults;
    }

    public function GetConfigurationValue(array $params)
    {
        extract($params);
        if (!$setting) {
            return ["result" => "error", "message" => "Parameter setting is required"];
        } else {
            $currentValue = Configuration::where('setting', $setting)->first();
            if (is_null($currentValue)) {
                return ["result" => "error", "message" => "Invalid name for parameter setting"];
            } else {
                return ["result" => "success", "setting" => $setting, "value" => $currentValue->value];
            }
        }
    }

    public function GetCurrencies()
    {
        $data = Currency::all();
        $apiresults = ["result" => "success", "totalresults" => count($data)];
        foreach ($data as $r) {
            $id = $r->id;
            $code = $r->code;
            $prefix = $r->prefix;
            $suffix = $r->suffix;
            $format = $r->format;
            $rate = $r->rate;
            $default = $r->default;
            $apiresults["currencies"]["currency"][] = ["id" => $id, "code" => $code, "prefix" => $prefix, "suffix" => $suffix, "format" => $format, "rate" => $rate, "default" => $default];
        }
        return $apiresults;
    }

    public function GetEmailTemplates(array $params)
    {
        extract($params);
        $data = Emailtemplate::orderBy("name");
        if ($type) {
            $data->where("type", "=", $type);
        }
        if ($language) {
            $data->where("language", "=", $language);
        } else {
            $data->where("language", "=", "");
        }
        if ($id) {
            $data->where("id", "=", $id);
        }

        $templates = $data->get();
        $apiresults = ["result" => "success", "totalresults" => $templates->count(), "emailtemplates" => ["emailtemplate" => []]];
        foreach ($templates as $template) {
            $apiresults["emailtemplates"]["emailtemplate"][] = ["id" => $template->id, "name" => $template->name, "subject" => $template->subject, "custom" => (bool)$template->custom, "message" => $template->message];
        }

        return $apiresults;
    }

    public function GetPaymentMethods()
    {
        $gateway = $this->_getGatewaysArray();
        $apiresults = ["result" => "success", "totalresults" => count($gateway)];
        foreach ($gateway as $module => $name) {
            $apiresults["paymentmethods"]["paymentmethod"][] = ["module" => $module, "displayname" => $name];
        }
        return $apiresults;
    }

    private function _getGatewaysArray()
    {
        $Paymentgateway = Paymentgateway::orderBy("order", "ASC")->select('gateway', 'value')->get();
        $gateways = [];
        foreach ($Paymentgateway as $r) {
            $gateways[$r->gateway] = $r->value;
        }
        return $gateways;
    }

    public function GetStaffOnline()
    {
        return [];
    }

    public function GetStats(int $timeline_days)
    {
        $stats = $this->admin->getAdminHomeStats("api");
        $apiresults = ["result" => "success"];
        foreach ($stats["income"] as $k => $v) {
            $apiresults["income_" . $k] = $v;
        }

        $result = Order::where('status', 'Pending')->count();
        $apiresults["orders_pending"] = $result;

        foreach ($stats["orders"]["today"] as $k => $v) {
            $apiresults["orders_today_" . $k] = $v;
        }
        foreach ($stats["orders"]["yesterday"] as $k => $v) {
            $apiresults["orders_yesterday_" . $k] = $v;
        }
        $apiresults["orders_thismonth_total"] = $stats["orders"]["thismonth"]["total"];
        $apiresults["orders_thisyear_total"] = $stats["orders"]["thisyear"]["total"];
        $apiresults["cancellations_pending"] = $stats["cancellations"]["pending"];
        $apiresults["todoitems_due"] = $stats["todoitems"]["due"];
        $apiresults["networkissues_open"] = $stats["networkissues"]["open"];
        $apiresults["billableitems_uninvoiced"] = $stats["billableitems"]["uninvoiced"];
        $apiresults["quotes_valid"] = $stats["quotes"]["valid"];
        $result = AdminLog::where('lastvisit', '>=', date("Y-m-d H:i:s", mktime(date("H"), date("i") - 15, date("s"), date("m"), date("d"), date("Y"))))
            ->where('logouttime', '0000-00-00')
            ->distinct()
            ->count('adminusername');
        $apiresults["staff_online"] = $result;
        $apiresults["timeline_data"] = [];

        $timelineDays = $timeline_days;
        if (0 < $timelineDays && $timelineDays <= 90) {
            $acceptedOrderStatus = Orderstatus::where('showactive', '=', 1)->pluck("title");
            foreach (range(0, $timelineDays - 1) as $days) {
                $date = Carbon::today()->subDays($days)->format("Y-m-d");
                $orders = Order::whereRaw("date_format(date, '%Y-%m-%d') = " . $date);
                $timelineData["new_orders"][$date] = $orders->count();
                $timelineData["accepted_orders"][$date] = $orders->whereIn("status", $acceptedOrderStatus)->count();
                $timelineData["income"][$date] = Format::Currency(Account::whereRaw("date_format(date, '%Y-%m-%d') = " . $date)->sum("amountin"));
                $timelineData["expenditure"][$date] = Format::Currency(Account::whereRaw("date_format(date, '%Y-%m-%d') = " . $date)->sum("amountout"));
                $timelineData["new_tickets"][$date] = Ticket::whereRaw("date_format(date, '%Y-%m-%d') = " . $date)->count();
            }
            $apiresults["timeline_data"] = $timelineData;
        }

        return $apiresults;
    }

    public function GetToDoItems(array $params)
    {
        extract($params);
        if (!$limitstart) {
            $limitstart = 0;
        }
        if (!$limitnum) {
            $limitnum = 25;
        }
        $data = Todolist::orderBy('id', 'DESC');
        $count = Todolist::orderBy('id', 'DESC');

        if ($status == "Incomplete") {
            $data->where('status', '!=', 'Completed');
            $count->where('status', '!=', 'Completed');
        } else {
            if ($status) {
                $data->where('status', $status);
                $count->where('status', $status);
            }
        }
        $result = $data->skip($limitstart)->take($limitnum)->get();
        $total = $count->count();
        $apiresults = ["result" => "success", "totalresults" => $total, "startnumber" => $limitstart, "numreturned" => count($result)];

        $data = [];
        foreach ($result as $r) {
            $data["title"] = $r->title;
            $data["description"] = strip_tags($r->description);
            $apiresults["items"]["item"][] = $r->toArray();
        }

        return $apiresults;
    }

    public function GetToDoItemStatuses()
    {
        $statuses = ["New" => ["count" => 0, "overdue" => 0], "Pending" => ["count" => 0, "overdue" => 0], "In Progress" => ["count" => 0, "overdue" => 0], "Completed" => ["count" => 0, "overdue" => 0], "Postponed" => ["count" => 0, "overdue" => 0]];
        $todo_result = Todolist::selectRaw('status , COUNT(*) AS count ')->groupBy('status')->get();
        foreach ($todo_result as $r) {
            $statuses[$r->status]["count"] = $r->count;
        }

        $todo_over_due_result = Todolist::selectRaw('status , COUNT(*) AS count ')->whereRaw('DATE(duedate) <= CURDATE()')->groupBy('status')->get();
        foreach ($todo_over_due_result as $r) {
            $statuses[$r->status]["overdue"] = $r->count;
        }
        $apiresults = ["result" => "success", "totalresults" => 5];
        foreach ($statuses as $key => $status) {
            $apiresults["todoitemstatuses"]["status"][] = ["type" => $key, "count" => $status["count"], "overdue" => $status["overdue"]];
        }
        return $apiresults;
    }

    public function LogActivity(array $params)
    {
        extract($params);
        $userid = (int)$clientid;
        $description = $description;
        $ip = Request::getClientIp(true);

        static $adminUsernames = null;
        $getAdmin = Admin::find($userid);
        $adminId = @(int)$getAdmin->id;
        $contactId = @$getAdmin->uuid;
    }

    public function SetConfigurationValue(array $params)
    {
        $setting = $params['setting'];
        $value = $params['value'];

        $currentValue = Configuration::where('setting', $setting)->first();
        if (is_null($currentValue)) {
            $apiresults = ["result" => "error", "message" => "Invalid name for parameter setting"];
        } else {
            $apiresults = [];
            $apiresults["result"] = "success";
            if ($value != $currentValue->value || $value == null) {
                $currentValue->value = $value;
                $currentValue->save();
            }
        }

        return $apiresults;
    }

    public function UpdateAnnouncement(array $params)
    {
        extract($params);
        $result = \App\Models\Announcement::find($announcementid);
        if (is_null($result)) {
            $apiresults = ["result" => "error", "message" => "Announcement ID Not Found"];
            return $apiresults;
        }

        $title = \App\Helpers\Sanitize::decode($title);
        $announcement = \App\Helpers\Sanitize::decode($announcement);

        if ($title) {
            $result->title = $title;
        }
        if (0 < strlen(trim($date))) {
            $result->date = $date;
        }
        if (0 < strlen(trim($announcement))) {
            $result->announcement = $announcement;
        }
        if (0 < strlen(trim($published))) {
            $result->published = $published;
        }

        $result->save();
        event(new AnnouncementEdit($params));
        return ["result" => "success", "announcementid" => $announcementid];
    }

    public function UpdateToDoItem(array $params)
    {
        extract($params);
        $getData = \App\Models\Todolist::find($itemid);
        if (is_null($getData)) {
            return ["result" => "error", "message" => "TODO Item ID Not Found"];
        } else {
            if ($date) {
                $getData->date = $date;
            }
            if ($title) {
                $getData->title = $title;
            }
            if ($description) {
                $getData->description = $description;
            }
            if ($adminid) {
                $getData->admin = $adminid;
            }
            if ($status) {
                $getData->status = $status;
            }
            if ($duedate) {
                $getData->duedate = $this->toMySQLDate($duedate);
            }

            $getData->save();
        }

        return ["result" => "success", "itemid" => $itemid];
    }

    function toMySQLDate($date)
    {
        switch (Cfg::get("DateFormat")) {
            case "MM/DD/YYYY":
                $day = substr($date, 3, 2);
                $month = substr($date, 0, 2);
                $year = substr($date, 6, 4);
                $hours = substr($date, 11, 2);
                $minutes = substr($date, 14, 2);
                $seconds = substr($date, 17, 2);
                break;
            case "YYYY-MM-DD":
            case "YYYY/MM/DD":
                $day = substr($date, 8, 2);
                $month = substr($date, 5, 2);
                $year = substr($date, 0, 4);
                $hours = substr($date, 11, 2);
                $minutes = substr($date, 14, 2);
                $seconds = substr($date, 17, 2);
                break;
            default:
                $day = substr($date, 0, 2);
                $month = substr($date, 3, 2);
                $year = substr($date, 6, 4);
                $hours = substr($date, 11, 2);
                $minutes = substr($date, 14, 2);
                $seconds = substr($date, 17, 2);
        }
        $day = sprintf("%02d", $day);
        $month = sprintf("%02d", $month);
        $year = sprintf("%04d", $year);
        $date = $year . "-" . $month . "-" . $day;
        if ($hours) {
            $hours = sprintf("%02d", $hours);
            $minutes = sprintf("%02d", $minutes);
            $seconds = sprintf("%02d", $seconds);
            $date .= " " . $hours . ":" . $minutes . ":" . $seconds;
        }
        return $date;
    }

    public function SendEmail(array $param)
    {
        $params = [
            'messagename' => $param['messagename'] ?? '',
            'id' => (int)$param['id'] ?? '',
            'customtype' => $param['customtype'] ?? '',
            'custommessage' => $param['custommessage'] ?? '',
            'customsubject' => $param['customsubject'] ?? '',
            'customvars' => $param['customvars'] ?? [],
            'nonl2br' => $param['nonl2br'] ?? ''
        ];
    
        $validCustomEmailTypes = ['general', 'product', 'domain', 'invoice', 'support', 'affiliate'];
        extract($params);
    
        if (!$messagename && !$customtype) {
            return ['result' => 'error', 'message' => 'You must provide either an existing email template name or a custom message type'];
        }
    
        if ($customtype) {
            if (!in_array($customtype, $validCustomEmailTypes)) {
                return ["result" => "error", "message" => "Invalid message type provided"];
            }
            if (!$customsubject) {
                return ["result" => "error", "message" => "A subject is required for a custom message"];
            }
            if (!$custommessage) {
                return ["result" => "error", "message" => "A message body is required for a custom message"];
            }
        }
    
        if (!$id || !is_numeric($id)) {
            return ["result" => "error", "message" => "A related ID is required"];
        }
    
        if ($customtype) {
            $messageBody = \App\Helpers\Sanitize::decode($custommessage);
            if (!$nonl2br) {
                $messageBody = nl2br($messageBody);
            }
            \App\Models\Emailtemplate::where('name', '=', $messagename)->delete();
            $template = new \App\Models\Emailtemplate();
            $template->type = $customtype;
            $template->name = "Mass Mail Template";
            $template->subject = $customsubject;
            $template->message = $messageBody;
            $template->plaintext = false;
            $template->disabled = false;
        } else {
            $template = \App\Models\Emailtemplate::where('name', $messagename)->where('language', '=', '')->first();
    
            if (is_null($template)) {
                return ["result" => "error", "message" => "Email Template not found"];
            }
    
            if ($template->disabled) {
                return ["result" => "error", "message" => "Email Template is disabled"];
            }
        }
    
        $custom = [];
    
        if ($customvars) {
            if (is_array($customvars)) {
                $custom = $customvars;
            } else {
                $client = new \App\Helpers\Client();
                $custom = $client->safe_unserialize(base64_decode($customvars));
            }
        }
    
        $paramEmail = [];
        $email = '';
    
        switch ($template->type) {
            case 'product':
                $product = DB::table($this->prefix . 'hosting as h')
                    ->join($this->prefix . 'clients as c', 'h.userid', '=', 'c.id')
                    ->join($this->prefix . 'servers as s', 'h.server', '=', 's.id')
                    ->select('h.*', 'c.firstname', 'c.lastname', 'c.email', 's.name as servername', 's.ipaddress as service_server_ip')
                    ->where('h.id', $id)->first();
    
                if (!$product) {
                    return ["result" => "error", "message" => "Product not found"];
                }
    
                $paramEmail = [
                    '{$client_name}' => $product->firstname . ' ' . $product->lastname,
                    '{$service_first_payment_amount}' => $product->firstpaymentamount,
                    '{service_product_name}' => 'Hosting A',
                    '{service_domain}' => $product->domain,
                    '{service_ns1}' => $product->domain,
                ];
    
                $email = $product->email;
                break;
    
            // Add other cases if needed
        }
    
        if (empty($email)) {
            return ["result" => "error", "message" => "Email address not found for the product"];
        }
    
        $body = $this->replaceEmailContent($template->message, $paramEmail);
    
        $emailData = [
            'to' => $email,
            'subject' => $template->subject,
            'body' => $body
        ];
    
        new Mail($emailData);
        return ["result" => "success"];
    }
    
    public function replaceEmailContent($content, $post = [])
    {
        foreach ($post as $k => $v) {
            $content = str_replace($k, $v, $content);
        }
        return $content;
    }

}