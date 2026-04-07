<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class PasswordAdmin implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $authadmin = auth()->guard('admin')->user();
        if (!$authadmin) {
            return false;
        }
        $plain = $value;
        $auth = new \App\Helpers\Auth;
        $hasher = new \App\Helpers\Password();
        $auth->getInfobyUsername($authadmin->username);

        $storedSecret = $authadmin->getAuthPassword();
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

        return Hash::check($plain, $storedSecret);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute is incorrect.';
    }
}
