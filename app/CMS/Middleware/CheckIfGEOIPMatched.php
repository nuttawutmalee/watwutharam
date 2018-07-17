<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\CMSHelper;
use Closure;

class CheckIfGEOIPMatched
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($geoipEnabled = session(CMSConstants::GEOIP_ENABLED)) {
            try {
                $location = geoip()->getLocation();
                if ($code = $location->getAttribute('code')) {
                    if (CMSHelper::checkIfLanguageExists($code)) {
                        if ($siteLanguages = session(CMSConstants::SITE_LANGUAGES)) {
                            if ($language = collect($siteLanguages)
                                ->where('code', strtolower($code))
                                ->first()) {

                                session([CMSConstants::SITE_MAIN_LANGUAGE => $language]);
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {}
        }

        return $next($request);
    }
}
