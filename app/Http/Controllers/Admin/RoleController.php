<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class RoleController extends Controller
{
    //
    public function listRoles(Request $request)
    {
        $roles = \Spatie\Permission\Models\Role::where("id", ">", 0)->orderBy("name", "asc")->where('guard_name', 'api')->get();
        $helper = new \App\Helpers\RoleHelper();
        return response()->json(array("data" => $helper->getTableData($roles)));
    }
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $roleId = $request->input("roleId") ?? 0;
            if ($roleId) {
                return $this->update($request);
            }
            $statusCode = 200;
            $roleName = trim($request->input("roleName") ?? "");
            if (!$roleName) {
                return response()->json(array("status" => "error", "errorMsg" => "Role name cannot be empty."));
            }
            if (\Spatie\Permission\Models\Role::where("name", "=", $roleName)->where('guard_name', 'api')->count()) {
                return response()->json(array("status" => "error", "errorMsg" => "A role with that name already exists. A role name must be unique."));
            }
            $roleDesc = $request->input("roleDescription") ?? "";
            $requestedAllow = $request->input("allow") ?? array();
            $catalogActions = \App\Helpers\ApiCatalog::getPermissions();
            $allowed = array();
            foreach ($catalogActions as $action) {
                if (array_key_exists($action, $requestedAllow)) {
                    array_push($allowed, $action);
                }
            }

            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api', 'description' => $roleDesc]);
            foreach ($allowed as $permission) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }

            $msg = sprintf("Created role \"%d%s\" with access to \"%s\"", $role->id, $role->name ? ": " . $role->name : "", implode(", ", array_keys(array_filter($role->permissions->pluck('name')->all()))));
            \App\Helpers\LogActivity::Save($msg);
            $data = array("status" => "success", "data" => $role->toArray(), "dismiss" => true);

            DB::commit();
            return response()->json($data, $statusCode);
        } catch (\Exception $e) {
            DB::rollback();
            $data = array("status" => "error", "errorMessage" => $e->getMessage());
            return response()->json($data, 200);
        }
    }
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $statusCode = 200;
            $roleId = $request->input("roleId") ?? 0;
            $role = \Spatie\Permission\Models\Role::find($roleId);
            $logMsgs = array();
            if ($role) {
                $newName = trim($request->input("roleName") ?? "");
                if (!$newName) {
                    return response()->json(array("status" => "error", "errorMsg" => "Role name cannot be empty."));
                }
                if ($newName != $role->name) {
                    if (\Spatie\Permission\Models\Role::where("name", "=", $newName)->where('guard_name', 'api')->count()) {
                        return response()->json(array("status" => "error", "errorMsg" => "A role with that name already exists. A role name must be unique."));
                    }
                    $logMsgs[] = sprintf("Role %d%s name changed from \"%s\" to \"%s\"", $roleId, $role->name ? ": " . $role->name : "", $role->name, $newName);
                    $role->name = $newName;
                }
                $newDescription = trim($request->input("roleDescription") ?? "");
                if ($newDescription != $role->description) {
                    $logMsgs[] = sprintf("Role %d%s description changed to \"%s\"", $roleId, $role->name ? ": " . $role->name : "", $newDescription);
                    $role->description = $newDescription;
                }
                $requestedAllow = $request->input("allow") ?? array();
                $catalogActions = \App\Helpers\ApiCatalog::getPermissions();
                $allowed = array();
                foreach ($catalogActions as $action) {
                    if (array_key_exists($action, $requestedAllow)) {
                        array_push($allowed, $action);
                    }
                }
                $previousList = (array_filter($role->permissions->pluck('name')->all()));
                $nowDenied = array_diff($previousList, $allowed);
                if ($nowDenied) {
                    $logMsgs[] = sprintf("Role %d update - permissions revoked: \"%s\"", $roleId, implode(", ", $nowDenied));
                }
                $nowAllowed = array_diff($allowed, $previousList);
                if ($nowAllowed) {
                    $logMsgs[] = sprintf("Role %d update - permissions granted: \"%s\"", $roleId, implode(", ", $nowAllowed));
                }
                // $role->setData(array());
                // $role->allow($allowed);
                if ($role->save()) {
                    $perms = [];
                    foreach ($allowed as $permission) {
                        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
                        // if (!$role->hasPermissionTo($permission)) {
                        //     $role->givePermissionTo($permission);
                        // }
                        $perms[] = $permission;
                    }
                    $role->syncPermissions($perms);
                    foreach ($logMsgs as $msg) {
                        \App\Helpers\LogActivity::Save($msg);
                    }
                    $data = array("status" => "success", "data" => $role->toArray(), "dismiss" => true);
                } else {
                    $statusCode = 500;
                    $data = array("status" => "error", "errorMsg" => "unknown error");
                }
            } else {
                $statusCode = 200;
                $data = array("status" => "error", "errorMsg" => "Unknown roled id " . (int) $roleId);
            }

            DB::commit();
            return response()->json($data, $statusCode);
        } catch (\Exception $e) {
            DB::rollback();
            $data = array("status" => "error", "errorMessage" => $e->getMessage());
            return response()->json($data, 200);
        }
    }
    public function manage(Request $request, $roleId = 0)
    {
        $role = \Spatie\Permission\Models\Role::findOrNew($roleId);
        $htmlPartial = view("authorization.partials.api-role-detail", array("apiCatalog" => \App\Helpers\ApiCatalog::getPermissions(), "role" => $role))->render();
        return response()->json(array("body" => $htmlPartial));
    }
    public function delete(Request $request, $roleId = 0)
    {
        $statusCode = 200;
        DB::beginTransaction();
        try {
            // $roleId = $request->input("roleId", 0);
            $role = \Spatie\Permission\Models\Role::find($roleId);
            if ($role) {
                \App\Models\Deviceauth::purgeRoleFromAllDevices($role);
                $msg = sprintf("Deleted role \"%d%s\" with access to \"\"", $roleId, $role->name ? ": " . $role->name : "", implode(", ", array_values(array_filter($role->permissions->pluck('name')->all()))));
                if ($role->delete()) {
                    \App\Helpers\LogActivity::Save($msg);
                    $data = array("status" => "success", "data" => $role->toArray());
                } else {
                    $statusCode = 500;
                    $data = array("status" => "error", "errorMessage" => "Failed to delete role: unknown error");
                }
            } else {
                $statusCode = 200;
                $data = array("status" => "error", "errorMessage" => "Failed to delete role: Unknown role id");
            }

            DB::commit();
            return response()->json($data, $statusCode);
        } catch (\Exception $e) {
            DB::rollback();
            $data = array("status" => "error", "errorMessage" => $e->getMessage());
            return response()->json($data, $statusCode);
        }
    }
}
