<?php

namespace App\CMS\Middleware;

use App\CMS\Helpers\CMSHelper;
use Closure;
use Illuminate\Http\Request;
use Spatie\PartialCache\PartialCache;
use Spatie\ResponseCache\Events\CacheMissed;
use App\CMS\Services\ResponseCache;
use Symfony\Component\HttpFoundation\Response;
use Spatie\ResponseCache\Events\ResponseCacheHit;

class CustomCacheResponse
{
    /** @var ResponseCache */
    protected $responseCache;

    /** @var  PartialCache */
    protected $partialCache;

    /**
     * CustomCacheResponse constructor.
     * @param ResponseCache $responseCache
     * @param PartialCache $partialCache
     */
    public function __construct(ResponseCache $responseCache, PartialCache $partialCache)
    {
        $this->responseCache = $responseCache;
        $this->partialCache = $partialCache;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($this->responseCache->enabled($request)) {
            if ($this->responseCache->hasBeenCached($request)) {
                event(new ResponseCacheHit($request));

                $caches = $this->responseCache->getCacheDataFor($request);
                $currentPage = CMSHelper::getCurrentPage();
                $currentGlobal = CMSHelper::getCurrentGlobalItems();
                $pageDiff = json_diff_inner($currentPage, $caches->page);
                $globalDiff = json_diff_inner($currentGlobal, $caches->global);

                if (empty($pageDiff) && empty($globalDiff)) {
                    return $this->responseCache->getCachedResponseFor($request);
                }
            }
        }

        $response = $next($request);

        if ($this->responseCache->enabled($request)) {
            $exceptionFound = $request->attributes->get('exception_found', false);
            if ($this->responseCache->shouldCache($request, $response) && ! $exceptionFound) {
                $this->responseCache->cacheResponse($request, $response);
                $this->responseCache->cacheData($request);
            }
        }

        event(new CacheMissed($request));

        return $response;
    }
}
