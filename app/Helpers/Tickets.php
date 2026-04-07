<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\Hooks;
use App\Helpers\Cfg;
use DB, Auth;

class Tickets extends TableModel
{
	public $ticketid = 0;
    public $data = array();
    public $deptids = array();
    public $deptnames = array();
    public $deptemails = array();
    protected $departmentFeedbackRequest = array();
    public $tagticketids = array();
    public function _execute($criteria = NULL)
    {
        if (is_array($criteria) && array_key_exists("tag_ticket_ids", $criteria)) {
            $this->tagticketids = $criteria["tag_ticket_ids"];
            unset($criteria["tag_ticket_ids"]);
        }
        return $this->getTickets($criteria);
    }
    public function getTickets($criteria = array())
    {
        global $aInt;
        $tagjoin = $criteria["tag"] ? " INNER JOIN tbltickettags ON tbltickettags.ticketid=tbltickets.id" : "";
        $query = " FROM tbltickets" . $tagjoin . " INNER JOIN tblticketdepartments ON tblticketdepartments.id=tbltickets.did LEFT JOIN tblclients ON tblclients.id=tbltickets.userid";
        $filters = $this->buildCriteria($criteria);
        if (count($filters)) {
            $query .= " WHERE " . implode(" AND ", $filters);
        }
        // $result = full_query("SELECT COUNT(tbltickets.id)" . $query);
		$result = DB::select(DB::raw("SELECT COUNT(tbltickets.id) as count" . $query));
		// HOTFIX: this
		dd($result);
        $data = $result->toArray();
        $this->getPageObj()->setNumResults($data['count']);
        $query .= " ORDER BY " . $this->getPageObj()->getOrderBy() . " " . $this->getPageObj()->getSortDirection();
        if ($this->getPageObj()->isPaginated()) {
            $query .= " LIMIT " . $this->getQueryLimit();
        }
        $tickets = array();
        // $result = full_query("SELECT tbltickets.*,tblticketdepartments.name AS deptname,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid" . $query);
		$result = DB::select(DB::raw("SELECT tbltickets.*,tblticketdepartments.name AS deptname,tblclients.firstname,tblclients.lastname,tblclients.companyname,tblclients.groupid" . $query));
        foreach ($result->toArray() as $data) {
            $id = $data["id"];
            $ticketnumber = $data["tid"];
            $did = $data["did"];
            $deptname = $data["deptname"];
            $puserid = $data["userid"];
            $name = $data["name"];
            $email = $data["email"];
            $date = $data["date"];
            $title = $data["title"];
            $message = $data["message"];
            $tstatus = $data["status"];
            $priority = $data["urgency"];
            $rawlastactivity = $data["lastreply"];
            $flag = $data["flag"];
            $adminread = $data["adminunread"];
            $firstname = $data["firstname"];
            $lastname = $data["lastname"];
            $companyname = $data["companyname"];
            $groupid = $data["groupid"];
            $adminread = explode(",", $adminread);
            $this->addTagCloudID($id);
            $unread = in_array(Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0, $adminread) ? 0 : 1;
            $alttitle = "";
            $title = trim($title);
            if (!$title) {
                $title = "&nbsp;- " . \Lang::get("admin.emailsnosubject") . " -&nbsp;";
            }
            if (80 < strlen($title)) {
                $alttitle = $title . "\n";
                $title = $this->getSummary($title, 80);
            }
            $alttitle .= $this->getSummary($message, 250);
            $myId = Auth::guard('web')->id();
            $myAssignedTicketsSection = !empty($criteria["flag"]) && $criteria["flag"] == $myId;
            if ($flag && (!$myAssignedTicketsSection || $flag != $myId)) {
                $deptname .= " (" . \App\Helpers\AdminFunctions::getAdminName($flag) . ")";
            }
            $date = (new \App\Helpers\Functions)->fromMySQLDate($date, 1);
            $lastactivity = (new \App\Helpers\Functions)->fromMySQLDate($rawlastactivity, 1);
            $tstatus = $this->getStatusColour($tstatus);
            $lastreply = $this->getShortLastReplyTime($rawlastactivity);
            $clientinfo = $puserid != "0" ? \App\Helpers\ClientHelper::outputClientLink($puserid, $firstname, $lastname, $companyname, $groupid) : $name;
            $tickets[] = array("id" => $id, "ticketnum" => $ticketnumber, "priority" => $priority, "department" => $deptname, "subject" => $title, "textsummary" => $alttitle, "clientname" => $clientinfo, "status" => $tstatus, "lastreply" => $lastreply, "unread" => $unread);
        }
        return $tickets;
    }
    private function buildCriteria($criteria)
    {
        $filters = array();
        $tag = isset($criteria["tag"]) ? $criteria["tag"] : "";
        if ($tag) {
            $filters[] = "tbltickettags.tag='" . \App\Helpers\Database::db_escape_string($tag) . "'";
            return $filters;
        }
        $status = isset($criteria["status"]) ? $criteria["status"] : "";
        $multiStatus = !empty($criteria["multiStatus"]) ? (array) $criteria["multiStatus"] : "";
        $ticketid = isset($criteria["ticketid"]) ? $criteria["ticketid"] : "";
        $multiDeptIds = !empty($criteria["multiDeptIds"]) ? (array) $criteria["multiDeptIds"] : "";
        $deptid = isset($criteria["deptid"]) ? $criteria["deptid"] : "";
        $subject = isset($criteria["subject"]) ? $criteria["subject"] : "";
        $email = isset($criteria["email"]) ? $criteria["email"] : "";
        $client = isset($criteria["client"]) ? $criteria["client"] : "";
        $clientid = isset($criteria["clientid"]) ? $criteria["clientid"] : "";
        $clientname = isset($criteria["clientname"]) ? $criteria["clientname"] : "";
        $flag = isset($criteria["flag"]) ? $criteria["flag"] : "";
        $notflaggedto = isset($criteria["notflaggedto"]) ? $criteria["notflaggedto"] : "";
        $priority = !empty($criteria["priority"]) ? (array) $criteria["priority"] : "";
        if ($client) {
            if (is_numeric($client)) {
                $clientid = $client;
            } else {
                $clientname = $client;
            }
        }
        $deptids = $this->getAdminsDeptIDs();
        $filters[] = "tbltickets.did IN (" . \App\Helpers\Database::db_build_in_array($deptids) . ")";
        if ($multiStatus) {
            $flagFilter = "";
            if (in_array("flagged", $multiStatus) && !$notflaggedto) {
                $multiStatus = array_flip($multiStatus);
                unset($multiStatus["flagged"]);
                $multiStatus = array_flip($multiStatus);
                $statuses = $multiStatus && in_array("any", $multiStatus) ? DB::table("tblticketstatuses")->pluck("title") : ($multiStatus ? $multiStatus : DB::table("tblticketstatuses")->whereShowactive(1)->pluck("title"));
                $flagFilter = " OR (tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statuses) . ") AND flag=" . (int) Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0 . ")";
            }
            if ($multiStatus && !in_array("any", $multiStatus)) {
                $filters[] = "(tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($multiStatus) . ")" . $flagFilter . ")";
            } else {
                if ($flagFilter) {
                    $filters[] = substr($flagFilter, 4);
                }
            }
        } else {
            if ($status == "Awaiting Reply" || $status == "awaitingreply" || $status == "") {
                $statusfilter = DB::table("tblticketstatuses")->whereShowawaiting(1)->pluck("title");
                $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilter) . ")";
            } else {
                if ($status == "All Tickets" || $status == "all" || $status == "any") {
                } else {
                    if ($status == "All Active Tickets" || $status == "active") {
                        $statusfilter = DB::table("tblticketstatuses")->whereShowactive(1)->pluck("title");
                        $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilter) . ")";
                    } else {
                        if ($status == "Flagged Tickets" || $status == "flagged") {
                            $statusfilter = DB::table("tblticketstatuses")->whereShowactive(1)->pluck("title");
                            $filters[] = "tbltickets.status IN (" . \App\Helpers\Database::db_build_in_array($statusfilter) . ") AND flag=" . (int) Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0;
                        } else {
                            $filters[] = "tbltickets.status='" . \App\Helpers\Database::db_escape_string($status) . "'";
                        }
                    }
                }
            }
        }
        if ($clientid || $subject || $email || $clientname) {
        } else {
            if (!\App\Helpers\AdminFunctions::checkPermission("View Flagged Tickets", true)) {
                $filters[] = "(flag=" . (int) Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0 . " OR flag=0)";
            }
        }
        if ($ticketid) {
            $filters[] = "tbltickets.tid='" . \App\Helpers\Database::db_escape_string($ticketid) . "'";
        } else {
            $filters[] = "tbltickets.merged_ticket_id = 0";
        }
        if ($clientid) {
            $filters[] = "tbltickets.userid='" . \App\Helpers\Database::db_escape_string($clientid) . "'";
        }
        if ($multiDeptIds) {
            $filters[] = "tbltickets.did IN (" . \App\Helpers\Database::db_build_in_array($multiDeptIds) . ")";
        } else {
            if ($deptid) {
                $filters[] = "tbltickets.did='" . \App\Helpers\Database::db_escape_string($deptid) . "'";
            }
        }
        if ($subject) {
            $filters[] = "(tbltickets.title LIKE '%" . \App\Helpers\Database::db_escape_string($subject) . "%' OR tbltickets.message LIKE '%" . \App\Helpers\Database::db_escape_string($subject) . "%')";
        }
        if ($email) {
            $filters[] = "(tbltickets.email LIKE '%" . \App\Helpers\Database::db_escape_string($email) . "%' OR tblclients.email LIKE '%" . \App\Helpers\Database::db_escape_string($email) . "%' OR tbltickets.name LIKE '%" . \App\Helpers\Database::db_escape_string($email) . "%')";
        }
        if ($clientname) {
            $filters[] = "(tbltickets.name LIKE '%" . \App\Helpers\Database::db_escape_string($clientname) . "%' OR concat(tblclients.firstname,' ',tblclients.lastname) LIKE '%" . \App\Helpers\Database::db_escape_string($clientname) . "%')";
        }
        if ($flag) {
            $filters[] = "tbltickets.flag=" . (int) $flag;
        }
        if ($notflaggedto) {
            $filters[] = "tbltickets.flag!=" . (int) $notflaggedto;
        }
        if ($priority) {
            $filters[] = "tbltickets.urgency IN (" . \App\Helpers\Database::db_build_in_array($priority) . ")";
        }
        return $filters;
    }
    public function getAdminsDeptIDs()
    {
        $deptids = array();
        // $admin_supportdepts = explode(",", get_query_val("tbladmins", "supportdepts", ));
        $admin_supportdepts = explode(",", \App\Models\Admin::where(array("id" => Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0))->value("supportdepts") ?? "");
        foreach ($admin_supportdepts as $deptid) {
            if (trim($deptid)) {
                $deptids[] = (int) $deptid;
            }
        }
        return $deptids;
    }
    public function getAdminSig()
    {
        $adminid = Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0;
        if (!$adminid) {
            return false;
        }
		return \App\Models\Admin::where(array("id" => $adminid))->value("signature") ?? "";
    }
    public function getStatuses($counts = false)
    {
        $ticketcounts = array();
        if ($counts) {
            $ticketcounts[] = array("label" => "Awaiting Reply", "count" => 0);
            $ticketcounts[] = array("label" => "All Active Tickets", "count" => 0);
            $ticketcounts[] = array("label" => "Flagged Tickets", "count" => 0);
            $admin_supportdepts_qry = $this->getAdminsDeptIDs();
            if (count($admin_supportdepts_qry) < 1) {
                $admin_supportdepts_qry[] = 0;
            }
            $query = "SELECT tblticketstatuses.title,(SELECT COUNT(tbltickets.id) FROM tbltickets WHERE did IN (" . \App\Helpers\Database::db_build_in_array($admin_supportdepts_qry) . ") AND tbltickets.status=tblticketstatuses.title),showactive,showawaiting FROM tblticketstatuses ORDER BY sortorder ASC";
        } else {
            $ticketcounts[] = "Awaiting Reply";
            $ticketcounts[] = "All Active Tickets";
            $ticketcounts[] = "Flagged Tickets";
            $query = "SELECT title FROM tblticketstatuses ORDER BY sortorder ASC";
        }
        $result = DB::select(DB::raw($query));
		// HOTFIX: this
		dd($result);
        foreach ($result->toArray() as $data) {
            if ($counts) {
                $ticketcounts[] = array("label" => $data[0], "count" => $data[1]);
                if ($data["showactive"]) {
                    $ticketcounts[1]["count"] += $data[1];
                }
                if ($data["showawaiting"]) {
                    $ticketcounts[0]["count"] += $data[1];
                }
            } else {
                $ticketcounts[] = $data[0];
            }
        }
        if ($counts) {
			$result = \App\Models\Ticket::whereRaw("status!='Closed' AND flag='" . (int) Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : 0 . "'");
            $data = $result;
            $ticketcounts[2]["count"] = $data->count();
        }
        return $ticketcounts;
    }
    public function getStatusesWithCounts()
    {
        return $this->getStatuses(true);
    }
    public function getAssignableStatuses()
    {
        $statuses = $this->getStatuses();
        unset($statuses[0]);
        unset($statuses[1]);
        unset($statuses[2]);
        return $statuses;
    }
    public function setID($ticketid)
    {
        $this->ticketid = (int) $ticketid;
        $data = $this->getData();
        return is_array($data) ? true : false;
    }
    public function getData($var = "")
    {
        if ($var) {
            return isset($this->data[$var]) ? $this->data[$var] : "";
        }
        $result = \App\Models\Ticket::where(array("id" => $this->ticketid))->first();
        $data = $result;
        if ($data) {
			$data = $data->toArray();
            $data["watchers"] = \App\Models\TicketWatcher::ofTicket($this->ticketid)->pluck("admin_id")->all();
        }
        $this->data = $data;
        return $data;
    }
    public function getDepartments()
    {
        if (count($this->deptids)) {
            return false;
        }
        $ticketDepartments = DB::table("tblticketdepartments")->orderBy("order")->get(array("id", "name", "email", "feedback_request"));
        foreach ($ticketDepartments as $ticketDepartment) {
            $this->deptids[] = $ticketDepartment->id;
            $this->deptnames[$ticketDepartment->id] = $ticketDepartment->name;
            $this->deptemails[$ticketDepartment->email] = $ticketDepartment->id;
            $this->departmentFeedbackRequest[$ticketDepartment->id] = $ticketDepartment->feedback_request;
        }
        return true;
    }
    public function getDeptName($deptid = "")
    {
        $this->getDepartments();
        if (!$deptid) {
            $deptid = $this->getData("did");
        }
        return $this->deptnames[$deptid];
    }
    public function getAdminsDepartments()
    {
        $this->getDepartments();
        $adminsdepts = $this->getAdminsDeptIDs();
        $depts = $this->deptnames;
        foreach ($depts as $deptid => $deptname) {
            if (!in_array($deptid, $adminsdepts)) {
                unset($depts[$deptid]);
            }
        }
        return $depts;
    }
    public function getClientName()
    {
        if (!count($this->data)) {
            $this->getData();
        }
        if ($this->getData("userid")) {
            if ($this->getData("contactid")) {
                $clientname = \App\Models\Contact::selectRaw("CONCAT(firstname,' ',lastname) as fullname")->where(array("id" => $this->getData("contactid"), "userid" => $this->getData("userid")))->value("fullname") ?? "";
            } else {
                $clientname = \App\Models\Client::selectRaw("CONCAT(firstname,' ',lastname) as fullname")->where(array("id" => $this->getData("userid")))->value("fullname") ?? "";
            }
        } else {
            $clientname = $this->getData("name");
        }
        return $clientname;
    }
    public function validateDept($deptid = "")
    {
        $this->getDepartments();
        if (in_array($deptid, $this->deptids)) {
            return true;
        }
        return false;
    }
    public function setDept($newdeptid)
    {
        if (!$this->validateDept($newdeptid)) {
            return false;
        }
        if ($newdeptid == $this->getData("did")) {
            return false;
        }
        if (!count($this->data)) {
            $this->getData();
        }
        \App\Helpers\Customfield::migrateCustomFields("support", $this->getData("id"), $newdeptid);
        \App\Models\Ticket::where(array("id" => $this->getData("id")))->update(array("did" => $newdeptid));
        $this->data["did"] = $newdeptid;
        $deptname = $this->getDeptName();
        $this->log("Department changed to " . $deptname);
        Hooks::run_hook("TicketDepartmentChange", array("ticketid" => $this->getData("id"), "deptid" => $newdeptid, "deptname" => $deptname));
        return true;
    }
    public function changeDept($newdeptid)
    {
        return $this->setDept($newdeptid);
    }
    public function setStatus($newstatus)
    {
        $validstatuses = $this->getAssignableStatuses();
        if ($newstatus == $this->getData("status")) {
            return false;
        }
        if (!in_array($newstatus, $validstatuses)) {
            return false;
        }
        \App\Models\Ticket::where(array("id" => $this->getData("id")))->update(array("status" => $newstatus));
        $this->log("Status changed to " . $newstatus);
        Hooks::run_hook("TicketStatusChange", array("ticketid" => $this->getData("id"), "status" => $newstatus));
        return true;
    }
    public function setSubject($newsubject)
    {
        $newsubject = trim($newsubject);
        if (!$newsubject) {
            return false;
        }
        if ($newsubject == $this->getData("title")) {
            return false;
        }
        \App\Models\Ticket::where(array("id" => $this->getData("id")))->update(array("title" => $newsubject));
        $this->log("Subject changed to '" . $newsubject . "'");
        Hooks::run_hook("TicketSubjectChange", array("ticketid" => $this->getData("id"), "subject" => $newsubject));
        return true;
    }
    public function setFlagTo($adminid)
    {
        $adminid = (int) $adminid;
        $validadminids = $this->getFlaggableStaff();
        if ($adminid != 0 && !array_key_exists($adminid, $validadminids)) {
            return false;
        }
        if ($adminid == $this->getData("flag")) {
            return false;
        }
        if (0 < $adminid) {
			$data = \App\Models\Admin::where(array("id" => $adminid));
            if (!$data->value("id")) {
                return false;
            }
            $adminname = trim($data->value("firstname") . " " . $data->value("lastname"));
            if (!$adminname) {
                $adminname = $data->value("username");
            }
        } else {
            if ($adminid < 0) {
                $adminid = 0;
            }
        }
        if (!count($this->data)) {
            $this->getData();
        }
        \App\Models\Ticket::where(array("id" => $this->getData("id")))->update(array("flag" => $adminid));
        if (0 < $adminid) {
            $this->log("Assigned to Staff Member " . $adminname);
        } else {
            $this->log("Staff Assignment Removed");
        }
        Hooks::run_hook("TicketFlagged", array("ticketid" => $this->getData("id"), "adminid" => $adminid, "adminname" => $adminname));
        return true;
    }
    public function setPriority($newpriority)
    {
        $validpriorities = $this->getPriorities();
        if ($newpriority == $this->getData("urgency")) {
            return false;
        }
        if (!in_array($newpriority, $validpriorities)) {
            return false;
        }
        \App\Models\Ticket::where(array("id" => $this->getData("id")))->update(array("urgency" => $newpriority));
        $this->log("Priority changed to " . $newpriority);
        Hooks::run_hook("TicketPriorityChange", array("ticketid" => $this->getData("id"), "priority" => $newpriority));
        return true;
    }
    public function sendAdminEmail($tplname, $adminid = "", $notifydeptadmins = false, $vars = array(), $getlatestmsg = false)
    {
        $messagetxt = "";
        if ($getlatestmsg) {
			$messagetxt = \App\Models\Ticketreply::where(array("tid" => $this->getData("id")))->orderBy("id", "DESC")->value("message") ?? "";
        }
        $tplvars = array("ticket_id" => $this->getData("id"), "ticket_tid" => $this->getData("tid"), "client_id" => $this->getData("userid"), "client_name" => $this->getClientName(), "ticket_department" => $this->getDeptName(), "ticket_subject" => $this->getData("title"), "ticket_priority" => $this->getData("urgency"), "ticket_message" => $this->formatMsg($messagetxt));
        if (is_array($vars)) {
            foreach ($vars as $k => $v) {
                $tplvars[$k] = $v;
            }
        }
        \App\Helpers\Functions::sendAdminMessage($tplname, $tplvars, "support", $this->getData("did"), $adminid, $notifydeptadmins);
    }
    public function log($msg)
    {
        \App\Helpers\Ticket::addTicketLog($this->getData("id"), $msg);
    }
    public function addTagCloudID($ticketid)
    {
        $this->tagticketids[] = (int) $ticketid;
    }
    public function getTagTicketIds()
    {
        return $this->tagticketids;
    }
    public function getTagCloudData()
    {
        if (!count($this->tagticketids)) {
            return array();
        }
        $tags = array();
        $result = DB::select(DB::raw("SELECT `tag`, COUNT(*) AS `count` FROM `tbltickettags` WHERE ticketid IN (" . \App\Helpers\Database::db_build_in_array($this->tagticketids) . ") GROUP BY `tag` ORDER BY `count` DESC"));
		// HOTFIX: this
		dd($result);
        foreach ($result->toArray() as $data) {
            $tags[] = $data;
        }
        return $tags;
    }
    public function buildTagCloud()
    {
        $tags = $this->getTagCloudData();
        $tagcount = count($tags);
        if ($tagcount) {
            $numtags = $tagcount / 10;
            $numtags = ceil($numtags);
            $output = "";
            $fontsize = "24";
            $i = 0;
            foreach ($tags as $tag) {
                $thisfontsize = $fontsize;
                if ($tag["count"] <= 1) {
                    $thisfontsize = "12";
                }
                $tagcontent = strip_tags($tag["tag"]);
                $tagcontent = htmlspecialchars($tagcontent);
                $output .= "<a href=\"supporttickets.php?tag=" . $tagcontent . "\" style=\"font-size:" . $thisfontsize . "px;\">" . $tagcontent . "</a> ";
                $i++;
                if ($i == $numtags) {
                    $fontsize -= 2;
                    $i = 0;
                }
            }
        } else {
            $output = "None";
        }
        return $output;
    }
    public function getShortLastReplyTime($lastreply)
    {
        return \App\Helpers\Ticket::getShortLastReplyTime($lastreply);
    }
    public function getLastReplyTime($lastreply = "", $from = "now")
    {
        return \App\Helpers\Ticket::getLastReplyTime($lastreply);
    }
    public function getSummary($text, $length = 100)
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
    public function getStatusColour($tstatus)
    {
        global $_LANG;
        static $ticketcolors = array();
        if (!array_key_exists($tstatus, $ticketcolors)) {
            $ticketcolors[$tstatus] = $color = \App\Models\Ticketstatus::where(array("title" => $tstatus))->value("color") ?? "";
        } else {
            $color = $ticketcolors[$tstatus];
        }
        $langstatus = preg_replace("/[^a-z]/i", "", strtolower($tstatus));
        if ($langstatus != "" && !empty($_LANG["supportticketsstatus" . $langstatus])) {
            $tstatus = $_LANG["supportticketsstatus" . $langstatus];
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
    public function getReplies()
    {
        $id = $this->getData("id");
        $replies = array();
        $result = \App\Models\Ticket::selectRaw("userid,contactid,name,email,date,title,message,admin,attachment,attachments_removed")->where(array("id" => $id));
        $data = $result;
        $userid = $data->value("userid");
        $contactid = $data->value("contactid");
        $name = $data->value("name");
        $email = $data->value("email");
        $date = $data->value("date");
        $title = $data->value("title");
        $message = $data->value("message");
        $admin = $data->value("admin");
        $attachment = $data->value("attachment");
        $attachmentsRemoved = (bool) (int) $data->value("attachments_removed");
        $friendlydate = substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ? Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F") : Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
        $friendlytime = date("H:i", strtotime($date));
        $date = (new \App\Helpers\Functions)->fromMySQLDate($date, true);
        $message = $this->formatMsg($message);
        if ($userid) {
            $name = \App\Helpers\ClientHelper::outputClientLink(array($userid, $contactid));
        }
        $attachments = $this->getTicketAttachmentsInfo("", $attachment, $attachmentsRemoved);
        $replies[] = array("id" => 0, "admin" => $admin, "userid" => $userid, "contactid" => $contactid, "clientname" => $name, "clientemail" => $email, "date" => $date, "friendlydate" => $friendlydate, "friendlytime" => $friendlytime, "message" => $message, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "numattachments" => count($attachments));
		$result = \App\Models\Ticketreply::where(array("tid" => $id))->orderBy("data", "ASC")->get();
        foreach ($result->toArray() as $data) {
            $replyid = $data["id"];
            $userid = $data["userid"];
            $contactid = $data["contactid"];
            $name = $data["name"];
            $email = $data["email"];
            $date = $data["date"];
            $message = $data["message"];
            $attachment = $data["attachment"];
            $attachmentsRemoved = (bool) (int) $data["attachments_removed"];
            $admin = $data["admin"];
            $rating = $data["rating"];
            $friendlydate = substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ? Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F") : Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
            $friendlytime = date("H:i", strtotime($date));
            $date = (new \App\Helpers\Functions)->fromMySQLDate($date, true);
            $message = $this->formatMsg($message);
            if ($userid) {
                $name = \App\Helpers\ClientHelper::outputClientLink(array($userid, $contactid));
            }
            $attachments = $this->getTicketAttachmentsInfo($replyid, $attachment, $attachmentsRemoved);
            $ratingstars = "";
            if ($admin && $rating) {
                for ($i = 1; $i <= 5; $i++) {
                    $ratingstars .= $i <= $rating ? "<img src=\"../images/rating_pos.png\" align=\"absmiddle\">" : "<img src=\"../images/rating_neg.png\" align=\"absmiddle\">";
                }
            }
            $replies[] = array("id" => $replyid, "admin" => $admin, "userid" => $userid, "contactid" => $contactid, "clientname" => $name, "clientemail" => $email, "date" => $date, "friendlydate" => $friendlydate, "friendlytime" => $friendlytime, "message" => $message, "attachments" => $attachments, "numattachments" => count($attachments), "rating" => $ratingstars);
        }
        if (Cfg::get("SupportTicketOrder") == "DESC") {
            krsort($replies);
        }
        return $replies;
    }
    public function formatMsg($message = "")
    {
        if (!$message) {
            $message = $this->getData("message");
        }
        $message = strip_tags($message);
        $message = preg_replace("/\\[div=\"(.*?)\"\\]/", "<div class=\"\$1\">", $message);
        $replacetags = array("b" => "strong", "i" => "em", "u" => "ul", "div" => "div");
        foreach ($replacetags as $k => $v) {
            $message = str_replace("[" . $k . "]", "<" . $k . ">", $message);
            $message = str_replace("[/" . $k . "]", "</" . $k . ">", $message);
        }
        $message = nl2br($message);
        $message = \App\Helpers\Functions::autoHyperLink($message);
        return $message;
    }
    public function getTicketAttachmentsInfo($replyid, $attachment, $removed = false)
    {
        $ticketid = $this->getData("id");
        $attachments = array();
        if ($attachment) {
            $attachment = explode("|", $attachment);
            foreach ($attachment as $num => $file) {
                $file = substr($file, 7);
                if ($removed) {
                    $attachments[] = array("filename" => $file, "dllink" => "", "deletelink" => "");
                    continue;
                }
                if ($replyid) {
                    // TODO: $attachments[] = array("filename" => $file, "dllink" => "dl.php?type=ar&id=" . $replyid . "&i=" . $num, "deletelink" => (string) $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&type=r&idsd=" . $replyid . "&filecount=" . $num . generate_token("link"));
                    $attachments[] = array("filename" => $file, "dllink" => "dl.php?type=ar&id=" . $replyid . "&i=" . $num, "deletelink" => (string) $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&type=r&idsd=" . $replyid . "&filecount=" . $num);
                } else {
                    // TODO: $attachments[] = array("filename" => $file, "dllink" => "dl.php?type=a&id=" . $ticketid . "&i=" . $num, "deletelink" => (string) $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&idsd=" . $ticketid . "&filecount=" . $num . generate_token("link"));
                    $attachments[] = array("filename" => $file, "dllink" => "dl.php?type=a&id=" . $ticketid . "&i=" . $num, "deletelink" => (string) $PHP_SELF . "?action=viewticket&id=" . $ticketid . "&removeattachment=true&idsd=" . $ticketid . "&filecount=" . $num);
                }
            }
        }
        return $attachments;
    }
    public function getNotes()
    {
        $notes = array();
		$result = \App\Models\Ticketnote::where(array("ticketid" => $this->getData("id")))->orderBy("date", "DESC")->get();
        foreach ($result->toArray() as $data) {
            $date = $data["date"];
            $friendlydate = substr($date, 0, 10) == date("Y-m-d") ? "" : (substr($date, 0, 4) == date("Y") ? Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F") : Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("l jS F Y"));
            $friendlytime = date("H:i", strtotime($date));
            $notes[] = array("id" => $data["id"], "admin" => $data["admin"], "date" => (new \App\Helpers\Functions)->fromMySQLDate($date, true), "friendlydate" => $friendlydate, "friendlytime" => $friendlytime, "message" => $this->formatMsg($data["message"]));
        }
        return $notes;
    }
    public function getFlaggableStaff()
    {
        $staff = array();
		$result = \App\Models\Admin::selectRaw("id,firstname,lastname")->whereRaw("disabled=0 OR id='" . (int) $this->getData("flag") . "'")->orderBy("firstname", "ASC")->orderBy("lastname", "ASC")->get();
        foreach ($result->toArray() as $data) {
            $staff[$data["id"]] = $data["firstname"] . " " . $data["lastname"];
        }
        return $staff;
    }
    public function getPriorities()
    {
        return array("Low", "Medium", "High");
    }
    public function getAllowedAttachments()
    {
        global $whmcs;
        $filetypes = Cfg::get("TicketAllowedFileTypes");
        $filetypes = explode(",", $filetypes);
        foreach ($filetypes as $k => $v) {
            $filetypes[$k] = trim($v);
        }
        return $filetypes;
    }
    public static function notifyTicketChanges($ticketId, array $changes, array $recipients = array(), array $removeRecipients = array())
    {
		$adminid = Auth::guard('admin')->check() ? Auth::guard('admin')->id() : 0;
		$ticket = new self();
        if ($ticket->setID($ticketId)) {
            $mergeFields = array();
            $mergeFields["ticket_id"] = $ticketId;
            $mergeFields["ticket_tid"] = $ticket->getData("tid");
            if (!empty($changes["Reply"])) {
                $markup = new \App\Helpers\ViewMarkup();
                $markupFormat = $markup->determineMarkupEditor("ticket_reply", $ticket->getData("editor"));
                $mergeFields["newReply"] = $markup->transform($changes["Reply"]["new"], $markupFormat);
                unset($changes["Reply"]);
            }
            if (!empty($changes["Note"])) {
                if (!isset($markup)) {
                    $markup = new \App\Helpers\ViewMarkup();
                }
                $markupFormat = $markup->determineMarkupEditor("ticket_note", $changes["note"]["editor"]);
                $mergeFields["newNote"] = $markup->transform($changes["Note"]["new"], $markupFormat);
                unset($changes["Note"]);
            }
            if (!empty($changes["Opened"]) && !isset($markup)) {
                $markup = new \App\Helpers\ViewMarkup();
                $markupFormat = $markup->determineMarkupEditor("ticket_note", $ticket->getData("editor"));
                $mergeFields["newTicket"] = $markup->transform($changes["Opened"]["new"], $markupFormat);
            }
            if (!empty($changes["Attachments"])) {
                $mergeFields["newAttachments"] = $changes["Attachments"];
                unset($changes["Attachments"]);
            }
            $mergeFields["changer"] = $changes["Who"];
            unset($changes["Who"]);
            $mergeFields["changes"] = $changes;
            $mergeFields["client_name"] = $ticket->getClientName();
            $mergeFields["client_id"] = $ticket->getData("userid");
            $mergeFields["ticket_department"] = $ticket->getDeptName();
            $mergeFields["ticket_subject"] = $ticket->getData("title");
            $mergeFields["ticket_priority"] = $ticket->getData("urgency");
            $includeFlagged = true;
            if (!empty($changes["Assigned To"])) {
                if ($changes["Assigned To"]["newId"] == $adminid) {
                    $includeFlagged = false;
                }
                if ($changes["Assigned To"]["oldId"] && $changes["Assigned To"]["oldId"] != $adminid) {
                    $recipients = array_merge($recipients, array($changes["Assigned To"]["oldId"]));
                }
            }
            if (!empty($changes["Department"])) {
                $recipients = array_merge($recipients, \App\Helpers\Ticket::getDepartmentNotificationIds($changes["Department"]["newId"]));
            }
            $recipients = array_unique(array_merge(0 < $ticket->getData("flag") && $includeFlagged ? array($ticket->getData("flag")) : array(), $recipients, \App\Models\TicketWatcher::ofTicket($ticket->ticketid)->pluck("admin_id")->all()));
            if ($removeRecipients) {
                $recipients = array_filter($recipients, function ($value) use($removeRecipients) {
                    return !in_array($value, $removeRecipients);
                });
            }
            $recipients = array_flip($recipients);
            unset($recipients[(int) $adminid]);
            $recipients = array_flip($recipients);
            if (0 < count($recipients)) {
                return \App\Helpers\Functions::sendAdminMessage("Support Ticket Change Notification", $mergeFields, "ticket_changes", $ticket->getData("did"), $recipients);
            }
        }
        return false;
    }
    public function getDepartmentFeedbackNotifications()
    {
        $this->getDepartments();
        if (!$this->departmentFeedbackRequest) {
            return false;
        }
        return isset($this->departmentFeedbackRequest[$this->getData("did")]) ? (bool) (int) $this->departmentFeedbackRequest[$this->getData("did")] : false;
    }
}
