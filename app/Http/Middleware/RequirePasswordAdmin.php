<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Auth\Middleware\RequirePassword;

class RequirePasswordAdmin extends RequirePassword
{
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ($this->shouldConfirmPassword($request)) {
            if ($request->expectsJson()) {
                return $this->responseFactory->json([
                    'message' => 'Admin Password confirmation required.',
                ], 423);
            }

            return $this->responseFactory->redirectGuest(
                $this->urlGenerator->route($redirectToRoute ?? 'admin.password.confirm')
            );
        }

        return $next($request);
    }
}
