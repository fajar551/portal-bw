<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DomainStatus
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
        if (defined("ADMINAREA")) {
			// TODO: \AdminLang
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
