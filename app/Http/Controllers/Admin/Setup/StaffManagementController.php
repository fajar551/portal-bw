<?php

namespace App\Http\Controllers\Admin\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Permission as SpatiePerms;
use Spatie\Permission\Models\Role;

//Models
use App\Models\AdminRole;
use App\Models\Admin;
//Helper
use App\Helpers\Password;
use Ramsey\Uuid\Uuid;
use App\Helpers\Permission;

use Validator;

class StaffManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    public function StaffManagement_adminusers()
    {
        // Auth::guard('admin')->user()->syncRoles([]);
        return view('pages.setup.staffmanagement.administratorusers.index');
    }
    public function StaffManagement_dtActiveAdmin(Request $request)
    {
        $pfx = $this->prefix;
        $query = Admin::select(\DB::raw("{$pfx}admins.*"))
            ->where("{$pfx}admins.disabled", "=", "0");

        return datatables()->of($query)->editColumn('name', function ($row) {
            $firstName = $row->firstname;
            $lastName = $row->lastname;
            $fullName = $firstName . " " . $lastName;
            return $fullName;
        })
            ->addColumn('roleName', function ($row) use ($pfx) {
                $roleNameRaw = Role::where('id', $row->roleid)->get();

                $roleName = "";
                foreach ($roleNameRaw as $key => $value) {
                    $roleName .= $value->name;
                }
                return $roleName;
            })
            ->addColumn('assignedDepts', function ($row) use ($pfx) {
                if ($row->supportdepts) {
                    $roleId = \DB::select(\DB::raw("SELECT {$pfx}admins.supportdepts FROM {$pfx}admins WHERE ${pfx}admins.id = $row->id"));
                    $roleName = [];
                    foreach ($roleId as $key => $value) {
                        $roleName[] = $value->supportdepts;
                    }

                    $roleIdArr = [];
                    foreach ($roleName as $key => $value) {
                        $roleIdArr[] = explode(",", $value);
                    }

                    $roleTextArr = [];
                    foreach ($roleIdArr as $key => $value) {
                        foreach ($value as $key => $item) {
                            if ($item != '') {
                                $deptName = \DB::select(\DB::raw("SELECT {$pfx}ticketdepartments.name FROM {$pfx}ticketdepartments WHERE {$pfx}ticketdepartments.id = $item"));
                                $roleTextArr[] = $deptName;
                            }
                        }
                    }

                    $objDept = [];
                    foreach ($roleTextArr as $key => $value) {
                        foreach ($value as $key => $item) {
                            $objDept[] = $item->name;
                        }
                    }
                    $strDept = implode(", ", $objDept);
                    return $strDept;
                } else {
                    return "None";
                }
            })
            ->editColumn('actions', function ($row) {
                $editRoute = route('admin.pages.setup.staffmanagement.administratorusers.editform', ['id' => $row->id]);
                $deleteRoute = route('admin.pages.setup.staffmanagement.administratorusers.delete', ['id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->rawColumns(['actions', 'supportdepts'])
            ->addIndexColumn()
            ->toJson();
    }
    public function StaffManagement_dtDisabledAdmin(Request $request)
    {
        $pfx = $this->prefix;
        $query = Admin::select(\DB::raw("{$pfx}admins.*"))
            ->where("{$pfx}admins.disabled", "=", "1");

        return datatables()->of($query)->editColumn('name', function ($row) {
            $firstName = $row->firstname;
            $lastName = $row->lastname;
            $fullName = $firstName . " " . $lastName;
            return $fullName;
        })
            ->editColumn('roleName', function ($row) {
                $roleNameRaw = Role::where('id', $row->roleid)->get();

                $roleName = "";
                foreach ($roleNameRaw as $key => $value) {
                    $roleName .= $value->name;
                }
                return $roleName;
            })
            ->addColumn('assignedDepts', function ($row) use ($pfx) {
                if ($row->supportdepts) {
                    $roleId = \DB::select(\DB::raw("SELECT {$pfx}admins.supportdepts FROM {$pfx}admins WHERE ${pfx}admins.id = $row->id"));
                    $roleName = [];
                    foreach ($roleId as $key => $value) {
                        $roleName[] = $value->supportdepts;
                    }

                    $roleIdArr = [];
                    foreach ($roleName as $key => $value) {
                        $roleIdArr[] = explode(",", $value);
                    }

                    $roleTextArr = [];
                    foreach ($roleIdArr as $key => $value) {
                        foreach ($value as $key => $item) {
                            $deptName = \DB::select(\DB::raw("SELECT {$pfx}ticketdepartments.name FROM {$pfx}ticketdepartments WHERE {$pfx}ticketdepartments.id = $item"));
                            $roleTextArr[] = $deptName;
                        }
                    }

                    $objDept = [];
                    foreach ($roleTextArr as $key => $value) {
                        foreach ($value as $key => $item) {
                            $objDept[] = $item->name;
                        }
                    }
                    $strDept = implode(", ", $objDept);
                    return $strDept;
                } else {
                    return "None";
                }
            })
            ->editColumn('actions', function ($row) {
                $editRoute = route('admin.pages.setup.staffmanagement.administratorusers.editform', ['id' => $row->id]);
                $deleteRoute = route('admin.pages.setup.staffmanagement.administratorusers.delete', ['id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->rawColumns(['actions'])
            ->addIndexColumn()
            ->toJson();
    }
    public function StaffManagement_adminusers_form(Request $request)
    {
        $getSuppDept = \App\Models\Ticketdepartment::all()->pluck('name', 'id')->toArray();
        $roles = Role::where('guard_name', 'admin')->get()->pluck('name', 'id')->toArray();
        return view('pages.setup.staffmanagement.administratorusers.add', ['roleList' => $roles, 'suppDept' => $getSuppDept]);
    }
    public function StaffManagement_adminusers_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'roleid' => 'required|numeric',
            'username' => 'required|string',
            'password' =>  'required|min:6',
            'password2' => 'required|string',
            'authmodule' => 'nullable',
            'authdata' => 'nullable',
            'firstname' => 'required|string',
            'lastname' => 'nullable|string',
            'email' => 'required|string',
            'signature' => 'nullable|string',
            'notes' => 'nullable|string',
            'ticketnotification' => 'nullable',
            'password_reset_key' => 'nullable',
            'password_reset_date' => 'nullable',
            'hidden_widgets' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.addform')->withInput($request->all())->withErrors($validator)->with('message', 'Can\'t create new Administrator , please fill forms correctly and try again');
        }

        $uuid = "";
        if (!$uuid) {
            $uuid = Uuid::uuid4();
            $uuid = $uuid->toString();
        }

        if ($request->password) {
            $password = $request->password;
            $hashPass = new Password();
            $hashedPass = $hashPass->hash($password);
        }

        $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($request->password));

        // ? Role Id Var
        $roleValue = $request->roleid;

        $newAdmin = new Admin();
        $newAdmin->uuid = $uuid;
        $newAdmin->roleid = $request->roleid;

        // ? Assign Role to user
        $queryRoleName = Role::where('id', $roleValue)->first();
        // $queryRoleName = Role::where('id', $roleValue)->get()->toArray();
        // foreach ($queryRoleName  as $key => $value) {
        //     $roleName = $value['name'];
        //     if (!$newAdmin->hasAnyRole($roleName)) {
        //         $newAdmin->syncRoles(array($roleName));
        //     }
        // };

        $newAdmin->username = $request->username;
        $newAdmin->password = $hashedPass;
        $newAdmin->passwordhash = $passwordHash;
        $newAdmin->firstname = $request->firstname;
        $newAdmin->lastname = $request->lastname ?? "";
        $newAdmin->email = $request->email;
        $newAdmin->signature = $request->signature ?? "";
        $newAdmin->notes = $request->notes ?? "";
        if ($request->supportdepts) {
            $dataDept = $request->supportdepts;
            $suppDeptString = implode(',', $dataDept);
            $newAdmin->supportdepts = $suppDeptString;
        }
        $newAdmin->template = $request->template = 0 ? 'blend' : 0;
        $newAdmin->language = $request->language = 0 ? 'english' : 0;
        $newAdmin->save();

        // assign role
        if ($queryRoleName) {
            $newAdmin->assignRole($queryRoleName);
        }

        return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.index', ['success' => 'Congratulation, a new Administrator has been created!']);
    }
    public function StaffManagement_adminusers_edit(Request $request)
    {
        $getSuppDept = \App\Models\Ticketdepartment::all()->pluck('name', 'id')->toArray();
        $roles = Role::where('guard_name', 'admin')->get()->pluck('name', 'id')->toArray();
        $selectedAdmins = Admin::findOrFail($request->id);
        $arrDepts = explode(',', $selectedAdmins->supportdepts);

        return view('pages.setup.staffmanagement.administratorusers.edit', ['roleList' => $roles, 'suppDept' => $getSuppDept, 'selectedDepts' => $arrDepts, 'selectedAdmins' => $selectedAdmins]);
    }
    public function StaffManagement_adminusers_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'nullable|string',
            'email' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('message', 'Can\'t update Administrator Data, please fill forms correctly and try again');
        }

        // ? Role Id Var
        $roleValue = $request->roleid;

        // ? Selected Admin 
        $updatedAdmin = Admin::findOrFail($id);
        $updatedAdmin->roleid = $request->roleid;

        // ? Assign Role To Users
        $queryRoleName = Role::where('id', $roleValue)->first();
        // $queryRoleName = Role::where('id', $roleValue)->get()->toArray();
        // foreach ($queryRoleName  as $key => $value) {
        //     $roleName = $value['name'];
        //     if (!$updatedAdmin->hasAnyRole($roleName)) {
        //         $updatedAdmin->syncRoles(array($roleName));
        //     }
        // };
        $updatedAdmin->firstname = $request->firstname;
        $updatedAdmin->lastname = $request->lastname;
        $updatedAdmin->email = $request->email;
        if ($request->password) {
            $password = $request->password;
            $hashPass = new Password();
            $passwordHash = (new Password())->hash(\App\Helpers\Sanitize::decode($password));
            $passwordHashed = $hashPass->hash($password);
            $updatedAdmin->password = $passwordHashed;
            $updatedAdmin->passwordhash = $passwordHash;
        }
        if ($request->supportdepts) {
            $dataDept = $request->supportdepts;
            $suppDeptString = implode(',', $dataDept);
            $updatedAdmin->supportdepts = $suppDeptString;
        }
        $updatedAdmin->signature = $request->signature ?? '';
        $updatedAdmin->notes = $request->notes ?? '';
        $updatedAdmin->template = $request->template ?? '';
        $updatedAdmin->language = $request->language ?? '';
        $updatedAdmin->disabled = $request->disabled;
        $updatedAdmin->save();

        // update role
        if ($queryRoleName) {
            // revoke role first
            foreach ($updatedAdmin->roles->where('guard_name', 'admin')->pluck('name') as $key => $roleName) {
                $updatedAdmin->removeRole($roleName);
            }
            $updatedAdmin->assignRole($queryRoleName);
        }

        return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.index')->with(['success' => 'Your changes to the admin account have been saved.']);
    }
    public function StaffManagement_adminusers_delete(Request $request, $id)
    {
        $data = $request->ajax();
        $roles = Role::where('guard_name', 'admin')->get()->pluck('name', 'id')->toArray();

        $adminData = Admin::findOrFail($id);

        if ($adminData->hasAnyRole($roles)) {
            $adminData->syncRoles([]);
        }

        $adminData->delete();
        return redirect()->route('admin.pages.setup.staffmanagement.administratorusers.index')->with(['success' => 'Selected users successfully deleted!']);
    }


    // ============================================================================================================

    //Administrator Roles Controller Not Active
    public function StaffManagement_adminroles()
    {
        $pfx = $this->prefix;
        return view('pages.setup.staffmanagement.administratorroles.index');
    }
    public function StaffManagement_dtAdminRoles(Request $request)
    {
        $pfx = $this->prefix;
        $query = AdminRole::select(\DB::raw("{$pfx}adminroles.*"));

        return datatables()->of($query)->editColumn('name', function ($row) {
            return $row->name;
        })
            ->addColumn('assignedUser', function ($row) use ($pfx) {
                $assignedUser = \DB::select(\DB::raw("SELECT {$pfx}admins.id, {$pfx}admins.username  FROM {$pfx}admins WHERE {$pfx}admins.roleid = $row->id"));
                $collectedUsername = [];
                foreach ($assignedUser as $key => $value) {
                    $route = route('admin.pages.setup.staffmanagement.administratorusers.editform', ['id' => $value->id]);
                    $collectedUsername[] = "<a href=\"{$route}\">$value->username</a>";
                }
                $linkUsername = implode(", ", $collectedUsername);
                return $linkUsername ? $linkUsername : 'None';
            })
            ->editColumn('actions', function ($row) {
                $editRoute = route('admin.pages.setup.staffmanagement.administratorroles.edit', ['id' => $row->id]);
                $deleteRoute = route('admin.pages.setup.staffmanagement.administratorroles.delete', ['id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->rawColumns(['actions', 'assignedUser'])
            ->addIndexColumn()
            ->toJson();
    }
    public function StaffManagement_adminroles_add()
    {
        return view('pages.setup.staffmanagement.administratorroles.add');
    }
    public function StaffManagement_adminroles_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.add')->withErrors($validator)->with(['error' => 'Please fill Role Name!']);
        }

        $newRoles = new AdminRole();
        $newRoles->name = $request->name;
        $newRoles->timestamps = false;
        $newRoles->save();
        $roleLatestId = AdminRole::all()->last()->id;
        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.edit', $roleLatestId);
    }
    public function StaffManagement_adminroles_edit(Request $request)
    {
        // *TODO: Admin Permission role
        $rolePermission = Permission::all();
        $id = $request->id;
        $selectedRole = AdminRole::findOrFail($id);
        $selectedPerms = \App\Models\AdminPerm::where('roleid', $id)->get();
        $permissionsId = [];
        foreach ($selectedPerms as $perms) {
            $permissionsId[] = $perms->permid;
        }
        return view('pages.setup.staffmanagement.administratorroles.edit', ['selectedRole' => $selectedRole, 'rolePermission' => $rolePermission, 'selectedPermissions' => $permissionsId]);
    }
    public function StaffManagement_adminroles_update(Request $request, $id)
    {
        $changes = array();
        $permissions = $newPermissions = $removedPermissions = $permissionList = $inserts = array();
        $rolePermission = \App\Models\AdminPerm::where('roleid', $id)->get();
        $permissions = [];
        foreach ($rolePermission as $perms) {
            $permissions[] = $perms->permid;
        }

        \DB::table('tbladminroles')->where('id', '=', $id)->update(array('name' => $request->name, 'reports' => $request->reports, 'systememails' => $request->systememails, 'accountemails' => $request->accountemails, 'supportemails' => $request->supportemails));
        $inputPermissions = $request->adminperms;

        if ($inputPermissions) {
            foreach ($inputPermissions as $key => $value) {
                $permissionList[] = $key;
                if (!in_array($key, $permissions)) {
                    $newPermissions[] = $key;
                }
                $inserts[] = array('roleid' => (int)$id, "permid" => $key);
            }
        }
        // dd($inputPermissions);
        // if($permissionList) {
        //     \DB::table('tbladminperms')->insert($inserts);
        // }

        foreach ($permissions as $permission) {
            if (!in_array($permission, $permissionList)) {
                $removedPermissions[] = $permission;
            }
        }

        if (array_filter($removedPermissions)) {
            foreach ($removedPermissions as $deleted) {
                $deleteQuery = \DB::table('tbladminperms')->where('roleid', '=', $id)->where('permid', '=', $deleted);
                $deleteQuery->delete();
            }
        }

        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.index')->with(['success' => 'Selected users successfully updated!']);
    }
    public function StaffManagement_adminroles_delete(Request $request, $id)
    {
        $data = $request->ajax();
        $adminData = AdminRole::findOrFail($id);
        $adminData->delete();
        return redirect()->route('admin.pages.setup.staffmanagement.administratorroles.index')->with(['success' => 'Selected Role successfully deleted!']);
    }
}
