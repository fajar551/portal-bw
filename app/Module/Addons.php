<?php

namespace App\Module;

use DB;
use App\Helpers\LogActivity;
use App\Helpers\Cfg;

use App\Models\Server as ServerModel;
use App\Models\Hosting;
use App\Models\Hostingaddon;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Spatie\Permission\Models\Role;

class Addons
{
    public $loadedmodule = NULL;
    protected $moduleParams = array();
    protected $cacheActiveModules = NULL;
    const FUNCTIONDOESNTEXIST = "!Function not found in module!";

    public function load($module)
    {
        if (\Module::find($module)) {
            $this->setLoadedModule($module);
            return true;
        }
        return false;
    }

    protected function setLoadedModule($module)
    {
        $this->loadedmodule = $module;
    }

    public function getLoadedModule()
    {
        return $this->loadedmodule;
    }

    public function getModulePath($module)
    {
        return \Module::getModulePath($module);
    }

    public function functionExists($name)
    {
        $moduleName = $this->getLoadedModule();
        $module = \Module::find($moduleName);
        if (!$moduleName || !$module) {
            return false;
        }
        $modName = $module->getName();
        $className = "\\Modules\\Addons\\{$modName}\\Http\\Controllers\\{$modName}Controller";
        $object = new $className();
        return method_exists($object, $name);
    }

    public function call($function, array $params = array())
    {
        if ($this->functionExists($function)) {
            // $params = array_merge($this->getParams(), $params);
            $moduleName = $this->getLoadedModule();
            $module = \Module::find($moduleName);
            if ($module) {
                $modName = $module->getName();
                $className = "\\Modules\\Addons\\{$modName}\\Http\\Controllers\\{$modName}Controller";
                $object = new $className();
    
                return $object->{$function}($params);
            } else {
                return "Module not found";
            }
        }
        return self::FUNCTIONDOESNTEXIST;
    }

    public function getConfig()
    {
        $config = [];
        if ($this->functionExists("config")) {
            $config = $this->call("config");
        }
        return $config;
    }

    public function activate()
    {
        $result = false;
        if ($this->functionExists("activate")) {
            $result = $this->call("activate");
        }
        return $result;
    }

    public function deactivate()
    {
        $result = false;
        if ($this->functionExists("deactivate")) {
            $result = $this->call("deactivate");
        }
        return $result;
    }

    public function getActiveModules()
    {
        return \App\Models\AddonModule::distinct("module")->pluck("module")->toArray();
    }

    public function isActive($moduleName)
    {
        if (is_null($this->cacheActiveModules)) {
            $this->cacheActiveModules = $this->getActiveModules();
        }
        return in_array($moduleName, $this->cacheActiveModules);
    }

    public function getAccess()
    {
        $module = $this->getLoadedModule();
        $access = \App\Models\AddonModule::where(array("module" => $module, "setting" => "access"))->value('value') ?? "";
        $access = explode(",", $access);
        $tempAccess = [];
        foreach ($access as $key => $roleid) {
            $role = Role::find($roleid);
            if ($role) {
                $tempAccess[] = $role->name;
            }
        }
        return $tempAccess;
    }

    public function findTemplate($templateName)
    {
        $module = \Module::find($this->getLoadedModule());
		if ($module) {
			if (strpos($templateName, ":") !== false) {
				return $templateName;
			} else {
				return $module->getLowerName()."::".$templateName;
			}
		}
		return "";
    }
}
