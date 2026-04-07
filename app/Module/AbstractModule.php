<?php

namespace App\Module;

abstract class AbstractModule
{
    protected $type = "";
    protected $loadedmodule = "";
    protected $metaData = array();
    protected $moduleParams = array();
    protected $usesDirectories = true;
    protected $cacheActiveModules = NULL;
    const TYPE_ADMIN = "admin";
    const TYPE_ADDON = "addons";
    const TYPE_FRAUD = "fraud";
    const TYPE_GATEWAY = "gateways";
    const TYPE_NOTIFICATION = "notifications";
    const TYPE_REGISTRAR = "registrar";
    const TYPE_REPORT = "reports";
    const TYPE_SECURITY = "security";
    const TYPE_SERVER = "servers";
    const TYPE_SOCIAL = "social";
    const TYPE_SUPPORT = "support";
    const TYPE_WIDGET = "widgets";
    const ALL_TYPES = NULL;
    const FUNCTIONDOESNTEXIST = "!Function not found in module!";
    public function getType()
    {
        return $this->type;
    }
    protected function setLoadedModule($module)
    {
        $this->loadedmodule = $module;
    }
    public function getLoadedModule()
    {
        return $this->loadedmodule;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function getList($type = "")
    {
        if ($type) {
            $this->setType($type);
        } else {
            $type = $this->getType();
        }
        // $base_dir = $this->getBaseModuleDir();
        // if (is_dir($base_dir)) {
        //     $modules = array();
        //     $dh = opendir($base_dir);
        //     while (false !== ($module = readdir($dh))) {
        //         if (!$this->usesDirectories) {
        //             $module = str_replace(".php", "", $module);
        //         }
        //         if (is_file($this->getModulePath($module)) && !in_array($module, $modules)) {
        //             $modules[] = $module;
        //         }
        //     }
        //     sort($modules);
        //     return $modules;
        // }
        // return false;

        $data = [];
        $modules = \Module::toCollection();
        foreach ($modules as $key => $module) {
            if (strpos($module->getPath(), '/'.ucfirst($type)) !== false && $module->getName() != "Callback") {
                $data[strtolower($module->getName())] = $module;
            }
        }
        return $data;
    }
    protected function getBaseModulesDir()
    {
        // return ROOTDIR . DIRECTORY_SEPARATOR . "modules";
        return base_path("Modules");
    }
    public function getBaseModuleDir()
    {
        return $this->getBaseModulesDir() . DIRECTORY_SEPARATOR . ucfirst($this->getType());
    }
    public function getModuleDirectory($module)
    {
        // return $this->getBaseModuleDir() . DIRECTORY_SEPARATOR . $module;
        $mod = \Module::find($module);
        return $mod ? $mod->getPath() : "";
    }
    public function getModulePath($module)
    {
        // if ($this->usesDirectories) {
        //     return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR . $module . ".php";
        // }
        // return $this->getBaseModuleDir() . DIRECTORY_SEPARATOR . $module . ".php";
        $mod = \Module::find($module);
        return $mod ? $mod->getPath() : "";
    }
    public function getAppMetaDataFilePath($module)
    {
        return $this->getModuleDirectory($module) . DIRECTORY_SEPARATOR . "module.json";
    }
    public function load($module, $globalVariable = NULL)
    {
        // $whmcs = \App::self();
        // $licensing = \DI::make("license");
        // $module = $whmcs->sanitize("0-9a-z_-", $module);
        // $modpath = $this->getModulePath($module);
        // \Log::debug("Attempting to load module", array("type" => $this->getType(), "module" => $module, "path" => $modpath));
        if (\Module::find($module)) {
            if (!is_null($globalVariable)) {
                global ${$globalVariable};
            }
            $this->setLoadedModule($module);
            $this->setMetaData($this->getMetaData());
            return true;
        }
        return false;
    }
    public function call($function, array $params = array())
    {
        // $whmcs = \App::self();
        // $licensing = \DI::make("license");
        if ($this->functionExists($function)) {
            $params = $this->prepareParams($params);
            $params = array_merge($this->getParams(), $params);
            $module = \Module::find($this->getLoadedModule());
            if ($module) {
                $modName = $module->getName();
                $type = ucfirst($this->getType());
                $className = "\\Modules\\{$type}\\{$modName}\\Http\\Controllers\\{$modName}Controller";
                $object = new $className();
                return $object->{$function}($params);
            } else {
                return "!Module not found!";
            }
        }
        return self::FUNCTIONDOESNTEXIST;
    }
    public function functionExists($name)
    {
        if (!$this->getLoadedModule()) {
            return false;
        }
        $module = \Module::find($this->getLoadedModule());
        if (!$module) {
            return false;
        }
        // return function_exists($this->getLoadedModule() . "_" . $name);
        $modName = $module->getName();
        $type = ucfirst($this->getType());
        $className = "\\Modules\\{$type}\\{$modName}\\Http\\Controllers\\{$modName}Controller";
        $object = new $className();
        return method_exists($object, $name);
    }
    protected function getMetaData()
    {
        $moduleName = $this->getLoadedModule();
        if ($this->functionExists("MetaData")) {
            return $this->call("MetaData");
        }
    }
    protected function setMetaData($metaData)
    {
        if (is_array($metaData)) {
            $this->metaData = $metaData;
            return true;
        }
        $this->metaData = array();
        return false;
    }
    public function getMetaDataValue($keyName)
    {
        return array_key_exists($keyName, $this->metaData) ? $this->metaData[$keyName] : "";
    }
    public function isMetaDataValueSet($keyName)
    {
        return array_key_exists($keyName, $this->metaData);
    }
    public function getDisplayName()
    {
        $DisplayName = $this->getMetaDataValue("DisplayName");
        if (!$DisplayName) {
            $DisplayName = ucfirst($this->getLoadedModule());
        }
        return \App\Helpers\Sanitize::makeSafeForOutput($DisplayName);
    }
    public function getAPIVersion()
    {
        $APIVersion = $this->getMetaDataValue("APIVersion");
        if (!$APIVersion) {
            $APIVersion = $this->getDefaultAPIVersion();
        }
        return $APIVersion;
    }
    public function getApplicationLinkDescription()
    {
        return $this->getMetaDataValue("ApplicationLinkDescription");
    }
    public function getLogoFilename()
    {
        $module = \Module::find($this->getLoadedModule());
        if ($module) {
            return \Module::asset($module->getLowerName().':logo.png');
        }
        return "";

        // $modulePath = $this->getBaseModuleDir() . DIRECTORY_SEPARATOR . $this->getLoadedModule() . DIRECTORY_SEPARATOR;
        // $logoExtensions = array(".png", ".jpg", ".gif");
        // $assetHelper = \DI::make("asset");
        // foreach ($logoExtensions as $extension) {
        //     if (file_exists($modulePath . "logo" . $extension)) {
        //         return $assetHelper->getWebRoot() . str_replace(ROOTDIR, "", $modulePath) . "logo" . $extension;
        //     }
        // }
        // return "";
    }
    public function getSmallLogoFilename()
    {
        // $modulePath = $this->getBaseModuleDir() . DIRECTORY_SEPARATOR . $this->getLoadedModule() . DIRECTORY_SEPARATOR;
        // $logoExtensions = array(".png", ".jpg", ".gif");
        // foreach ($logoExtensions as $extension) {
        //     if (file_exists($modulePath . "logo_small" . $extension)) {
        //         return str_replace(ROOTDIR, "", $modulePath) . "logo_small" . $extension;
        //     }
        // }
        // return "";

        $module = \Module::find($this->getLoadedModule());
        if ($module) {
            return \Module::asset($module->getLowerName().':logo_small.png');
        }
        return "";
    }
    protected function getDefaultAPIVersion()
    {
        $moduleType = $this->getType();
        switch ($moduleType) {
            case "gateways":
                $version = "1.0";
                break;
            default:
                $version = "1.1";
        }
        return $version;
    }
    public function prepareParams($params)
    {
        // $whmcs = \App::self();
        $this->addParam("cbmsVersion", "1.0");
        if (version_compare($this->getAPIVersion(), "1.1", "<")) {
            $params = \App\Helpers\Sanitize::convertToCompatHtml($params);
        } else {
            if (version_compare($this->getAPIVersion(), "1.1", ">=")) {
                $params = \App\Helpers\Sanitize::decode($params);
            }
        }
        return $params;
    }
    protected function addParam($key, $value)
    {
        $this->moduleParams[$key] = $value;
        return $this;
    }
    public function getParams()
    {
        $moduleParams = $this->moduleParams;
        return $this->prepareParams($moduleParams);
    }
    public function getParam($key)
    {
        $moduleParams = $this->getParams();
        return isset($moduleParams[$key]) ? $moduleParams[$key] : "";
    }
    public function findTemplate($templateName)
    {
        // $templateName = preg_replace("/\\.tpl\$/", "", $templateName);
        // $whmcs = \App::self();
        // $currentTheme = $whmcs->getClientAreaTemplate()->getName();
        // $templatePath = DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $currentTheme;
        // $modulePath = DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . $this->getLoadedModule();
        // $moduleTemplateProvidedByTheme = $templatePath . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $this->getType() . DIRECTORY_SEPARATOR . $this->getLoadedModule() . DIRECTORY_SEPARATOR . $templateName . ".tpl";
        // $themeSpecificModuleTemplate = $modulePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $currentTheme . DIRECTORY_SEPARATOR . $templateName . ".tpl";
        // $moduleTemplateInModuleSubdirectory = $modulePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $templateName . ".tpl";
        // $moduleTemplateInModuleDirectory = $modulePath . DIRECTORY_SEPARATOR . $templateName . ".tpl";
        // if (file_exists(ROOTDIR . $moduleTemplateProvidedByTheme)) {
        //     return $moduleTemplateProvidedByTheme;
        // }
        // if (file_exists(ROOTDIR . $themeSpecificModuleTemplate)) {
        //     return $themeSpecificModuleTemplate;
        // }
        // if (file_exists(ROOTDIR . $moduleTemplateInModuleSubdirectory)) {
        //     return $moduleTemplateInModuleSubdirectory;
        // }
        // if (file_exists(ROOTDIR . $moduleTemplateInModuleDirectory)) {
        //     return $moduleTemplateInModuleDirectory;
        // }
        // return "";
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
    public function isApplicationLinkSupported()
    {
        return $this->functionExists("CreateApplicationLink") && $this->functionExists("DeleteApplicationLink");
    }
    public function isApplicationLinkingEnabled()
    {
        $appLink = \App\Models\AppLink::firstOrNew(array("module_type" => $this->getType(), "module_name" => $this->getLoadedModule()));
        return $appLink->isEnabled;
    }
    public function activate(array $parameters = array())
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function deactivate(array $parameters = array())
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function updateConfiguration(array $parameters = array())
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function getConfiguration()
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function getActiveModules()
    {
        return array();
    }
    public function isActive($moduleName)
    {
        if (is_null($this->cacheActiveModules)) {
            $this->cacheActiveModules = $this->getActiveModules();
        }
        return in_array($moduleName, $this->cacheActiveModules);
    }
    public function getApps()
    {
        $apps = array();
        foreach ($this->getList() as $module) {
            // $apps[] = \WHMCS\Apps\App\Model::factoryFromModule($this, $module);
        }
        return $apps;
    }
    public function getAdminActivationForms($moduleName)
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
    public function getAdminManagementForms($moduleName)
    {
        throw new \App\Exceptions\Module\NotImplemented();
    }
}
