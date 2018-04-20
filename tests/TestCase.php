<?php

namespace Tests;

use Artisan;
use Tests\Stubs\User;
use GetCandy\Api\Providers\ApiServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected $requiresRefresh = false;

    protected function setUp()
    {
        $databaseExists = file_exists(__DIR__ . '/database.sqlite');

        parent::setUp();

        $this->artisan('cache:forget', ['key' => 'spatie.permission.cache']);

        if (!$databaseExists || $this->requiresRefresh) {
            if ($databaseExists) {
                unlink(__DIR__ . '/database.sqlite');
            }
            touch(__DIR__ . '/database.sqlite');

            $this->loadLaravelMigrations(['--database' => 'testing']);
            $this->artisan('migrate', ['--database' => 'testing']);
            $this->artisan('db:seed', ['--class' => '\Seeds\TestingDatabaseSeeder']);
        }

    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        //Blergh but we need the config
        $app['config']['permission'] = require(
            realpath(__DIR__ . '/../vendor/spatie/laravel-permission/config/permission.php')
        );

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

    }

    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class
        ];
    }
}
