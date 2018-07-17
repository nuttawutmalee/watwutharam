<?php

namespace App\Api\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeveloperOnly
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
        /** @noinspection PhpUndefinedMethodInspection */
        if ($currentToken = JWTAuth::getToken()) {
            /** @noinspection PhpUndefinedMethodInspection */
            if ($authUser = JWTAuth::parseToken()->authenticate()) {
                /** @noinspection PhpUndefinedMethodInspection */
                if ($authUser->isDeveloper()) {
                    return $next($request);
                }

                throw new AuthorizationException();
            }
        }

        throw new TokenMismatchException();
    }
}