<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Auth;

class AddonModule
{
    public static function getSetting($module)
    {
        $data = [];
        $module = \App\Models\AddonModule::where('module', $module)->get();
        if ($module) {
            // $data = $module->toArray();
            foreach ($module as $key => $value) {
                $data[$value->setting] = $value->value;
            }
        }
        return $data;
    }

    public static function adminSidebarOutput()
    {
        $addonsmodule = new \App\Module\Addons();
        $admin = Auth::guard('admin')->user();
        $activemodules = $addonsmodule->getActiveModules();
        $mods = [];
        foreach ($activemodules as $key => $module) {
            $moduleName = $module;
            if ($addonsmodule->load($moduleName)) {
                if ($addonsmodule->isActive($moduleName)) {
                    $allowedroles = $addonsmodule->getAccess();
                    if ($admin->hasAnyRole($allowedroles)) {
                        $modulelink = route('admin.addonsmodule', ['module' => $moduleName]);
                        $modulevars = array("module" => $moduleName, "modulelink" => $modulelink);
                        $settings = self::getSetting($moduleName);
                        $configs = $addonsmodule->getConfig();
                        foreach ($settings as $key => $value) {
                            $modulevars[$key] = $value;
                        }
                        $mods[] = [
                            'module' => $moduleName,
                            'name' => isset($configs['name']) ? $configs['name'] : $moduleName,
                            'link' => $modulelink,
                        ];
                    }
                }
            }
        }
        return $mods;
    }
}
