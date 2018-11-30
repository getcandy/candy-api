<?php

namespace Tests;

use TaxCalculator;
use Tests\Stubs\User;
use GetCandy\Api\Providers\ApiServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $requiresRefresh = false;

    protected function setUp()
    {
        // Make sure storage path is there

        if (! file_exists(__DIR__.'/../storage')) {
            mkdir(__DIR__.'/../storage');
        }
        $databaseExists = file_exists(__DIR__.'/../storage/database.sqlite');

        parent::setUp();

        $this->artisan('cache:forget', ['key' => 'spatie.permission.cache']);

        if (! $databaseExists || $this->requiresRefresh) {
            if ($databaseExists) {
                unlink(__DIR__.'/../storage/database.sqlite');
            }
            touch(__DIR__.'/../storage/database.sqlite');

            $this->loadLaravelMigrations(['--database' => 'testing']);
            $this->artisan('migrate', ['--database' => 'testing']);
            $this->artisan('db:seed', ['--class' => '\Seeds\TestingDatabaseSeeder']);
        }

        // By Default, set up everything as taxable
        TaxCalculator::setTax(
            app('api')->taxes()->getDefaultRecord()
        );
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        //Blergh but we need the config
        $app['config']['permission'] = require realpath(__DIR__.'/../vendor/spatie/laravel-permission/config/permission.php');
        $app['config']['hashids'] = require realpath(__DIR__.'/../config/hashids.php');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/../storage/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

        // GetCandy specific
        $app['config']->set('getcandy', require realpath(__DIR__.'/../config/getcandy.php'));

        $app['config']->set('services', [
            'braintree' => [
                'key' => env('BRAINTREE_PUBLIC_KEY'),
                'secret' => env('BRAINTREE_PRIVATE_KEY'),
                '3D_secure' => env('3D_SECURE', false),
                'merchant_id' => env('BRAINTREE_MERCHANT'),
                'merchants' => [
                    'default' => env('BRAINTREE_GBP_MERCHANT'),
                    'eur' => env('BRAINTREE_EUR_MERCHANT'),
                ],
            ],
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class,
            \Vinkla\Hashids\HashidsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'CurrencyConverter' => \GetCandy\Api\Core\Currencies\Facades\CurrencyConverter::class,
            'TaxCalculator' => \Facades\GetCandy\Api\Core\Taxes\TaxCalculator::class,
            'PriceCalculator' => \Facades\GetCandy\Api\Core\Pricing\PriceCalculator::class,
            'GetCandy' => \Facades\GetCandy\Api\Core\Helpers\GetCandy::class,
        ];
    }
}
