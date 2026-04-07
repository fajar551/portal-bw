<?php

namespace App\Exceptions\Authentication;

use Exception;

class AbstractAuthenticationException extends \App\Exceptions\HttpCodeException
{
    //
    const DEFAULT_HTTP_CODE = 403;
}
