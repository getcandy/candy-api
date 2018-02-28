<?php

namespace GetCandy\Api\Providers;

use Carbon\Carbon;
use GetCandy\Api\Console\Commands\ElasticIndexCommand;
use GetCandy\Api\Console\Commands\InstallGetCandyCommand;
use GetCandy\Api\Currencies\CurrencyConverter;
use GetCandy\Api\Factory;
use GetCandy\Api\Http\Middleware\CheckClientCredentials;
use GetCandy\Api\Http\Middleware\SetCurrencyMiddleware;
use GetCandy\Api\Http\Middleware\SetCustomerGroups;
use GetCandy\Api\Http\Middleware\SetLocaleMiddleware;
use GetCandy\Api\Http\Middleware\SetTaxMiddleware;
use GetCandy\Api\Search\SearchContract;
use GetCandy\Api\Users\Contracts\UserContract;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use League\Fractal\Manager;
use Route;
use Validator;
use GetCandy\Api\Users\Services\UserService;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mapValidators();
        $this->publishConfig();
        $this->mapValidators();
        $this->mapBindings();
        $this->initPassport();
        $this->registerMiddleware();
        $this->mapRoutes();
        $this->mapCommands();
        $this->loadMigrations();
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../config/getcandy.php' => config_path('getcandy.php'),
            __DIR__ . '/../../config/hashids.php' => config_path('hashids.php'),
            __DIR__ . '/../../config/assets.php' => config_path('assets.php'),
        ]);
    }

    /**
     * Get some routes mapped
     *
     * @return void
     */
    protected function mapRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.client.php');
    }

    /**
     * Load migrations
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Extend our validators
     *
     * @return void
     */
    protected function mapValidators()
    {
        Validator::extend('unique_name_in_group', 'GetCandy\Api\Http\Validators\AttributeValidator@uniqueNameInGroup');
        Validator::extend('hashid_is_valid', 'GetCandy\Api\Http\Validators\HashidValidator@validForModel');
        Validator::extend('valid_structure', 'GetCandy\Api\Http\Validators\AttributeValidator@validateData');
        Validator::extend('unique_category_attribute', 'GetCandy\Api\Http\Validators\CategoriesValidator@uniqueCategoryAttributeData');
        Validator::extend('unique_route', 'GetCandy\Api\Http\Validators\RoutesValidator@uniqueRoute');
        Validator::extend('check_coupon', 'GetCandy\Api\Discounts\Validators\DiscountValidator@checkCoupon');
        Validator::extend('valid_locales', 'GetCandy\Api\Http\Validators\LocaleValidator@validate');
        Validator::extend('enabled', 'GetCandy\Api\Http\Validators\BaseValidator@enabled');
        Validator::extend('asset_url', 'GetCandy\Api\Http\Validators\AssetValidator@validAssetUrl');
        Validator::extend('valid_discount', 'GetCandy\Api\Discounts\Validators\DiscountValidator@validate');
        Validator::extend('unique_lines', 'GetCandy\Api\Baskets\Validators\BasketValidator@uniqueLines');
        Validator::extend('valid_payment_token', 'GetCandy\Api\Payments\Validators\PaymentTokenValidator@validate');
        Validator::extend('valid_order', 'GetCandy\Api\Orders\Validators\OrderIsActiveValidator@validate');
    }

    public function mapCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ElasticIndexCommand::class,
                InstallGetCandyCommand::class,
            ]);
        }
    }

    /**
     * Do our application bindings
     *
     * @return void
     */
    protected function mapBindings()
    {

        $this->app->register(
            \Alaouy\Youtube\YoutubeServiceProvider::class
        );

        $this->app->bind(\GetCandy\Api\Shipping\ShippingCalculator::class, function ($app) {
            return $app->make(\GetCandy\Api\Shipping\ShippingCalculator::class);
        });

        $this->app->singleton(UserContract::class, function ($app) {
            return $app->make(UserService::class);
        });

        $this->app->singleton('currency_converter', function ($app) {
            return new CurrencyConverter;
        });

        $this->app->singleton('api', function ($app) {
            return $app->make(Factory::class);
        });

        $this->app->singleton('fractal', function ($app) {
            return new Manager();
        });

        $this->app->singleton(SearchContract::class, function ($app) {
            return $app->make(config('getcandy.search.client'));
        });

        $mediaDrivers = config('assets.upload_drivers', []);

        foreach ($mediaDrivers as $name => $driver) {
            $this->app->singleton($name . '.driver', function ($app) use ($driver) {
                return $app->make($driver);
            });
        }
    }

    /**
     * Fires up Passport
     *
     * @return void
     */
    protected function initPassport()
    {
        Passport::tokensCan([
            'read' => 'Read API'
        ]);
        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addMinutes(60));
        Passport::refreshTokensExpireIn(Carbon::now()->addMinutes(60));
    }

    /**
     * Register our middleware
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('api.client', CheckClientCredentials::class);
        $this->app['router']->aliasMiddleware('api.currency', SetCurrencyMiddleware::class);
        $this->app['router']->aliasMiddleware('api.customer_groups', SetCustomerGroups::class);
        $this->app['router']->aliasMiddleware('api.locale', SetLocaleMiddleware::class);
        $this->app['router']->aliasMiddleware('api.tax', SetTaxMiddleware::class);
    }
}
