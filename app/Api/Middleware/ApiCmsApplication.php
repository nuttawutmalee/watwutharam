<?php

namespace App\Api\Middleware;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use App\Api\Constants\ValidationRuleConstants;
use Closure;
use Illuminate\Support\Facades\DB;

class ApiCmsApplication
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
        $path = $request->path();

        if (app()->environment('testing')) {
            $isFileRequest = !! preg_match('/^' . preg_quote(HelperConstants::UPLOADS_FOLDER_TESTING, '/') . '/', $path) && \GuzzleHttp\Psr7\mimetype_from_filename($path);
        } else {
            $isFileRequest = !! preg_match('/^' . preg_quote(HelperConstants::UPLOADS_FOLDER, '/') . '/', $path) && \GuzzleHttp\Psr7\mimetype_from_filename($path);
        }

        if ($isFileRequest) return $next($request);

        if ($request->hasHeader(ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER)) {
            $application = $request->header(ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER);
        } else {
            $application = $request->input(ValidationRuleConstants::CMS_APPLICATION_NAME_FIELD);
            $request->offsetUnset(ValidationRuleConstants::CMS_APPLICATION_NAME_FIELD);
        }

        if ( ! is_null($application)) {
            $cmsConfig = config('cms.' . $application);
            $databaseConfig = config('database.connections.' . $application);
            if ($cmsConfig && $databaseConfig) {
                set_cms_application($application);
                /** @noinspection PhpUndefinedMethodInspection */
                DB::setDefaultConnection($application);
                return $next($request);
            }
        }

        $apiPrefix = env('API_PREFIX', 'api');

        if ( !! preg_match('/^' . $apiPrefix . '/', $path)) {
            throw new \Exception(ErrorMessageConstants::INVALID_CMS_HEADER);
        } else {
            return $next($request);
        }
    }
}