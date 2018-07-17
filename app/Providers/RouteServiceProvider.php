<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Api\V1\Controllers';

    /**
     * @var string
     */
    protected $cmsNamespace = 'App\CMS\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::pattern('name', '^((?!uploads(\/' . config('cms.crops_path') . ')?)[\s\S])*$');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapClientApiRoutes();

        $this->mapApiRoutes();

        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::middleware('web')
             ->namespace($this->cmsNamespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * @return void
     */
    protected function mapClientApiRoutes()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Route::prefix('client-api')
            ->middleware('client-api')
            ->namespace($this->cmsNamespace)
            ->group(base_path('routes/client-api.php'));
    }
}