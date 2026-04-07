<?php

namespace App\Helpers\Log;

class Activity
{
    protected $criteria = array();
    protected $outputFormatting = true;
    public function setOutputFormatting($enable)
    {
        $this->outputFormatting = $enable ? true : false;
    }
    public function getOutputFormatting()
    {
        return $this->outputFormatting;
    }
    public function prune()
    {
        $activitylimit = (int) \App\Helpers\Cfg::get("ActivityLimit");
        // $result = select_query("tblactivitylog", "", "userid=0", "id", "DESC", $activitylimit . ",9999");
        $result = \App\Models\ActivityLog::where('userid', "0")->orderBy("id", "DESC")->offset($activitylimit)->limit(9999)->get();
        foreach ($result->toArray() as $data) {
            \App\Models\ActivityLog::where(array("id" => $data["id"]))->delete();
        }
        return true;
    }
    public function setCriteria($where)
    {
        if (is_array($where)) {
            $this->criteria = $where;
            return true;
        }
        return false;
    }
    public function getCriteria($key)
    {
        return array_key_exists($key, $this->criteria) ? $this->criteria[$key] : "";
    }
    protected function buildCriteria()
    {
        $userid = $this->getCriteria("userid");
        $date = $this->getCriteria("date");
        $description = $this->getCriteria("description");
        $username = $this->getCriteria("username");
        $ipaddress = $this->getCriteria("ipaddress");
        $where = array();
        if ($userid) {
            $where[] = "userid='" . (int) $userid . "'";
        }
        if ($date) {
            $where[] = "date>'" . (new \App\Helpers\SystemHelper)->toMySQLDate($date) . "' AND date<='" . (new \App\Helpers\SystemHelper)->toMySQLDate($date) . " 23:59:59'";
        }
        if ($description) {
            $where[] = "description LIKE '%" . \App\Helpers\Database::db_escape_string($description) . "%'";
        }
        if ($username) {
            $where[] = "user='" . \App\Helpers\Database::db_escape_string($username) . "'";
        }
        if ($ipaddress) {
            $where[] = " ipaddr='" . \App\Helpers\Database::db_escape_string($ipaddress) . "'";
        }
        return implode(" AND ", $where);
    }
    public function getTotalCount()
    {
        // $result = select_query("tblactivitylog", "COUNT(id)", $this->buildCriteria());
        $result = \App\Models\ActivityLog::whereRaw($this->buildCriteria())->count();
        $data = $result;
        return $data;
    }
    public function getLogEntries($page = 0, $limit = 0)
    {
        $page = (int) $page;
        $limit = (int) $limit;
        if (!$limit) {
            $limit = (int) \App\Helpers\Cfg::get("NumRecordstoDisplay");
        }
        $logs = array();
        $result = \App\Models\ActivityLog::whereRaw($this->buildCriteria())->orderBy("id", "DESC")->offset($page * $limit)->limit($limit)->get();
        foreach ($result->toArray() as $data) {
            $id = $data["id"];
            $userid = $data["userid"];
            $date = $data["date"];
            $description = $data["description"];
            $username = $data["user"];
            $ipaddress = $data["ipaddr"];
            if ($this->getOutputFormatting()) {
                $date = (new \App\Helpers\Functions)->fromMySQLDate($date, true);
                $description = \App\Helpers\Sanitize::makeSafeForOutput($description);
                $username = \App\Helpers\Sanitize::makeSafeForOutput($username);
                $ipaddress = \App\Helpers\Sanitize::makeSafeForOutput($ipaddress);
                $description = $this->autoLink($description);
            }
            $logs[] = array("id" => (int) $id, "userid" => (int) $userid, "date" => $date, "description" => $description, "username" => $username, "ipaddress" => $ipaddress);
        }
        return $logs;
    }
    public function autoLink($description)
    {
        $patterns = $replacements = array();
        $patterns[] = "/User ID: (.*?) - Contact ID: (.*?) /";
        $patterns[] = "/User ID: (.*?) (?!- Contact)/";
        $patterns[] = "/Service ID: (.*?) /";
        $patterns[] = "/Service Addon ID: (\\d+)(\\D*?)/";
        $patterns[] = "/Domain ID: (.*?) /";
        $patterns[] = "/Invoice ID: (.*?) /";
        $patterns[] = "/Quote ID: (.*?) /";
        $patterns[] = "/Order ID: (.*?) /";
        $patterns[] = "/Transaction ID: (.*?) /";
        $patterns[] = "/Product ID: (\\d+)(\\D*?)/";
        $replacements[] = "<a href=\"clientscontacts.php?userid=\$1&contactid=\$2\">Contact ID: \$2</a> ";
        $replacements[] = "<a href=\"clientssummary.php?userid=\$1\">User ID: \$1</a> ";
        $replacements[] = "<a href=\"clientsservices.php?id=\$1\">Service ID: \$1</a> ";
        $replacements[] = "<a href=\"clientsservices.php?aid=\$1\">Service Addon ID: \$1</a>";
        $replacements[] = "<a href=\"clientsdomains.php?id=\$1\">Domain ID: \$1</a> ";
        $replacements[] = "<a href=\"invoices.php?action=edit&id=\$1\">Invoice ID: \$1</a> ";
        $replacements[] = "<a href=\"quotes.php?action=manage&id=\$1\">Quote ID: \$1</a> ";
        $replacements[] = "<a href=\"orders.php?action=view&id=\$1\">Order ID: \$1</a> ";
        $replacements[] = "<a href=\"transactions.php?action=edit&id=\$1\">Transaction ID: \$1</a> ";
        $replacements[] = "<a href=\"configproducts.php?action=edit&id=\$1\">Product ID: \$1</a>";
        $description = preg_replace($patterns, $replacements, $description . " ");
        return trim($description);
    }
}

?>