<?php

namespace App\CMS\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class JsonResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Response::macro('apiJson', function ($data, $err = null, $status_code = null) {
            if (is_null($err)) {
                /** @noinspection PhpUndefinedMethodInspection */
                return Response::json([
                    'result' => true,
                    'data' => $data
                ], (is_null($status_code)) ? BaseResponse::HTTP_OK : $status_code);
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                return Response::json([
                    'result' => false,
                    'message' => ($err instanceof \Exception) ? $err->getMessage() : $err,
                    'status_code' => (is_null($status_code)) ? BaseResponse::HTTP_NOT_FOUND : $status_code
                ], (is_null($status_code)) ? BaseResponse::HTTP_NOT_FOUND : $status_code);
            }
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}