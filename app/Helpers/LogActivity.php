<?php
namespace App\Helpers;

use App\Helpers\Hooks;

// Import Model Class here
use App\Models\ActivityLog;

// Import Package Class here
use App\Events\LogActivities;

// Import Laravel Class here
use Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LogActivity
{
	// protected $request;

	public function __construct(Request $request)
	{
		// $this->request = $request;
	}

	/**
	 * Save
	 * 
	 * Save log string
	 */
	public static function Save($description, $userid = 0)
	{
		global $remote_ip;
		$remote_ip = Request::ip();
		$admin = Auth::guard('admin')->user();
		$user = Auth::guard('web')->user();
        static $adminUsernames = NULL;
        $adminId = $admin ? $admin->id : 0;
        $contactId = session('cid') ? session('cid') : 0;
        $userid = $user ? $user->id : 0;
        if (!is_null($adminId)) {
            if (!isset($adminUsernames[$adminId])) {
				$result = \App\Models\Admin::select('username')->where(array("id" => $adminId));
                $data = $result;
                $adminUsernames[$adminId] = $data->value("username") ?? "";
            }
            $username = $adminUsernames[$adminId];
        } else {
            if (\App\Helpers\Application::isApiRequest()) {
                $username = "Local API User";
            } else {
                if (!is_null($userid) && !is_null($contactId)) {
                    $username = "Sub-Account " . $contactId;
                } else {
                    if (!is_null($userid)) {
                        $username = "Client";
                    } else {
                        $username = "System";
                    }
                }
            }
        }
        if (!$userid && defined("CLIENTAREA") && $user) {
            $userid = $user->id;
        }
        if (strpos($description, "password") !== false) {
            $description = preg_replace("/(password(?:hash)?`=')(.*)(',|' )/", "\${1}--REDACTED--\${3}", $description);
        }
        ActivityLog::insert(array("date" => \Carbon\Carbon::now(), "description" => $description, "user" => $username, "userid" => $userid, "ipaddr" => $remote_ip));
        if (function_exists("run_hook")) {
            Hooks::run_hook("LogActivity", array("description" => $description, "user" => $username, "userid" => (int) $userid, "ipaddress" => $remote_ip));
        }
	}
}
