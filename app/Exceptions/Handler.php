<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Psr\Log\LoggerInterface;

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
//        \League\OAuth2\Server\Exception\OAuthServerException::class,
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
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException) {
            try {
                $logger = $this->container->make(LoggerInterface::class);
            } catch (Exception $e) {
                throw $exception; // throw the original exception
            }

            $logger->error(
                $exception->getMessage(),
                ['exception' => $exception]
            );
        } else {
            parent::report($exception);
        }
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

        // oAuth Exception(s)
        if ($exception instanceof \Illuminate\Auth\AuthenticationException ||
            $exception->getPrevious() instanceof \Illuminate\Auth\AuthenticationException)
            if (\App::environment('production'))
                return response()->json(['error' => 'Authentication error'], 401);

        if($exception instanceof \App\Exceptions\Festigeek\FailedInternalRequestException ||
            $exception->getPrevious() instanceof \App\Exceptions\Festigeek\FailedInternalRequestException)
            if (\App::environment('production'))
                return response()->json(['error' => 'Authentication error'], 500);

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

//        dd($exception); // Only for BIG trouble.

        // TODO Delete if not needed
//        return parent::render($request, $exception);
//
//        if (method_exists('getStatusCode', $exception))
//            $status = $exception->getStatusCode();
//        else
//            $status = 'unavailable';
//
//        return response()->json(['error' => $exception->getMessage(), 'status_code' => $status, 'class' => get_class($exception), 'trace' => $exception->getTraceAsString()], 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
