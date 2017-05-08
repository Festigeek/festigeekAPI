<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Tymon\JWTAuth\Exceptions\JWTException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // HTTP Exceptions
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException ||
             $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException){
            switch($exception->getStatusCode()) {
                case 403:
                    return response()->json(['error' => 'Forbidden Access'], 403);
                    break;
                case 404:
                    return response()->json(['error' => 'Page not found'], 404);
                    break;
            }
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
            $exception->getPrevious() instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException)
            return response()->json(['error' => 'Page not found'], 404);

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException ||
            $exception->getPrevious() instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException)
            return response()->json(['error' => 'Wrong HTTP Method'], 405);

        // JWT Ecexptions
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException ||
            $exception->getPrevious() instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException)
            return response()->json(['error' => 'Token has expired'], 401);

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException ||
            $exception->getPrevious() instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException)
            return response()->json(['error' => 'Token is invalid'], 401);

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException ||
            $exception->getPrevious() instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException)
            return response()->json(['error' => 'Token not provided'], 401);

        // Other Exceptions in production
        if (\App::environment('production'))
            return response()->json(['error' => 'Internal error'], 500);

        /////////////////
        // Developpement
        /////////////////

        // PayPalException
        if($exception instanceof \PayPal\Exception\PayPalConnectionException)
            return response()->json(['error' => 'PayPal Connection Exception'], 502);

        if($exception instanceof \PayPal\Exception\PayPalConfigurationException)
            return response()->json(['error' => 'PayPal Configuration Exception'], 502);

        if($exception instanceof \PayPal\Exception\PayPalInvalidCredentialException)
            return response()->json(['error' => 'PayPal Invalid Credential Exception'], 502);

        if($exception instanceof \PayPal\Exception\PayPalMissingCredentialException)
            return response()->json(['error' => 'PayPal Missing Credential Exception'], 502);

        dd($exception);
        //return parent::render($request, $exception);

        if (method_exists('getStatusCode', $exception))
            $status = $exception->getStatusCode();
        else
            $status = 'unavailable';

        return response()->json(['error' => $exception->getMessage(), 'status_code' => $status, 'class' => get_class($exception), 'trace' => $exception->getTraceAsString()], 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
