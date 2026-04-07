<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\EloquentUserProvider as AdminProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class CustomAdminProvider extends AdminProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * @see https://return2.net/laravel-auth-custom-login-validation/
     * Overrides the framework defaults validate credentials method 
     *
     * @param UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials) {
        $plain = $credentials['password'];
        $auth = new \App\Helpers\Auth;
        $hasher = new \App\Helpers\Password();
        $auth->getInfobyUsername($user->username);

        $storedSecret = $user->getAuthPassword();
        if ($plain) {
            if ($auth->isAdminPWHashSet()) {
                $storedSecret = $auth->getAdminPWHash();
            } else {
                $storedSecret = $auth->getLegacyAdminPW();
                $storedSecretInfo = $hasher->getInfo($storedSecret);
                if ($storedSecretInfo["algoName"] != \App\Helpers\Password::HASH_MD5 && $storedSecretInfo["algoName"] != \App\Helpers\Password::HASH_UNKNOWN) {
                    $plain = md5($password);
                }
            }
        }

        return $this->hasher->check($plain, $storedSecret);
    }
}
