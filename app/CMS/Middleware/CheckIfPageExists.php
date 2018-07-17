<?php

namespace App\CMS\Middleware;


use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\APIHelper;
use App\CMS\Helpers\CMSHelper;
use Closure;
use Illuminate\Http\Response as BaseResponse;

class CheckIfPageExists
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
        if ( ! CMSHelper::checkIfTemplateExists('page')) {
            return abort(BaseResponse::HTTP_NOT_FOUND);
        }

        session([CMSConstants::GLOBAL_ITEMS => APIHelper::getGlobalItemData()]);

        if ($pageData = APIHelper::getPageData()) {
            $request->attributes->add([
                'pid' => $pageData->id,
                'code' => CMSHelper::getCurrentLanguageCode(),
                'page' => $pageData,
                'global' => CMSHelper::getCurrentGlobalItems()
            ]);

            session([CMSConstants::PAGE => $pageData]);
            session([CMSConstants::TEMPLATE => isset_not_empty($pageData->template)]);
        }

        return $next($request);
    }

}