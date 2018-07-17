<?php

namespace App\Api\Commands;

use App\Api\Constants\ErrorMessageConstants;
use BackupManager\Manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CmsRecover extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:recover {database : Database connection name} {filename : Backup filename} {--scheduling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Database recover';

    /**
     * The laravel backup manager
     *
     * @var Manager
     */
    private $manager;

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
     *
     * @return mixed
     */
    public function handle()
    {
        $connection = $this->argument('database');

        if ($this->option('scheduling')) {
            $filesystem = 'cms-scheduling-backup';
            $backupFilesystem = 'local-scheduling';
        } else {
            $filesystem = 'cms-backup';
            $backupFilesystem = 'local';
        }

        $filename = $connection . '/' . $this->argument('filename');

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! Storage::disk($filesystem)->exists($filename)) {
            $this->info(ErrorMessageConstants::FILE_NOT_FOUND);
            die();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($filename, $filesystem, $backupFilesystem, $connection) {
            $this->manager->makeRestore()->run($backupFilesystem, $filename, $connection, 'gzip');
        });

        /** @noinspection PhpUndefinedMethodInspection */
        Storage::disk($filesystem)->delete($filename);

        $this->info(ucfirst($connection) . ': CMS DATABASE RECOVERED SUCCESSFULLY!');

        $this->callSilent('cms:flush');

        return;
    }
}
