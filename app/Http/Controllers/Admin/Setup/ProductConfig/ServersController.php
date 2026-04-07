<?php

namespace App\Http\Controllers\Admin\Setup\ProductConfig;
use Illuminate\Http\Request;

use App\Helpers\Password;
use App\Http\Controllers\Controller;

use App\Models\Server as ServerModel;
use App\Models\Servergroup;
use App\Models\Servergroupsrel;
use Dotenv\Result\Success;

use Nwidart\Modules\Facades\Module;
use Validator;

class ServersController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:admin']);
        $this->prefix = \Database::prefix();
    }
    public function ServerConfig()
    {

        // $moduleId = 1;
        // $server = new \App\Module\Server();
        // $testGet = $server->getModuleType($moduleId);
        // dd($testGet);
        return view('pages.setup.prodsservices.serverconfig.index');
    }
    public function ServerConfig_dtServers(Request $request)
    {
        $pfx = $this->prefix;
        $query = ServerModel::all();

        return datatables()->of($query)->editColumn('name', function ($row) {
            if ($row->disabled == 1) {
                return "<span class=\"text-danger font-italic\">{$row->name} (Disabled)</span>";
            } else {
                return $row->name;
            }
        })->editColumn('ipaddress', function ($row) {
            return $row->ipaddress;
        })->editColumn('cbmsusagestats', function ($row) {
            return "0%";
        })->editColumn('remoteusagestats', function ($row) {
            return "Not available";
        })->editColumn('status', function ($row) {
            $statusDisabled = $row->disabled;
            $routeUpdate = route('admin.pages.setup.prodsservice.serverconfig.updateServer', $row->id);
            if ($statusDisabled == 0) {
                return "<a href=\"#\" onclick=\"updateActiveServer('{$routeUpdate}', 0)\" type=\"button\" class=\"btn btn-xs text-success p-1 act-edit\" data-id=\"{$row->id}\" title=\"Disable Server\"><i class=\"fas fa-check-circle\"></i></a>";
            } else {
                return "<a href=\"#\" type=\"button\" class=\"btn btn-xs text-danger p-1 act-edit\" data-id=\"{$row->id}\" title=\"Enable Server\"><i class=\"fas fa-times-circle\"></i></a> ";
            }
        })->editColumn('actions', function ($row) {
            $editRoute = route('admin.pages.setup.prodsservice.serverconfig.edit', ['id' => $row->id]);
            $deleteRoute = route('admin.pages.setup.prodsservice.serverconfig.delete', ['id' => $row->id]);
            $action = "";

            $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit mr-3\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";

            $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

            return $action;
        })->rawColumns(['name', 'actions', 'status'])
            ->addIndexColumn()
            ->toJson();
    }

    public function ServerConfig_add()
    {
        $server = new \App\Module\Server();
        $modules = $server->getListWithDisplayNames();   
        return view('pages.setup.prodsservices.serverconfig.add', ['moduleList' => $modules]);
    }

    public function ServerConfig_insert(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'hostname' => 'required|string',
            'ipaddress' => 'nullable|string',
            'assignedips' => 'nullable',
            'monthlycost' => 'required|numeric',
            'noc' => 'nullable',
            'maxaccounts' => 'required|numeric',
            'statusaddress' => 'nullable|string',
            'disabled' => 'nullable|numeric',
            'nameserver1' => 'nullable|string',
            'nameserver1ip' => 'nullable|numeric',
            'nameserver2' => 'nullable|string',
            'nameserver2ip' => 'nullable|numeric',
            'nameserver3' => 'nullable|string',
            'nameserver3ip' => 'nullable|numeric',
            'nameserver4' => 'nullable|string',
            'nameserver4ip' => 'nullable|numeric',
            'nameserver5' => 'nullable|string',
            'nameserver5ip' => 'nullable|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'accesshash' => 'nullable|string',
            'secure' => 'nullable|string',
            'port' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('message', 'Can\'t create new Servers , please fill forms correctly and try again');
        }

        if ($request->password) {
            $password = $request->password;
            $hashPass = new Password();
            $passwordHashed = $hashPass->hash($password);
        }

        $newServer = new ServerModel();
        $newServer->name = $request->name;
        $newServer->ipaddress = $request->ipaddress ?? '';
        $newServer->assignedips = $request->assignedips ?? '';
        $newServer->hostname = $request->hostname;
        $newServer->monthlycost = $request->monthlycost;
        $newServer->noc = $request->noc ?? '';
        $newServer->statusaddress = $request->statusaddress ?? '';
        $newServer->nameserver1 = $request->nameserver1 ?? '';
        $newServer->nameserver2 = $request->nameserver2 ?? '';
        $newServer->nameserver3 = $request->nameserver3 ?? '';
        $newServer->nameserver4 = $request->nameserver4 ?? '';
        $newServer->nameserver5 = $request->nameserver5 ?? '';
        $newServer->nameserver1ip = $request->nameserver1ip ?? '';
        $newServer->nameserver2ip = $request->nameserver2ip ?? '';
        $newServer->nameserver3ip = $request->nameserver3ip ?? '';
        $newServer->nameserver4ip = $request->nameserver4ip ?? '';
        $newServer->nameserver5ip = $request->nameserver5ip ?? '';
        $newServer->maxaccounts = $request->maxaccounts;
        $newServer->type = $request->type;
        $newServer->username = $request->username;
        $newServer->password = (new \App\Helpers\Pwd())->encrypt(trim($request->password));
        $newServer->accesshash = $request->accesshash ?? '';
        $newServer->secure = $request->secure ?? 'off';
        $newServer->port = $request->port;
        $newServer->active = 1;
        $newServer->disabled = $request->disabled;
        $newServer->timestamps = false;
        $newServer->save();
        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index')->with(['success' => 'A New Server has been created!']);
    }

    public function ServerConfig_edit(Request $request, $id)
    {
        $modules = [];
        $servers = Module::all();

        foreach ($servers as $key => $server) {
            if (strpos($server->getPath(), '/Servers') !== false) {
                $modules[] = $server->getLowerName();
            }
        }

        $serverSelectedById = ServerModel::findOrFail($id);

        return view('pages.setup.prodsservices.serverconfig.edit', ['serverSelected' => $serverSelectedById, 'modulesSelected' => $modules]);
    }

    public function ServerConfig_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'hostname' => 'required|string',
            'ipaddress' => 'nullable|string',
            'assignedips' => 'nullable',
            'monthlycost' => 'required|numeric',
            'noc' => 'nullable',
            'maxaccounts' => 'required|numeric',
            'statusaddress' => 'nullable|string',
            'disabled' => 'nullable|numeric',
            'nameserver1' => 'nullable|string',
            'nameserver1ip' => 'nullable|numeric',
            'nameserver2' => 'nullable|string',
            'nameserver2ip' => 'nullable|numeric',
            'nameserver3' => 'nullable|string',
            'nameserver3ip' => 'nullable|numeric',
            'nameserver4' => 'nullable|string',
            'nameserver4ip' => 'nullable|numeric',
            'nameserver5' => 'nullable|string',
            'nameserver5ip' => 'nullable|numeric',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'accesshash' => 'nullable|string',
            'secure' => 'nullable|string',
            'port' => 'nullable|numeric',
        ]);


        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('message', 'Can\'t update a Server, please fill forms correctly and try again');
        }

        $serverSelectedById = ServerModel::findOrFail($id);
        $serverSelectedById->name = $request->name;
        $serverSelectedById->ipaddress = $request->ipaddress;
        $serverSelectedById->assignedips = $request->assignedips ?? '';
        $serverSelectedById->hostname = $request->hostname;
        $serverSelectedById->monthlycost = $request->monthlycost;
        $serverSelectedById->noc = $request->noc ?? '';
        $serverSelectedById->statusaddress = $request->statusaddress ?? '';
        $serverSelectedById->nameserver1 = $request->nameserver1 ?? '';
        $serverSelectedById->nameserver2 = $request->nameserver2 ?? '';
        $serverSelectedById->nameserver3 = $request->nameserver3 ?? '';
        $serverSelectedById->nameserver4 = $request->nameserver4 ?? '';
        $serverSelectedById->nameserver5 = $request->nameserver5 ?? '';
        $serverSelectedById->nameserver1ip = $request->nameserver1ip ?? '';
        $serverSelectedById->nameserver2ip = $request->nameserver2ip ?? '';
        $serverSelectedById->nameserver3ip = $request->nameserver3ip ?? '';
        $serverSelectedById->nameserver4ip = $request->nameserver4ip ?? '';
        $serverSelectedById->nameserver5ip = $request->nameserver5ip ?? '';
        $serverSelectedById->maxaccounts = $request->maxaccounts;
        $serverSelectedById->type = $request->type;
        $serverSelectedById->username = $request->username;
        if ($request->password) {
            $password = $request->password;
            $hashPass = new Password();
            $passwordHashed = $hashPass->hash($password);
            // $serverSelectedById->password = $passwordHashed;
            $serverSelectedById->password = (new \App\Helpers\Pwd())->encrypt(trim($password));
        }
        $serverSelectedById->accesshash = $request->accesshash ?? '';
        $serverSelectedById->secure = $request->secure ?? 'off';
        $serverSelectedById->port = $request->port ?? null;
        $serverSelectedById->active = 1;
        $serverSelectedById->disabled = $request->disabled ?? 0;
        $serverSelectedById->timestamps = false;
        // dd($request);
        $serverSelectedById->save();

        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index')->with(['success' => 'An Server successfully updated!']);
    }
    public function ServerConfig_disabledServer(Request $request, $id)
    {
        $serverSelectedById = ServerModel::findOrFail($id);
        $serverSelectedById->disabled = $request->getContent();
        $serverSelectedById->timestamps = false;
        $serverSelectedById->save();
        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index');
    }
    public function ServerConfig_delete($id)
    {
        $serverSelected = ServerModel::findOrFail($id);
        $serverSelected->delete();
        return redirect()->route('admin.pages.setup.prodsservices.serverconfig.index')->with(['success' => 'An Server successfully deleted!']);
    }



    //* Add Server Group *//

    public function ServerConfig_add_group()
    {
        $serverList = ServerModel::all()->pluck('name', 'id')->toArray();
        return view('pages.setup.prodsservices.serverconfig.add-group', ['serverList' => $serverList]);
    }

    public function ServerConfig_dtServerGroup()
    {
        $groupList = Servergroup::all();
        return datatables()->of($groupList)->editColumn('name', function ($row) {
            return $row->name;
        })
            ->editColumn('filltype', function ($row) {
                if ($row->filltype == 1) {
                    return 'Add to the least full server';
                } else {
                    return 'Fill active server until full then switch to next least used';
                }
            })
            ->addColumn('servers', function ($row) {
                $serverList = Servergroupsrel::where('groupid', $row->id)->pluck('serverid')->all();

                $serverName = [];
                foreach ($serverList as $key => $servers) {
                    $serverNameArr = ServerModel::where("id", $servers)->pluck('name')->toArray();
                    foreach ($serverNameArr as $key => $server) {
                        $serverName[] = $server;
                    }
                }
                $serverNameStr = implode(", ", $serverName);
                return $serverNameStr ? $serverNameStr : 'None';
            })
            ->editColumn('actions', function ($row) {
                $editRoute = route('admin.pages.setup.prodsservice.serverconfig.edit-group', ['id' => $row->id]);
                $deleteRoute = route('admin.pages.setup.prodsservice.serverconfig.delete-group', ['id' => $row->id]);
                $action = "";

                $action .= "<a href=\"{$editRoute}\" type=\"button\" class=\"btn btn-xs text-primary p-1 act-edit mr-3\" data-id=\"{$row->id}\" title=\"Edit\"><i class=\"fa fa-edit\"></i></a> ";
                $action .= "<button onclick=\"ConfirmDelete('{$deleteRoute}')\" type=\"button\" id=\"act-delete\" class=\"btn btn-xs text-danger p-1 \" data-id=\"{$row->id}\" title=\"Delete\"><i class=\"fa fa-trash\"></i></button> ";

                return $action;
            })
            ->rawColumns(['actions'])
            ->addIndexColumn()
            ->toJson();
    }

    public function ServersConfig_add_group_insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['name_req' => "Group name is required, try again!"]);
        }

        $newGroup = new Servergroup();
        $newGroup->name = $request->name;
        $newGroup->filltype = $request->filltype ?? 1;
        $newGroup->timestamps = false;
        $newGroup->save();
        $groupid = $newGroup->id;
        $selectedServerIdArr = $request->selectedServer;
        if ($request->selectedServer) {
            foreach ($selectedServerIdArr as $key => $serverid) {
                $newGrouprel = new Servergroupsrel();
                $newGrouprel->groupid = $groupid;
                $newGrouprel->serverid = $serverid;
                $newGrouprel->timestamps = false;
                $newGrouprel->save();
            }
        }

        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index')->with(['success' => 'A new group has been created!']);
    }

    public function ServerConfig_edit_group($id)
    {
        $selectedGroup = Servergroup::findOrFail($id);
        $serverList = ServerModel::all()->pluck('name', 'id')->toArray();
        $selectedServer = Servergroupsrel::where('groupid', $id)->pluck('serverid')->all();

        $serverName = [];
        foreach ($selectedServer as $key => $servers) {
            $serverName[] = ServerModel::where("id", $servers)->pluck('name', 'id')->toArray();
        };

        $serverNew = [];
        foreach ($serverName as $id => $servers) {
            foreach ($servers as $key => $name) {
                $serverNew[] = $name;
            }
        }

        $newServerList = array_diff($serverList, $serverNew);

        return view('pages.setup.prodsservices.serverconfig.edit-group', ['selectedGroup' => $selectedGroup, 'serverList' => $newServerList, 'serverName' => $serverName]);
    }

    public function ServerConfig_group_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with(['name_req' => "Group name is required, try again!"]);
        }

        Servergroupsrel::where('groupid', $id)->delete();
        $newGroup = Servergroup::findOrFail($id);
        $newGroup->name = $request->name;
        $newGroup->filltype = $request->filltype ?? 1;
        $newGroup->timestamps = false;
        $selectedServerStr = $request->selectedServer;

        if ($selectedServerStr) {
            $selectedServerInt = array_map('intval', $selectedServerStr);
            foreach ($selectedServerInt as $key => $serverId) {
                Servergroupsrel::create(['groupid' => $id, 'serverid' => $serverId]);
            }
        }

        $newGroup->save();
        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index')->with(['success' => 'A group has been updated!']);
    }

    public function ServerConfig_group_delete($id)
    {
        Servergroupsrel::where('groupid', $id)->delete();
        $selectedServer = Servergroup::findOrFail($id);
        $selectedServer->delete();
        return redirect()->route('admin.pages.setup.prodsservice.serverconfig.index')->with(['success' => 'A group has been deleted!']);
    }
}
