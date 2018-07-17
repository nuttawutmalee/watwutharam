<?php

namespace App\Api\Providers;

use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthPolicyServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Api\Models\User' => 'App\Api\Policies\UserPolicy',
        'App\Api\Models\Site' => 'App\Api\Policies\SitePolicy',
        'App\Api\Models\Language' => 'App\Api\Policies\LanguagePolicy',
        'App\Api\Models\RedirectUrl' => 'App\Api\Policies\RedirectUrlPolicy',

        'App\Api\Models\Component' => 'App\Api\Policies\ComponentPolicy',
        'App\Api\Models\ComponentOption' => 'App\Api\Policies\ComponentOptionPolicy',

        'App\Api\Models\GlobalItem' => 'App\Api\Policies\GlobalItemPolicy',
        'App\Api\Models\GlobalItemOption' => 'App\Api\Policies\GlobalItemOptionPolicy',

        'App\Api\Models\Template' => 'App\Api\Policies\TemplatePolicy',
        'App\Api\Models\TemplateItem' => 'App\Api\Policies\TemplateItemPolicy',
        'App\Api\Models\TemplateItemOption' => 'App\Api\Policies\TemplateItemOptionPolicy',

        'App\Api\Models\Page' => 'App\Api\Policies\PagePolicy',
        'App\Api\Models\PageItem' => 'App\Api\Policies\PageItemPolicy',
        'App\Api\Models\PageItemOption' => 'App\Api\Policies\PageItemOptionPolicy',

        'App\Api\Models\CmsLog' => 'App\Api\Policies\CmsLogPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
