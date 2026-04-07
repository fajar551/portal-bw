<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RoleHelper
{
	public function getTableData($roles)
    {
        $tableData = array();
        // $catalogActions = \App\Helpers\ApiCatalog::get()->getActions();
        $catalogActions = \App\Helpers\ApiCatalog::getPermissions();
        foreach ($roles as $role) {
            $actionNames = $allowedActions = array();
            foreach ($role->permissions->sortBy('name')->values()->all() as $permission) {
                if (in_array($permission->name, $catalogActions)) {
                    $actionNames[] = $permission->name;
                }
            }
            if (empty($actionNames)) {
                $allowedActions[] = \Lang::get("admin.none");
            } else {
                $allowedActions = $actionNames;
            }
            $tableData[] = array("btnExpand" => "<i class=\"fas fa-caret-right text-muted\" aria-hidden=\"true\"></i>", "name" => $role->name, "description" => $role->description, "btnGroup" => $this->getActionBtnGroup($role->id), "allowedActions" => $allowedActions);
        }
        return $tableData;
    }
    protected function getActionBtnGroup($id)
    {
        $btnDelete = sprintf("<div class=\"btn btn-default btn-sm\" data-toggle=\"confirmation\"\n                    id=\"btnRoleDeleteId%d\"\n                    data-btn-ok-label=\"%s\"\n                    data-btn-ok-icon=\"fas fa-trash-alt\"\n                    data-btn-ok-class=\"btn-success\"\n                    data-btn-cancel-label=\"%s\"\n                    data-btn-cancel-icon=\"fas fa-ban\"\n                    data-btn-cancel-class=\"btn-default\"\n                    data-title=\"%s\"\n                    data-content=\"%s\"\n                    data-popout=\"true\"\n                    data-placement=\"left\"\n                    data-container=\"#btnRoleConf%d\"\n                    onclick=\"deleteApiRole(this)\" \ndata-target-url=\"%s/%d\"\n                    >%s</div>", $id, \Lang::get("admin.delete"), \Lang::get("admin.cancel"), \Lang::get("admin.areYouSure"), \Lang::get("admin.deleteconfirmitem"), $id, route("admin.admin-setup-authz-api-roles-delete"), $id, "<i class=\"fas fa-trash-alt\"></i>");
        $btnEdit = sprintf("<a href=\"%s/%d\"\n               data-modal-title=\"Role Management\"\n               data-modal-size=\"modal-lg\"\n               data-modal-class=\"modal-manage-api-role\"\n               data-btn-submit-id=\"btnUpdateApiRole\"\n               data-datatable-reload-success=\"tblApiRoles\"\n               data-btn-submit-label=\"%s\"\n               onclick=\"return false;\"\n               class=\"btn btn-default btn-sm open-modal\"><i class=\"fas fa-pencil-alt\"></i></a>", route("admin.admin-setup-authz-api-roles-manage"), $id, \Lang::get("admin.save"));
        return sprintf("<div id=\"btnRoleConf%d\"></div>\n            <div class=\"btn-group pull-right\" id=\"btnRoleGroupId%d\">\n            %s\n            %s\n            </div>", $id, $id, $btnEdit, $btnDelete);
    }
}
