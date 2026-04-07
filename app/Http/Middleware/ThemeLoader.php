<?php

namespace App\Http\Middleware;

use Closure;
use Hexadog\ThemesManager\Http\Middleware\ThemeLoader as HexadogThemeLoader; 

class ThemeLoader extends HexadogThemeLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $theme = null)
    {
        // if ($request->segment(1) !== 'admin') {
        //     $theme = 'hexadog/one';
        // }

        // return $next($request);
        return parent::handle($request, $next, $theme);
    }
}
