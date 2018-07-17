<?php

namespace App\Api\Middleware;

use Closure;

class AutoImageOptimizer
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('cms.' . get_cms_application() . '.auto_image_optimizer_enabled')) {
            $jpeg = config('imageoptimizer.options.jpegoptim_bin');
            $png = config('imageoptimizer.options.pngquant_bin');
            $gif = config('imageoptimizer.options.gifsicle_bin');

            if ($jpeg && $png && $gif) {
                try {
                    if ($request->method() != 'GET') {

                        /** @var \Approached\LaravelImageOptimizer\ImageOptimizer $imageOptimizer */
                        $imageOptimizer = app('Approached\LaravelImageOptimizer\ImageOptimizer');

                        foreach ($request->allFiles() as $file) {
                            if (is_array($file)) {
                                foreach ($file as $item)  {
                                    if ($this->isImageFile($item)) {
                                        $imageOptimizer->optimizeUploadedImageFile($item);
                                    }
                                }
                            } else {
                                if ($this->isImageFile($file)) {
                                    $imageOptimizer->optimizeUploadedImageFile($file);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }
        }

        return $next($request);
    }

    /**
     * Return true if a file is an image
     *
     * @param $file
     * @return bool
     */
    protected function isImageFile($file)
    {
        /** @var \Illuminate\Http\File $file */
        return substr($file->getMimeType(), 0, 5) == 'image';
    }
}