<?php

namespace App\Console;

use App\Api\Commands\CmsBackup;
use App\Api\Commands\CmsBackupList;
use App\Api\Commands\CmsFlush;
use App\Api\Commands\CmsInstall;
use App\Api\Commands\CmsRecover;
use App\Api\Commands\CmsRollbackDB;
use App\Api\Commands\CmsUpdateDB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CmsRollbackDB::class,
        CmsUpdateDB::class,
        CmsInstall::class,
        CmsFlush::class,
        CmsBackup::class,
        CmsBackupList::class,
        CmsRecover::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Scheduling backup for every application
        $schedule->command(CmsBackup::class, [
                'database' => 'scheduling',
                '--scheduling'
            ])
            ->weekly()
            ->fridays()
            ->name('Database scheduling backup')
            ->withoutOverlapping()
            ->evenInMaintenanceMode();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        /** @noinspection PhpIncludeInspection */
        require base_path('routes/console.php');
    }
}
