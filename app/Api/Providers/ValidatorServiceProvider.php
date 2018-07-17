<?php

namespace App\Api\Providers;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\CMS\Helpers\CMSHelper;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var \Illuminate\Validation\Factory $validator */
        $validator = $this->app['validator'];

        /** @noinspection PhpUnusedParameterInspection */
        $validator->extend('is_boolean', function ($attribute, $value, $parameters, $validator) {
            return is_boolean($value, false);
        });

        /** @noinspection PhpUnusedParameterInspection */
        $validator->replacer('is_boolean', function ($message, $attribute, $role, $parameters) {
            return str_replace(':attribute', $attribute, 'The :attribute must be boolean.');
        });

        /** @noinspection PhpUnusedParameterInspection */
        $validator->extend('is_element_type', function ($attribute, $value, $parameters, $validator) {
            if ( ! is_string($value)) {
                return false;
            }
            $elementTypes = array_map(function ($type) {
                return strtolower($type);
            }, OptionElementTypeConstants::ELEMENT_TYPES ?: []);

            return in_array(strtolower($value), $elementTypes);
        });

        /** @noinspection PhpUnusedParameterInspection */
        $validator->replacer('is_element_type', function ($message, $attribute, $role, $parameters) {
            $elementTypes = join(', ', OptionElementTypeConstants::ELEMENT_TYPES);
            return str_replace(':attribute', $attribute, 'The :attribute must be one of these (' . $elementTypes . '.)');
        });

        /** @noinspection PhpUnusedParameterInspection */
        $validator->extend('is_option_type', function ($attribute, $value, $parameters, $validator) {
            if ( ! is_string($value)) {
                return false;
            }
            return in_array(strtoupper($value), OptionValueConstants::OPTION_TYPES);
        });

        /** @noinspection PhpUnusedParameterInspection */
        $validator->replacer('is_option_type', function ($message, $attribute, $role, $parameters) {
            $optionTypes = join(', ', OptionValueConstants::OPTION_TYPES);
            return str_replace(':attribute', $attribute, 'The :attribute must be one of these (' . $optionTypes . '.)');
        });

        /** @noinspection PhpUnusedParameterInspection */
        app('Dingo\Api\Exception\Handler')->register(function (NotFoundHttpException $exception) {
            $request = request();

            if ($request->is('api/*') || $request->is('client-api/*')) {
                if ($request->expectsJson()) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    return response()->apiJson(null, $exception->getMessage(), $exception->getCode());
                }
            }

            if ($request->is('uploads/*')) {
                if ($request->is('uploads/_crops/*')
                    && !! preg_match('/(.*\/)(?:_crops\/)(.*)(?:-(?:\d+|_)x(?:\d+|_).*)(\.\w+)$/', $request->fullUrl())) {
                    $reversed = preg_replace('/(.*\/)(?:_crops\/)(.*)(?:-(?:\d+|_)x(?:\d+|_).*)(\.\w+)$/', '$1$2$3', $request->fullUrl());
                    return redirect($reversed);
                } else {
                    /** @noinspection PhpUndefinedMethodInspection */
                    return response()->apiJson(null, $exception->getMessage(), $exception->getStatusCode() ?: 500);
                }
            }

            if ( ! env('APP_DEBUG', false)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $statusCode = $exception->getStatusCode() ?: 500;
                $defaultTemplate = 'errors.' . $statusCode;

                if ($site = CMSHelper::getSite()) {
                    $template = CMSHelper::getTemplatePath('errors.' . $statusCode);

                    if (view()->exists($template)) {
                        return response()->view($template);
                    }
                }

                if (view()->exists($defaultTemplate)) {
                    return response()->view($defaultTemplate);
                }
            }

            return response()->json([
                'result' => false,
                'message' => '404 Not Found',
                'status_code' => 404
            ], 404);
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
