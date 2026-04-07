<?php

namespace App\Http\Middleware;

use Closure;
use Hexadog\ThemesManager\Facades\ThemesManager;

class ThemeSwitcher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $theme = '')
    {
        try {
            ThemesManager::set($theme);
        } catch (\Exception $e) {
            exit("Invalid Order Form Template Name: ".$theme);
        }
        return $next($request);
    }
}
