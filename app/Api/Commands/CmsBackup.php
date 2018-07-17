<?php

namespace App\Api\Commands;

use App\Api\Constants\LogConstants;
use BackupManager\Filesystems\Destination;
use BackupManager\Manager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CmsBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:backup {database : Database connection name} {--scheduling : Backup database as a scheduling backup file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Database backup';

    /**
     * The laravel backup manager
     *
     * @var Manager
     */
    private $manager;

    /**
     * The maximum backup files
     *
     * @var int
     */
    private $maxSchedulingBackupFile = 7;

    /**
     * Create a new command instance.
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     * @throws \Exception
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now()->format(LogConstants::DATABASE_BACKUP_DATE_FORMAT);

        $connection = $this->argument('database');

        if ($this->option('scheduling')) {
            $connections = config('database.connections');

            if (is_null($connections)) {
                throw new \Exception('No database connections');
            }

            /** @noinspection PhpUnusedParameterInspection */
            $connections = collect($connections)
                ->reject(function ($connection, $key) {
                    return preg_match('/testing/', $key);
                })
                ->keys()
                ->all();

            foreach ($connections as $db) {
                try {
                    if (config('cms.' . $db . '.scheduling_backup')) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $files = Storage::disk('cms-scheduling-backup')->allFiles($db);

                        if (count($files) >= $this->maxSchedulingBackupFile) {
                            if ($firstFile = collect($files)->first()) {
                                /** @noinspection PhpUndefinedMethodInspection */
                                Storage::disk('cms-scheduling-backup')->delete($firstFile);
                            }
                        }

                        $destinationPath = $db . '/scheduling_cms_backup_' . $date . '.sql';

                        /** @noinspection PhpUndefinedMethodInspection */
                        DB::transaction(function () use ($destinationPath, $db) {
                            $this->manager->makeBackup()->run($db, [new Destination('local-scheduling', $destinationPath)], 'gzip');
                        });

                        $this->info(ucfirst($db) . ': CMS BACKUP SUCCESSFULLY!');
                        $this->info('FILENAME: ' . preg_replace('/' . preg_quote($db, '/') . '\//', '',$destinationPath) . '.gz');
                    }
                } catch (\Exception $e) {}
            }
        } else {
            $environment = env('APP_ENV', 'local');
            $destinationPath = $connection . '/cms_backup_' . $environment . '_' . $date . '.sql';

            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($destinationPath, $connection) {
                $this->manager->makeBackup()->run($connection, [new Destination('local', $destinationPath)], 'gzip');
            });

            $this->info(ucfirst($connection) . ': CMS BACKUP SUCCESSFULLY!');
            $this->info('FILENAME: ' . preg_replace('/' . preg_quote($connection, '/') . '\//', '',$destinationPath) . '.gz');
        }

        return true;
    }
}
