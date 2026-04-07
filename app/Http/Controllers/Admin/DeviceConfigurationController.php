<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceConfigurationController extends Controller
{
    //
    private function createErrorResponse($message = NULL, $code = 500)
    {
        if (is_null($message)) {
            $message = "This request could not be processed.";
        }
        $data = array("errorMsg" => $message);
        if (is_array($message)) {
            $data = $message;
        }
        return response()->json($data, $code);
    }
    public function getDevices(Request $request)
    {
        try {
            $adminDevices = \App\Models\Deviceauth::where("is_admin", "=", 1)->get();
            $tableData = (new \App\Helpers\DeviceHelper())->getTableData($adminDevices);
            return response()->json(array("data" => $tableData));
        } catch (\Exception $e) {
            return $this->createErrorResponse();
        }
    }
    public function generate(Request $request)
    {
        try {
            $device = \App\Models\Deviceauth::newAdminDevice(\App\User\Admin::find($request->input("admin_id")), $request->input("description"));
            $roles = $request->input("roleIds") ?? array();
            if (!empty($roles)) {
                $foundRoles = \Spatie\Permission\Models\Role::whereIn("id", $roles)->get();
                foreach ($foundRoles as $role) {
                    $device->addRole($role);
                }
                $secret = $device->secret;
                $device->save();
                $msg = sprintf("Created API Credential identifier \"%s\" for Admin \"%d: %s\"", $device->identifier, $device->admin->id, $device->admin->username);
                \App\Helpers\LogActivity::Save($msg);
                $data = array("body" => view("authentication.partials.generated-api-credentials", array("identifier" => $device->identifier, "secret" => $secret))->render());
                return response()->json($data);
            } else {
                return $this->createErrorResponse(array("status" => "error", "errorMsg" => "At least one role must be assigned."), 200);
            }
        } catch (\Exception $e) {
            return $this->createErrorResponse();
        }
    }
    public function manage(Request $request, $id = 0)
    {
        $deviceId = $id ? $id : $request->input("id");
        $device = \App\Models\Deviceauth::find($deviceId);
        if (!$device) {
            return $this->createErrorResponse();
        }
        $roles = \Spatie\Permission\Models\Role::where(['guard_name' => 'api'])->get();
        $htmlPartial = view("authentication.partials.edit-api-credentials", array("device" => $device, "roles" => $roles))->render();
        return response()->json(array("body" => $htmlPartial));
    }
    public function update(Request $request, $id = 0)
    {
        $deviceId = $id ? $id : $request->input("id");
        $device = \App\Models\Deviceauth::find($deviceId);
        if (!$device) {
            return $this->createErrorResponse();
        }
        $device->description = $request->input("description");
        $currentRoles = $device->rolesCollection();
        $roleIds = $request->input("roleIds") ?? array();
        if ($roleIds) {
            $roles = \Spatie\Permission\Models\Role::whereIn("id", $roleIds)->where('guard_name', 'api')->get();
        } else {
            $roles = array();
        }
        if (count($roles) === 0) {
            return $this->createErrorResponse(array("status" => "error", "errorMsg" => "At least one role must be assigned."), 200);
        }
        foreach ($currentRoles as $roleId => $role) {
            $device->removeRole($role);
            if (!$roles->find($roleIds)) {
            }
        }
        foreach ($roles as $role) {
            $device->addRole($role);
        }
        $device->save();
        return response()->json(array("status" => "success", "dismiss" => true));
    }
    public function delete(Request $request, $id = 0)
    {
        try {
            $device = \App\Models\Deviceauth::find($request->input("id") ?? $id);
            if ($device) {
                $identifier = $device->identifier;
                if ($device->delete()) {
                    $msg = sprintf("Deleted API Credential identifier \"%s\" for Admin \"%d: %s\"", $identifier, $device->admin->id, $device->admin->username);
                    \App\Helpers\LogActivity::Save($msg);
                }
            }
            $data = array("status" => "okay");
            return response()->json($data);
        } catch (\Exception $e) {
            return $this->createErrorResponse();
        }
    }
    public function createNew(Request $request)
    {
        $adminUserSelectOptions = array();
        $adminUsers = \App\User\Admin::orderBy("firstname")->orderBy("lastname")->get();
        foreach ($adminUsers as $admin) {
            $adminUserSelectOptions[] = "<option value=\"" . $admin->id . "\">" . $admin->firstname . " " . $admin->lastname . "</option>";
        }
        $adminUserSelectOptions = implode("\n", $adminUserSelectOptions);
        $roles = \Spatie\Permission\Models\Role::where(['guard_name' => 'api'])->get();
        $body = view("authentication.partials.create-api-credentials", array("adminUserSelectOptions" => $adminUserSelectOptions, "roles" => $roles))->render();
        return response()->json(array("title" => \Lang::get("admin.apicredentialscreate"), "body" => $body));
    }
    public function index(Request $request)
    {
        $aInt = new \App\Helpers\Admin("Manage API Credentials", false);
        // $aInt->title = \Lang::get("admin.setup.apicredentials");
        // $aInt->sidebar = "config";
        // $aInt->icon = "admins";
        // $aInt->helplink = "API_Authentication_Credentials";
        // $aInt->setResponseType($aInt::RESPONSE_HTML_MESSAGE);
        $apiCatalog = \App\Helpers\ApiCatalog::getPermissions();
        $modalRole = $aInt->modal("NewAPIRole", \Lang::get("admin.apirolecreate"), view("authorization.partials.api-role-detail", array("apiCatalog" => $apiCatalog))->render(), array(array("title" => "Cancel"), array("type" => "submit", "title" => \Lang::get("admin.generalsave"), "class" => "btn-primary", "onclick" => "false")), "large", "primary");
        // return view("authentication.manage-api-credentials", array("modalRole" => $modalRole, "csrfToken" => $csrfToken));
        return view('authentication.manage-api-credentials', compact('modalRole'));
    }
}
