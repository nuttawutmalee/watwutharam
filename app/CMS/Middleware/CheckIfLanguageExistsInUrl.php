<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\APIHelper;
use App\CMS\Helpers\CMSHelper;
use Closure;
use Illuminate\Http\Response as BaseResponse;

class CheckIfLanguageExistsInUrl
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
        $siteLanguages = CMSHelper::getSiteLanguages();
        $mainLanguage = CMSHelper::getSiteMainLanguage();

        if (empty($url)) {
            if ($mainLanguage) {
                if ($code = $mainLanguage->code) {
                    session([CMSConstants::CURRENT_LANGUAGE_CODE => $code]);

                    if ($siteLanguages) {
                        if ($targetLanguage = collect($siteLanguages)->where('code', $code)->first()) {
                            session([CMSConstants::CURRENT_LOCALE => $targetLanguage->locale ?: null]);
                            session([CMSConstants::CURRENT_HREFLANG => $targetLanguage->hreflang ?: null]);

                            return $next($request);
                        }
                    }
                }
            }

            return abort(BaseResponse::HTTP_NOT_FOUND);
        }

        $segments = explode('/', $url);
        $languageCode = null;

        if (sizeof($segments) == 1) {
            if (CMSHelper::checkIfLanguageExists(end($segments))) {
                $languageCode = end($segments);
            }
        } else {
            $temp = $segments;
            $alreadyHasLanguage = false;
            while (sizeof($temp) > 0) {
                $urlPart = array_shift($temp);
                if (CMSHelper::checkIfLanguageExists($urlPart)) {
                    if ($alreadyHasLanguage) {
                        return abort(BaseResponse::HTTP_NOT_FOUND);
                    }

                    $alreadyHasLanguage = true;
                    $languageCode = $urlPart;
                }
            }
        }

        if (isset_not_empty($languageCode)) {
            session([CMSConstants::CURRENT_LANGUAGE_CODE => $languageCode]);
        } else {
            if ($mainLanguage) {
                session([CMSConstants::CURRENT_LANGUAGE_CODE => collect($mainLanguage)->get(CMSConstants::CODE, 'en')]);
            }
        }

        if ( ! empty($languageCode)) {
            if ($siteLanguages) {
                if ($targetLanguage = collect($siteLanguages)->where('code', $languageCode)->first()) {
                    session([CMSConstants::CURRENT_LOCALE => $targetLanguage->locale ?: null]);
                    session([CMSConstants::CURRENT_HREFLANG => $targetLanguage->hreflang ?: null]);
                }
            }
        }

        if ($mainLanguage) {
            if ($code = $mainLanguage->code) {
                if ($code === $languageCode) {
                    $segments = explode('/', $url);
                    $path = join('/', array_filter($segments, function ($segment) use ($languageCode) {
                        return $segment !== $languageCode;
                    }));
                    return redirect($path);
                }

                return $next($request);
            }

        }

        return abort(BaseResponse::HTTP_NOT_FOUND);
    }
}
