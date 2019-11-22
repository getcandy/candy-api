<?php

namespace Tests;

use Facades\GetCandy\Api\Core\Pricing\PriceCalculator;
use Facades\GetCandy\Api\Core\Taxes\TaxCalculator;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Currencies\Facades\CurrencyConverter;
use GetCandy\Api\Core\Facades\GetCandyFacade;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Providers\ApiServiceProvider;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Tests\Stubs\User;
use Vinkla\Hashids\HashidsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $adminRoutes = [
        'import',
        'activity-log',
        'associations/groups',
        'categories/parent/{parentID?}',
        'collections/{collection}/routes',
        'products/variants',
        'products/{product}/urls',
    ];

    protected $clientRoutes = [
        'orders/process',
        'basket-lines',
        'payments/3d-secure',
        'payments/provider',
        'payments/providers',
        'payments/types',
    ];

    protected function setUp() : void
    {
        parent::setUp();

        $this->artisan('cache:forget', ['key' => 'spatie.permission.cache']);
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->artisan('db:seed', ['--class' => '\Seeds\TestingDatabaseSeeder']);
        $this->withFactories(dirname(__DIR__).'/database/factories');

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

        $config = [
            'permission' => require __DIR__.'/../vendor/spatie/laravel-permission/config/permission.php',
            'hashids' => require __DIR__.'/../config/hashids.php',
            'auth.providers.users.model' => User::class,
            'services.sagepay.vendor' => 'SagePay',
            'getcandy' => require __DIR__.'/../config/getcandy.php',
        ];

        foreach ($config as $key => $value) {
            $app['config']->set($key, $value);
        }

    }

    protected function getPackageProviders($app)
    {
        return [
            PassportServiceProvider::class,
            ApiServiceProvider::class,
            PermissionServiceProvider::class,
            ActivitylogServiceProvider::class,
            HashidsServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'CurrencyConverter' => CurrencyConverter::class,
            'TaxCalculator' => TaxCalculator::class,
            'PriceCalculator' => PriceCalculator::class,
            'GetCandy' => GetCandyFacade::class,
        ];
    }

    protected function getinitalbasket($user = null)
    {
        $variant = ProductVariant::first();
        $basket = Basket::forceCreate([
            'currency' => 'GBP',
        ]);

        if ($user) {
            $basket->user_id = $user->id;
            $basket->save();
        }

        BasketLine::forceCreate([
            'product_variant_id' => $variant->id,
            'basket_id' => $basket->id,
            'quantity' => 1,
            'total' => $variant->price,
        ]);

        return $this->app->make(BasketFactory::class)->init($basket)->get();
    }

}
