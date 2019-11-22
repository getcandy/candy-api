<?php

namespace Tests;

use TaxCalculator;
use Tests\Stubs\User;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Providers\ApiServiceProvider;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('cache:forget', ['key' => 'spatie.permission.cache']);
        $this->artisan('vendor:publish', ['--provider' => 'Spatie\Activitylog\ActivitylogServiceProvider', '--tag' => 'migrations']);
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->artisan('db:seed', ['--class' => '\Seeds\TestingDatabaseSeeder']);
        // $this->artisan('passport:install');

        // // By Default, set up everything as taxable
        // TaxCalculator::setTax(
        //     app('api')->taxes()->getDefaultRecord()
        // );

        // Make sure our channel is set.
        $channel = app()->getInstance()->make(ChannelFactoryInterface::class);
        $channel->set(Channel::first());
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
            'database' => ':memory:',
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
            'sagepay' => [
                'vendor' => 'SagePay',
            ],
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ApiServiceProvider::class,
            \Spatie\Permission\PermissionServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
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

    protected function getinitalbasket($user = null)
    {
        $variant = \GetCandy\Api\Core\Products\Models\ProductVariant::first();
        $basket = \GetCandy\Api\Core\Baskets\Models\Basket::forceCreate([
            'currency' => 'GBP',
        ]);

        if ($user) {
            $basket->user_id = $user->id;
            $basket->save();
        }

        \GetCandy\Api\Core\Baskets\Models\BasketLine::forceCreate([
            'product_variant_id' => $variant->id,
            'basket_id' => $basket->id,
            'quantity' => 1,
            'total' => $variant->price,
        ]);

        return $this->app->make(BasketFactory::class)->init($basket)->get();
    }
}
