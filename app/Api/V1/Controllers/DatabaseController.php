<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Middleware\ApiCmsApplication;
use BackupManager\Filesystems\Destination;
use BackupManager\Manager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseController extends BaseController
{
    /**
     * The laravel backup manager
     *
     * @var Manager
     */
    private $manager;

    /**
     * DatabaseController constructor.
     *
     * @param Manager $manager
     */
    function __construct(Manager $manager)
    {
        $this->manager = $manager;

        $this->middleware('developer.only', [
            'except' => [
                'listBackup',
                'unlock'
            ]
        ]);

        $this->middleware(ApiCmsApplication::class, [
            'only' => ['unlock']
        ]);
    }

    /**
     * Backup the database
     *
     * @param Request $request
     * @return mixed
     */
    public function backup(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $applicationName = get_cms_application();

        $date = Carbon::now()->format(LogConstants::DATABASE_BACKUP_DATE_FORMAT);
        $environment = env('APP_ENV', 'local');
        $destinationPath = $applicationName . '/cms_backup_' . $environment . '_' . $date . '.sql';

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($destinationPath, $applicationName) {
            $this->manager->makeBackup()->run($applicationName, [new Destination('local', $destinationPath)], 'gzip');
        });

        $destinationPath = preg_replace('/^' . preg_quote($applicationName, '/') . '\//', '', $destinationPath);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($destinationPath . '.gz');
    }

    /**
     * Return a backup file list
     *
     * @param Request $request
     * @return mixed
     */
    public function listBackup(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $applicationName = get_cms_application();

        /** @noinspection PhpUndefinedMethodInspection */
        $files = Storage::disk('cms-backup')->allFiles();

        if ( ! empty($files)) {
            $files = collect($files)
                ->reject(function ($file) use ($applicationName) {
                    return !! preg_match('/' . $applicationName . '\/ransom/', $file);
                })
                ->filter(function ($file) {
                    return !! preg_match('/\.sql\.gz$/', $file);
                })
                ->map(function ($file) use ($applicationName) {
                    $createdDate = preg_replace('/^' . $applicationName . '\/cms_backup_(?:.*)_(.*)(?:\.sql.*)/', '$1', $file);
                    $createdDate = Carbon::createFromFormat(LogConstants::DATABASE_BACKUP_DATE_FORMAT, $createdDate)->format('Y-m-d h:i:s A');
                    return [
                        'filename' => preg_replace('/^' . $applicationName . '\//', '', $file),
                        'created_at' => $createdDate
                    ];
                });
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($files);
    }

    /**
     * Recover a backup file
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function recover(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'filename' => 'required|string'
        ]);

        $applicationName = get_cms_application();

        $filename = $applicationName . '/' . $request->input('filename');

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! Storage::disk('cms-backup')->exists($filename)) {
            throw new \Exception(ErrorMessageConstants::FILE_NOT_FOUND);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($filename, $applicationName) {
            $this->manager->makeRestore()->run('local', $filename, $applicationName, 'gzip');
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Storage::disk('cms-backup')->delete($filename);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Lock the database (delete & create a ransom backup file)
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function lock(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $applicationName = get_cms_application();

        $ransomCode = config('cms.' . $applicationName . '.ransom_code');

        if (is_null($ransomCode)) {
            throw new \Exception(ErrorMessageConstants::CODE_NOT_FOUND);
        }

        $ransomFilename = $applicationName . '/ransom' . $ransomCode . '.sql';

        /** @noinspection PhpUndefinedMethodInspection */
        if (Storage::disk('cms-backup')->exists($ransomFilename)) {
            throw new \Exception(ErrorMessageConstants::FILE_ALREADY_EXISTS);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($ransomFilename, $applicationName) {
            $this->manager->makeBackup()->run($applicationName, [new Destination('local', $ransomFilename)], 'gzip');
        });

        Artisan::call('migrate:reset', [
            '--database' => $applicationName
        ]);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Unlock the ransom file
     *
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function unlock(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'code' => 'required|string'
        ]);

        $applicationName = get_cms_application();

        $code = $request->input('code');
        $ransomCode = config('cms.' . $applicationName . '.ransom_code');

        if (is_null($ransomCode)) {
            throw new \Exception(ErrorMessageConstants::CODE_NOT_FOUND);
        }

        if ($code !== $ransomCode) {
            throw new \Exception(ErrorMessageConstants::INVALID_UNLOCK_DATABASE_CODE);
        }

        $ransomFilename = $applicationName . '/ransom' . $ransomCode . '.sql.gz';

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! Storage::disk('cms-backup')->exists($ransomFilename)) {
            throw new \Exception(ErrorMessageConstants::FILE_NOT_FOUND);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($ransomFilename, $applicationName) {
            $this->manager->makeRestore()->run('local', $ransomFilename, $applicationName, 'gzip');
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Storage::disk('cms-backup')->delete($ransomFilename);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }
}

