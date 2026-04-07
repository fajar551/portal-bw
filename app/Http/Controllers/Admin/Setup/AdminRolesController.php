<?php

namespace App\Http\Controllers\Admin\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Models\Admin;
use Validator;
use DataTables;

class AdminRolesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }

    public function adminRole_index()
    {
        //* check users who included on specific role //
        // $users = Admin::role('Billing')->get();
        // $roles = Role::where('guard_name', 'admin')->get()->pluck('name', 'id')->toArray();
        // $user = Admin::findOrFail(2);
        // dd($users);
        return view('pages.setup.staffmanagement.administratorroles.index');
    }

    public function getAdministratorRoleList()
    {
        $pfx = $this->prefix;
        $roles = Role::where('guard_name', 'admin')->get();

        return DataTables::of($roles)
            ->addColumn('assignedUser', function ($data) use ($pfx) {
                $assignedUser = \DB::select(\DB::raw("SELECT {$pfx}admins.id, {$pfx}admins.username  FROM {$pfx}admins WHERE {$pfx}admins.roleid = $data->id"));

                $collectUsername = [];
                foreach ($assignedUser as $key => $value) {
                    $route = route('admin.pages.setup.staffmanagement.administratorusers.editform', ['id' => $value->id]);
                    $collectUsername[] = "<a href=\"{$route}\">$value->username</a>";
                }

                $linkUsername = implode(", ", $collectUsername);
                return $linkUsername ? $linkUsername : 'None';
            })
            ->editColumn('name', function ($data) {
                return $data->name;
            })
            ->editColumn('actions', function ($data) {
                $editRoute = route('admin.pages.setup.staffmanagement.administratorroles.edit', $data->id);
                $deleteRoute = route('admin.pages.setup.staffmanagement.administratorroles.delete', $data->id);
                $action = "";

                $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$data->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$data->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->rawColumns(['actions', 'assignedUser'])
            ->addIndexColumn()
            ->toJson();
    }

    public function addAdminRoleForm()
    {
        return view('pages.setup.staffmanagement.administratorroles.add');
    }

    public function createNewAdminRole(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.add')->withErrors($validator)->with(['error' => 'Please fill Role Name!']);
        }

        $getRoleNameInput = $request->name;

        if ($getRoleNameInput) {
            Role::create(['name' => $getRoleNameInput, 'guard_name' => 'admin']);
        }
        $roleLatestId = Role::all()->last()->id;
        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.edit', $roleLatestId);
    }

    public function editAdminRoleForm(Request $request, $id)
    {
        $selectedRole = Role::findOrFail($id);
        $getPermissions = Permission::where('guard_name', 'admin')->get();

        $permissions = array();
        foreach ($getPermissions as $key => $permission) {
            $permissions[] = $permission->name;
        }
        $activePermissionList = $selectedRole->getAllPermissions()->pluck('name')->toArray();

        return view('pages.setup.staffmanagement.administratorroles.edit', ['selectedRole' => $selectedRole, 'permissions' => $permissions, 'activePermissionList' => $activePermissionList]);
    }

    public function updateAdminRole(Request $request, $id)
    {
        $roleName = $request->name; //rolename

        $selectedPermissions = $request->adminperms;
        $unselectedPermissions = $request->adminpermsdisable; //permission name/value

        $role = Role::findOrFail($id);
        $role->name = $roleName;

        //Assign and Revoke Permission To Role
        if ($selectedPermissions && !$unselectedPermissions) {
            foreach ($selectedPermissions as $key => $permission) {
                $permission = Permission::where(['name' => $permission, 'guard_name' => 'admin'])->first();
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } elseif (!$selectedPermissions && $unselectedPermissions) {
            foreach ($unselectedPermissions as $key => $dPermission) {
                $role->revokePermissionTo($dPermission);
            }
        } elseif ($selectedPermissions && $unselectedPermissions) {
            foreach ($unselectedPermissions as $key => $dPermission) {
                $role->revokePermissionTo($dPermission);
            }
            foreach ($selectedPermissions as $key => $permission) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        $role->save();
        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.index')->with(['success' => 'Role has been succesfully updated!']);
    }

    public function deleteAdminRole(Request $request, $id)
    {
        $data = $request->ajax();
        $adminData = Role::findOrFail($id);
        $adminData->delete();
        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.index')->with(['success' => 'Selected Role successfully deleted!']);
    }
}
