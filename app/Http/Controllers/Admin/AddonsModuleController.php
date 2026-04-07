<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AddonsModuleController extends Controller
{
    //
    public function index(Request $request)
    {
        $moduleName = $request->get('module');
        $admin = Auth::guard('admin')->user();
        // $roles = $admin->roles->where('guard_name', 'admin')->pluck('name')->toArray();
        
        if ($moduleName) {
            $addonsmodule = new \App\Module\Addons();
            if ($addonsmodule->load($moduleName)) {
                if ($addonsmodule->isActive($moduleName)) {
                    $allowedroles = $addonsmodule->getAccess();
                    if ($admin->hasAnyRole($allowedroles)) {
                        $modulelink = route('admin.addonsmodule', ['module' => $moduleName]);
                        $modulevars = array("module" => $moduleName, "modulelink" => $modulelink);
                        $configarray = $addonsmodule->getConfig();
                        $settings = \App\Helpers\AddonModule::getSetting($moduleName);
                        foreach ($settings as $key => $value) {
                            $modulevars[$key] = $value;
                        }
                        if ($modulevars["version"] != $configarray["version"]) {
                            if ($addonsmodule->functionExists('upgrade')) {
                                $addonsmodule->call("upgrade", $modulevars);
                            }
                            \App\Models\AddonModule::where(array("module" => $module, "setting" => "version"))->update(array("value" => $configarray["version"]));
                        }
                        return $addonsmodule->call("output", $modulevars);
                    }
                    return \Lang::get('admin.permissionsaccessdenied');
                }
                return 'Module not active';
            }
            return 'Invalid Addon Module Name';
        }

        return redirect()->route('admin.pages.setup.addonsmodule.index');
    }

}
