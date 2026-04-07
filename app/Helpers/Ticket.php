<?php
namespace App\Helpers;

use DB;
use Auth;
use Cfg, LogActivity, Customfield;

// Import Model Class here
use App\Models\Ticket as TicketModel;
use App\Models\Ticketdepartment;
use App\Models\Ticketnote;
use App\Models\Ticketfeedback;
use App\Models\Contact;
use App\Models\Client;
use App\Models\Ticketreply;
use App\Models\Tickettag;
use App\Models\Ticketlog;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Application;

class Ticket
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * GetDepartmentName
	 * 
	 */
	public static function GetDepartmentName($deptId)
	{
		static $departmentNames = NULL;
    if (is_null($departmentNames)) {
      $departmentNames = Ticketdepartment::all()->pluck("name", "id")->toArray();
    }
    $departmentName = "";
    if (array_key_exists($deptId, $departmentNames)) {
			$departmentName = $departmentNames[$deptId];
    }
    return $departmentName;
	}

	/**
	 * OpenNewTicket
	 * 
	 */
	public static function openNewTicket($userid, $contactid, $deptid, $tickettitle, $message, $urgency, $attachmentsString = "", array $from = array(), $relatedservice = "", $ccemail = "", $noemail = "", $admin = "", $markdown = false)
	{
		global $CONFIG;
		$result = \App\Models\Ticketdepartment::where(array("id" => $deptid));
		$data = $result;
		$deptid = $data->value("id");
		$noautoresponder = $data->value("noautoresponder");
		if (!$deptid) {
			exit("Department Not Found. Exiting.");
		}
		$ccemail = trim($ccemail);
		$tickettitle = self::processutf8mb4($tickettitle);
		$message = self::processutf8mb4($message);
		if ($userid) {
			$name = $email = "";
			if (0 < $contactid) {
				$data = \App\Models\Contact::where(array("id" => $contactid, "userid" => $userid));
				$ccemail .= $ccemail ? "," . $data->value("email") : $data->value("email");
			} else {
				$data = \App\Models\Client::where(array("id" => $userid));
			}
			if ($admin) {
				$message = str_replace("[NAME]", $data->value("firstname") . " " . $data->value("lastname"), $message);
				$message = str_replace("[FIRSTNAME]", $data->value("firstname"), $message);
				$message = str_replace("[EMAIL]", $data->value("email"), $message);
			}
			$clientname = $data->value("firstname") . " " . $data->value("lastname");
		} else {
			if ($admin) {
				$message = str_replace("[NAME]", $from["name" ?? ""], $message);
				$message = str_replace("[FIRSTNAME]", current(explode(" ", $from["name"] ?? "")), $message);
				$message = str_replace("[EMAIL]", $from["email"] ?? "", $message);
			}
			$clientname = $from["name"];
		}
		// $ccemail = implode(",", array_unique(explode(",", $ccemail)));
		$ccemail = array_unique(explode(",", $ccemail));
		foreach ($ccemail as $key => $value) {
			if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
				unset($ccemail[$key]);
			}
		}
		$ccemail = implode(",", $ccemail);
		$length = 8;
		$seeds = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$c = NULL;
		$seeds_count = strlen($seeds) - 1;
		for ($i = 0; $i < $length; $i++) {
			$c .= $seeds[rand(0, $seeds_count)];
		}
		$tid = self::genTicketMask();
		if (!in_array($urgency, array("High", "Medium", "Low"))) {
			$urgency = "Medium";
		}
		$editor = $markdown ? "markdown" : "plain";
		$table = "tbltickets";
		$array = array("tid" => $tid, "userid" => $userid, "contactid" => $contactid, "did" => $deptid, "date" => \Carbon\Carbon::now(), "title" => $tickettitle, "message" => $message, "urgency" => $urgency, "status" => "Open", "attachment" => $attachmentsString, "lastreply" => \Carbon\Carbon::now(), "name" => $from["name"] ?? "", "email" => $from["email"] ?? "", "c" => $c, "clientunread" => "1", "adminunread" => "", "service" => $relatedservice, "cc" => $ccemail, "editor" => $editor);
		if ($admin) {
			$array["admin"] = \App\Helpers\AdminFunctions::getAdminName();
		}
		$id = DB::table($table)->insertGetId($array);
		$tid = self::genTicketMask($id);
		\App\Models\Ticket::where(array("id" => $id))->update(array("tid" => $tid));
		if (!$noemail) {
			if ($admin) {
				\App\Helpers\Functions::sendMessage("Support Ticket Opened by Admin", $id);
			} else {
				if (!$noautoresponder) {
					\App\Helpers\Functions::sendMessage("Support Ticket Opened", $id);
				}
			}
		}
		$deptname = self::getdepartmentname($deptid);
		if (!$noemail) {
			$changes = array();
			$changes["Opened"] = array("new" => $message);
			$changes["Who"] = $admin ? $array["admin"] : $clientname;
			if ($attachmentsString) {
				$changes["Attachments"] = self::ticketgenerateattachmentslistfromstring($attachmentsString);
			}
			\App\Helpers\Tickets::notifyTicketChanges($id, $changes, self::getDepartmentNotificationIds($deptid));
		}
		if ($admin) {
			self::addticketlog($id, "New Support Ticket Opened");
		} else {
			self::addticketlog($id, "New Support Ticket Opened");
		}
		if ($admin) {
			\App\Helpers\Hooks::run_hook("TicketOpenAdmin", array("ticketid" => $id, "ticketmask" => $tid, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency));
		} else {
			\App\Helpers\Hooks::run_hook("TicketOpen", array("ticketid" => $id, "ticketmask" => $tid, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency));
		}
		return array("ID" => $id, "TID" => $tid, "C" => $c, "Subject" => $tickettitle);
	}

	/**
	 * AddReply
	 */
	public static function AddReplyOLD($ticketid, $userid, $contactid, $message, $admin, $attachmentsString = "", $from = "", $status = "", $noemail = "", $api = false, $markdown = false, $changes = array())
	{
        global $CONFIG;
		if (!is_array($from)) {
			$from = array("name" => "", "email" => "");
		}
		$adminname = "";
		$message = $message;

		if ($admin) {
			$data = TicketModel::select('userid','contactid','name','email')->where('id', $ticketid)->first();
			if (0 < $data->userid) {
				if (0 < $data->contactid) {
					$data = Contact::select('firstname','lastname','email')->where('id', $data->contactid)->where('userid', $data->userid)->first();
				} else {
					$data = Client::select('firstname','lastname','email')->where('id', $data->userid)->first();
				}
				$message = str_replace("[NAME]", $data->firstname . " " . $data->lastname, $message);
				$message = str_replace("[FIRSTNAME]", $data->firstname, $message);
				$message = str_replace("[EMAIL]", $data->email, $message);
			} else {
				$message = str_replace("[NAME]", $data->name, $message);
				$message = str_replace("[FIRSTNAME]", current(explode(" ", $data->name)), $message);
				$message = str_replace("[EMAIL]", $data->email, $message);
			}
			// TODO: getAdminName((int) $admin)
			$adminname = $api ? $admin : "";
		}

		$editor = $markdown ? "markdown" : "plain";

		$ticketreplyid = new Ticketreply;
		$ticketreplyid->tid = $ticketid;
		$ticketreplyid->userid = $userid;
		$ticketreplyid->contactid = $contactid ?? 0;
		$ticketreplyid->name = $from["name"];
		$ticketreplyid->email = $from["email"];
		$ticketreplyid->date = \Carbon\Carbon::now();
		$ticketreplyid->message = $message;
		$ticketreplyid->admin = $adminname;
		$ticketreplyid->attachment = $attachmentsString;
		$ticketreplyid->editor = $editor;
		$ticketreplyid->save();

		$data = TicketModel::find($ticketid, ["tid", "did", "title", "urgency", "flag", "status"]);
		$tid = $data->tid;
		$deptid = $data->did;
		$tickettitle = $data->title;
		$urgency = $data->urgency;
		$flagadmin = $data->flag;
		$oldStatus = $data->status;

		if ($userid || $contactid) {
			$clientname = $contactid ? Contact::find($contactid)->fullName : Client::find($userid)->fullName;
		} else {
			$clientname = $from["name"];
		}
		
		$deptname = self::GetDepartmentName($deptid);

		if ($admin) {
			if ($status == "") {
				$status = "Answered";
			}
			$updateqry = array("status" => $status, "clientunread" => "1", "lastreply" => \Carbon\Carbon::now());
			if (isset($CONFIG["TicketLastReplyUpdateClientOnly"]) && $CONFIG["TicketLastReplyUpdateClientOnly"]) {
				unset($updateqry["lastreply"]);
			}
			\App\Models\Ticket::where('id', $ticketid)->update($updateqry);
			self::addticketlog($ticketid, "New Ticket Response");
			if (!$noemail) {
				\App\Helpers\Functions::sendMessage("Support Ticket Reply", $ticketid, $ticketreplyid);
			}
			\App\Helpers\Hooks::run_hook("TicketAdminReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "admin" => $adminname, "status" => $status));
		} else {
			$status = "Customer-Reply";
			$updateqry = array("status" => "Customer-Reply", "clientunread" => "1", "adminunread" => "", "lastreply" => \Carbon\Carbon::now());
			$UpdateLastReplyTimestamp = Cfg::get("UpdateLastReplyTimestamp");
			if ($UpdateLastReplyTimestamp == "statusonly" && ($oldStatus == $status || $oldStatus == "Open" && $status == "Customer-Reply")) {
				unset($updateqry["lastreply"]);
			}
			\App\Models\Ticket::where('id', $ticketid)->update($updateqry);
			self::addticketlog($ticketid, "New Ticket Response made by User");
			\App\Helpers\Hooks::run_hook("TicketUserReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "status" => $status));
		}
		if ($oldStatus != $status) {
			$changes["Status"] = array("old" => $oldStatus, "new" => $status);
		}
		$changes["Reply"] = array("new" => $message);
		if ($attachmentsString) {
			$changes["Attachments"] = self::ticketgenerateattachmentslistfromstring($attachmentsString);
		}
		$recipients = array();
		if (!$admin) {
			$changes["Who"] = $clientname;
			// $recipients = $flagadmin ? array($flagadmin) : !$noemail ? self::getDepartmentNotificationIds($deptid) : array();
			if ($flagadmin) {
				$recipients = array($flagadmin);
			} else {
				if (!$noemail) {
					$recipients = self::getDepartmentNotificationIds($deptid);
				} else {
					$recipients = array();
				}
			}
		} else {
			$changes["Who"] = $adminname;
		}
		// TODO: WHMCS\Tickets::notifyTicketChanges($ticketid, $changes, $recipients);
	}
	public static function AddReply($ticketid, $userid, $contactid, $message, $admin, $attachmentsString = "", $from = "", $status = "", $noemail = "", $api = false, $markdown = false, $changes = array())
	{
		global $CONFIG;
		if (!is_array($from)) {
			$from = array("name" => "", "email" => "");
		}
		$adminname = "";
		$message = self::processutf8mb4($message);
		if ($admin) {
			$data = \App\Models\Ticket::where(array("id" => $ticketid));
			if (0 < ($data->value("userid") ?? 0)) {
				if (0 < ($data->value("contactid") ?? 0)) {
					$data = \App\Models\Contact::where(array("id" => $data->value("contactid"), "userid" => $data->value("userid")));
				} else {
					$data = \App\Models\Client::where(array("id" => $data->value("userid")));
				}
				$message = str_replace("[NAME]", $data->value("firstname") . " " . $data->value("lastname"), $message);
				$message = str_replace("[FIRSTNAME]", $data->value("firstname"), $message);
				$message = str_replace("[EMAIL]", $data->value("email"), $message);
			} else {
				$message = str_replace("[NAME]", $data->value("name"), $message);
				$message = str_replace("[FIRSTNAME]", current(explode(" ", $data->value("name"))), $message);
				$message = str_replace("[EMAIL]", $data->value("email"), $message);
			}
			$adminname = $api ? $admin : \App\Helpers\AdminFunctions::getAdminName((int) $admin);
		}
		$editor = $markdown ? "markdown" : "plain";
		$table = "tblticketreplies";
		$array = array("tid" => $ticketid, "userid" => $userid, "contactid" => $contactid ?? 0, "name" => $from["name"] ?? "", "email" => $from["email"] ?? "", "date" => \Carbon\Carbon::now(), "message" => $message, "admin" => $adminname, "attachment" => $attachmentsString, "editor" => $editor);
		$ticketreplyid = \App\Models\Ticketreply::insertGetId($array);
		$data = DB::table("tbltickets")->find($ticketid, array("tid", "did", "title", "urgency", "flag", "status"));
		$tid = $data->tid;
		$deptid = $data->did;
		$tickettitle = $data->title;
		$urgency = $data->urgency;
		$flagadmin = $data->flag;
		$oldStatus = $data->status;
		if ($userid || $contactid) {
			$clientname = $contactid ? \App\Models\Contact::find($contactid)->fullName : \App\User\Client::find($userid)->fullName;
		} else {
			$clientname = $from["name"];
		}
		$deptname = self::getdepartmentname($deptid);
		if ($admin) {
			if ($status == "") {
				$status = "Answered";
			}
			$updateqry = array("status" => $status, "clientunread" => "1", "lastreply" => \Carbon\Carbon::now());
			if (isset($CONFIG["TicketLastReplyUpdateClientOnly"]) && $CONFIG["TicketLastReplyUpdateClientOnly"]) {
				unset($updateqry["lastreply"]);
			}
			\App\Models\Ticket::where(array("id" => $ticketid))->update($updateqry);
			self::addticketlog($ticketid, "New Ticket Response");
			if (!$noemail) {
				\App\Helpers\Functions::sendMessage("Support Ticket Reply", $ticketid, $ticketreplyid);
			}
			\App\Helpers\Hooks::run_hook("TicketAdminReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "admin" => $adminname, "status" => $status));
		} else {
			$status = "Customer-Reply";
			$updateqry = array("status" => "Customer-Reply", "clientunread" => "1", "adminunread" => "", "lastreply" => \Carbon\Carbon::now());
			$UpdateLastReplyTimestamp = Cfg::get("UpdateLastReplyTimestamp");
			if ($UpdateLastReplyTimestamp == "statusonly" && ($oldStatus == $status || $oldStatus == "Open" && $status == "Customer-Reply")) {
				unset($updateqry["lastreply"]);
			}
			\App\Models\Ticket::where(array("id" => $ticketid))->update($updateqry);
			self::addticketlog($ticketid, "New Ticket Response made by User");
			\App\Helpers\Hooks::run_hook("TicketUserReply", array("ticketid" => $ticketid, "replyid" => $ticketreplyid, "userid" => $userid, "deptid" => $deptid, "deptname" => $deptname, "subject" => $tickettitle, "message" => $message, "priority" => $urgency, "status" => $status));
		}
		if ($oldStatus != $status) {
			$changes["Status"] = array("old" => $oldStatus, "new" => $status);
		}
		$changes["Reply"] = array("new" => $message);
		if ($attachmentsString) {
			$changes["Attachments"] = self::ticketgenerateattachmentslistfromstring($attachmentsString);
		}
		$recipients = array();
		if (!$admin) {
			$changes["Who"] = $clientname;
			$recipients = $flagadmin ? array($flagadmin) : (!$noemail ? self::getDepartmentNotificationIds($deptid) : array());
		} else {
			$changes["Who"] = $adminname;
		}
		\App\Helpers\Tickets::notifyTicketChanges($ticketid, $changes, $recipients);
	}
	public static function processUtf8Mb4($message)
	{
		$cutUtf8Mb4 = Cfg::get("CutUtf8Mb4");
		if (!$cutUtf8Mb4) {
			return $message;
		}
		$emojis = array("/[\\x{1F600}\\x{1F601}]/u" => ":)", "/[\\x{1F603}-\\x{1F606}]/u" => ":D", "/[\\x{1F609}\\x{1F60A}]/u" => ";)", "/\\x{1F610}/u" => ":|", "/[\\x{1F612}\\x{1F61E}\\x{1F61F}]/u" => ":(", "/\\x{1F61B}/u" => ":P", "/\\x{1F622}/u" => ":'(");
		$cleanText = $message;
		$cleanText = preg_replace(array_keys($emojis), array_values($emojis), $cleanText);
		$removePatterns = array("/[\\x{1F600}-\\x{1F64F}]/u", "/[\\x{1F300}-\\x{1F5FF}]/u", "/[\\x{1F680}-\\x{1F6FF}]/u", "/[\\x{2600}-\\x{26FF}]/u", "/[\\x{2700}-\\x{27BF}]/u");
		$cleanText = preg_replace($removePatterns, "", $cleanText);
		return $cleanText;
	}

	/**
	 * AddNote
	 * 
	 */
	public static function AddNote($tid, $message, $markdown = false, $attachments = "")
	{
		$auth = Auth::guard('admin')->user();

		$adminid = $auth ? $auth->id : 0;

		$table = new Ticketnote;
		$table->ticketid = $tid;
		$table->date = \Carbon\Carbon::now();
		$table->admin = $auth ? $auth->firstname : "system";
		$table->message = $message;
		$table->attachments = $attachments;
		$table->editor = $markdown ? "markdown" : "plain";
		$table->save();

		self::addTicketLog($tid, "Ticket Note Added");

		\App\Helpers\Hooks::run_hook("TicketAddNote", array("ticketid" => $tid, "message" => $message, "adminid" => $adminid, "attachments" => $attachments));
	}

	/**
	 * GenTicketMask
	 * 
	 */
	public static function GenTicketMask($id = "")
	{
		$lowercase = "abcdefghijklmnopqrstuvwxyz";
    $uppercase = "ABCDEFGHIJKLMNOPQRSTUVYWXYZ";
    $ticketmaskstr = "";
    $ticketmask = trim(Cfg::get("TicketMask"));
		if (!$ticketmask) {
			$ticketmask = "%n%n%n%n%n%n";
		}
		$masklen = strlen($ticketmask);
		for ($i = 0; $i < $masklen; $i++) {
			$maskval = $ticketmask[$i];
			if ($maskval == "%") {
					$i++;
					$maskval .= $ticketmask[$i];
					if ($maskval == "%A") {
							$ticketmaskstr .= $uppercase[rand(0, 25)];
					} else {
							if ($maskval == "%a") {
									$ticketmaskstr .= $lowercase[rand(0, 25)];
							} else {
									if ($maskval == "%n") {
											$ticketmaskstr .= strlen($ticketmaskstr) ? rand(0, 9) : rand(1, 9);
									} else {
											if ($maskval == "%y") {
													$ticketmaskstr .= date("Y");
											} else {
													if ($maskval == "%m") {
															$ticketmaskstr .= date("m");
													} else {
															if ($maskval == "%d") {
																	$ticketmaskstr .= date("d");
															} else {
																	if ($maskval == "%i") {
																			$ticketmaskstr .= $id;
																	}
															}
													}
											}
									}
							}
					}
			} else {
					$ticketmaskstr .= $maskval;
			}
		}

		$tid = TicketModel::select('id')->where('tid', $ticketmaskstr)->first();
    if ($tid) {
			$ticketmaskstr = self::GenTicketMask($id);
    }
    return $ticketmaskstr;
	}

	/**
	 * DeleteTicket
	 * 
	 * 
	 */
	public static function DeleteTicket($ticketid, $replyid = 0)
	{
		$auth = Auth::guard('admin')->user();

		$ticketid = (int) $ticketid;
		$replyid = (int) $replyid;
		$attachments = array();

		$ticketreplies = Ticketreply::query();
		$ticketreplies->select('attachment');
		if (0 < $replyid) {
			$ticketreplies->where('id', $replyid);
		} else {
			$ticketreplies->where('tid', $ticketid);
		}
		$ticketreplies->get();
		foreach ($ticketreplies as $ticketreply) {
			$attachments[] = $ticketreply->attachment;
		}

		if (!$replyid) {
			$data = TicketModel::select('did', 'attachment')->where('id', $ticketid)->first();
			$deptid = $data->did;
			$attachments[] = $data->attachment;
		}

		foreach ($attachments as $attachment) {
			if ($attachment) {
				$attachment = explode("|", $attachment);
				foreach ($attachment as $filename) {
					Storage::disk('attachments')->delete($filename);
				}
			}
		}

		if (!$replyid) {
			$customfields = \App\Helpers\Customfield::getCustomFields("support", $deptid, $ticketid, true);
			foreach ($customfields as $field) {
				\App\Models\Customfieldsvalue::where('fieldid', $field["id"])->where('relid', $ticketid)->delete();
			}

			Tickettag::where('ticketid', $ticketid)->delete();
			Ticketnote::where('ticketid', $ticketid)->delete();
			Ticketlog::where('tid', $ticketid)->delete();
			Ticketreply::where('tid', $ticketid)->delete();
			TicketModel::where('id', $ticketid)->delete();
			LogActivity::Save("Deleted Ticket - Ticket ID: " . $ticketid);
			$adminid = $auth ? $auth->id : 0;
			\App\Helpers\Hooks::run_hook("TicketDelete", array("ticketId" => $ticketid, "adminId" => $adminid));
		} else {
			Ticketreply::where('id', $replyid)->delete();
			self::addticketlog($ticketid, "Deleted Ticket Reply (ID: " . $replyid . ")");
			LogActivity::Save("Deleted Ticket Reply - ID: " . $replyid);

			$adminid = $auth ? $auth->id : 0;
			\App\Helpers\Hooks::run_hook("TicketDeleteReply", array("ticketId" => $ticketid, "replyId" => $replyid, "adminId" => $adminid));
		}
	}

	/**
	 * CloseTicket
	 */
	public static function CloseTicket($id)
	{
		$ticket = DB::table("tbltickets")->find($id);
		if (is_null($ticket)) {
			return false;
		}
		if ($ticket->status == "Closed") {
			return false;
		}
		$changes = array();
		if (defined("CLIENTAREA") || Application::isClientAreaRequest()) {
			self::addticketlog($id, "Closed by Client");
			// TODO: $changes["Who"] = WHMCS\Session::get("cid") ? WHMCS\User\Client\Contact::find(WHMCS\Session::get("cid"))->fullName : WHMCS\User\Client::find(WHMCS\Session::get("uid"))->fullName;
			$changes["Who"] = session("cid") ? \App\Models\Contact::find(session("cid"))->fullName : \App\Models\Client::find(Auth::user()->id)->fullName;
		} else {
			if (defined("ADMINAREA") || defined("APICALL") || Application::isAdminAreaRequest() || Application::isApiRequest()) {
				self::addticketlog($id, "Status changed to Closed");
				$changes["Who"] = \App\Helpers\AdminFunctions::getAdminName(Auth::guard('admin')->user() ? Auth::guard('admin')->user()->id : 0);
			} else {
				self::addticketlog($id, "Ticket Auto Closed For Inactivity");
				$changes["Who"] = "System";
			}
		}
		$changes["Status"] = array("old" => $ticket->status, "new" => "Closed");
		\App\Models\Ticket::where('id', $ticket->id)->update(array("status" => "Closed"));
		$skipFeedbackRequest = false;
		$skipNotification = false;
		$responses = \App\Helpers\Hooks::run_hook("TicketClose", array("ticketid" => $id));
		foreach ($responses as $response) {
			if (array_key_exists("skipFeedbackRequest", $response) && $response["skipFeedbackRequest"]) {
				$skipFeedbackRequest = true;
			}
			if (array_key_exists("skipNotification", $response) && $response["skipNotification"]) {
				$skipNotification = true;
			}
		}
		if (!$skipFeedbackRequest) {
			$department = DB::table("tblticketdepartments")->find($ticket->did);
			if ($department->feedback_request) {
				$feedbackcheck = \App\Models\Ticketfeedback::select("id")->where("ticketid", $id)->value("id");
				if (!$feedbackcheck) {
					\App\Helpers\Functions::sendMessage("Support Ticket Feedback Request", $id);
				}
			}
		}
		if (!$skipNotification) {
			\App\Helpers\Tickets::notifyTicketChanges($id, $changes);
		}
		return true;
	}

	/**
	 * getStatusColour
	 */
	public static function getStatusColour($tstatus, $htmlOutput = true)
	{
		global $_LANG;
		static $ticketcolors = array();

		if (!array_key_exists($tstatus, $ticketcolors)) {
			$ticketcolors[$tstatus] = $color = \App\Models\Ticketstatus::select("color")->where("title", $tstatus)->value("color");
		} else {
			$color = $ticketcolors[$tstatus];
		}
		if ($htmlOutput) {
			$langstatus = preg_replace("/[^a-z]/i", "", strtolower($tstatus));
			if ($_LANG["supportticketsstatus" . $langstatus] ?? __("client.supportticketsstatus{$langstatus}")) {
				$tstatus = $_LANG["supportticketsstatus" . $langstatus] ?? __("client.supportticketsstatus{$langstatus}");
			}
			$statuslabel = "";
			if ($color) {
				$statuslabel .= "<span style=\"color:" . $color . "\">";
			}
			$statuslabel .= $tstatus;
			if ($color) {
				$statuslabel .= "</span>";
			}
			return $statuslabel;
		}
		return $color;
	}

	/**
	 * addTicketLog
	 */
	public static function addTicketLog($tid, $action)
	{
		$auth = Auth::guard('admin')->user();
		// if (isset($_SESSION["adminid"])) {
		if ($auth) {
			$action .= " (by " . $auth->name . ")";
		}
		\App\Models\Ticketlog::insert(array("date" => \Carbon\Carbon::now(), "tid" => $tid, "action" => $action));
	}

	/**
	 * ticketGenerateAttachmentsListFromString
	 */
	public static function ticketGenerateAttachmentsListFromString($attachmentsString)
	{
		$attachmentsOutput = "";
		$attachmentsString = trim($attachmentsString);
		if ($attachmentsString) {
			$attachmentsOutput .= "<br /><br /><strong>Attachments</strong><br />";
			$attachments = explode("|", $attachmentsString);
			foreach ($attachments as $i => $attachment) {
				$attachmentsOutput .= $i + 1 . ". " . substr($attachment, 7) . "<br />";
			}
		}
		return $attachmentsOutput;
	}

	/**
	 * getDepartmentNotificationIds
	 */
	public static function getDepartmentNotificationIdsOLD($departmentId)
	{
		// TODO: $admins = \App\Models\Admin::where("tbladmins.disabled", "=", "0")->where("tbladmins.ticketnotifications", "!=", "")->get(array("tbladmins.id", "tbladmins.supportdepts", "tbladmins.ticketnotifications"));
		$admins = \App\Models\Admin::join("roles", "tbladmins.roleid", "=", "roles.id")->where("tbladmins.disabled", "=", "0")->where("tbladmins.ticketnotifications", "!=", "")->get(array("tbladmins.id", "tbladmins.supportdepts", "tbladmins.ticketnotifications"));
		$notificationAdmins = array();
		foreach ($admins as $admin) {
			if (in_array($departmentId, $admin->supportDepartmentIds ?? []) && in_array($departmentId, $admin->receivesTicketNotifications)) {
				$notificationAdmins[] = $admin->id;
			}
		}
		return $notificationAdmins;
	}
	public static function getDepartmentNotificationIds($departmentId)
	{
		// TODO: $admins = \App\User\Admin::join("roles", "tbladmins.roleid", "=", "roles.id")->where("tbladmins.disabled", "=", "0")->where("roles.supportemails", "=", "1")->where("tbladmins.ticketnotifications", "!=", "")->get(array("tbladmins.id", "tbladmins.supportdepts", "tbladmins.ticketnotifications"));
		$admins = \App\User\Admin::join("roles", "tbladmins.roleid", "=", "roles.id")->where("tbladmins.disabled", "=", "0")->where("tbladmins.ticketnotifications", "!=", "")->get(array("tbladmins.id", "tbladmins.supportdepts", "tbladmins.ticketnotifications"));
		$notificationAdmins = array();
		foreach ($admins as $admin) {
			if (in_array($departmentId, $admin->supportDepartmentIds) && in_array($departmentId, $admin->receivesTicketNotifications)) {
				$notificationAdmins[] = $admin->id;
			}
		}
		return $notificationAdmins;
	}
	/** 
	 * notifyTicketChanges
	 */

	public static function notifyTicketChanges($ticketId, array $changes, array $recipients = array(), array $removeRecipients = array()){
		if ($ticketId) {
			$ticket=\App\Models\Ticket::with('client')->find($ticketId);
			//dd($ticket);
			$mergeFields = array();
			$mergeFields["ticket_id"] = $ticketId;
			$mergeFields["ticket_tid"] = $ticket->tid;
			if(!empty($changes["Reply"])){
				$markup = new \App\Helpers\ViewMarkup();
				$markupFormat = $markup->determineMarkupEditor("ticket_reply", $ticket->editor);
            $mergeFields["newReply"] = $markup->transform($changes["Reply"]["new"], $markupFormat);
            unset($changes["Reply"]);
			}
			if (!empty($changes["Note"])) {
				if(!isset($markup)) {
					$markup = new \App\Helpers\ViewMarkup();
			 	}
				$markupFormat = $markup->determineMarkupEditor("ticket_note", $changes["note"]["editor"]);
            $mergeFields["newNote"] = $markup->transform($changes["Note"]["new"], $markupFormat);
            unset($changes["Note"]);
			}
			if(!empty($changes["Opened"]) && !isset($markup)){
				$markup = new \App\Helpers\ViewMarkup();
				$markupFormat = $markup->determineMarkupEditor("ticket_note", $ticket->getData("editor"));
				$mergeFields["newTicket"] = $markup->transform($changes["Opened"]["new"], $markupFormat);

			}
			if (!empty($changes["Attachments"])) {
				$mergeFields["newAttachments"] = $changes["Attachments"];
				unset($changes["Attachments"]);
		  	}
			$mergeFields["changer"] = $changes["Who"] ?? '';
			unset($changes["Who"]);
         $mergeFields["changes"] = $changes;
         $mergeFields["client_name"] = $ticket->client->firstname.' '.$ticket->client->lastname;
         $mergeFields["client_id"] = $ticket->userid;
         $mergeFields["ticket_department"] = \App\Models\Ticketdepartment::find($ticket->did)->name;
         $mergeFields["ticket_subject"] = $ticket->title;
         $mergeFields["ticket_priority"] =$ticket->urgency;
         $includeFlagged = true;
			if (!empty($changes["Assigned To"])) {
				if ($changes["Assigned To"]["newId"] == Auth::guard('admin')->user()->id) {
					 $includeFlagged = false;
				}
				if ($changes["Assigned To"]["oldId"] && $changes["Assigned To"]["oldId"] != Auth::guard('admin')->user()->id) {
					 $recipients = array_merge($recipients, array($changes["Assigned To"]["oldId"]));
				}
			}
				if (!empty($changes["Department"])) {
					$recipients = array_merge($recipients,  self::getDepartmentNotificationIds(@$changes["Department"]["newId"]));
			  	}
				$recipients = array_unique(array_merge(0 < $ticket->flag && $includeFlagged ? array($ticket->flag) : array(), $recipients, \App\Models\TicketWatcher::where('ticket_id',$ticket->ticketid)->pluck("admin_id")->all()));
            if ($removeRecipients) {
                $recipients = array_filter($recipients, function ($value) use($removeRecipients) {
                    return !in_array($value, $removeRecipients);
                });
            }
				$recipients = array_flip($recipients);
				//dd(Auth::guard('admin')->user()->id);
				if(isset($recipients[(int) Auth::guard('admin')->user()->id])){
					unset($recipients[(int) Auth::guard('admin')->user()->id]);
				}
				$recipients = array_flip($recipients);
            if (0 < count($recipients)) {
                return \App\Helpers\Functions::sendAdminMessage("Support Ticket Change Notification", $mergeFields, "ticket_changes",$ticket->tid, $recipients);
            }
		  }
		  return false;
	}

	public static function getTimeBetweenDates($lastreply, $from = "now")
	{
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
	public static function getShortLastReplyTime($lastreply)
	{
		$timeparts = self::gettimebetweendates($lastreply);
		$str = "";
		if (0 < $timeparts["days"]) {
			$str .= $timeparts["days"] . "d ";
		}
		$str .= $timeparts["hours"] . "h ";
		$str .= $timeparts["minutes"] . "m";
		return $str;
	}
	public static function getLastReplyTime($lastreply)
	{
		$timeparts = self::gettimebetweendates($lastreply);
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
	public static function getTicketDuration($start, $end)
	{
		$timeparts = self::gettimebetweendates($start, $end);
		$str = "";
		if (0 < $timeparts["days"]) {
			$str .= $timeparts["days"] . " " . \Lang::get("client.days") . " ";
		}
		if (0 < $timeparts["hours"]) {
			$str .= $timeparts["hours"] . " " . \Lang::get("client.hours") . " ";
		}
		if (0 < $timeparts["minutes"]) {
			$str .= $timeparts["minutes"] . " " . \Lang::get("client.minutes") . " ";
		}
		$str .= $timeparts["seconds"] . " " . \Lang::get("client.seconds") . " ";
		return $str;
	}
	public static function checkTicketAttachmentExtension($file_name)
	{
		return \App\Helpers\FileUpload::isExtensionAllowed($file_name);
	}
	public static function uploadTicketAttachments($isAdmin = false)
    {
        $attachments = Request::file('attachments');
        $attachmentString = [];
        
        if (Request::hasFile('attachments')) {
            $directory = 'Files/';
            
            foreach ($attachments as $attachment) {
                $uuid = (string) Str::uuid();
                $filename = $uuid . "." . $attachment->getClientOriginalExtension();
                $content = file_get_contents($attachment);

                // Ensure the directory exists
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Save the file
                file_put_contents($directory . $filename, $content);

                // Store the full path in the attachmentString
                $attachmentString[] = 'Files/' . $filename;
            }
        }
        $attachmentString = implode('|', $attachmentString);
        return $attachmentString;
    }
	public static function ClientRead($tid)
	{
		\App\Models\Ticket::where(array("id" => $tid))->update(array("clientunread" => ""));
	}
	public static function getKBAutoSuggestions($text)
	{
		$kbarticles = array();
		$hookret = \App\Helpers\Hooks::run_hook("SubmitTicketAnswerSuggestions", array("text" => $text));
		if (count($hookret)) {
			foreach ($hookret as $hookdat) {
				foreach ($hookdat as $arrdata) {
					$kbarticles[] = $arrdata;
				}
			}
		} else {
			$ignorewords = array("able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards", "again", "against", "ain't", "allow", "allows", "almost", "alone", "along", "already", "also", "although", "always", "among", "amongst", "another", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "aren't", "around", "aside", "asking", "associated", "available", "away", "awfully", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "c'mon", "came", "can't", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes", "clearly", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "currently", "definitely", "described", "despite", "didn't", "different", "does", "doesn't", "doing", "don't", "done", "down", "downwards", "during", "each", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "exactly", "example", "except", "fifth", "first", "five", "followed", "following", "follows", "former", "formerly", "forth", "four", "from", "further", "furthermore", "gets", "getting", "given", "gives", "goes", "going", "gone", "gotten", "greetings", "hadn't", "happens", "hardly", "hasn't", "have", "haven't", "having", "he's", "hello", "help", "hence", "here", "here's", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "himself", "hither", "hopefully", "howbeit", "however", "i'll", "i've", "ignored", "immediate", "inasmuch", "indeed", "indicate", "indicated", "indicates", "inner", "insofar", "instead", "into", "inward", "isn't", "it'd", "it'll", "it's", "itself", "just", "keep", "keeps", "kept", "know", "known", "knows", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let's", "like", "liked", "likely", "little", "look", "looking", "looks", "mainly", "many", "maybe", "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "myself", "name", "namely", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "next", "nine", "nobody", "none", "noone", "normally", "nothing", "novel", "nowhere", "obviously", "often", "okay", "once", "ones", "only", "onto", "other", "others", "otherwise", "ought", "ours", "ourselves", "outside", "over", "overall", "particular", "particularly", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provides", "quite", "rather", "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saying", "says", "second", "secondly", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "should", "shouldn't", "since", "some", "somebody", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "such", "sure", "take", "taken", "tell", "tends", "than", "thank", "thanks", "thanx", "that", "that's", "thats", "their", "theirs", "them", "themselves", "then", "thence", "there", "there's", "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "think", "third", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "together", "took", "toward", "towards", "tried", "tries", "truly", "trying", "twice", "under", "unfortunately", "unless", "unlikely", "until", "unto", "upon", "used", "useful", "uses", "using", "usually", "value", "various", "very", "want", "wants", "wasn't", "we'd", "we'll", "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's", "whatever", "when", "whence", "whenever", "where", "where's", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who's", "whoever", "whole", "whom", "whose", "will", "willing", "wish", "with", "within", "without", "won't", "wonder", "would", "wouldn't", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", "zero");
			$text = str_replace("\n", " ", $text);
			$textparts = explode(" ", strtolower($text));
			$validword = 0;
			foreach ($textparts as $k => $v) {
				if (in_array($v, $ignorewords) || strlen($textparts[$k]) <= 3 || 100 <= $validword) {
					unset($textparts[$k]);
				} else {
					$validword++;
				}
			}
			$kbarticles = self::getKBAutoSuggestionsQuery("title", $textparts, "5");
			if (count($kbarticles) < 5) {
				$numleft = 5 - count($kbarticles);
				$kbarticles = array_merge($kbarticles, self::getKBAutoSuggestionsQuery("article", $textparts, $numleft, $kbarticles));
			}
		}
		return $kbarticles;
	}
	public static function getKBAutoSuggestionsQuery($field, $textparts, $limit, $existingkbarticles = "")
	{
		$kbarticles = array();
		$where = "";
		foreach ($textparts as $textpart) {
			$where .= (string) $field . " LIKE '%" . \App\Helpers\Database::db_escape_string($textpart) . "%' OR ";
		}
		$where = !$where ? "id!=''" : substr($where, 0, -4);
		if (is_array($existingkbarticles)) {
			$existingkbids = array();
			foreach ($existingkbarticles as $v) {
				$existingkbids[] = (int) $v["id"];
			}
			$where = "(" . $where . ")";
			if (0 < count($existingkbids)) {
				$where .= " AND id NOT IN (" . \App\Helpers\Database::db_build_in_array($existingkbids) . ")";
			}
		}
		// $result = DB::select(DB::raw("SELECT id,parentid FROM tblknowledgebase WHERE " . $where . " ORDER BY useful DESC LIMIT 0," . (int) $limit));
		$result = \App\Models\Knowledgebase::whereRaw($where)->orderBy("useful", "DESC")->limit($limit)->get();
		foreach ($result->toArray() as $data) {
			$articleid = $data["id"];
			$parentid = $data["parentid"];
			if ($parentid) {
				$articleid = $parentid;
			}
			$result2 = DB::select(DB::raw("SELECT tblknowledgebaselinks.categoryid FROM tblknowledgebase INNER JOIN tblknowledgebaselinks ON tblknowledgebase.id=tblknowledgebaselinks.articleid INNER JOIN tblknowledgebasecats ON tblknowledgebasecats.id=tblknowledgebaselinks.categoryid WHERE (tblknowledgebase.id=" . (int) $articleid . " OR tblknowledgebase.parentid=" . (int) $articleid . ") AND tblknowledgebasecats.hidden=''"));
			$data = $result2->toArray();
			$categoryid = $data["categoryid"];
			if ($categoryid) {
				$result2 = DB::select(DB::raw("SELECT * FROM tblknowledgebase WHERE (id=" . (int) $articleid . " OR parentid=" . (int) $articleid . ") AND (language='" . \App\Helpers\Database::db_escape_string(\Session::get("Language")) . "' OR language='') ORDER BY language DESC"));
				$data = $result2->toArray();
				$title = $data["title"];
				$article = $data["article"];
				$views = $data["views"];
				$kbarticles[] = array("id" => $articleid, "category" => $categoryid, "title" => $title, "article" => self::ticketsummary($article), "text" => $article);
			}
		}
		return $kbarticles;
	}
	public static function ticketsummary($text, $length = 100)
	{
		$tail = "...";
		$text = strip_tags($text);
		$txtl = strlen($text);
		if ($length < $txtl) {
			for ($i = 1; $text[$length - $i] != " "; $i++) {
				if ($i == $length) {
					return substr($text, 0, $length) . $tail;
				}
			}
			$text = substr($text, 0, $length - $i + 1) . $tail;
		}
		return $text;
	}
	public static function checkTicketAttachmentSize()
	{
		$postMaxSizeIniSetting = ini_get("post_max_size");
		$postMaxSize = self::convertinisize($postMaxSizeIniSetting);
		$contentLength = (int) $_SERVER["CONTENT_LENGTH"];
		if (!$contentLength) {
			return true;
		}
		if ($postMaxSize < $contentLength) {
			LogActivity::Save(sprintf("A ticket attachment submission of %d bytes total was rejected due to PHP post_max_size setting being too small (%s or %d bytes).", $contentLength, $postMaxSizeIniSetting, $postMaxSize));
			return false;
		}
		$uploadMaxFileSizeIniSetting = ini_get("upload_max_filesize");
		$uploadMaxFileSize = self::convertinisize($uploadMaxFileSizeIniSetting);
		if (isset($_FILES)) {
			if (is_array($_FILES["attachments"]["error"])) {
				$fileTooLarge = in_array(UPLOAD_ERR_INI_SIZE, $_FILES["attachments"]["error"]);
			} else {
				$fileTooLarge = $_FILES["attachments"]["error"] == UPLOAD_ERR_INI_SIZE;
			}
			if ($fileTooLarge) {
				LogActivity::Save(sprintf("A ticket attachment was rejected due to PHP upload_max_filesize setting being too small (%s or %d bytes).", $uploadMaxFileSizeIniSetting, $uploadMaxFileSize));
				return false;
			}
		}
		return true;
	}
	public static function convertIniSize($size)
	{
		$multipliers = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);
		$mod = strtoupper(substr($size, -1, 1));
		$mult = $multipliers[$mod] ?: 1;
		if (1 < $mult) {
			$size = (int) substr($size, 0, -1);
		}
		return $size * $mult;
	}
	public static function validateAdminTicketAccess($ticketid)
	{
		$auth = Auth::guard('admin')->user();
		$adminid = $auth ? $auth->id : 0;
		$data = \App\Models\Ticket::where(array("id" => $ticketid));
		$id = $data->value("id");
		$deptid = $data->value("did");
		$flag = $data->value("flag");
		$mergedTicketId = $data->value("merged_ticket_id");
		if (!$id) {
			return "invalidid";
		}
		if (!in_array($deptid, self::getadmindepartmentassignments()) && !\App\Helpers\AdminFunctions::checkPermission("Access All Tickets Directly", true)) {
			return "deptblocked";
		}
		if ($flag && $flag != $adminid && !\App\Helpers\AdminFunctions::checkPermission("View Flagged Tickets", true) && !\App\Helpers\AdminFunctions::checkPermission("Access All Tickets Directly", true)) {
			return "flagged";
		}
		if ($mergedTicketId) {
			return "merged" . $mergedTicketId;
		}
		return false;
	}
	public static function getAdminDepartmentAssignments()
	{
		$auth = Auth::guard('admin')->user();
		$adminid = $auth ? $auth->id : 0;
		static $DepartmentIDs = array();
		if (count($DepartmentIDs)) {
			return $DepartmentIDs;
		}
		$result = \App\Models\Admin::where(array("id" => $adminid));
		$data = $result;
		$supportdepts = $data->value("supportdepts");
		$supportdepts = explode(",", $supportdepts);
		foreach ($supportdepts as $k => $v) {
			if (!$v) {
				unset($supportdepts[$k]);
			}
		}
		$DepartmentIDs = $supportdepts;
		return $supportdepts;
	}
	public static function AdminRead($tid)
	{
		$auth = Auth::guard('admin')->user();
		$adminid = $auth ? $auth->id : 0;
		$result = \App\Models\Ticket::where(array("id" => $tid));
		$data = $result;
		$adminread = $data->value("adminunread");
		// $adminreadarray = $adminread ? explode(",", $adminread) : array();
		$adminreadarray = $adminread ? $adminread : array();
		if (!in_array($adminid, $adminreadarray)) {
			$adminreadarray[] = $adminid;
			\App\Models\Ticket::where(array("id" => $tid))->update(array("adminunread" => implode(",", $adminreadarray)));
		}
	}
	
}



