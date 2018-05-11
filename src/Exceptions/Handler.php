<?php

namespace GetCandy\Api\Exceptions;

use Exception;
use GetCandy\Api\Traits\Fractal;
use Illuminate\Auth\AuthenticationException;
use GetCandy\Exceptions\Api\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use Fractal;

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
        if (($request->headers->get('accept-content') || $request->ajax()) && $exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            switch ($statusCode) {
                case 400:
                    $response = $this->errorWrongArgs();
                    break;
                case 401:
                    $response = $this->errorUnauthorized();
                    break;
                case 403:
                    $response = $this->errorForbidden();
                    break;
                case 404:
                    $response = $this->errorNotFound();
                    break;
                case 500:
                    $response = $this->errorInternalError();
                    break;
                default:
                    $response = $this->setStatusCode($statusCode)->respondWithError($exception->getMessage());
                    break;
            }

            return $response;
        } elseif ($exception instanceof AuthorizationException) {
            return $this->errorUnauthorized();
        }

        return parent::render($request, $exception);
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

        return redirect()->guest(route('hub.login'));
    }
}
