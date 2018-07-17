<?php

namespace App\CMS\Middleware;

use Closure;
use Illuminate\Http\Response as BaseResponse;

class CheckIfLandingPageExists
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
        if (config('cms-client.landing.mode') == false || strtolower(config('cms-client.landing.mode')) == 'false') {
            return $next($request);
        }

        if($page = $this->checkLandingPage()) {
            echo $page;
            die();
        } else {
            return abort(BaseResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return bool|string
     */
    private function checkLandingPage()
    {
        $landingPage = public_path() . '/' . config('cms-client.landing.landing_folder') . '/' . config('cms-client.landing.landing_page');
        if (file_exists($landingPage)) {
            $page = file_get_contents($landingPage);
            if ( ! empty($page)) {
                return $page;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
