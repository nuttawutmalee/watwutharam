<?php

namespace App\CMS\Controllers;

use App\CMS\Constants\CMSConstants;
use App\CMS\Helpers\APIHelper;
use App\CMS\Helpers\CMSHelper;
use App\CMS\Tools\Query;
use GrahamCampbell\HTMLMin\Facades\HTMLMin;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response as BaseResponse;
use Bkwld\Croppa\Facade as Croppa;

class CMSController extends BaseController
{
    /**
     * The cache manager instance.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * CMSController constructor.
     * @param CacheManager $cache
     */
    function __construct(CacheManager $cache)
    {
        $this->cache = $cache;

        $this->middleware('client-form-token', [
            'only' => [
                'upload',
                'multipleUpload',
                'listUploads',
                'renameFile',
                'deleteFile',
                'clearCache',
                'getPaginatorTemplates'
            ]
        ]);

        if (env('APP_DEBUG')) {
            $this->middleware('doNotCacheResponse', ['only' => ['index', 'preview', 'getSiteMapXML', 'getMainSiteMapXML']]);
        } else {
            $this->middleware('cache', ['only' => 'index']);
            $this->middleware('doNotCacheResponse', ['only' => 'preview', 'getSiteMapXML', 'getMainSiteMapXML']);
        }
    }

    /**
     * Return a view to make a viewpoint later
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        if (empty(CMSHelper::getCurrentPage())) {
            return abort(BaseResponse::HTTP_NOT_FOUND);
        }

        return view(CMSHelper::getTemplatePath('page'), [
            CMSConstants::RENDER_DATA => CMSHelper::prepareTemplates()
        ]);
    }

    /**
     * Return a preview page
     *
     * @param Request $request
     * @return string
     */
    public function preview(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'site' => 'required|array',
            'page_data' => 'required|array',
            'global_item_data' => 'required|array',
            'language_code' => 'sometimes|string'
        ]);

        $site = json_decode(json_encode($request->input('site')), false);
        $siteCollection = collect($site);
        $siteMainLanguage = $siteCollection->get('main_language');
        $siteLanguages = $siteCollection->get('languages');

        $pageData = json_decode(json_encode($request->input('page_data'), false));
        $globalItemData = json_decode(json_encode($request->input('global_item_data'), false));

        $languageCode = collect($siteMainLanguage)->get(CMSConstants::CODE, 'en');
        if ($request->exists('language_code')) {
            $languageCode = $request->input('language_code');
        }

        /** @var null|\ArrayObject $currentLanguage */
        $currentLanguage = collect($siteLanguages)
            ->where(CMSConstants::CODE, $languageCode)
            ->first() ?: null;

        session()->put(CMSConstants::CMS_PAGE_PREVIEW_ACTIVE, true);
        session()->put(CMSConstants::SITE, json_decode(json_encode($siteCollection->except('languages', 'main_language')->all()), false));
        session()->put(CMSConstants::SITE_MAIN_LANGUAGE, $siteMainLanguage);
        session()->put(CMSConstants::SITE_LANGUAGES, $siteLanguages);
        session()->put(CMSConstants::CURRENT_LANGUAGE_CODE, $languageCode);
        session()->put(CMSConstants::CURRENT_LOCALE, $currentLanguage
            ? isset_not_empty($currentLanguage->locale)
            : null);
        session()->put(CMSConstants::CURRENT_HREFLANG, $currentLanguage
            ? isset_not_empty($currentLanguage->hreflang)
            : null);

        if ( ! CMSHelper::checkIfTemplateExists('page')) {
            abort(BaseResponse::HTTP_NOT_FOUND);
        }

        session()->put(CMSConstants::PAGE, $pageData);
        session()->put(CMSConstants::TEMPLATE, $pageData->template);
        session()->put(CMSConstants::GLOBAL_ITEMS, $globalItemData);

        $view = view(CMSHelper::getTemplatePath('page'))->render(function () {
            session()->flush();
            session()->put(CMSConstants::CMS_PAGE_PREVIEW_ACTIVE, false);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $compressed = HTMLMin::html($view);

        return $compressed;
    }

    /**
     * Return a site map as an xml file
     *
     * @param Request $request
     * @return mixed
     */
    public function getMainSiteMapXML(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        $file = public_path(CMSConstants::SITEMAPS_FOLDER . '/' . $site->domain_name . '/sitemap.xml');

        if (file_exists($file)) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->file($file);
        }

        $siteMapXML = APIHelper::getSiteMapXML();

        /** @noinspection PhpUndefinedMethodInspection */
        return response($siteMapXML)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Return a site map as an xml file
     *
     * @param Request $request
     * @param $languageCode
     * @return mixed
     */
    public function getSiteMapXML(/** @noinspection PhpUnusedParameterInspection */ Request $request, $languageCode)
    {
        $site = CMSHelper::getSite();

        if (is_null($site)) return null;

        $file = public_path(CMSConstants::SITEMAPS_FOLDER . '/' . $site->domain_name . '/' . $languageCode . '/sitemap.xml');

        if (file_exists($file)) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->file($file);
        }

        $siteMapXML = APIHelper::getSiteMapXML($languageCode);

        /** @noinspection PhpUndefinedMethodInspection */
        return response($siteMapXML)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Return all uploaded files list
     *
     * @param Request $request
     * @return mixed
     */
    public function listUploads(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $cropsPath = config('cms-client.crops_path');
        $uploadsPath = config('cms-client.uploads_path');

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson([
            'root' => $this->trim_uploads_path($uploadsPath),
            'structure' => $this->get_upload_path_structure(null, [$cropsPath])
        ]);
    }

    /**
     * Store a quick upload
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function upload(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'file' => 'sometimes|required_without:image_base64_content|file',
            'image_base64_content' => 'sometimes|required_without:file|string',
            'image_filename' => 'sometimes|string',
            'path' => 'required|string',
            'max_file_size' => 'sometimes|string|nullable'
        ]);

        $isBase64 = false;

        if ($request->exists('file')) {
            $file = $request->file('file');
            $fileSize = $file->getSize();
            $maxFileSize = $request->input('max_file_size');

            if ( ! is_null($maxFileSize)) {
                $maxFileSize = intval($maxFileSize) * 1024;

                if ($fileSize > $maxFileSize) {
                    throw new \Exception('File size is too large!');
                }
            }
        } else {
            $isBase64 = true;
            $base64 = $request->input('image_base64_content');
            $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        }

        $path = preg_replace('/^\//', '', $request->input('path'));

        $fileName = $request->input('image_filename');

        if ( ! is_null($fileName)) {
            $dirName = pathinfo($path, PATHINFO_DIRNAME);
            $basename = pathinfo($path, PATHINFO_BASENAME);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $fileName = preg_replace('/^\//', '', $fileName);

            if ($dirName === '.') {
                if ($path === '.') {
                    $path = $fileName;
                } else {
                    if (empty($extension)) {
                        $path = $basename . '/' . $fileName;
                    } else {
                        $path = $fileName;
                    }
                }
            } else {
                if (empty($extension)) {
                    $path = $path . '/' . $fileName;
                } else {
                    $path = $dirName . '/' . $fileName;
                }
            }
        }

        $uploadsPath = config('cms-client.uploads_path');
        $trimmedUploadsPath = $this->trim_uploads_path($uploadsPath);
        $trimmedPath = $trimmedUploadsPath . '/' . preg_replace('/^\/|\\\/', '', $this->trim_uploads_path($path));
        $trimmedPath = preg_replace('/^\/|\\\/', '', $trimmedPath);

        $this->guardAgainstInvalidFilePath($trimmedPath);

        $this->guardAgainstReservedFilePath($trimmedPath);

        /** @noinspection PhpUndefinedMethodInspection */
        if (Storage::exists($trimmedPath)) {
            $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
            $name = pathinfo($trimmedPath, PATHINFO_FILENAME);
            $extension = pathinfo($trimmedPath, PATHINFO_EXTENSION);

            if (preg_match('/(.*?)_(\d+)$/', $name, $match)) {
                $base = $match[1];
                $number = intVal($match[2]);
            } else {
                $base = $name;
                $number = 0;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            do {
                $trimmedPath = $dirName . '/' . $base . '_' . ++$number . '.' . $extension;
            } while (Storage::exists($trimmedPath));
        }

        $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
        $baseName = pathinfo($trimmedPath, PATHINFO_BASENAME);

        if ($isBase64) {
            if ($dirName === '.') {
                $savedPath = $baseName;
            } else {
                $savedPath = $dirName . '/' . $baseName;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            Storage::put($savedPath, $file);
        } else {
            if ($dirName === '.') {
                $dirName = '';
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $savedPath = Storage::putFileAs(
                $dirName, $file, $baseName
            );
        }

        $savedPath = $uploadsPath . '/' . $savedPath;

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($savedPath);
    }

    public function multipleUpload(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'files' => 'sometimes|array|required_without:files_meta.*.image_base64_content',
            'files.*' => 'required|file',
            'files_meta' => 'required|array',
            'files_meta.*.image_base64_content' => 'sometimes|required_without:files|string',
            'files_meta.*.image_filename' => 'sometimes|string',
            'files_meta.*.path' => 'required|string',
            'files_meta.*.max_file_size' => 'sometimes|string|nullable'
        ]);

        $filesMeta = $request->input('files_meta');
        $toBeUploaded = [];
        $result = [];

        $uploadsPath = config('cms-client.uploads_path');
        $trimmedUploadsPath = $this->trim_uploads_path($uploadsPath);

        if ($request->exists('files')) {
            $files = $request->file('files');

            /** @var UploadedFile $file */
            foreach ($files as $key => $file) {
                $meta = isset_not_empty($filesMeta[$key]);

                if (is_null($meta)) {
                    throw new \Exception('File meta is missing');
                }

                $fileSize = $file->getSize();
                $maxFileSize = isset_not_empty($meta['max_file_size']);

                if (!is_null($maxFileSize)) {
                    $maxFileSize = intval($maxFileSize) * 1024;

                    if ($fileSize > $maxFileSize) {
                        throw new \Exception('File size is too large!');
                    }
                }

                $path = preg_replace('/^\//', '', isset_not_empty($meta['path']));

                $fileName = isset_not_empty($meta['image_filename']);

                if (!is_null($fileName)) {
                    $dirName = pathinfo($path, PATHINFO_DIRNAME);
                    $basename = pathinfo($path, PATHINFO_BASENAME);
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $fileName = preg_replace('/^\//', '', $fileName);

                    if ($dirName === '.') {
                        if ($path === '.') {
                            $path = $fileName;
                        } else {
                            if (empty($extension)) {
                                $path = $basename . '/' . $fileName;
                            } else {
                                $path = $fileName;
                            }
                        }
                    } else {
                        if (empty($extension)) {
                            $path = $path . '/' . $fileName;
                        } else {
                            $path = $dirName . '/' . $fileName;
                        }
                    }
                }

                $trimmedPath = $trimmedUploadsPath . '/' . preg_replace('/^\/|\\\/', '', $this->trim_uploads_path($path));
                $trimmedPath = preg_replace('/^\/|\\\/', '', $trimmedPath);

                $this->guardAgainstInvalidFilePath($trimmedPath);

                $this->guardAgainstReservedFilePath($trimmedPath);

                /** @noinspection PhpUndefinedMethodInspection */
                if (Storage::exists($trimmedPath)) {
                    $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
                    $name = pathinfo($trimmedPath, PATHINFO_FILENAME);
                    $extension = pathinfo($trimmedPath, PATHINFO_EXTENSION);

                    if (preg_match('/(.*?)_(\d+)$/', $name, $match)) {
                        $base = $match[1];
                        $number = intVal($match[2]);
                    } else {
                        $base = $name;
                        $number = 0;
                    }

                    /** @noinspection PhpUndefinedMethodInspection */
                    do {
                        $trimmedPath = $dirName . '/' . $base . '_' . ++$number . '.' . $extension;
                    } while (Storage::exists($trimmedPath));
                }

                $toBeUploaded[] = [
                    'path' => $trimmedPath,
                    'file' => $file,
                    'is_base64' => false
                ];
            }
        } else {
            foreach ($filesMeta as $meta) {
                $base64 = isset_not_empty($meta['image_base64_content']);

                if (is_null($base64)) {
                    throw new \Exception('Base64 is missing');
                }

                $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));

                $path = preg_replace('/^\//', '', isset_not_empty($meta['path']));

                $fileName = isset_not_empty($meta['image_filename']);

                if (!is_null($fileName)) {
                    $dirName = pathinfo($path, PATHINFO_DIRNAME);
                    $basename = pathinfo($path, PATHINFO_BASENAME);
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $fileName = preg_replace('/^\//', '', $fileName);

                    if ($dirName === '.') {
                        if ($path === '.') {
                            $path = $fileName;
                        } else {
                            if (empty($extension)) {
                                $path = $basename . '/' . $fileName;
                            } else {
                                $path = $fileName;
                            }
                        }
                    } else {
                        if (empty($extension)) {
                            $path = $path . '/' . $fileName;
                        } else {
                            $path = $dirName . '/' . $fileName;
                        }
                    }
                }

                $trimmedPath = $trimmedUploadsPath . '/' . preg_replace('/^\/|\\\/', '', $this->trim_uploads_path($path));
                $trimmedPath = preg_replace('/^\/|\\\/', '', $trimmedPath);

                $this->guardAgainstInvalidFilePath($trimmedPath);

                $this->guardAgainstReservedFilePath($trimmedPath);

                /** @noinspection PhpUndefinedMethodInspection */
                if (Storage::exists($trimmedPath)) {
                    $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
                    $name = pathinfo($trimmedPath, PATHINFO_FILENAME);
                    $extension = pathinfo($trimmedPath, PATHINFO_EXTENSION);

                    if (preg_match('/(.*?)_(\d+)$/', $name, $match)) {
                        $base = $match[1];
                        $number = intVal($match[2]);
                    } else {
                        $base = $name;
                        $number = 0;
                    }

                    /** @noinspection PhpUndefinedMethodInspection */
                    do {
                        $trimmedPath = $dirName . '/' . $base . '_' . ++$number . '.' . $extension;
                    } while (Storage::exists($trimmedPath));
                }

                $toBeUploaded[] = [
                    'path' => $trimmedPath,
                    'file' => $file,
                    'is_base64' => true
                ];
            }
        }


        foreach ($toBeUploaded as $upload) {
            $trimmedPath = $upload['path'];
            $file = $upload['file'];

            $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
            $baseName = pathinfo($trimmedPath, PATHINFO_BASENAME);

            if (isset_not_empty($upload['is_base64'])) {
                if ($dirName === '.') {
                    $savedPath = $baseName;
                } else {
                    $savedPath = $dirName . '/' . $baseName;
                }

                /** @noinspection PhpUndefinedMethodInspection */
                Storage::put($savedPath, $file);
            } else {
                if ($dirName === '.') {
                    $dirName = '';
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $savedPath = Storage::putFileAs(
                    $dirName, $file, $baseName
                );
            }

            $savedPath = $uploadsPath . '/' . $savedPath;

            $result[] = $savedPath;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
    }

    /**
     * Rename/Move a file
     *
     * @param Request $request
     * @return mixed
     * @throws FileNotFoundException
     */
    public function renameFile(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'current_file_path' => 'string|required',
            'new_file_path' => 'string|required'
        ]);

        $currentFilePath = $this->trim_uploads_path(preg_replace('/^\//', '', $request->input('current_file_path')));
        $newFilePath = $this->trim_uploads_path(preg_replace('/^\//', '', $request->input('new_file_path')));

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! Storage::exists($currentFilePath)) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(null, 'File not found', 500);
        }

        $this->guardAgainstInvalidFilePath($newFilePath);

        /** @noinspection PhpUndefinedMethodInspection */
        Croppa::reset('uploads/' . $currentFilePath);

        /** @noinspection PhpUndefinedMethodInspection */
        Storage::move($currentFilePath, $newFilePath);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Delete a file
     *
     * @param Request $request
     * @return mixed
     */
    public function deleteFile(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'target_file_path' => 'array|required|min:1'
        ]);

        $targetFilePath = $request->input('target_file_path');

        collect($targetFilePath)
            ->map(function ($path) {
                $path = $this->trim_uploads_path(preg_replace('/^\//', '', $path));

                /** @noinspection PhpUndefinedMethodInspection */
                if ( ! Storage::exists($path)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    return response()->apiJson(null, 'File not found', 500);
                }

                return $path;
            })
            ->each(function ($path) {
                /** @noinspection PhpUndefinedMethodInspection */
                Croppa::delete('uploads/' . $path);
            });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Remote clear cache
     *
     * @param Request $request
     * @return mixed
     */
    public function clearCache(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('responsecache:flush');
            Artisan::call('view:clear');
            session()->flush();

            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(true);
        } catch (\Exception $exception) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(null, 'Unable to clear cache', 500);
        }
    }

    /**
     * Return list of available custom paginator templates
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getPaginatorTemplates(/** @noinspection PhpUnusedParameterInspection */Request $request, $domainName)
    {
        $path = resource_path('views/' . CMSConstants::TEMPLATE_VIEW_PATH . '/' . $domainName . '/' . CMSConstants::PAGINATOR_DIRECTORY);

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! File::exists($path)) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson([]);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $templates = collect(File::allFiles($path))
            ->map(function ($template) {
                $fileName = pathinfo($template, PATHINFO_FILENAME);
                return preg_replace('/\.blade$/', '', $fileName);
            })
            ->filter()
            ->all();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($templates);
    }

    /**
     * Return rendered pagination
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function renderPaginator(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'query' => 'required'
        ]);

        $query = $request->input('query');
        $query = json_decode(json_encode($query), false);
        $data = $request->input('data', []);

        $queryPaginator = new Query($query);

        if ( ! $queryPaginator->isPagination()) {
            throw new \Exception('This query is not a paginator');
        }

        $renderedTemplate = $queryPaginator->render($data)->toHtml();

        /** @noinspection PhpUndefinedMethodInspection */
        return HTMLMin::html($renderedTemplate);
    }

    /**
     * Throw an exception if the file path is invalid
     *
     * @param $path
     * @throws \Exception
     */
    private function guardAgainstInvalidFilePath($path)
    {
        if ( ! str_is_valid_path($path) || !! preg_match('/\/?\.+\//', $path)) {
            throw new \Exception('File path is invalid');
        }
    }

    /**
     * Throw an exception if the file path is already reserved
     *
     * @param $path
     * @param array $reserved
     * @throws \Exception
     */
    private function guardAgainstReservedFilePath($path, $reserved = [])
    {
        if ( ! is_array($reserved)) {
            $reserved = (array) $reserved;
        }

        $reserved[] = config('cms-client.crops_path');

        $needle = join('|', $reserved);
        if (preg_match('/^(' . preg_quote($needle, '/') . ')/', $path)) {
            throw new \Exception('File path is reserved');
        }
    }

    /**
     * Return a trimmed file path
     *
     * @param $path
     * @param string $uploads
     * @return mixed
     */
    private function trim_uploads_path($path, $uploads = 'uploads')
    {
        return preg_replace('/^' . preg_quote($uploads, '/') . '(\/)?/i', '', $path);
    }

    /**
     * Return a file structure of cms uploads folder
     *
     * @param null $directory
     * @param array $exceptions
     * @return array
     */
    private function get_upload_path_structure($directory = null, $exceptions = [])
    {
        $uploadsPath = config('cms-client.uploads_path');
        $root = $this->trim_uploads_path($uploadsPath);
        $directory = $root . '/' . preg_replace('/^(\/|\\\)?' . preg_quote($root, '/') . '/', '', $directory);

        /** @noinspection PhpUndefinedMethodInspection */
        $directories = Storage::directories($directory);
        /** @noinspection PhpUndefinedMethodInspection */
        $files = Storage::files($directory);

        $directoryData = [];
        $fileData = [];

        if ( ! empty($directories)) {
            foreach ($directories as $key => $value) {
                $value = $this->trim_uploads_path($value);
                if ( ! in_array($value, $exceptions)) {
                    $items = $this->get_upload_path_structure($value);
                    array_push($directoryData, [
                        "name" => pathinfo($value, PATHINFO_BASENAME),
                        "type" => CMSConstants::DIRECTORY,
                        "items" => $items
                    ]);
                }
            }
        }

        if ( ! empty($files)) {
            foreach ($files as $key => $value) {
                $value = $this->trim_uploads_path($value);
                if ( ! in_array($value, $exceptions)) {
                    array_push($fileData, [
                        "name" => pathinfo($value, PATHINFO_BASENAME),
                        "type" => CMSConstants::FILE
                    ]);
                }
            }
        }

        return array_merge($directoryData, $fileData);
    }
}
