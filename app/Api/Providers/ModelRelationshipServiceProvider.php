<?php

namespace App\Api\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class ModelRelationshipServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'ComponentOption' => 'App\Api\Models\ComponentOption',
            'TemplateItemOption' => 'App\Api\Models\TemplateItemOption',
            'PageItemOption' => 'App\Api\Models\PageItemOption',
            'GlobalItemOption' => 'App\Api\Models\GlobalItemOption',
            'PageItem' => 'App\Api\Models\PageItem',
            'GlobalItem' => 'App\Api\Models\GlobalItem',
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
