<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\APIHelper;
use App\CMS\Helpers\CMSHelper;
use Closure;
use Illuminate\Http\Response as BaseResponse;

class CheckIfRedirectUrlExists
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
        $url = APIHelper::getCurrentFriendlyUrl();

        $destinationUrl = null;
        $statusCode = BaseResponse::HTTP_FOUND;
        if ($site = session(CMSConstants::SITE)) {
            $originalUrl = $url;

            if (empty($originalUrl)) {
                if ($currentLanguageCode = CMSHelper::getCurrentLanguageCode()) {
                    $originalUrl = $currentLanguageCode . remove_leading_slashes(config('cms-client.homepage_path'), '/');
                } else {
                    $originalUrl = config('cms-client.homepage_path');
                }
            }

            $originalUrl = remove_leading_slashes($originalUrl, '/');

            $redirectUrl = APIHelper::getRedirectUrlBySourceUrl($originalUrl);

            if ( ! is_null($redirectUrl)) {
                if (isset($redirectUrl->destination_url)) {
                    $destinationUrl = (is_null($redirectUrl->destination_url)) ? '/' : $redirectUrl->destination_url;
                }

                if (isset($redirectUrl->status_code)) {
                    if (intval($redirectUrl->status_code) !== 0) {
                        $statusCode = $redirectUrl->status_code;
                    }
                }

                if (is_null($destinationUrl)) {
                    return $next($request);
                } else {
                    $destinationUrl = CMSHelper::url($destinationUrl);
                    $scheme = parse_url($destinationUrl, PHP_URL_SCHEME);
                    $away = (empty($scheme)) ? 'http://' . $destinationUrl : $destinationUrl;
                    return redirect()->away($away, $statusCode);
                }
            }
        }

        if ($code = CMSHelper::getCurrentLanguageCode()) {
            $homepageUrl = preg_replace('/^' . preg_quote($code, '/') . '\//', '', $url);

            if ($homepageUrl === config('cms-client.homepage_path')) {
                $mainLanguage = CMSHelper::getSiteMainLanguage();

                if ($code && $mainLanguage) {
                    return ($mainLanguage->code === $code) ? redirect('/') : redirect('/' . $code . '/');
                } else {
                    return redirect('/');
                }
            }
        }

        return $next($request);
    }
}
