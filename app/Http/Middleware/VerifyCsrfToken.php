<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        'callback/*',
        '*/callback',
        'modules/gateways/callback/*',
        'services/servicedetails/*',
        'cart.php',
        '/cart*',
        'apis/*',
        '*/widget/*',
        'billinginfo/viewinvoice/web/*',
    ];
}
