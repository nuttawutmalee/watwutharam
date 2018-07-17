<?php

namespace App\Api\Providers;

use App\Api\Models\CmsRole;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\ComponentOptionDate;
use App\Api\Models\ComponentOptionDecimal;
use App\Api\Models\ComponentOptionInteger;
use App\Api\Models\ComponentOptionString;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\GlobalItemOptionDate;
use App\Api\Models\GlobalItemOptionDecimal;
use App\Api\Models\GlobalItemOptionInteger;
use App\Api\Models\GlobalItemOptionString;
use App\Api\Models\Language;
use App\Api\Models\PageItemOptionDate;
use App\Api\Models\PageItemOptionDecimal;
use App\Api\Models\PageItemOptionInteger;
use App\Api\Models\PageItemOptionString;
use App\Api\Models\SiteTranslation;
use App\Api\Models\TemplateItemOptionDate;
use App\Api\Models\TemplateItemOptionDecimal;
use App\Api\Models\TemplateItemOptionInteger;
use App\Api\Models\TemplateItemOptionString;
use App\Api\Observers\CmsRoleObserver;
use App\Api\Observers\ComponentObserver;
use App\Api\Observers\ComponentOptionDateObserver;
use App\Api\Observers\ComponentOptionDecimalObserver;
use App\Api\Observers\ComponentOptionIntegerObserver;
use App\Api\Observers\ComponentOptionObserver;
use App\Api\Observers\ComponentOptionStringObserver;
use App\Api\Observers\GlobalItemObserver;
use App\Api\Observers\GlobalItemOptionDateObserver;
use App\Api\Observers\GlobalItemOptionDecimalObserver;
use App\Api\Observers\GlobalItemOptionIntegerObserver;
use App\Api\Observers\GlobalItemOptionObserver;
use App\Api\Observers\GlobalItemOptionStringObserver;
use App\Api\Observers\LanguageObserver;
use App\Api\Observers\PageItemObserver;
use App\Api\Observers\PageItemOptionDateObserver;
use App\Api\Observers\PageItemOptionDecimalObserver;
use App\Api\Observers\PageItemOptionIntegerObserver;
use App\Api\Observers\PageItemOptionObserver;
use App\Api\Observers\PageItemOptionStringObserver;
use App\Api\Observers\PageObserver;
use App\Api\Observers\RedirectUrlObserver;
use App\Api\Observers\SiteObserver;
use App\Api\Observers\SiteTranslationObserver;
use App\Api\Observers\TemplateItemObserver;
use App\Api\Observers\TemplateItemOptionDateObserver;
use App\Api\Observers\TemplateItemOptionDecimalObserver;
use App\Api\Observers\TemplateItemOptionIntegerObserver;
use App\Api\Observers\TemplateItemOptionObserver;
use App\Api\Observers\TemplateItemOptionStringObserver;
use App\Api\Observers\TemplateObserver;
use App\Api\Observers\UserObserver;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\RedirectUrl;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use App\Api\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class ModelObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Schema::defaultStringLength(191);

        User::observe(UserObserver::class);
        CmsRole::observe(CmsRoleObserver::class);

        /* Core */
        Site::observe(SiteObserver::class);
        RedirectUrl::observe(RedirectUrlObserver::class);
        
        Component::observe(ComponentObserver::class);
        ComponentOption::observe(ComponentOptionObserver::class);
        ComponentOptionDate::observe(ComponentOptionDateObserver::class);
        ComponentOptionDecimal::observe(ComponentOptionDecimalObserver::class);
        ComponentOptionInteger::observe(ComponentOptionIntegerObserver::class);
        ComponentOptionString::observe(ComponentOptionStringObserver::class);
        
        Template::observe(TemplateObserver::class);
        TemplateItem::observe(TemplateItemObserver::class);
        TemplateItemOption::observe(TemplateItemOptionObserver::class);
        TemplateItemOptionDate::observe(TemplateItemOptionDateObserver::class);
        TemplateItemOptionDecimal::observe(TemplateItemOptionDecimalObserver::class);
        TemplateItemOptionInteger::observe(TemplateItemOptionIntegerObserver::class);
        TemplateItemOptionString::observe(TemplateItemOptionStringObserver::class);
        
        Page::observe(PageObserver::class);
        PageItem::observe(PageItemObserver::class);
        PageItemOption::observe(PageItemOptionObserver::class);
        PageItemOptionDate::observe(PageItemOptionDateObserver::class);
        PageItemOptionDecimal::observe(PageItemOptionDecimalObserver::class);
        PageItemOptionInteger::observe(PageItemOptionIntegerObserver::class);
        PageItemOptionString::observe(PageItemOptionStringObserver::class);
        
        GlobalItem::observe(GlobalItemObserver::class);
        GlobalItemOption::observe(GlobalItemOptionObserver::class);
        GlobalItemOptionDate::observe(GlobalItemOptionDateObserver::class);
        GlobalItemOptionDecimal::observe(GlobalItemOptionDecimalObserver::class);
        GlobalItemOptionInteger::observe(GlobalItemOptionIntegerObserver::class);
        GlobalItemOptionString::observe(GlobalItemOptionStringObserver::class);
        
        Language::observe(LanguageObserver::class);
        SiteTranslation::observe(SiteTranslationObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
