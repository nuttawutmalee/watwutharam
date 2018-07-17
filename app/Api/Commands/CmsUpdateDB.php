<?php

namespace App\Api\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CmsUpdateDB extends Command
{
/**
     * The name and signature of the console command.
     *
     * @var string
 */
    protected $signature = 'cms:update-db {database : Database connection name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Update Database, do the migration';

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
        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () {
            $verbose = $this->option('verbose');

            $connection = $this->argument('database');

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                DB::connection($connection)->getPdo();
                set_cms_application($connection);
                /** @noinspection PhpUndefinedMethodInspection */
                DB::setDefaultConnection($connection);
                $this->info('Database name: ' . $connection);
                $this->info('Database connected!');
            } catch (\Exception $e) {
                $this->error('Could not connect to database. Please check your configuration.');
                die();
            }

            $this->question("\r\nThe next process will migrate the database, some data might be lost depending on the migration itself.");

            if ($this->confirm('Do you wish to continue', false)) {
                try {
                    if ($verbose) {
                        $this->call('migrate', [
                            '--database' => $connection
                        ]);
                    } else {
                        $this->callSilent('migrate', [
                            '--database' => $connection
                        ]);
                    }
                    $this->info('  Database migrated!');
                }  catch (\Exception $e) {
                    $this->error("\r\nError: CMS cannot be updated (" . $e->getMessage() . ")");
                }
            }
        });

        return;
    }
}
