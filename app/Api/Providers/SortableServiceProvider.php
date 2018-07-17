<?php

namespace App\Api\Providers;

use App\Api\Models\GlobalItem;
use App\Api\Models\PageItem;
use App\Api\Models\TemplateItem;
use Illuminate\Support\ServiceProvider;

class SortableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        TemplateItem::deleting(function (TemplateItem $model) {
            $model->next()->decrement('display_order');
        });

        PageItem::deleting(function (PageItem $model) {
            $model->next()->decrement('display_order');
        });

        GlobalItem::deleting(function (GlobalItem $model) {
            $model->next()->decrement('display_order');
        });
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
