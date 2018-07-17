<?php

namespace App\Api\Commands;

use Illuminate\Console\Command;

class CmsFlush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Flush Api/Cache';

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
        $verbose = $this->option('verbose');
        $bar = $this->output->createProgressBar(6);

        //Api cache
        if ($verbose) {
            $this->call('api:cache');
        } else {
            $this->callSilent('api:cache');
        }
        $bar->advance();
        $this->info('  Api cached!');

        //Cache
        if ($verbose) {
            $this->call('cache:clear');
        } else {
            $this->callSilent('cache:clear');
        }
        $bar->advance();
        $this->info('  Cache cleared!');

        //Config
        if ($verbose) {
            $this->call('config:clear');
        } else {
            $this->callSilent('config:clear');
        }
        $bar->advance();
        $this->info('  Config cleared!');

        //Route
        if ($verbose) {
            $this->call('route:clear');
        } else {
            $this->callSilent('route:clear');
        }
        $bar->advance();
        $this->info('  Route cleared!');

        //View
        if ($verbose) {
            $this->call('view:clear');
        } else {
            $this->callSilent('view:clear');
        }
        $bar->advance();
        $this->info('  view cleared!');

        //Optimize
        if ($verbose) {
            $this->call('optimize');
        } else {
            $this->callSilent('optimize');
        }
        $bar->advance();
        $this->info('  Framework optimized!');
        $bar->finish();

        return;
    }
}
