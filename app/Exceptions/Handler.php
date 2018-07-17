<?php

namespace App\Exceptions;

use App\CMS\Helpers\CMSHelper;
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
        if (env('APP_DEBUG', false) || strtolower(env('APP_DEBUG', false)) === 'true') {
        	parent::report($exception);
	    }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        $request->attributes->add(['exception_found' => true]);

        if ($request->is('api/*') || $request->is('client-api/*')) {
            if ($request->expectsJson()) {
                /** @noinspection PhpUndefinedMethodInspection */
                return response()->apiJson(null, $exception->getMessage(), $exception->getCode());
            }
        }

        if ($request->is('uploads/*')) {
            if ($request->is('uploads/_crops/*')
                && !! preg_match('/(.*\/)(?:_crops\/)(.*)(?:-(?:\d+|_)x(?:\d+|_).*)(\.\w+)$/', $request->fullUrl())) {
                $reversed = preg_replace('/(.*\/)(?:_crops\/)(.*)(?:-(?:\d+|_)x(?:\d+|_).*)(\.\w+)$/', '$1$2$3', $request->fullUrl());
                return redirect($reversed);
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                return response()->apiJson(null, $exception->getMessage(), $exception->getStatusCode() ?: 500);
            }
        }

        if ($this->isHttpException($exception)) {
            if ( ! env('APP_DEBUG', false)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $statusCode = $exception->getStatusCode() ?: 500;
                $defaultTemplate = 'errors.' . $statusCode;

                if ($site = CMSHelper::getSite()) {
                    $template = CMSHelper::getTemplatePath('errors.' . $statusCode);

                    if (view()->exists($template)) {
                        return response()->view($template, [], $statusCode);
                    }
                }

                if (view()->exists($defaultTemplate)) {
                    return response()->view($defaultTemplate, [], $statusCode);
                }
            }
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
    protected function unauthenticated($request, /** @noinspection PhpUnusedParameterInspection */AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(null, 'Unauthenticated.', 401);
        }

        return redirect()->guest(route('login'));
    }
}
