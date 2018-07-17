<?php

namespace App\CMS\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as BaseResponse;

class MockUpController extends BaseController
{
    /**
     * Return a mock-up page
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $path = $request->getPathInfo();

        if ($path) {
            $path = remove_leading_slashes($path);

            if (empty($path)) {
                $path = 'homepage';
            }

            $method = 'get' . preg_replace('/-/', '', preg_replace('/-|_/', '-', ucwords($path, '-')));

            if ( ! method_exists($this, $method)) {
                return abort(BaseResponse::HTTP_NOT_FOUND);
            }

            $templateName = config('cms-client.mockup.template_name');

            if (is_null($templateName)) {
                return abort(BaseResponse::HTTP_NOT_FOUND);
            }

            return $this->{$method}($request);
        }

        return abort(BaseResponse::HTTP_NOT_FOUND);
    }

    /**
     * Return a view path for mock-up page
     *
     * @param string $view
     * @return string
     */
    private function getViewPath($view = '')
    {
        return config('cms-client.mockup.views_path', 'mockup') . '.' . config('cms-client.mockup.template_name')  . '.' . $view;
    }

    /**
     * Return a mock-up view
     *
     * @param string $view
     * @param array $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function mockup($view = '', $data = [])
    {
        if ( ! view()->exists($this->getViewPath($view))) {
            return abort(BaseResponse::HTTP_NOT_FOUND);
        }

        return view($this->getViewPath($view), $data);
    }

    /**
     * MOCK-UP SECTION
     */

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getHomepage(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "/"
        ];

        return $this->mockup('homepage', $data);
    }

    public function getAbout(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "about"
        ];

        return $this->mockup('about', $data);
    }

    public function getText(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "text"
        ];

        return $this->mockup('text', $data);
    }

    public function getContact(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "contact"
        ];

        return $this->mockup('contact', $data);
    }

    public function getGallery(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "gallery"
        ];

        return $this->mockup('gallery', $data);
    }

    public function getNews(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "news"
        ];
        return $this->mockup('articles', $data);
    }

    public function getArticles(/** @noinspection PhpUnusedParameterInspection */ Request  $request)
    {
        // Mock-up Data
        $data = [
            "page" => "articles"
        ];

        return $this->mockup('articles', $data);
    }
}