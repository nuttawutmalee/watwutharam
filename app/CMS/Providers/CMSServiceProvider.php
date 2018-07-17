<?php

namespace App\CMS\Providers;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\CMSHelper;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;

class CMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //Disable query logging
        if (env('APP_DEBUG', false) || strtolower(env('APP_DEBUG', false)) === 'true') {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::connection()->disableQueryLog();
        }

        //Blade extension
        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('text', function ($arguments) {
            return "{{ " . join(" or ", array_pad(explode(',', str_replace(['(', ')', ' ', "'"], '', $arguments)), 2, "''")) . " }}";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('unescaped', function ($arguments) {
            return "{!! " . join(" or ", array_pad(explode(',', str_replace(['(', ')', ' ', "'"], '', $arguments)), 2, "''")) . " !!}";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('dd', function ($expression) {
            return "<?php dd($expression); ?>";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('has', function ($expression) {
            return "<?php if (isset_not_empty($expression)) : ?>";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('elsehas', function () {
            return "<?php else: ?>";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('elsehasif', function ($expression) {
            return "<?php elseif ($expression): ?>";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('endhas', function () {
            return "<?php endif; ?>";
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('honeypot', function () {
            return CMSHelper::generateHoneypotField();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('cmstoken', function () {
            return CMSHelper::generateFormTokenField();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('cmsappname', function () {
            return CMSHelper::generateCMSApplicationNameField();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('headscripts', function () {
            return '{!! ' . CMSHelper::generateAdditionalScripts(CMSConstants::ADDITIONAL_SCRIPTS_HEAD) . '!!}';
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('bodytopscripts', function () {
            return '{!! ' . CMSHelper::generateAdditionalScripts(CMSConstants::ADDITIONAL_SCRIPTS_BODY_TOP) . '!!}';
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('bodybottomscripts', function () {
            return '{!! ' . CMSHelper::generateAdditionalScripts(CMSConstants::ADDITIONAL_SCRIPTS_BODY_BOTTOM) . '!!}';
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('recaptchascript',function (){
            return CMSHelper::generateReCaptchaScript();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Blade::directive('recaptchafield',function (){
            return CMSHelper::generateReCaptchaField();
        });

        $stack = new HandlerStack();
        $stack->setHandler(\GuzzleHttp\choose_handler());
        /** @noinspection PhpUndefinedMethodInspection */
        $stack->push(
            new CacheMiddleware(
                new GreedyCacheStrategy(
                    new LaravelCacheStorage(
                        Cache::store('file')
                    ), (int)config('cms-client.api.cache_ttl')
                )
            ),
            'cache'
        );

        $client = new Client(['base_uri' => config('cms-client.api.domain'), 'handler' => $stack]);
        $this->app->instance(CMSConstants::CMS_API, $client);
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
