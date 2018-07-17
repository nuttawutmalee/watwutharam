<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\APIHelper;
use Closure;
use Illuminate\Http\Response as BaseResponse;

class CheckIfSiteExists
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
        if (env('APP_DEBUG')) {
            $request->attributes->add(['responsecache.doNotCache' => true]);
        }

        if ($data = APIHelper::getSiteByDomainName()) {
            $data = collect($data);

            $request->attributes->add(['sid' => $data->get('id')]);

            session([
                CMSConstants::SITE => json_decode(json_encode($data->except('languages', 'main_language')->all()), false),
                CMSConstants::SITE_MAIN_LANGUAGE => $data->get('main_language'),
                CMSConstants::SITE_LANGUAGES => $data->get('languages')
            ]);

            if ($site = session(CMSConstants::SITE)) {
                $geoipEnabled = isset($site->geoip_enabled) ? $site->geoip_enabled : false;
                session([CMSConstants::GEOIP_ENABLED => $geoipEnabled]);
            }

            return $next($request);
        }

        return abort(BaseResponse::HTTP_NOT_FOUND);
    }

}
