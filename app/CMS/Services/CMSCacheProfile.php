<?php

namespace App\CMS\Services;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use DateTime;
use Carbon\Carbon;

class CMSCacheProfile implements CacheProfile {
    /**
     *
     * @param Request $request
     * @return bool
     */
    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->ajax()) {
            return false;
        }

        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }

    /**
     * @param Response $response
     * @return bool
     */
    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful() || $response->isRedirection();
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled');
    }

    /**
     * @param Request $request
     * @return DateTime
     */
    public function cacheRequestUntil(Request $request): DateTime
    {
        return Carbon::now()->addMinutes(
            config('responsecache.cache_lifetime_in_minutes')
        );
    }

    /**
     * @param Request $request
     * @return string
     */
    public function cacheNameSuffix(Request $request): string
    {
        if ($url = urlencode(url()->current())) {
            return $url;
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isRunningInConsole(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return app()->runningInConsole();
    }
}
