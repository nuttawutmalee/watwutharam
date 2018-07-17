<?php

namespace Tests;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\User;
use Faker\Factory;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class CmsApiTestCase extends BaseTestCase
{
    use DatabaseMigrations, DatabaseTransactions;

    /**
     * @var string $testIncompleteMessage
     */
    private static $testIncompleteMessage = 'This test has not been implement yet.';

    /**
     * @var null|User
     */
    protected static $developer = null;

    /**
     * @var array
     */
    protected static $developerAuthorizationHeader = ['Accept' => 'application/json'];

    /**
     * @var string
     */
    protected static $apiPrefix = 'api';

    /**
     * @var null|\Faker\Generator
     */
    protected static $faker = null;

    /**
     * @return mixed
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        set_cms_application('testing');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::setDefaultConnection('testing');

        Artisan::call('migrate', [
            '--database' => 'testing'
        ]);
        Artisan::call('db:seed', [
            '--database' => 'testing',
            '--class' => 'InitialTestingDataSeeder'
        ]);

        return $app;
    }

    /**
     * Setup before class
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$faker = Factory::create();
    }

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        self::$developer = User::where('email', 'developers@cms.com')->first();
        self::$developerAuthorizationHeader = $this->getAuthorizationHeader($this->getToken());
    }

    /**
     * @param null $token
     * @return array
     */
    protected function getAuthorizationHeader($token = null)
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER => 'testing'
        ];
    }

    /**
     * @param array $header
     * @return array
     */
    protected function getURLEncodedHeader($header = [])
    {
        $header[ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER] = 'testing';
        $header['Content-Type'] = 'application/x-www-form-urlencoded';
        return $header;
    }

    /**
     * @param string $email
     * @return null
     */
    protected function getToken($email = 'developers@cms.com')
    {
        if ($user = User::where('email', '=', $email)->first()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return JWTAuth::fromUser($user);
        } else {
            return null;
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return mixed
     */
    protected function login($email = 'developers@cms.com', $password = 'developers')
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return JWTAuth::attempt(['email' => $email, 'password' => $password]);
    }

    /**
     * @param null $message
     */
    protected function incomplete($message = null)
    {
        $this->markTestIncomplete($message ?: self::$testIncompleteMessage);
    }

    /**
     * @param string $password
     * @return \Faker\Generator|User
     */
    protected function mockUser($password = 'developers')
    {
        return factory(User::class)->create(['password' => $password]);
    }

    /**
     * @return string
     */
    protected function randomVariableName()
    {
        return self::$faker->unique()->regexify('[a-z]{2,4}_[a-z]{2,10}');
    }

    /**
     * @param $path
     * @param int $number
     * @return string
     */
    protected function setNumberInFilename($path, $number = 1)
    {
        return pathinfo($path, PATHINFO_DIRNAME) . '/' . pathinfo($path, PATHINFO_FILENAME) . '_' . $number . '.' . pathinfo($path, PATHINFO_EXTENSION);
    }
}
