<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, ThrottlesLogins;

    /**
     * Max login attempts allowed.
     */
    public $maxAttempts = 5;

    /**
     * Number of minutes to lock the login.
     */
    public $decayMinutes = 3;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest:admin')->except('logout');
    }

    public function field(Request $request)
    {
        $email = $this->username();
        return filter_var($request->get($email), FILTER_VALIDATE_EMAIL) ? $email : 'username';
    }

    protected function credentials(Request $request)
    {
        $field = $this->field($request);

        return [
            $field     => $request->get($this->username()),
            'password' => $request->get('password'),
            'disabled'   => 0,
        ];
    }

    protected function validateLogin(Request $request)
    {
        $field = $this->field($request);

        $messages = ["{$this->username()}.exists" => 'The account you are trying to login is not activated or it has been disabled.'];

        $this->validate($request, [
            $this->username() => "required|exists:App\Models\Admin,{$field},disabled,0",
            'password'        => 'required',
        ], $messages);
    }

    protected function loggedOut(Request $request)
    {
        return redirect()->route('admin.login');
    }

    protected function guard() 
    {
        return Auth::guard('admin');
    }

    protected function authenticated(Request $request, $user)
    {
        DB::beginTransaction();
        try {
            \App\Models\AdminLog::insert(array("adminusername" => $user->username, "logintime" => \Carbon\Carbon::now(), "lastvisit" => \Carbon\Carbon::now(), "ipaddress" => \App\Helpers\CurrentUser::getIP(), "sessionid" => session()->getId()));
            
            $user->loginattempts = 0;
            $user->save();

            $resetTokenId = \App\Models\Transientdata::where("data->id", $user->id)->where("data->email", $user->email)->value('id');
            if ($resetTokenId) {
                \App\Models\Transientdata::where(array("id" => $resetTokenId))->delete();
            }
            \App\Helpers\Hooks::run_hook("AdminLogin", array("adminid" => $user->id, "username" => $user->username));
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function logout(Request $request)
    {
        $auth = $this->guard()->user();
        $adminid = $auth ? $auth->id : 0;
        \App\User\AdminLog::where(array("sessionid" => session()->getId()))->update(array("logouttime" => \Carbon\Carbon::now()));
        \App\Helpers\Hooks::run_hook("AdminLogout", array("adminid" => $adminid));

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }

    // public function logout()
    // {
    //     // Auth::guard('admin')->logout();
    //     // return redirect()->route('admin.login');
    //     if(Auth::guard('admin')->check()) // this means that the admin was logged in.
    //     {
    //         Auth::guard('admin')->logout();
    //         return redirect()->route('admin.login');
    //     }

    //     $this->guard()->logout();
    //     $request->session()->invalidate();

    //     return $this->loggedOut($request) ?: redirect('/admin/login');
    // }

    // public function login(Request $request)
    // {
    //     // Validate form data
    //     $this->validate($request, [
    //         'email' => 'required|string',
    //         'password' => 'required|string'
    //     ]);

    //     // Attempt to log the user in
    //     if(Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember))
    //     {
    //         return redirect()->intended(route('admin.dashboard'));
    //     }

    //     // if unsuccessful
    //     return redirect()->back()->withInput($request->only('email','remember'))->with(['error_login' => 'Wrong Email or Password, please try again!']);
    // }

    // public function username()
    // {
    //     $login = request()->input('email');
    //     $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     request()->merge([$field => $login]);
    //     return $field;
    // }
}
