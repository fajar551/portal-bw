<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class DeviceHelper
{
	public function getTableData($devices)
    {
        $tableData = array();
        foreach ($devices as $device) {
            $roles = $device->rolesCollection();
            if ($roles) {
                $roleNames = array();
                foreach ($roles as $role) {
                    $roleNames[] = $role->name;
                }
                $roleNames = implode(", ", $roleNames);
            } else {
                $roleNames = \Lang::get("admin.none");
            }
            $tableData[] = array($device->identifier, $this->getDescriptionBtn($device), $device->admin->fullName . " (" . $device->admin->username . ")", $roleNames, $this->getLastAccessText($device), $this->getActionBtnGroup($device->id));
        }
        return $tableData;
    }
    protected function getLastAccessText(\App\Models\Deviceauth $device)
    {
        if ($device->last_access == "0000-00-00 00:00:00" || empty($device->last_access) || substr($device->last_access, 0, 1) == "-") {
            return \Lang::get("admin.billableitemsnever");
        }
        return \Carbon\Carbon::parse($device->last_access)->diffForHumans();
    }
    protected function getDescriptionBtn(\App\Models\Deviceauth $device)
    {
        $description = $device->description ?: "None provided";
        return sprintf("<a href=\"#\" \n            data-type=\"text\" \n            data-name=\"description\" \n            data-pk=\"%s\"\n            data-url=\"configapicredentials.php\" \n            data-title=\"Enter description\" \n            class=\"inline-editable\"\n            >%s</a>", $device->id, $description);
    }
    protected function getActionBtnGroup($id)
    {
        $btnDelete = sprintf("<div class=\"btn btn-default btn-sm\" data-toggle=\"confirmation\"\n                    id=\"btnDeviceDeleteId%d\"\n                    data-btn-ok-label=\"%s\"\n                    data-btn-ok-icon=\"fas fa-trash-alt\"\n                    data-btn-ok-class=\"btn-success\"\n                    data-btn-cancel-label=\"%s\"\n                    data-btn-cancel-icon=\"fas fa-ban\"\n                    data-btn-cancel-class=\"btn-default\"\n                    data-title=\"%s\"\n                    data-content=\"%s\"\n                    data-popout=\"true\"\n                    data-placement=\"left\"\n                    data-container=\"#btnDeviceConf%d\"\n                    onclick=\"deleteDeveice(this)\"\ndata-target-url=\"%s/%d\"\n                    ><i class=\"fas fa-trash-alt\"></i></div>", $id, \Lang::get("admin.delete"), \Lang::get("admin.cancel"), \Lang::get("admin.areYouSure"), \Lang::get("admin.deleteconfirmitem"), $id, route("admin.admin-setup-authz-api-devices-delete"), $id);
        $btnEdit = sprintf("<a href=\"%s/%d\"\n               id=\"btnDeviceUpdateId%d\"\n               data-modal-title=\"Credential Management\"\n               data-btn-submit-id=\"btnUpdateDevice\"\n               data-datatable-reload-success=\"tblDevice\"\n               data-btn-submit-label=\"%s\"\n               onclick=\"return false;\"\n               class=\"btn btn-default btn-sm open-modal\"\n               ><i class=\"fas fa-pencil-alt\"></i></a>", route("admin.admin-setup-authz-api-devices-manage"), $id, $id, \Lang::get("admin.save"));
        return sprintf("<div id=\"btnDeviceConf%d\"></div>\n            <div class=\"btn-group pull-right\" id=\"btnDeviceGroupId%d\">\n            %s\n            %s\n            </div>", $id, $id, $btnEdit, $btnDelete);
    }
}
