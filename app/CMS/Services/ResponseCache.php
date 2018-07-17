<?php

namespace App\CMS\Services;

use Illuminate\Http\Request;
use Spatie\ResponseCache\RequestHasher;
use Symfony\Component\HttpFoundation\Response;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;

class ResponseCache
{
    /** @var ResponseCacheRepository */
    protected $cache;

    /** @var RequestHasher */
    protected $hasher;

    /** @var CacheProfile */
    protected $cacheProfile;

    public function __construct(ResponseCacheRepository $cache, RequestHasher $hasher, CacheProfile $cacheProfile)
    {
        $this->cache = $cache;
        $this->hasher = $hasher;
        $this->cacheProfile = $cacheProfile;
    }

    public function enabled(Request $request)
    {
        return $this->cacheProfile->enabled($request);
    }

    public function shouldCache(Request $request, Response $response)
    {
        if ($request->attributes->has('responsecache.doNotCache')) {
            return false;
        }

        if (! $this->cacheProfile->shouldCacheRequest($request)) {
            return false;
        }

        return $this->cacheProfile->shouldCacheResponse($response);
    }

    public function cacheResponse(Request $request, Response $response)
    {
        if (config('responsecache.add_cache_time_header')) {
            $response = $this->addCachedHeader($response);
        }

        $this->cache->put(
            $this->getCacheKey($request),
            $response,
            $this->cacheProfile->cacheRequestUntil($request)
        );

        return $response;
    }

    public function cacheData(Request $request)
    {
        $key = $this->getCacheKey($request);

        if ($request->attributes->has('page')) {
            $this->cache->putData(
                $key . '-page',
                $request->attributes->get('page'),
                $this->cacheProfile->cacheRequestUntil($request)
            );
        }

        if ($request->attributes->has('global')) {
            $this->cache->putData(
                $key . '-global',
                $request->attributes->get('global'),
                $this->cacheProfile->cacheRequestUntil($request)
            );
        }
    }

    public function hasBeenCached(Request $request)
    {
        return config('responsecache.enabled')
            ? $this->cache->has($this->getCacheKey($request))
            : false;
    }

    public function getCachedResponseFor(Request $request)
    {
        return $this->cache->get($this->getCacheKey($request));
    }

    public function getCacheDataFor(Request $request)
    {
        $key = $this->getCacheKey($request);

        return json_decode(json_encode([
            'page' => $this->cache->getData($key . '-page'),
            'global' => $this->cache->getData($key . '-global'),
            'templates' => $this->cache->getData($key . '-templates')
        ]));
    }

    public function flush()
    {
        $this->cache->flush();
    }

    protected function addCachedHeader(Response $response)
    {
        $clonedResponse = clone $response;

        $clonedResponse->headers->set('laravel-responsecache', 'cached on '.date('Y-m-d H:i:s'));

        return $clonedResponse;
    }

    private function getCacheKey(Request $request)
    {
        if ($request->attributes->has('sid') && $request->attributes->has('pid') && $request->attributes->has('code')) {
            $key = 'cms-response-' . $request->attributes->get('sid') . '-' . $request->attributes->get('pid') . '-' . $request->attributes->get('code');
        } else {
            $key = $this->hasher->getHashFor($request);
        }

        return $key;
    }
}