<?php

namespace App\Http\Middleware;

use Closure;

class APIAllowedIPsMiddleware
{
    public $allowedIps = [
        '127.0.0.1',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $whitelistedips = (new \App\Helpers\Client)->safe_unserialize(\App\Helpers\Cfg::get("APIAllowedIPs"));
        $whitelistedips = collect($whitelistedips);
        $whitelistedips = $whitelistedips->pluck('ip')->unique()->filter()->all();

        $client_ip = \App\Helpers\Application::getRemoteIp();
        $ips = array_merge($whitelistedips, $this->allowedIps);

        if (!in_array($client_ip, $ips)) {
            return \App\Helpers\ResponseAPI::Error([
                'message' => "You are restricted to access the api.",
                'current_ip' => $client_ip,
            ], 403);
        }
        return $next($request);
    }
}
