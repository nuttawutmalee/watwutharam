<?php

namespace App\Api\Middleware;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\ValidationRuleConstants;
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
        $token = config('cms.' . get_cms_application() . '.form_token');

        if (empty($token)) throw new \Exception(ErrorMessageConstants::FORM_TOKEN_IS_REQUIRED);

        if ( ! $request->hasHeader(ValidationRuleConstants::FORM_TOKEN_HEADER) && ! $request->exists(ValidationRuleConstants::FORM_TOKEN_BODY)) {
            throw new \Exception(ErrorMessageConstants::FORM_TOKEN_IS_MISSING);
        }

        if ($request->hasHeader(ValidationRuleConstants::FORM_TOKEN_HEADER)) {
            $formToken = $request->header(ValidationRuleConstants::FORM_TOKEN_HEADER);
        } else {
            $formToken = $request->input(ValidationRuleConstants::FORM_TOKEN_BODY);
            $request->offsetUnset(ValidationRuleConstants::FORM_TOKEN_BODY);
        }

        if ($token !== $formToken) throw new \Exception(ErrorMessageConstants::FORM_TOKEN_MISMATCHED);

        return $next($request);
    }
}