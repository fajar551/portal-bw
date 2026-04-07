<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME_ADMIN;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    protected function rules()
    {
        // $currentauth = \Auth::guard('admin')->user();
        // $auth = new \App\Helpers\Auth;
        // $hasher = new \App\Helpers\Password();
        // $auth->getInfobyUsername($currentauth->username);

        return [
            // 'password' => 'required|password:admin',
            // 'password' => 'required',
            'password' => ['required', new \App\Rules\PasswordAdmin]
        ];
    }
}
