<?php

namespace App\Helpers\Domain;

use App\Helpers\Application;

class Status
{
    protected $statusValues = NULL;
    const PENDING = "Pending";
    const PENDING_REGISTRATION = "Pending Registration";
    const PENDING_TRANSFER = "Pending Transfer";
    const ACTIVE = "Active";
    const GRACE = "Grace";
    const REDEMPTION = "Redemption";
    const EXPIRED = "Expired";
    const TRANSFERRED_AWAY = "Transferred Away";
    const CANCELLED = "Cancelled";
    const FRAUD = "Fraud";
    public function all()
    {
        return $this->statusValues;
    }
    public function allWithTranslations()
    {
        $statuses = array();
        foreach ($this->statusValues as $status) {
            $statuses[$status] = $this->translate($status);
        }
        return $statuses;
    }
    protected function translate($status)
    {
        $status = strtolower(str_replace(" ", "", $status));
        if (Application::isAdminAreaRequest()) {
            return \Lang::get("status." . $status);
        }
        return \Lang::get("status." . $status);
    }
    public function translatedDropdownOptions(array $selectedStatus = NULL)
    {
        $options = "";
        foreach ($this->allWithTranslations() as $dbValue => $translation) {
            $selected = is_array($selectedStatus) && in_array($dbValue, $selectedStatus) ? " selected=\"selected\"" : "";
            $options .= "<option value=\"" . $dbValue . "\"" . $selected . ">" . $translation . "</option>";
        }
        return $options;
    }
}

?>
