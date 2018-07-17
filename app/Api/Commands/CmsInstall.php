<?php

namespace App\Api\Commands;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CmsInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:install {database : Database connection name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'CMS Installation';

    /**
     * .env file
     *
     * @var string
     */
    protected $envContent = <<<ENV_CONTENT
APP_ENV=local
APP_KEY=base64:43QTwb32a0HLHdA6INKu2qU58k1lxAjTiBtpVVOEpFc=
APP_DEBUG=false
APP_LOG=daily
APP_LOG_LEVEL=debug
APP_URL=http://localhost

BROADCAST_DRIVER=log
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=V

API_STANDARDS_TREE=vnd
API_SUBTYPE=cms_api
API_PREFIX=api
API_DOMAIN=cms-api.dev
API_VERSION=v1
API_NAME="CMS API"
API_CONDITIONAL_REQUEST=false
API_STRICT=false
API_DEFAULT_FORMAT=json
API_DEBUG=false
ENV_CONTENT;

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

            /** @noinspection PhpUndefinedMethodInspection */
            $envPath = App::environmentFilePath();
            if ( ! file_exists($envPath)) {
                file_put_contents($envPath, $this->envContent);
                $this->info('Environment file created!');
            }

            if ( ! config('cms.' . $connection)) {
                throw new \Exception('Please setup cms config file first.');
            }

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

            $this->question("\r\nThe next process will delete all data in the database and begin creating site(s) for you.");

            if ($this->confirm('Do you wish to continue?', true)) {
                $sites = [];
                $order = 1;

                try {
                    do {
                        $this->alert('SITE ' . $order);

                        $languageOrder = 1;
                        $site = [
                            'domain_name' => null,
                            'site_url' => null,
                            'main_language' => [
                                'code' => null,
                                'name' => null,
                                'locale' => null,
                                'hreflang' => null
                            ],
                            'additional_languages' => []
                        ];

                        $domainName = $this->ask('What is your site\'s domain name?');
                        $siteUrl = $this->ask('What is your site\'s url?');
                        $mainSiteLanguageCode = $this->ask("[{$languageOrder}] Main site language code", 'en');
                        $mainSiteLanguageName = $this->ask("[{$languageOrder}] Main site language name", 'English');
                        $mainSiteLanguageLocale = $this->ask("[{$languageOrder}] Main site language locale (optional)", 'en-GB');
                        $mainSiteLanguageHrefLang = $this->ask("[{$languageOrder}] Main site language hreflang (optional)", 'en');

                        $site['domain_name'] = $domainName;
                        $site['site_url'] = $siteUrl;
                        $site['main_language']['code'] = $mainSiteLanguageCode;
                        $site['main_language']['name'] = $mainSiteLanguageName;
                        $site['main_language']['locale'] = $mainSiteLanguageLocale;
                        $site['main_language']['hreflang'] = $mainSiteLanguageHrefLang;

                        while ($this->confirm('Do you wish to add more languages to this site (' . $domainName . '?)')) {
                            ++$languageOrder;

                            $code = $this->ask("[{$languageOrder}] Language code");
                            $name = $this->ask("[{$languageOrder}] Language name");
                            $locale = $this->ask("[{$languageOrder}] Language locale (optional)");
                            $hreflang = $this->ask("[{$languageOrder}] Language hreflang (optional)");

                            array_push($site['additional_languages'], [
                                'code' => $code,
                                'name' => $name,
                                'locale' => $locale,
                                'hreflang' => $hreflang
                            ]);
                        }

                        array_push($sites, $site);

                        $order++;
                    } while ($this->confirm('Do you wish to add more sites?'));

                    $this->info('Installing...');

                    //Creating sites and its languages
                    $bar = $this->output->createProgressBar(5 + count($sites));

                    if ($verbose) {
                        $this->call('migrate:reset', [
                            '--database' => $connection
                        ]);
                    } else {
                        $this->callSilent('migrate:reset', [
                            '--database' => $connection
                        ]);
                    }
                    $bar->advance();
                    $this->info('  Database truncated!');

                    //Migrate
                    if ($verbose) {
                        $this->call('migrate', [
                            '--database' => $connection
                        ]);
                    } else {
                        $this->callSilent('migrate', [
                            '--database' => $connection
                        ]);
                    }
                    $bar->advance();
                    $this->info('  Database migrated!');

                    //Seed
                    if ($verbose) {
                        $this->call('db:seed', [
                            '--class' => 'InitialDataSeeder',
                            '--database' => $connection
                        ]);
                    } else {
                        $this->callSilent('db:seed', [
                            '--class' => 'InitialDataSeeder',
                            '--database' => $connection
                        ]);
                    }
                    $bar->advance();
                    $this->info('  Database seeded!');

                    $createdSites = [];

                    foreach ($sites as $site) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        if (Schema::hasColumn('sites', 'site_url')) {
                        /** @var $created \App\Api\Models\Site */
                            $created = Site::firstOrCreate([
                                'domain_name' => $site['domain_name'],
                                'site_url' => $site['site_url'],
                            ]);
                        } else {
                            /** @var $created \App\Api\Models\Site */
                        $created = Site::firstOrCreate([
                            'domain_name' => $site['domain_name']
                        ]);
                        }

                        /** @var $mainLanguage \App\Api\Models\Language */
                        $mainLanguage = Language::firstOrCreate([
                            'code' => $site['main_language']['code'],
                            'name' => $site['main_language']['name'],
                            'locale' => $site['main_language']['locale'],
                            'hreflang' => $site['main_language']['hreflang']
                        ]);
                        $mainLanguageSyncData = [];
                        $mainLanguageSyncData[$mainLanguage->getKey()] = ['is_main' => true];

                        $additionalLanguages = array_unique($site['additional_languages']);
                        $additionalLanguageSyncData = [];

                        /** @var $createdLanguage \App\Api\Models\Language */
                        foreach ($additionalLanguages as $language) {
                            $createdLanguage = Language::firstOrCreate([
                                'code' => $language['code'],
                                'name' => $language['name'],
                                'locale' => $language['locale'],
                                'hreflang' => $language['hreflang']
                            ]);

                            $additionalLanguageSyncData[$createdLanguage->getKey()] = [];
                        }

                        if (empty($additionalLanguageSyncData)) {
                            $languageSyncData = $mainLanguageSyncData;
                        } else {
                            $languageSyncData = collect($mainLanguageSyncData)
                                ->merge(collect($additionalLanguageSyncData))
                                ->all();
                        }

                        $created->languages()->sync($languageSyncData);

                        /**
                         * Generate Template (homepage)
                         */
                        $created->templates()->create([
                            'name' => 'Homepage Template',
                            'variable_name' => 'homepage'
                        ]);

                        /**
                         * Generate a global item for GEO IP
                         */
                        /** @var GlobalItem $geoipEnabled */
                        $geoipEnabled = $created->globalItems()->create([
                            'name' => 'GEOIP Enabled?',
                            'variable_name' => 'geoip_enabled'
                        ]);
                        /** @var GlobalItemOption $geoipEnabledValue */
                        $geoipEnabledValue = $geoipEnabled->globalItemOptions()->create([
                            'name' => 'Value',
                            'variable_name' => 'value',
                            'is_required' => true,
                            'is_active' => true
                        ]);
                        $geoipEnabledValue->upsertOptionValue(OptionValueConstants::INTEGER, 0);
                        $geoipEnabledValue->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
                            'checkedValue' => '',
                            'checkedText' => 'Yes',
                            'uncheckedText' => 'No'
                        ]);

                        /**
                         * Generate additional scripts
                         */
                        /** @var GlobalItem $additionalScripts */
                        $additionalScripts = $created->globalItems()->create([
                            'name' => 'Additional Scripts',
                            'variable_name' => 'additional_scripts'
                        ]);

                        /** @var GlobalItemOption $additionalScriptsHead */
                        $additionalScriptsHead = $additionalScripts->globalItemOptions()->create([
                            'name' => 'Head Tag Section',
                            'variable_name' => 'head',
                            'is_required' => false,
                            'is_active' => true
                        ]);
                        $additionalScriptsHead->upsertOptionValue(OptionValueConstants::STRING, '');
                        $additionalScriptsHead->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
                            'scriptFormat' => true
                        ]);

                        /** @var GlobalItemOption $additionalScriptsBodyTop */
                        $additionalScriptsBodyTop = $additionalScripts->globalItemOptions()->create([
                            'name' => 'Body Top Section',
                            'variable_name' => 'body_top',
                            'is_required' => false,
                            'is_active' => true
                        ]);
                        $additionalScriptsBodyTop->upsertOptionValue(OptionValueConstants::STRING, '');
                        $additionalScriptsBodyTop->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
                            'scriptFormat' => true
                        ]);

                        /** @var GlobalItemOption $additionalScriptsBodyBottom */
                        $additionalScriptsBodyBottom = $additionalScripts->globalItemOptions()->create([
                            'name' => 'Body Bottom Section',
                            'variable_name' => 'body_bottom',
                            'is_required' => false,
                            'is_active' => true
                        ]);
                        $additionalScriptsBodyBottom->upsertOptionValue(OptionValueConstants::STRING, '');
                        $additionalScriptsBodyBottom->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
                            'scriptFormat' => true
                        ]);

                        // Seed data
                        $components = Component::whereIn('variable_name', [
                            'metadata',
                            'google_plus_metadata',
                            'open_graph_metadata',
                            'twitter_card_metadata',
                            'twitter_card_metdata', // Typo backward compatibility
                            'theme_color_metadata',
                            'ms_tile_metadata',
                            'favicon_group',
                            'apple_touch_icon_group',
                            'ms_application_icon_group'
                        ])->get();

                        if (count($components) > 0) {
                            collect($components)->each(function ($component) use ($created) {
                                /** @var Component $component */
                                if ($component->variable_name === 'twitter_card_metdata') {
                                    $created->globalItems()->create([
                                        'name' => $component->name,
                                        'variable_name' => 'twitter_card_metadata',
                                        'component_id' => $component->getKey()
                                    ]);
                                } else {
                                    $created->globalItems()->create([
                                        'name' => $component->name,
                                        'variable_name' => $component->variable_name,
                                        'component_id' => $component->getKey()
                                    ]);
                                }
                            });
                        }

                        $createdSites[] = $created->fresh();

                        $bar->advance();
                    }

                    if ($verbose) {
                        $this->call('optimize');
                    } else {
                        $this->callSilent('optimize');
                    }
                    $this->info("  Framework optimized!");
                    $bar->advance();

                    if ($verbose) {
                        $this->call('api:cache');
                    } else {
                        $this->callSilent('api:cache');
                    }
                    $this->info("  Api routes cached!");
                    $bar->advance();

                    $bar->finish();

                    $this->info("  CMS installed successfully!");

                    $headers = ['#', 'Domain Name', 'Status', 'Created At'];
                    $rows = collect($createdSites)->map(function ($site, $key) {
                        return [
                            ++$key,
                            $site->domain_name,
                            $site->is_active ? 'Active' : 'Inactive',
                            $site->created_at
                        ];
                    })->toArray();
                    $this->table($headers, $rows);
                } catch (\Exception $e) {
                    $this->error("\r\nError: CMS cannot be installed! (" . $e->getMessage() . ")");
                }
            }
        });

        return;
    }
}
