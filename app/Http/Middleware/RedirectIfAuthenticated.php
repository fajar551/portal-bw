<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use Route;

class RedirectIfAuthenticated
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            if(Route::is('admin.*')) {
                return route('admin.login');
            }

            return route('login');
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
          // return redirect(RouteServiceProvider::HOME);
            switch ($guard) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                    break;
                default:
                    return redirect(RouteServiceProvider::HOME);
                    break;
            }
        }

        return $next($request);
    }
}
