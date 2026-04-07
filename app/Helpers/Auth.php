<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Auth as AuthSystem;

class Auth
{
	private $inputusername = "";
    private $admindata = array();
    private $logincookie = "";
    private $hasPasswordHashField = true;
    private function getInfo($where, $resource = NULL, $restrictToEnabled = true)
    {
        if ($restrictToEnabled) {
            $where["disabled"] = "0";
        }
        $passwordHashField = "passwordhash,";
        // $installedVersion = \DI::make("app")->getDBVersion();
        // $lasVersionWithoutHashField = new Version\SemanticVersion("5.3.8-release.1");
        // $schemaIsSane = Version\SemanticVersion::compare($installedVersion, $lasVersionWithoutHashField, ">");
		$schemaIsSane = true;
        if (!$schemaIsSane) {
            $this->hasPasswordHashField = false;
            $passwordHashField = "";
        }
        // $result = select_query("tbladmins", , $where, "", "", "", "", $resource);
		$result = \App\Models\Admin::selectRaw("id,roleid,username,password,email," . $passwordHashField . "template,language,authmodule,loginattempts,disabled")
			->where($where)
			->first();
        $data = $result;
		if ($data) {
			$data = $data->makeVisible(['password', 'passwordhash']);
			$this->admindata = $data->toArray();
		}
        return $this->admindata["id"] ? true : false;
    }
    public function getInfobyID($adminid, $resource = NULL, $restrictToEnabled = true)
    {
        if (!is_numeric($adminid)) {
            return false;
        }
        return $this->getInfo(array("id" => (int) $adminid), $resource, $restrictToEnabled);
    }
    public function getInfobyUsername($username, $restrictToEnabled = true)
    {
        $this->inputusername = $username;
        return $this->getInfo(array("username" => $username), null, $restrictToEnabled);
    }
    public function comparePasswordInputWithHook($userInput, $isApi = false)
    {
        $hookName = $isApi ? "AuthAdminApi" : "AuthAdmin";
        $admin = \App\Models\Admin::find($this->getAdminID());
        try {
            if ($isApi) {
                $hookResults = \App\Helpers\Hooks::run_hook("AuthAdminApi", array($userInput, $admin), true);
                $expectedResults = count(\App\Helpers\Hooks::get_registered_hooks($hookName));
            } else {
                $hookResults = \App\Helpers\Hooks::run_hook("AuthAdmin", array($userInput, $admin), true);
                $expectedResults = count(\App\Helpers\Hooks::get_registered_hooks($hookName));
            }
            if (count($hookResults) < $expectedResults) {
                return false;
            }
            if ($expectedResults < 1) {
                return false;
            }
            $oneHookResponseTrue = null;
            $oneHookResponseFalse = null;
            foreach ($hookResults as $result) {
                if ($result && is_null($oneHookResponseTrue)) {
                    $oneHookResponseTrue = true;
                } else {
                    if (!$result && is_null($oneHookResponseFalse)) {
                        $oneHookResponseFalse = true;
                    }
                }
            }
            if ($oneHookResponseTrue && is_null($oneHookResponseFalse)) {
                $result = true;
            } else {
                $result = false;
            }
        } catch (\Exception $e) {
            $result = false;
            \App\Helpers\LogActivity::Save($hookName . " Hook Exception: " . $e->getMessage());
        }
        return $result;
    }
    public function comparePassword($password)
    {
        $adminLoginHooks = \App\Helpers\Hooks::get_registered_hooks("AuthAdmin");
        if (!empty($adminLoginHooks)) {
            return $this->comparePasswordInputWithHook($password, false);
        }
        $result = false;
        $password = trim($password);
        if ($password) {
            $hasher = new \App\Helpers\Password();
            if ($this->isAdminPWHashSet()) {
                $storedSecret = $this->getAdminPWHash();
            } else {
                $storedSecret = $this->getLegacyAdminPW();
                $storedSecretInfo = $hasher->getInfo($storedSecret);
                if ($storedSecretInfo["algoName"] != \App\Helpers\Password::HASH_MD5 && $storedSecretInfo["algoName"] != \App\Helpers\Password::HASH_UNKNOWN) {
                    $password = md5($password);
                }
            }
            try {
                $result = $hasher->verify($password, $storedSecret);
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Failed to verify admin password hash: " . $e->getMessage());
            }
        }
        return $result;
    }
    public function compareApiPassword($password)
    {
        $adminLoginHooks = \App\Helpers\Hooks::get_registered_hooks("AuthAdminApi");
        if (!empty($adminLoginHooks)) {
            return $this->comparePasswordInputWithHook($password, true);
        }
        $result = false;
        $password = trim($password);
        $storedHash = $this->getLegacyAdminPW();
        if ($password && $storedHash) {
            $hasher = new \App\Helpers\Password();
            try {
                $info = $hasher->getInfo($storedHash);
                if ($info["algoName"] == \App\Helpers\Password::HASH_MD5) {
                    $result = $hasher->assertBinarySameness($password, $this->getLegacyAdminPW());
                } else {
                    if ($info["algoName"] != \App\Helpers\Password::HASH_UNKNOWN) {
                        $result = $hasher->verify($password, $storedHash);
                    }
                }
            } catch (Exception $e) {
                \App\Helpers\LogActivity::Save("Failed to verify API password hash: " . $e->getMessage());
            }
        }
        return $result;
    }
    public function isTwoFactor()
    {
        return $this->admindata["authmodule"] ? true : false;
    }
    public function getAdminID()
    {
        return $this->admindata["id"];
    }
    public function getAdminRoleId()
    {
        return (int) $this->admindata["roleid"];
    }
    public function getAdminUsername()
    {
        return $this->admindata["username"];
    }
    public function getAdminEmail()
    {
        return $this->admindata["email"];
    }
    public function getLegacyAdminPW()
    {
        return !empty($this->admindata["password"]) ? $this->admindata["password"] : "";
    }
    public function getAdminPWHash()
    {
        return !empty($this->admindata["passwordhash"]) ? $this->admindata["passwordhash"] : "";
    }
    public function isAdminPWHashSet()
    {
        $passwordHash = $this->getAdminPWHash();
        return empty($passwordHash) ? false : true;
    }
    public function generateNewPasswordHashAndStore($password)
    {
        $hasher = new \App\Helpers\Password();
        $result = false;
        if ($this->hasPasswordHashField) {
            try {
                $hashedSecret = $hasher->hash($password);
				$result = \App\Models\Admin::where(array("id" => $this->getAdminID()))->update(array("passwordhash" => $hashedSecret));
                if ($result !== false) {
                    $this->admindata["passwordhash"] = $hashedSecret;
                }
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Failed to rehash admin password: " . $e->getMessage());
            }
        }
        return $result;
    }
    public function generateNewPasswordHashAndStoreForApi($password)
    {
        $hasher = new \App\Helpers\Password();
        $result = false;
        if ($this->hasPasswordHashField) {
            try {
                $hashedSecret = $hasher->hash($password);
				$result = \App\Models\Admin::where(array("id" => $this->getAdminID()))->update(array("password" => $hashedSecret));
                if ($result !== false) {
                    $this->admindata["password"] = $hashedSecret;
                }
            } catch (\Exception $e) {
                \App\Helpers\LogActivity::Save("Failed to rehash admin password: " . $e->getMessage());
            }
        }
        return $result;
    }
    public function getAdminTemplate()
    {
        return $this->admindata["template"];
    }
    public function getAdminLanguage()
    {
        return $this->admindata["language"];
    }
    public function getAdmin2FAModule()
    {
        return $this->admindata["authmodule"];
    }
    private function getAdminUserAgent()
    {
        return array_key_exists("HTTP_USER_AGENT", $_SERVER) ? $_SERVER["HTTP_USER_AGENT"] : "";
    }
    public function isActive()
    {
        return $this->admindata["disabled"] != 1;
    }
    // public function generateAdminSessionHash($whmcsclass = false)
    // {
    //     $whmcs = \DI::make("app");
    //     if ($whmcsclass) {
    //         $haship = $whmcsclass->get_config("DisableSessionIPCheck") ? "" : Utility\Environment\CurrentUser::getIP();
    //         $cchash = $whmcsclass->get_hash();
    //     } else {
    //         $haship = \App\Helpers\Cfg::get("DisableSessionIPCheck") ? "" : Utility\Environment\CurrentUser::getIP();
    //         $cchash = $whmcs->get_hash();
    //     }
    //     $hash = sha1($this->getAdminID() . $this->getAdminUserAgent() . $this->getAdminPWHash() . $haship . substr(sha1($cchash), 20));
    //     return $hash;
    // }
    // public function setSessionVars($whmcsclass = false)
    // {
    //     $_SESSION["adminid"] = $this->getAdminID();
    //     $_SESSION["adminpw"] = $this->generateAdminSessionHash($whmcsclass);
    //     conditionally_set_token(genRandomVal());
    // }
    // public function processLogin($createAdminLogEntry = true)
    // {
    //     $whmcs = \App::self();
    //     if ($createAdminLogEntry) {
    //         insert_query("tbladminlog", array("adminusername" => $this->getAdminUsername(), "logintime" => "now()", "lastvisit" => "now()", "ipaddress" => Utility\Environment\CurrentUser::getIP(), "sessionid" => session_id()));
    //     }
    //     update_query("tbladmins", array("loginattempts" => "0"), array("username" => $this->getAdminUsername()));
    //     $resetTokenId = get_query_val("tbltransientdata", "id", array("data" => json_encode(array("id" => $this->getAdminID(), "email" => $this->getAdminEmail()))));
    //     if ($resetTokenId) {
    //         delete_query("tbltransientdata", array("id" => $resetTokenId));
    //     }
    //     \App\Helpers\Hooks::run_hook("AdminLogin", array("adminid" => $this->getAdminID(), "username" => $this->getAdminUsername()));
    // }
    // public function getRememberMeCookie()
    // {
    //     $remcookie = Cookie::get("AU");
    //     if (!$remcookie) {
    //         $remcookie = Cookie::get("AUser");
    //     }
    //     return $remcookie;
    // }
    // public function isValidRememberMeCookie($whmcsclass = false)
    // {
    //     $whmcs = \DI::make("app");
    //     $cookiedata = $this->getRememberMeCookie();
    //     if ($cookiedata) {
    //         $cookiedata = explode(":", $cookiedata);
    //         $resource = $whmcsclass !== false ? $whmcsclass->getDatabaseObj()->getConnection() : $whmcs->getDatabaseObj()->getConnection();
    //         if ($this->getInfobyID($cookiedata[0], $resource)) {
    //             if ($whmcsclass) {
    //                 $hash = $whmcsclass->get_hash();
    //             } else {
    //                 $hash = $whmcs->get_hash();
    //             }
    //             $cookiehashcompare = sha1($this->generateAdminSessionHash($whmcsclass) . $hash);
    //             if ($cookiedata[1] == $cookiehashcompare && $this->isAdminPWHashSet()) {
    //                 return true;
    //             }
    //         }
    //     }
    //     return false;
    // }
    // public function setRememberMeCookie()
    // {
    //     $whmcs = \DI::make("app");
    //     Cookie::set("AU", $this->getAdminID() . ":" . sha1($_SESSION["adminpw"] . $whmcs->get_hash()), "12m");
    // }
    // public function unsetRememberMeCookie()
    // {
    //     Cookie::delete("AU");
    // }
    private function getWhiteListedIPs()
    {
        // $whmcs = \DI::make("app");
        $ips = array();
        $whitelistedips = (array) (new \App\Helpers\Client)->safe_unserialize(\App\Helpers\Cfg::get("WhitelistedIPs"));
        foreach ($whitelistedips as $whitelisted) {
            $ips[] = $whitelisted["ip"];
        }
        return $ips;
    }
    private function isWhitelistedIP($ip)
    {
        $whitelistedips = $this->getWhiteListedIPs();
        if (in_array($ip, $whitelistedips)) {
            return true;
        }
        $ipparts = explode(".", $ip);
        if (3 <= count($ipparts)) {
            $ip = $ipparts[0] . "." . $ipparts[1] . "." . $ipparts[2] . ".*";
            if (in_array($ip, $whitelistedips)) {
                return true;
            }
        }
        if (2 <= count($ipparts)) {
            $ip = $ipparts[0] . "." . $ipparts[1] . ".*.*";
            if (in_array($ip, $whitelistedips)) {
                return true;
            }
        }
        return false;
    }
    private function isBanEnabled()
    {
        return 0 < \App\Helpers\Cfg::get("InvalidLoginBanLength") ? true : false;
    }
    private function getLoginBanDate()
    {
        return date("Y-m-d H:i:s", mktime(date("H"), date("i") + \App\Helpers\Cfg::get("InvalidLoginBanLength"), date("s"), date("m"), date("d"), date("Y")));
    }
    protected function sendWhitelistedIPNotice()
    {
        return (bool) \App\Helpers\Cfg::get("sendFailedLoginWhitelist");
    }
    public function failedLogin()
    {
        if (!$this->isBanEnabled()) {
            return false;
        }
        $remote_ip = \App\Helpers\CurrentUser::getIP();
        if ($this->isWhitelistedIP($remote_ip)) {
            if ($this->sendWhitelistedIPNotice()) {
                if (isset($this->admindata["username"])) {
                    $username = $this->admindata["username"];
                    \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Admin Failed Login Attempt", "<p>A recent login attempt failed.  Details of the attempt are below.</p>" . "<p>Date/Time: " . date("d/m/Y H:i:s") . "<br>" . "Username: " . $username . "<br>" . "IP Address: " . $remote_ip . "<br>" . "Hostname: " . gethostbyaddr($remote_ip) . "</p>");
                } else {
                    \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Admin Failed Login Attempt", "<p>A recent login attempt failed.  Details of the attempt are below.</p>" . "<p>Date/Time: " . date("d/m/Y H:i:s") . "<br>" . "Username: " . $this->inputusername . "<br>" . "IP Address: " . $remote_ip . "<br>" . "Hostname: " . gethostbyaddr($remote_ip) . "</p>");
                }
            }
            return false;
        }
        $loginfailures = (new \App\Helpers\Client)->safe_unserialize(\App\Helpers\Cfg::get("LoginFailures"));
        if (!array_key_exists($remote_ip, $loginfailures) || !is_array($loginfailures[$remote_ip])) {
            $loginfailures[$remote_ip] = array();
        }
        if ($loginfailures[$remote_ip]["expires"] < time()) {
            $loginfailures[$remote_ip]["count"] = 0;
        }
        $loginfailures[$remote_ip]["count"]++;
        $loginfailures[$remote_ip]["expires"] = time() + 30 * 60;
        if (3 <= $loginfailures[$remote_ip]["count"]) {
            unset($loginfailures[$remote_ip]);
            \App\Models\Bannedip::insert(array("ip" => $remote_ip, "reason" => "3 Invalid Login Attempts", "expires" => $this->getLoginBanDate()));
        }
        \App\Helpers\Cfg::setValue("LoginFailures", (new \App\Helpers\Pwd)->safe_serialize($loginfailures));
        if (isset($this->admindata["username"])) {
            $username = $this->admindata["username"];
            \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Admin Failed Login Attempt", "<p>A recent login attempt failed.  Details of the attempt are below.</p><p>Date/Time: " . date("d/m/Y H:i:s") . "<br>Username: " . $username . "<br>IP Address: " . $remote_ip . "<br>Hostname: " . gethostbyaddr($remote_ip) . "</p>");
            \App\Helpers\LogActivity::Save("Failed Admin Login Attempt - Username: " . $username);
        } else {
            \App\Helpers\Functions::sendAdminNotification("system", "WHMCS Admin Failed Login Attempt", "<p>A recent login attempt failed.  Details of the attempt are below.</p><p>Date/Time: " . date("d/m/Y H:i:s") . "<br>Username: " . $this->inputusername . "<br>IP Address: " . $remote_ip . "<br>Hostname: " . gethostbyaddr($remote_ip) . "</p>");
            \App\Helpers\LogActivity::Save("Failed Admin Login Attempt - IP: " . $remote_ip);
        }
    }
    public static function getID()
    {
        return self::isLoggedIn() ? AuthSystem::guard('admin')->user()->id : 0;
    }
    public static function isLoggedIn()
    {
        // return isset($_SESSION["adminid"]);
		return AuthSystem::guard('admin')->check();
    }

	// next ...
}
