<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Helpers\ResponseAPI;
use App;

class Includes extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('apiallowedips');
    }

    public function api(Request $request)
    {
        try {
            $action = $request->input("action");
            $username = $request->input("username") ?? "";
            $password = $request->input("password") ?? "";
            $route = Route::getRoutes()->getByName($action);

            if (!$route) {
                throw new \Exception("Action not found");
            }

            // validate identifier
            if ($username) {
                $device = $this->verifyDeviceCredentials($username, $password);
                $admin = $device->admin;
            } else {
                try {
                    $device = $this->verifyDeviceCredentials($username, $password, true);
                    $admin = $device->admin;
                } catch (\Exception $e) {
                    $admin = $this->verifyAdminCredentials($username, $password);
                }
            }
            if ($admin) {
                if (!$admin->isAllowedToAuthenticate()) {
                    throw new \App\Exceptions\Api\AuthException("Access Denied: Authentication not permitted");
                }
                if (!$admin->hasPermission("API Access")) {
                    throw new \App\Exceptions\Api\AuthException("Access Denied");
                }
            } else {
                throw new \App\Exceptions\Api\AuthException("Access Denied");
            }

            // authz
            if (!$device->permissions()->isAllowed($action)) {
                throw new \App\Exceptions\Authorization\AccessDenied("Invalid Permissions: API action \"" . $action . "\" is not allowed");
            }

            // check ip
            if (\App\Helpers\Application::isVisitorIPBanned()) {
                throw new \WHMCS\Exception\Api\AuthException("IP Banned");
            }

            // $params = $request->all();
            $response = App::call($route->action['controller']);

            return json_decode($response->content(), true);
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function verifyDeviceCredentials($userProvidedIdentifier, $userProvidedSecret, $allowCompatVerification = false)
    {
        if (!$userProvidedIdentifier || !$userProvidedSecret) {
            throw new \App\Exceptions\Api\AuthException("Invalid or missing credentials");
        }
        $device = \App\Models\Deviceauth::byIdentifier($userProvidedIdentifier)->first();
        if (is_null($device)) {
            throw new \App\Exceptions\Api\AuthException("Invalid or missing credentials");
        }
        if (!$device->is_admin || !$device->admin instanceof \App\User\Admin) {
            throw new \App\Exceptions\Api\AuthException("Invalid administrative identifier");
        }
        if ($device->admin->disabled) {
            throw new \App\Exceptions\Api\AuthException("Administrator Account Disabled");
        }
        $isVerified = $device->verify($userProvidedSecret);
        if (!$isVerified && $allowCompatVerification) {
            $isVerified = $device->verifyCompat($userProvidedSecret);
        }
        if (!$isVerified) {
            $adminAuth = new \App\Helpers\Auth();
            $adminAuth->getInfobyID($device->admin->id);
            $adminAuth->failedLogin();
            throw new \App\Exceptions\Authentication\InvalidSecret("Authentication Failed");
        }
        $device->last_access = \Carbon\Carbon::now();
        $device->save();
        return $device;
    }

    public function verifyAdminCredentials($userProvidedUsername, $userProvidedPassword)
    {
        $adminAuth = new \App\Helpers\Auth();
        $user = $adminAuth->getInfobyUsername($userProvidedUsername, false);
        if (!$user) {
            $adminAuth->failedLogin();
            throw new \App\Exceptions\Api\AuthException("Authentication Failed");
        }
        if (!$adminAuth->isActive()) {
            throw new \App\Exceptions\Api\AuthException("Administrator Account Disabled");
        }
        $hasher = new \App\Helpers\Password();
        try {
            $info = $hasher->getInfo($userProvidedPassword);
            if ($info["algoName"] != \App\Helpers\Password::HASH_MD5) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            \App\Helpers\LogActivity::Save("Unable to inspect user provided API password");
            throw new \App\Exceptions\Api\AuthException("Invalid password provided");
        }
        if (!$adminAuth->compareApiPassword($userProvidedPassword)) {
            $adminAuth->failedLogin();
            throw new \App\Exceptions\Authentication\InvalidSecret("Authentication Failed");
        }
        try {
            $needsRehash = $hasher->needsRehash($adminAuth->getLegacyAdminPW());
            if ($needsRehash) {
                $adminAuth->generateNewPasswordHashAndStoreForApi($userProvidedPassword);
            }
        } catch (\Exception $e) {
            \App\Helpers\LogActivity::Save("Failed to validate password rehash: " . $e->getMessage());
        }
        return \App\User\Admin::find($adminAuth->getAdminID());
    }

    public function apiOLD(Request $request)
    {
        try {
            $action = $request->input("action");
            $username = $request->input("username") ?? "";
            $password = $request->input("password") ?? "";
            $route = Route::getRoutes()->getByName($action);

            if (!$route) {
                return ResponseAPI::Error([
                    'message' => 'Action not found'
                ]);
            }

            $url = url($route->uri);
            $params = $request->all();

            // $response = Http::withOptions(['verify' => false])->withHeaders([
            //     'Accept' => 'application/json',
            // ])->withBasicAuth($username, $password)->post($url, $params);
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'Accept' => 'application/json',
            ])->post($url, $params);

            return $response->json();
        } catch (\Exception $e) {
            return ResponseAPI::Error([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
