<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use Closure;

class DetermineCurrency
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @throws \Exception
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $currency = config('cms-client.default_currency', null);

        try {
            $location = geoip()->getLocation();
            $currency = isset_not_empty($location->currency);
        } catch (\Exception $exception) {}

        session([CMSConstants::CURRENT_BASE_CURRENCY => $currency]);

        return $next($request);
    }
}