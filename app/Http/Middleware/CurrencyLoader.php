<?php

namespace App\Http\Middleware;

use Closure;

class CurrencyLoader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user('web');
        if ($user) {
            $currency = \App\Helpers\Format::getCurrency($user->id);
            $GLOBALS['currency'] = $currency;
        }
        // \Log::debug("CurrencyLoader", json_decode(json_encode($request->user('web')),true));
        return $next($request);
    }
}
