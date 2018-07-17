<?php

namespace App\Api\Commands;

use App\Api\Constants\LogConstants;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CmsBackupList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:backup-list {database : Database connection name} {--scheduling : Display scheduling backup files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Database backup list';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
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
            $regex = '^' . $connection . '\/scheduling_cms_backup_(.*)(?:.sql.*)';
        } else {
            $filesystem = 'cms-backup';
            $regex = '^' . $connection . '\/cms_backup_(?:.*)_(.*)(?:.sql.*)';
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $files = Storage::disk($filesystem)->allFiles($connection);

        if ( ! empty($files)) {
            $files = collect($files)
                ->reject(function ($file) use ($connection) {
                    return !! preg_match('/' . $connection . '\/ransom/', $file);
                })
                ->filter(function ($file) {
                    return !! preg_match('/\.sql\.gz$/', $file);
                })
                ->map(function ($file) use ($regex, $connection) {
                    $createdDate = preg_replace('/' . $regex . '/', '$1', $file);
                    $createdDate = Carbon::createFromFormat(LogConstants::DATABASE_BACKUP_DATE_FORMAT, $createdDate)->format('Y-m-d h:i:s A');
                    return [
                        'filename' => preg_replace('/^' . $connection . '\//', '', $file),
                        'created_at' => $createdDate
                    ];
                });

            $headers = ['#', 'Filename', 'Created At'];
            $rows = collect($files)->map(function ($file, $key) {
                return [
                    ++$key,
                    $file['filename'],
                    $file['created_at']
                ];
            })->toArray();
            $this->table($headers, $rows);
        } else {
            $this->info('BACKUP FILES NOT FOUND!');
        }

        return true;
    }
}
