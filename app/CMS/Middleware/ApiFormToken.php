<?php

namespace App\CMS\Middleware;

use App\CMS\Constants\CMSConstants;
use Closure;

class ApiFormToken
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
        $token = config('cms-client.api.form_token');

        if (empty($token)) throw new \Exception('Form token is missing form the config');

        if ( ! $request->hasHeader(CMSConstants::API_FORM_TOKEN_HEADER) && ! $request->exists(CMSConstants::API_FORM_TOKEN_FIELD)) {
            throw new \Exception('Form token is required');
        }

        if ($request->hasHeader(CMSConstants::API_FORM_TOKEN_HEADER)) {
            $formToken = $request->header(CMSConstants::API_FORM_TOKEN_HEADER);
        } else {
            $formToken = $request->input(CMSConstants::API_FORM_TOKEN_FIELD);
            $request->offsetUnset(CMSConstants::API_FORM_TOKEN_FIELD);
        }

        if ($token !== $formToken) throw new \Exception('Form token mismatched');

        return $next($request);
    }
}