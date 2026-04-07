<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\Format;
use App\Models\Currency;
use App\Models\Client;
use App\Models\Account;
use App\Models\Admin;
use Auth;

class AdminFunctions
{
	public function __construct(){
		$this->prefix=Database::prefix();

	}

	public function getAdminHomeStats($type = "")
	{
		global $currency;
		$stats = array();
		$currency = \App\Helpers\format::getCurrency(0, 1);
		if (!$type || in_array($type, array("income", "api"))) {
			$todaysincome = \App\Models\Account::selectRaw("SUM((amountin-fees-amountout)/rate) AS sum")->whereRaw("date LIKE '" . date("Y-m-d") . "%'")->value("sum") ?? 0;
			$stats["income"]["today"] = \App\Helpers\Format::formatCurrency($todaysincome);
			$todaysincome = \App\Models\Account::selectRaw("SUM((amountin-fees-amountout)/rate) AS sum")->whereRaw("date LIKE '" . date("Y-m-") . "%'")->value("sum") ?? 0;
			$stats["income"]["thismonth"] = \App\Helpers\Format::formatCurrency($todaysincome);
			$todaysincome = \App\Models\Account::selectRaw("SUM((amountin-fees-amountout)/rate) AS sum")->whereRaw("date LIKE '" . date("Y-") . "%'")->value("sum") ?? 0;
			$stats["income"]["thisyear"] = \App\Helpers\Format::formatCurrency($todaysincome);
			$todaysincome = \App\Models\Account::selectRaw("SUM((amountin-fees-amountout)/rate) AS sum")->value("sum") ?? 0;
			$stats["income"]["alltime"] = \App\Helpers\Format::formatCurrency($todaysincome);
			if ($type == "income") {
				return $stats;
			}
		}
		$result = (array) DB::select(DB::raw("SELECT SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM tblaccounts WHERE tblaccounts.invoiceid=tblinvoices.id)),0) as total FROM tblinvoices WHERE tblinvoices.status='Unpaid' AND duedate<'" . date("Ymd") . "'"));
		$data = $result;
		// list($overdueinvoices, $stats["invoices"]["overduebalance"]) = $data;
		$stats["invoices"]["overduebalance"] = $data[0]->total;
		$result = DB::select(DB::raw("SELECT COUNT(*) as total FROM tblcancelrequests INNER JOIN tblhosting ON tblhosting.id=tblcancelrequests.relid WHERE (tblhosting.domainstatus!='Cancelled' AND tblhosting.domainstatus!='Terminated')"));
		$data = $result;
		$stats["cancellations"]["pending"] = $data[0]->total;
		$stats["orders"]["today"]["cancelled"] = 0;
		$stats["orders"]["today"]["pending"] = $stats["orders"]["today"]["cancelled"];
		$stats["orders"]["today"]["fraud"] = $stats["orders"]["today"]["pending"];
		$stats["orders"]["today"]["active"] = $stats["orders"]["today"]["fraud"];
		$query = "SELECT status,COUNT(*) as total FROM tblorders WHERE date LIKE '" . date("Y-m-d") . "%' GROUP BY status";
		$result = DB::select(DB::raw($query));
		foreach ($result as $data) {
			$stats["orders"]["today"][preg_replace("/[^a-z0-9_]+/", "_", strtolower($data->status))] = $data->total;
		}
		$stats["orders"]["today"]["total"] = $stats["orders"]["today"]["active"] + $stats["orders"]["today"]["fraud"] + $stats["orders"]["today"]["pending"] + $stats["orders"]["today"]["cancelled"];
		$stats["orders"]["yesterday"]["cancelled"] = 0;
		$stats["orders"]["yesterday"]["pending"] = $stats["orders"]["yesterday"]["cancelled"];
		$stats["orders"]["yesterday"]["fraud"] = $stats["orders"]["yesterday"]["pending"];
		$stats["orders"]["yesterday"]["active"] = $stats["orders"]["yesterday"]["fraud"];
		$query = "SELECT status,COUNT(*) as total FROM tblorders WHERE date LIKE '" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) . "%' GROUP BY status";
		$result = DB::select(DB::raw($query));
		foreach ($result as $data) {
			$stats["orders"]["yesterday"][preg_replace("/[^a-z0-9_]+/", "_", strtolower($data->status))] = $data->total;
		}
		$stats["orders"]["yesterday"]["total"] = $stats["orders"]["yesterday"]["active"] + $stats["orders"]["yesterday"]["fraud"] + $stats["orders"]["yesterday"]["pending"] + $stats["orders"]["yesterday"]["cancelled"];
		$query = "SELECT COUNT(*) as total FROM tblorders WHERE date LIKE '" . date("Y-m-") . "%'";
		$result = DB::select(DB::raw($query));
		$data = $result;
		$stats["orders"]["thismonth"]["total"] = $data[0]->total;
		$query = "SELECT COUNT(*) as total FROM tblorders WHERE date LIKE '" . date("Y-") . "%'";
		$result = DB::select(DB::raw($query));
		$data = $result;
		$stats["orders"]["thisyear"]["total"] = $data[0]->total;
		global $disable_admin_ticket_page_counts;
		if (!$disable_admin_ticket_page_counts) {
			$ticketStats = (new \App\Helpers\HelperApi)->localAPI("GetTicketCounts", $type == "api" ? array("includeCountsByStatus" => true) : array());
			$stats["tickets"]["allactive"] = $ticketStats["allActive"];
			$stats["tickets"]["awaitingreply"] = $ticketStats["awaitingReply"];
			$stats["tickets"]["flaggedtickets"] = $ticketStats["flaggedTickets"];
			foreach ($ticketStats["status"] ?? [] as $status => $count) {
				$stats["tickets"][$status] = $count;
			}
		}
		$query = "SELECT COUNT(*) as total FROM tbltodolist WHERE status!='Completed' AND status!='Postponed' AND duedate<='" . date("Y-m-d") . "'";
		$result = DB::select(DB::raw($query));
		$data = $result;
		$stats["todoitems"]["due"] = $data[0]->total;
		$query = "SELECT COUNT(*) as total FROM tblnetworkissues WHERE status!='Scheduled' AND status!='Resolved'";
		$result = DB::select(DB::raw($query));
		$data = $result;
		$stats["networkissues"]["open"] = $data[0]->total;
		$result = \App\Models\Billableitem::where(array("invoicecount" => "0"))->count();
		$data = $result;
		$stats["billableitems"]["uninvoiced"] = $data;
		$result = \App\Models\Quote::where("validuntil", ">", date("Ymd"))->count();
		$data = $result;
		$stats["quotes"]["valid"] = $data;
		return $stats;
	}
	public function getAdminHomeStatsOLD($type=''){
		$stats = array();
		$currency=Currency::where('default',1)->first();
		//$currency=$this->getCurrency(0,1);
		//dd($currency);
		//DB::enableQueryLog(); 
		if (!$type || in_array($type, array("income", "api"))) {
			$todaysincome=Account::selectRaw('SUM((amountin-fees-amountout)) as total')->whereDate('date',date('Y-m-d'))->first();
			$stats["income"]["today"] = Format::Currency($todaysincome->total);
			$todaysincome=Account::select(DB::raw('SUM((amountin-fees-amountout)/rate) as total'))->whereMonth('date',date('m'))->whereYear('date',date('Y'))->first();
			$stats["income"]["thismonth"] =  Format::Currency($todaysincome->total);

			$todaysincome=Account::select(DB::raw('SUM((amountin-fees-amountout)/rate) as total'))->whereYear('date',date('Y'))->first();
			$stats["income"]["thisyear"] =  Format::Currency($todaysincome->total); 
			if ($type == "income") {
				return $stats;
			}
		}

		$data=DB::table($this->prefix.'invoices')
				->where($this->prefix.'invoices.status','=','Unpaid')
				->where($this->prefix.'invoices.duedate','<',date("Ymd"))
				->select(DB::raw('SUM(total)-COALESCE(SUM((SELECT SUM(amountin) FROM '.$this->prefix.'accounts WHERE '.$this->prefix.'accounts.invoiceid='.$this->prefix.'invoices.id)),0)'))
				->get();
		$data=(array) json_decode($data,true);
		@list($overdueinvoices, $stats["invoices"]["overduebalance"]) = $data;

		//$result = full_query("SELECT COUNT(*) FROM tblcancelrequests INNER JOIN tblhosting ON tblhosting.id=tblcancelrequests.relid WHERE (tblhosting.domainstatus!='Cancelled' AND tblhosting.domainstatus!='Terminated')");
   		//$data = mysql_fetch_array($result);

		$data=DB::table($this->prefix.'cancelrequests')
				->join($this->prefix.'hosting',$this->prefix.'cancelrequests.relid','=',$this->prefix.'hosting.id')
				->where($this->prefix.'hosting.domainstatus','!=','Cancelled')
				->where($this->prefix.'hosting.domainstatus','!=','Terminated')
				->count();
		$stats["cancellations"]["pending"] = $data;
		$stats["orders"]["today"]["cancelled"] = 0;
		$stats["orders"]["today"]["pending"] = $stats["orders"]["today"]["cancelled"];
		$stats["orders"]["today"]["fraud"] = $stats["orders"]["today"]["pending"];
		$stats["orders"]["today"]["active"] = $stats["orders"]["today"]["fraud"];

		//$query = "SELECT status,COUNT(*) FROM tblorders WHERE date LIKE '" . date("Y-m-d") . "%' GROUP BY status";
   		//$result = full_query($query);

		$result=DB::table($this->prefix.'orders')
				->whereDate('date', date("Y-m-d"))
				->selectRaw('status, COUNT(*) as tt')
				->groupBy('status')
				->get();
	//	dd($result);
		foreach($result as $r){
			$stats["orders"]["today"][preg_replace("/[^a-z0-9_]+/", "_", strtolower($r->status))] = $r->tt;
		}
		$stats["orders"]["today"]["total"] = $stats["orders"]["today"]["active"] + $stats["orders"]["today"]["fraud"] + $stats["orders"]["today"]["pending"] + $stats["orders"]["today"]["cancelled"];
		$stats["orders"]["yesterday"]["cancelled"] = 0;
		$stats["orders"]["yesterday"]["pending"] = $stats["orders"]["yesterday"]["cancelled"];
		$stats["orders"]["yesterday"]["fraud"] = $stats["orders"]["yesterday"]["pending"];
		$stats["orders"]["yesterday"]["active"] = $stats["orders"]["yesterday"]["fraud"];

		//$query = "SELECT status,COUNT(*) FROM tblorders WHERE date LIKE '" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))) . "%' GROUP BY status";
   		//$result = full_query($query);

		$result=DB::table($this->prefix.'orders')
		   ->where('date','like',  date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))).'%')
		   ->selectRaw('status, COUNT(*) as tt')
		   ->groupBy('status')
		   ->get();
		foreach($result as $r){
			$stats["orders"]["yesterday"][preg_replace("/[^a-z0-9_]+/", "_",strtolower($r->status))] = $r->tt;
		}
		$stats["orders"]["yesterday"]["total"] = $stats["orders"]["yesterday"]["active"] + $stats["orders"]["yesterday"]["fraud"] + $stats["orders"]["yesterday"]["pending"] + $stats["orders"]["yesterday"]["cancelled"];

		$result=DB::table($this->prefix.'orders')
		   ->whereYear('date',date('Y'))
		   ->whereMonth('date',date('m'))
		   ->groupBy('status')
		   ->count();
		$stats["orders"]["thismonth"]["total"] = $result;
		//dd($result);

		$result=DB::table($this->prefix.'orders')
		   ->whereYear('date',date('Y'))
		   ->groupBy('status')
		   ->count();
		   $stats["orders"]["thisyear"]["total"] = $result;
		//print_r($stats);
		/* if (!$disable_admin_ticket_page_counts) {
			$ticketStats = localAPI("GetTicketCounts", $type == "api" ? array("includeCountsByStatus" => true) : array());
			$stats["tickets"]["allactive"] = $ticketStats["allActive"];
			$stats["tickets"]["awaitingreply"] = $ticketStats["awaitingReply"];
			$stats["tickets"]["flaggedtickets"] = $ticketStats["flaggedTickets"];
			foreach ($ticketStats["status"] as $status => $count) {
				$stats["tickets"][$status] = $count;
			}
		} */
		
		$result=DB::table($this->prefix.'todolist')
		   ->where('status','!=','Completed')
		   ->where('status','!=','Postponed')
		   ->where('duedate','<=', date("Y-m-d") )
		   ->count();
		$stats["todoitems"]["due"] = $result;


		$result=DB::table($this->prefix.'networkissues')
		   ->where('status','!=','Scheduled')
		   ->where('status','!=','Resolved')
		   ->count();
		$stats["networkissues"]["open"] = $result;


		$result=DB::table($this->prefix.'billableitems')
		   ->where('invoicecount','=',0)
		   ->count();
		   $stats["billableitems"]["uninvoiced"] = $result;

		$result=DB::table($this->prefix.'quotes')
		->where('validuntil','>',date("Ymd"))
		->count();
		$stats["quotes"]["valid"] = $result;
		

		return $stats;
	}
	

	public function getCurrency($userid = "", $cartcurrency = "")
    {
        static $usercurrencies = array();
        static $currenciesdata = array();
        if ($cartcurrency) {
            $currencyid = $cartcurrency;
        }
        if ($userid) {
            if (isset($usercurrencies[$userid])) {
                $currencyid = $usercurrencies[$userid];
            } else {
				$usercurrencies[$userid] =Client::select('currency')->find($userid)->currency;
                $currencyid = $usercurrencies[$userid];
            }
        }
        if (isset($currencyid)) {
            if (isset($currenciesdata[$currencyid])) {
                $data = $currenciesdata[$currencyid];
            } else {
				$data[$currencyid]=Currency::find($currencyid)->toArray();
				$data=$data[$currencyid];
			}
        } else {
			$data=Currency::where('default',1)->first()->toArray();
        }
        $currency_array = array("id" => $data["id"], "code" => $data["code"], "prefix" => $data["prefix"], "suffix" => $data["suffix"], "format" => $data["format"], "rate" => $data["rate"]);
        return $currency_array;
    }

    /**
     * logAdminActivity
     */
	public static function logAdminActivity($description)
    {
		\App\Helpers\LogActivity::Save($description);
	}

	public static function getServerDropdownOptions($selectedServerId = 0)
    {
		$pfx = (new self())->prefix;
        $servers = $disabledServers = "";
        $serverData = \DB::table("{$pfx}servers")->orderBy("name")->get(["id", "name", "disabled"]);

        foreach ($serverData as $server) {
            $id = $server->id;
            $serverName = $server->name;
            $serverDisabled = $server->disabled;
            if ($serverDisabled) {
                $serverName .= " (" . __("admin.emailtplsdisabled") . ")";
            }
            $selected = "";
            if ($selectedServerId == $id) {
                $selected .= "selected=\"selected\"";
            }
            $serverTemp = "<option value=\"" . $id . "\" " . $selected . ">" . $serverName . "</option>";
            if ($serverDisabled) {
                $disabledServers .= $serverTemp;
            } else {
                $servers .= $serverTemp;
            }
        }
		
        return [
			"servers" => $servers, 
			"disabledServers" => $disabledServers
		];
    }

    public static function infoBox($title, $description, $status = "info")
    {
        global $infobox;

        if ($status == "error" || $status == "success") {
            $class = $status . "box";
        } else {
            $class = "infobox";
        }

        $infobox = sprintf("<div class=\"%s\"><strong><span class=\"title\">%s</span></strong><br />%s</div>", $class, $title, $description);
        
        return $infobox;
    }

    public static function infoBoxMessage($title, $description)
    {
        return sprintf("<span class=\"title\">%s</span>:&nbsp;%s", __($title), __($description));
    }

	public static function checkPermissionOLD($action, $noredirect = "")
	{
		if (!$noredirect) {
			return auth()->guard("admin")->user()->checkPermissionTo($action);
		}

		// TODO: Implement redirect view 
		return abort(403, self::getNoPermissionMessage());
	}
	public static function checkPermission($action, $noredirect = "")
	{
		return auth()->guard("admin")->user()->checkPermissionTo($action);
	}

	public static function getNoPermissionMessage()
	{
		return __("admin.permissionsaccessdenied") . " - " . __("admin.permissionsnopermission");
	}

	public static function getAdminName($adminId = 0)
	{
		static $adminNames = NULL;
		if (!$adminNames) {
			$adminNames = array();
		}

		$adminId = $adminId ?: Auth::guard('admin')->user()->id;
		
		if (!empty($adminNames[$adminId])) {
			return $adminNames[$adminId];
		}

		// get_query_vals("tbladmins", "firstname,lastname", array("id" => $adminId));
		$data = Admin::select("firstname", "lastname")->find($adminId)->toArray();
		$adminName = trim($data["firstname"] . " " . $data["lastname"]);
		$adminNames[$adminId] = $adminName;

		return $adminName;
	}

    /**
     * replacePasswordWithMasks
     */
    public static function replacePasswordWithMasks($password)
    {
        if (0 < strlen($password)) {
            return str_pad("", strlen($password), "*");
        }
        return "";
    }

    /**
     * interpretMaskedPasswordChangeForStorage
     */
    public static function interpretMaskedPasswordChangeForStorage($newPassword, $originalPassword)
    {
        if (!$newPassword) {
            return "";
        }
        if (self::hasmaskedpasswordchanged($newPassword, $originalPassword)) {
            return (new \App\Helpers\Pwd)->encrypt(\App\Helpers\Sanitize::decode($newPassword));
        }
        return false;
    }

    /**
     * hasMaskedPasswordChanged
     */
    public static function hasMaskedPasswordChanged($newPassword, $originalPassword)
    {
        $passwordInputIsOnlyMask = str_replace("*", "", $newPassword) == "";
        $passwordInputIsMaskExactlyAsLongAsPreviousPassword = strlen($newPassword) == strlen($originalPassword);
        $previousPasswordIsOnlyMaskMarks = str_replace("*", "", $originalPassword) == "";
        if (!$originalPassword && $newPassword || !($passwordInputIsMaskExactlyAsLongAsPreviousPassword && $passwordInputIsOnlyMask) || $originalPassword && !$passwordInputIsMaskExactlyAsLongAsPreviousPassword && !$passwordInputIsOnlyMask) {
            return true;
        }
        return false;
    }
}
