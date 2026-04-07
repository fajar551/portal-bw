<?php

namespace App\Mail\Entity;

use App\Helpers\Cfg;

class Admin extends \App\Helpers\Emailer
{
    protected $isNonClientEmail = true;
    public function __construct($message, $entityId, $extraParams = NULL)
    {
        parent::__construct($message, $entityId, $extraParams);
        $this->message->setFromName(Cfg::getValue("SystemEmailsFromName"));
        $this->message->setFromEmail(Cfg::getValue("SystemEmailsFromEmail"));
    }
    protected function getEntitySpecificMergeData($userId, $extra)
    {
        $adminUrl = config('app.url');
        $adminUrl .= "/" . env('ADMIN_ROUTE_PREFIX')."/";
        $this->massAssign(array("whmcs_admin_url" => $adminUrl, "whmcs_admin_link" => "<a href=\"" . $adminUrl . "\">" . $adminUrl . "</a>"));
    }
    public function determineAdminRecipientsAndSender($to, $deptid, $adminid, $ticketnotify)
    {
        if ($deptid) {
            $result = \App\Models\Ticketdepartment::find($deptid);
            $data = $result->toArray();
            $fromEmail = $data["email"];
            $fromName = Cfg::getValue("CompanyName") . " " . $data["name"];
            $this->message->setFromName($fromName);
            $this->message->setFromEmail($fromEmail);
        }
        if ($adminid) {
            if (is_array($adminid)) {
                $where = "tbladmins.disabled = 0 AND tbladmins.id IN (" . \App\Helpers\Database::db_build_in_array($adminid) . ")";
            } else {
                $where = "tbladmins.disabled=0 AND tbladmins.id='" . (int) $adminid . "'";
            }
        } else {
            if (in_array($to, array("ticket_changes", "mentions"))) {
                return false;
            }
            $where = "tbladmins.disabled=0 AND tbladminroles." . \App\Helpers\Database::db_escape_string($to) . "emails='1'";
            if ($deptid) {
                $where .= " AND tbladmins.ticketnotifications!=''";
            }
        }
        // $result = select_query("tbladmins", "firstname,lastname,email,supportdepts,ticketnotifications", $where, "", "", "", "tbladminroles ON tbladminroles.id=tbladmins.roleid");
        $result = \App\Models\Admin::select("firstname","lastname","email","supportdepts","ticketnotifications")->whereRaw($where)->join("tbladminroles", "tbladminroles.id", "=", "tbladmins.roleid")->get();
        foreach ($result->toArray() as $data) {
            if ($data["email"]) {
                $adminsend = true;
                if ($ticketnotify) {
                    $ticketnotifications = $data["ticketnotifications"];
                    $ticketnotifications = explode(",", $ticketnotifications);
                    if (!$adminid && !in_array($deptid, $ticketnotifications)) {
                        $adminsend = false;
                    }
                } else {
                    if ($deptid) {
                        $supportdepts = $data["supportdepts"];
                        $supportdepts = explode(",", $supportdepts);
                        if (!$adminid && !in_array($deptid, $supportdepts)) {
                            $adminsend = false;
                        }
                    }
                }
                if ($adminsend) {
                    $this->message->addRecipient("to", $data["email"], $data["firstname"] . " " . $data["lastname"]);
                }
            }
        }
    }
}

?>
