<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use ResponseAPI;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->view('error.404', [], 404);
        }
    
        return parent::render($request, $exception);
        
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException && $request->expectsJson()) {
            return ResponseAPI::Error([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException && $request->expectsJson()) {
            return ResponseAPI::Error([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            // return response()->json(['error' => 'Unauthenticated.'], 401);
            return ResponseAPI::Error([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $guard = Arr::get($exception->guards(), 0);

        switch ($guard) {
            case 'admin':
                $login = 'admin.login';
            break;

            default:
                $login = 'login';
            break;
        }

        return redirect()->guest(route($login));

        // if ($request->is('admin') || $request->is('admin/*')) {
        //     return redirect()->guest(route('admin.login'));
        // }

        // return redirect()->guest(route('login'));
    }
}