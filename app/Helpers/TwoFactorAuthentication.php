<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthentication
{
	protected $settings = array();
    protected $clientmodules = array();
    protected $adminmodules = array();
    protected $adminmodule = "";
    protected $adminsettings = array();
    protected $admininfo = array();
    protected $clientmodule = "";
    protected $clientsettings = array();
    protected $clientinfo = array();
    protected $adminid = "";
    protected $clientid = "";
    public function __construct()
    {
        $this->loadSettings();
    }
    protected function loadSettings()
    {
        $this->settings = (new \App\Helpers\Client)->safe_unserialize(\App\Helpers\Cfg::getValue("2fasettings"));
        if (!isset($this->settings["modules"])) {
            return false;
        }
        foreach ($this->settings["modules"] as $module => $data) {
            if (!empty($data["clientenabled"])) {
                $this->clientmodules[] = $module;
            }
            if (!empty($data["adminenabled"])) {
                $this->adminmodules[] = $module;
            }
        }
        return true;
    }
    public function getModuleSettings($module)
    {
        return is_array($this->settings["modules"][$module]) ? $this->settings["modules"][$module] : array();
    }
    public function getModuleSetting($module, $name)
    {
        $settings = $this->getModuleSettings($module);
        return isset($settings[$name]) ? $settings[$name] : null;
    }
    public function setModuleSetting($module, $name, $value)
    {
        $this->settings["modules"][$module][$name] = $value;
        return $this;
    }
    public function isModuleEnabled($module)
    {
        return $this->isModuleEnabledForClients($module) || $this->isModuleEnabledForAdmins($module);
    }
    public function isModuleEnabledForClients($module)
    {
        $settings = $this->getModuleSettings($module);
        return (bool) $settings["clientenabled"];
    }
    public function isModuleEnabledForAdmins($module)
    {
        $settings = $this->getModuleSettings($module);
        return (bool) $settings["adminenabled"];
    }
    public function setModuleClientEnablementStatus($module, $status)
    {
        $this->setModuleSetting($module, "clientenabled", (int) (bool) $status);
        return $this;
    }
    public function setModuleAdminEnablementStatus($module, $status)
    {
        $this->setModuleSetting($module, "adminenabled", (int) (bool) $status);
        return $this;
    }
    public function isForced()
    {
        if ($this->clientid) {
            return $this->isForcedClients();
        }
        if ($this->adminid) {
            return $this->isForcedAdmins();
        }
        return false;
    }
    public function isForcedClients()
    {
        return (bool) $this->settings["forceclient"];
    }
    public function isForcedAdmins()
    {
        return (bool) $this->settings["forceadmin"];
    }
    public function setForcedClients($status)
    {
        $this->settings["forceclient"] = (int) (bool) $status;
        return $this;
    }
    public function setForcedAdmins($status)
    {
        $this->settings["forceadmin"] = (int) (bool) $status;
        return $this;
    }
    public function save()
    {
        \App\Helpers\Cfg::setValue("2fasettings", (new \App\Helpers\Client)->safe_serialize($this->settings));
        return $this;
    }
    // public function isActiveClients()
    // {
    //     return count($this->clientmodules) ? true : false;
    // }
    public function isActiveAdmins()
    {
        return count($this->adminmodules) ? true : false;
    }
    public function setClientID($clientId)
    {
        // $this->clientid = $id;
        $this->clientid = $clientId;
        $this->adminid = "";
        return $this->loadClientSettings();
    }
    public function setAdminID($id)
    {
        $this->clientid = "";
        $this->adminid = $id;
        return $this->loadAdminSettings();
    }
    protected function loadClientSettings()
    {
		$data = \App\Models\Client::where("id", $this->clientid)->where("status", "!=", "Closed")->first();
        if (!$data) {
            return false;
        }
        $data->makeVisible(['authdata']);
		$data = $data->toArray();
        $this->clientmodule = $data["authmodule"];
        $this->clientsettings = (new \App\Helpers\Client)->safe_unserialize($data["authdata"]);
        if (!is_array($this->clientsettings)) {
            $this->clientsettings = array();
        }
        unset($data["authmodule"]);
        unset($data["authdata"]);
        $data["username"] = $data["email"];
        $this->clientinfo = $data;
        return true;
    }
    protected function loadAdminSettings()
    {
        $data = \App\Models\Admin::where(array("id" => $this->adminid, "disabled" => "0"))->first();
        if (!$data) {
            return false;
        }
		$data = $data->toArray();
        $this->adminmodule = $data["authmodule"];
        $this->adminsettings = (new \App\Helpers\Client)->safe_unserialize($data["authdata"]);
        if (!is_array($this->adminsettings)) {
            $this->adminsettings = array();
        }
        unset($data["authmodule"]);
        unset($data["authdata"]);
        $this->admininfo = $data;
        return true;
    }
    public function getAvailableModules()
    {
        if ($this->clientid) {
            return $this->getAvailableClientModules();
        }
        if ($this->adminid) {
            return $this->getAvailableAdminModules();
        }
        return array_unique(array_merge($this->getAvailableClientModules(), $this->getAvailableAdminModules()));
    }
    protected function getAvailableClientModules()
    {
        return $this->clientmodules;
    }
    protected function getAvailableAdminModules()
    {
        return $this->adminmodules;
    }
    // public function isEnabled()
    // {
    //     if ($this->clientid) {
    //         return $this->isEnabledClient();
    //     }
    //     if ($this->adminid) {
    //         return $this->isEnabledAdmin();
    //     }
    //     return false;
    // }
    // protected function isEnabledClient()
    // {
    //     return $this->clientmodule ? true : false;
    // }
    // protected function isEnabledAdmin()
    // {
    //     return $this->adminmodule ? true : false;
    // }
    protected function getModule()
    {
        if ($this->clientid) {
            return $this->clientmodule;
        }
        if ($this->adminid) {
            return $this->adminmodule;
        }
        return false;
    }
    public function moduleCall($function, $module = "", $extraParams = array())
    {
        $mod = new \App\Module\Security();
        $module = $module ? $module : $this->getModule();
        $loaded = $mod->load($module);
        if (!$loaded) {
            return false;
        }
        $params = $this->buildParams($module);
        $params = array_merge($params, $extraParams);
        $result = $mod->call($function, $params);
        return $result;
    }
    protected function buildParams($module)
    {
        $params = array();
        $params["settings"] = $this->settings["modules"][$module];
        $params["user_info"] = $this->clientid ? $this->clientinfo : $this->admininfo;
        $params["user_settings"] = $this->clientid ? $this->clientsettings : $this->adminsettings;
        $params["post_vars"] = $_POST;
        $params["twoFactorAuthentication"] = $this;
        return $params;
    }
    public function activateUser($module, $settings = array())
    {
        // $encryptionHash = \App::getApplicationConfig()->cc_encryption_hash;
		$encryptionHash = config('portal.hash.cc_encryption_hash');
        if ($this->clientid) {
            $backupCode = sha1($encryptionHash . $this->clientid . time());
            $backupCode = substr($backupCode, 0, 16);
            $settings["backupcode"] = sha1($backupCode);
            \App\Models\Client::where(array("id" => $this->clientid))->update(array("authmodule" => $module, "authdata" => (new \App\Helpers\Client)->safe_serialize($settings)));
            return substr($backupCode, 0, 4) . " " . substr($backupCode, 4, 4) . " " . substr($backupCode, 8, 4) . " " . substr($backupCode, 12, 4);
        }
        if ($this->adminid) {
            $backupCode = sha1($encryptionHash . $this->adminid . time());
            $backupCode = substr($backupCode, 0, 16);
            $settings["backupcode"] = sha1($backupCode);
            \App\Models\Admin::where(array("id" => $this->adminid))->update(array("authmodule" => $module, "authdata" => (new \App\Helpers\Client)->safe_serialize($settings)));
            return substr($backupCode, 0, 4) . " " . substr($backupCode, 4, 4) . " " . substr($backupCode, 8, 4) . " " . substr($backupCode, 12, 4);
        }
        return false;
    }
    public function disableUser()
    {
        if ($this->clientid) {
            \App\Models\Client::where(array("id" => $this->clientid))->update(array("authmodule" => "", "authdata" => ""));
            return true;
        }
        if ($this->adminid) {
            \App\Models\Admin::where(array("id" => $this->adminid))->update(array("authmodule" => "", "authdata" => ""));
            return true;
        }
        return false;
    }
    public function validateAndDisableUser($inputVerifyPassword)
    {
        if (!$this->isEnabled()) {
            throw new \Exception("Not enabled");
        }
        $inputVerifyPassword = \App\Helpers\Sanitize::decode($inputVerifyPassword);
        if ($this->clientid) {
			$databasePassword = \App\Models\Client::where(array("id" => $this->clientid))->value('password') ?? "";
            $hasher = new \App\Helpers\Password();
            if (!$hasher->verify($inputVerifyPassword, $databasePassword)) {
                throw new \Exception("Password incorrect. Please try again.");
            }
        } else {
            if ($this->adminid) {
                $auth = new \App\Helpers\Auth();
                $auth->getInfobyID($this->adminid);
                if (!$auth->comparePassword($inputVerifyPassword)) {
                    throw new \Exception("Password incorrect. Please try again.");
                }
            } else {
                throw new \Exception("No user defined");
            }
        }
        $this->disableUser();
        return true;
    }
    public function saveUserSettings($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        if ($this->clientid) {
            $this->clientsettings = array_merge($this->clientsettings, $arr);
            \App\Models\Client::where(array("id" => $this->clientid))->update(array("authdata" => (new \App\Helpers\Client)->safe_serialize($this->clientsettings)));
            return true;
        }
        if ($this->adminid) {
            $this->adminsettings = array_merge($this->adminsettings, $arr);
            \App\Models\Admin::where(array("id" => $this->adminid))->update(array("authdata" => (new \App\Helpers\Client)->safe_serialize($this->adminsettings)));
            return true;
        }
        return false;
    }
    public function getUserSetting($var)
    {
        if ($this->clientid) {
            return isset($this->clientsettings[$var]) ? $this->clientsettings[$var] : "";
        }
        if ($this->adminid) {
            return isset($this->adminsettings[$var]) ? $this->adminsettings[$var] : "";
        }
        return false;
    }
    public function verifyBackupCode($code)
    {
        $backupCode = $this->getUserSetting("backupcode");
        if (!$backupCode) {
            return false;
        }
        $code = preg_replace("/[^a-z0-9]/", "", strtolower($code));
        $code = sha1($code);
        return $backupCode == $code;
    }
    public function generateNewBackupCode()
    {
        // $encryptionHash = \App::getApplicationConfig()->cc_encryption_hash;
		$encryptionHash = config('portal.hash.cc_encryption_hash');
        $uid = $this->clientid ? $this->clientid : $this->adminid;
        $backupCode = sha1($encryptionHash . $uid . time() . rand(10000, 99999));
        $backupCode = substr($backupCode, 0, 16);
        $this->saveUserSettings(array("backupcode" => sha1($backupCode)));
        return substr($backupCode, 0, 4) . " " . substr($backupCode, 4, 4) . " " . substr($backupCode, 8, 4) . " " . substr($backupCode, 12, 4);
    }


    public function enable()
{
    try {
        if (!$this->clientid && !$this->adminid) {
            throw new \Exception("No user defined");
        }

        // Get available modules
        $modules = $this->getAvailableModules();
        if (empty($modules)) {
            throw new \Exception("No 2FA modules available");
        }

        // Use the first available module
        $defaultModule = $modules[0];

        // Generate backup code and activate
        $backupCode = $this->activateUser($defaultModule);
        if (!$backupCode) {
            throw new \Exception("Failed to generate backup code");
        }

        // Log the action
        $userId = $this->clientid ?: $this->adminid;
        $userType = $this->clientid ? 'Client' : 'Admin';
        \Log::info("2FA Enabled for {$userType} ID: {$userId}");

        return [
            'success' => true,
            'backupCode' => $backupCode,
            'module' => $defaultModule
        ];

    } catch (\Exception $e) {
        \Log::error('2FA Enable Error:', [
            'message' => $e->getMessage(),
            'user_id' => $this->clientid ?: $this->adminid
        ]);
        throw $e;
    }
}

public function disable()
{
    try {
        if (!$this->clientid && !$this->adminid) {
            throw new \Exception("No user defined");
        }

        // Check if 2FA is enabled
        if (!$this->isEnabled()) {
            throw new \Exception("Two-factor authentication is not enabled");
        }

        // Disable 2FA
        $result = $this->disableUser();
        if (!$result) {
            throw new \Exception("Failed to disable two-factor authentication");
        }

        // Log the action
        $userId = $this->clientid ?: $this->adminid;
        $userType = $this->clientid ? 'Client' : 'Admin';
        \Log::info("2FA Disabled for {$userType} ID: {$userId}");

        return true;

    } catch (\Exception $e) {
        \Log::error('2FA Disable Error:', [
            'message' => $e->getMessage(),
            'user_id' => $this->clientid ?: $this->adminid
        ]);
        throw $e;
    }
}

public function isActiveClients()
{
    try {
        // Check if there are any enabled modules for clients
        if (empty($this->clientmodules)) {
            return false;
        }

        // Check if forced 2FA is enabled for clients
        if ($this->isForcedClients()) {
            return true;
        }

        // Check module settings
        foreach ($this->clientmodules as $module) {
            if ($this->isModuleEnabledForClients($module)) {
                return true;
            }
        }

        return false;

    } catch (\Exception $e) {
        \Log::error('2FA isActiveClients Error:', [
            'message' => $e->getMessage()
        ]);
        return false;
    }
}

public function isEnabled()
{
    try {
        // Check for client
        if ($this->clientid) {
            return $this->isEnabledClient();
        }

        // Check for admin
        if ($this->adminid) {
            return $this->isEnabledAdmin();
        }

        return false;

    } catch (\Exception $e) {
        \Log::error('2FA isEnabled Error:', [
            'message' => $e->getMessage(),
            'user_id' => $this->clientid ?: $this->adminid
        ]);
        return false;
    }
}

protected function isEnabledClient()
{
    try {
        // Check if client module is set
        if (empty($this->clientmodule)) {
            return false;
        }

        // Check if module is in available modules
        if (!in_array($this->clientmodule, $this->clientmodules)) {
            return false;
        }

        // Check if module is enabled for clients
        if (!$this->isModuleEnabledForClients($this->clientmodule)) {
            return false;
        }

        // Check client settings
        if (empty($this->clientsettings)) {
            return false;
        }

        return true;

    } catch (\Exception $e) {
        \Log::error('2FA isEnabledClient Error:', [
            'message' => $e->getMessage(),
            'client_id' => $this->clientid
        ]);
        return false;
    }
}

protected function isEnabledAdmin()
{
    try {
        // Check if admin module is set
        if (empty($this->adminmodule)) {
            return false;
        }

        // Check if module is in available modules
        if (!in_array($this->adminmodule, $this->adminmodules)) {
            return false;
        }

        // Check if module is enabled for admins
        if (!$this->isModuleEnabledForAdmins($this->adminmodule)) {
            return false;
        }

        // Check admin settings
        if (empty($this->adminsettings)) {
            return false;
        }

        return true;

    } catch (\Exception $e) {
        \Log::error('2FA isEnabledAdmin Error:', [
            'message' => $e->getMessage(),
            'admin_id' => $this->adminid
        ]);
        return false;
    }
}
}