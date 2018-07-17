<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadController extends BaseController
{
    /**
     * UploadController constructor.
     */
    function __construct()
    {
        //
    }

    /**
     * Return all uploaded files list
     *
     * @param Request $request
     * @return mixed
     */
    public function listUploads(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $cropsPath = config('cms.' . get_cms_application() . '.crops_path', '_crops');
        $uploadedFilesPath = config('cms.' . get_cms_application() . '.uploaded_files_path', '_uploaded_files');
        $uploadsPath = config('cms.' . get_cms_application() . '.uploads_path');

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson([
            'root' => trim_uploads_path($uploadsPath),
            'structure' => get_upload_path_structure(null, [$cropsPath, $uploadedFilesPath])
        ]);
    }

    /**
     * Store a quick upload
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function quickUpload(Request $request)
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
                    throw new \Exception(ErrorMessageConstants::FILE_SIZE_IS_LARGER_THAN_RECOMMENDED);
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

        $uploadsPath = trim_uploads_path(config('cms.' . get_cms_application() . '.uploads_path'));
        $trimmedPath = $uploadsPath . '/' . preg_replace('/^\/|\\\/', '', trim_uploads_path($path));
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
                $trimmedPath = $dirName . '/' . $base . '_' .  ++$number . '.' . $extension;
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

        if (app()->environment('testing')) {
            $savedPath = HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $savedPath;
        } else {
            $savedPath = HelperConstants::UPLOADS_FOLDER . '/' . $savedPath;
        }

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

        $uploadsPath = config('cms.uploads_path');
        $trimmedUploadsPath = trim_uploads_path($uploadsPath);

        if ($request->exists('files')) {
            $files = $request->file('files');

            /** @var UploadedFile $file */
            foreach ($files as $key => $file) {
                $meta = isset($filesMeta[$key]) ? $filesMeta[$key] : null;

                if (is_null($meta)) {
                    throw new \Exception('File meta is missing');
                }

                $fileSize = $file->getSize();
                $maxFileSize = isset($meta['max_file_size']) ? $meta['max_file_size'] : null;

                if (!is_null($maxFileSize)) {
                    $maxFileSize = intval($maxFileSize) * 1024;

                    if ($fileSize > $maxFileSize) {
                        throw new \Exception('File size is too large!');
                    }
                }

                $path = preg_replace('/^\//', '', isset($meta['path']) ? $meta['path'] : null);

                $fileName = isset($meta['image_filename']) ? $meta['image_filename'] : null;

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

                $trimmedPath = $trimmedUploadsPath . '/' . preg_replace('/^\/|\\\/', '', trim_uploads_path($path));
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
                $base64 = isset($meta['image_base64_content']) ? $meta['image_base64_content'] : null;

                if (is_null($base64)) {
                    throw new \Exception('Base64 is missing');
                }

                $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));

                $path = preg_replace('/^\//', '', isset($meta['path']) ? $meta['path'] : null);

                $fileName = isset($meta['image_filename']) ? $meta['image_filename'] : null;

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

                $trimmedPath = $trimmedUploadsPath . '/' . preg_replace('/^\/|\\\/', '', trim_uploads_path($path));
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


        foreach($toBeUploaded as $upload) {
            $trimmedPath = $upload['path'];
            $file = $upload['file'];

            $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
            $baseName = pathinfo($trimmedPath, PATHINFO_BASENAME);

            if ($upload['is_base64']) {
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

            if (app()->environment('testing')) {
                $savedPath = HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $savedPath;
            } else {
                $savedPath = HelperConstants::UPLOADS_FOLDER . '/' . $savedPath;
            }

            $result[] = $savedPath;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
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
            throw new \Exception(ErrorMessageConstants::FILE_PATH_INVALID);
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

        $reserved[] = config('cms.' . get_cms_application() . '.crops_path');

        $needle = join('|', $reserved);
        if (preg_match('/^(' . preg_quote($needle, '/') . ')/', $path)) {
            throw new \Exception(ErrorMessageConstants::FILE_PATH_IS_RESERVED);
        }
    }
}
