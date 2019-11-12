<?php

namespace GetCandy\Api\Providers;

use Carbon\Carbon;
use GetCandy\Api\Console\Commands\CandySearchIndexCommand;
use GetCandy\Api\Console\Commands\InstallGetCandyCommand;
use GetCandy\Api\Console\Commands\ScoreProductsCommand;
use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Factory;
use GetCandy\Api\Core\Users\Contracts\UserContract;
use GetCandy\Api\Core\Users\Services\UserService;
use GetCandy\Api\Http\Middleware\CheckClientCredentials;
use GetCandy\Api\Http\Middleware\DetectHubRequestMiddleware;
use GetCandy\Api\Http\Middleware\SetChannelMiddleware;
use GetCandy\Api\Http\Middleware\SetCurrencyMiddleware;
use GetCandy\Api\Http\Middleware\SetCustomerGroups;
use GetCandy\Api\Http\Middleware\SetLocaleMiddleware;
use GetCandy\Api\Http\Middleware\SetTaxMiddleware;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use League\Fractal\Manager;
use Validator;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadProviders();
        $this->loadTranslations();
        $this->mapValidators();
        $this->publishConfig();
        $this->mapValidators();
        $this->mapBindings();
        $this->initPassport();
        $this->registerMiddleware();
        $this->mapCommands();
        $this->loadMigrations();
    }

    /**
     * Load up our module providers.
     *
     * @return void
     */
    protected function loadProviders()
    {
        $providers = [
            ActivityLogServiceProvider::class,
            ChannelServiceProvider::class,
            BasketServiceProvider::class,
            CurrencyServiceProvider::class,
            DiscountServiceProvider::class,
            OrderServiceProvider::class,
            PaymentServiceProvider::class,
            PricingServiceProvider::class,
            ProductServiceProvider::class,
            SearchServiceProvider::class,
            ShippingServiceProvider::class,
            TaxServiceProvider::class,
            UtilServiceProvider::class,
            ReportsServiceProvider::class,
        ];
        foreach ($providers as $provider) {
            $this->app->register($provider, true);
        }
    }

    protected function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'getcandy');
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/getcandy.php' => config_path('getcandy.php'),
            __DIR__.'/../../config/hashids.php' => config_path('hashids.php'),
            __DIR__.'/../../config/assets.php' => config_path('assets.php'),
            __DIR__.'/../../config/permission.php' => config_path('permission.php'),
            __DIR__.'/../../config/search.php' => config_path('search.php'),
            __DIR__.'/../../config/tags.php' => config_path('tags.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__.'/../../config/services.php', 'services'
        );
    }

    /**
     * Load migrations.
     *
     * @return void
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Extend our validators.
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
        Validator::extend('check_coupon', 'GetCandy\Api\Core\Discounts\Validators\DiscountValidator@checkCoupon');
        Validator::extend('valid_locales', 'GetCandy\Api\Http\Validators\LocaleValidator@validate');
        Validator::extend('enabled', 'GetCandy\Api\Http\Validators\BaseValidator@enabled');
        Validator::extend('asset_url', 'GetCandy\Api\Http\Validators\AssetValidator@validAssetUrl');
        Validator::extend('valid_discount', 'GetCandy\Api\Core\Discounts\Validators\DiscountValidator@validate');
        Validator::extend('unique_lines', 'GetCandy\Api\Core\Baskets\Validators\BasketValidator@uniqueLines');
        Validator::extend('in_stock', 'GetCandy\Api\Core\Baskets\Validators\BasketValidator@inStock', trans('getcandy::validation.in_stock'));
        Validator::extend('valid_order', 'GetCandy\Api\Core\Orders\Validators\OrderIsActiveValidator@validate');
        Validator::extend('min_quantity', 'GetCandy\Api\Core\Baskets\Validators\BasketValidator@minQuantity');
        Validator::extend('min_batch', 'GetCandy\Api\Core\Baskets\Validators\BasketValidator@minBatch');
    }

    public function mapCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CandySearchIndexCommand::class,
                InstallGetCandyCommand::class,
                ScoreProductsCommand::class,
            ]);
        }
    }

    /**
     * Do our application bindings.
     *
     * @return void
     */
    protected function mapBindings()
    {
        $this->app->register(
            \Alaouy\Youtube\YoutubeServiceProvider::class
        );

        $this->app->singleton(UserContract::class, function ($app) {
            return $app->make(UserService::class);
        });

        $this->app->singleton('currency_converter', function ($app) {
            return $app->make(CurrencyConverter::class);
        });

        $this->app->singleton('api', function ($app) {
            return $app->make(Factory::class);
        });

        $this->app->singleton('fractal', function ($app) {
            return new Manager();
        });

        $mediaDrivers = config('assets.upload_drivers', []);

        $this->app->singleton(GetCandy::class, function ($app) {
            return new GetCandy;
        });

        foreach ($mediaDrivers as $name => $driver) {
            $this->app->singleton($name.'.driver', function ($app) use ($driver) {
                return $app->make($driver);
            });
        }
    }

    /**
     * Fires up Passport.
     *
     * @return void
     */
    protected function initPassport()
    {
        Passport::tokensCan([
            'read' => 'Read API',
        ]);
        Passport::routes();

        Passport::tokensExpireIn(
            Carbon::now()->addMinutes(config('getcandy.token_lifetime', 60))
        );
        Passport::refreshTokensExpireIn(
            Carbon::now()->addMinutes(config('getcandy.refresh_token_lifetime', 60))
        );
    }

    /**
     * Register our middleware.
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
        $this->app['router']->aliasMiddleware('api.channels', SetChannelMiddleware::class);
        $this->app['router']->aliasMiddleware('api.detect_hub', DetectHubRequestMiddleware::class);
    }
}
