<?php

namespace App\Exceptions\Authorization;

use Exception;

class AbstractAuthorizationException extends \App\Exceptions\HttpCodeException
{
    //
    const DEFAULT_HTTP_CODE = 403;
}
