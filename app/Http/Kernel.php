<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Barryvdh\Cors\HandleCors::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'bindings',
            'jwt.auth',
            \Barryvdh\Cors\HandleCors::class,
            \App\Api\Middleware\AutoImageOptimizer::class,
            \App\Api\Middleware\ApiCmsApplication::class,
        ],

        'client-api' => [
            'throttle:60,1',
            'bindings',
        ],

        'client-form-token' => [
            'doNotCacheResponse',
            \Barryvdh\Cors\HandleCors::class,
            \App\CMS\Middleware\ApiFormToken::class,
            \App\CMS\Middleware\AutoImageOptimizer::class,
        ],

        'api-form-token' => [
            \Barryvdh\Cors\HandleCors::class,
            \App\Api\Middleware\ApiFormToken::class,
        ],

        'cms' => [
            'landing.exist',
            'site.exist',
            'geoip.matched',
            'language.exist',
            'redirectUrl.exist',
            'determine.currency',
            'page.exist',
            \GrahamCampbell\HTMLMin\Http\Middleware\MinifyMiddleware::class,
        ],

        'cache' => [
            \App\CMS\Middleware\CustomCacheResponse::class
        ],

        'cms-mockup' => [
            'doNotCacheResponse',
            'landing.exist',
            \GrahamCampbell\HTMLMin\Http\Middleware\MinifyMiddleware::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        //API
        'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
        'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        'developer.only' => \App\Api\Middleware\DeveloperOnly::class,

        //CMS
        'site.exist' => \App\CMS\Middleware\CheckIfSiteExists::class,
        'landing.exist' => \App\CMS\Middleware\CheckIfLandingPageExists::class,
        'geoip.matched' => \App\CMS\Middleware\CheckIfGEOIPMatched::class,
        'redirectUrl.exist' => \App\CMS\Middleware\CheckIfRedirectUrlExists::class,
        'language.exist' => \App\CMS\Middleware\CheckIfLanguageExistsInUrl::class,
        'page.exist' => \App\CMS\Middleware\CheckIfPageExists::class,
        'determine.currency' => \App\CMS\Middleware\DetermineCurrency::class,
        'doNotCacheResponse' => \Spatie\ResponseCache\Middlewares\DoNotCacheResponse::class,
    ];
}
