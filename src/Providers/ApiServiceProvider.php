<?php

namespace GetCandy\Api\Providers;

use Validator;
use Carbon\Carbon;
use League\Fractal\Manager;
use GetCandy\Api\Core\Factory;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Payments\PaymentManager;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Discounts\DiscountFactory;
use GetCandy\Api\Core\Users\Services\UserService;
use GetCandy\Api\Core\Discounts\DiscountInterface;
use GetCandy\Api\Http\Middleware\SetTaxMiddleware;
use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Users\Contracts\UserContract;
use GetCandy\Api\Http\Middleware\SetCustomerGroups;
use GetCandy\Api\Http\Middleware\SetLocaleMiddleware;
use GetCandy\Api\Console\Commands\ElasticIndexCommand;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Console\Commands\ScoreProductsCommand;
use GetCandy\Api\Http\Middleware\SetCurrencyMiddleware;
use GetCandy\Api\Core\Products\Factories\ProductFactory;
use GetCandy\Api\Http\Middleware\CheckClientCredentials;
use GetCandy\Api\Console\Commands\InstallGetCandyCommand;
use GetCandy\Api\Core\Baskets\Interfaces\BasketInterface;
use GetCandy\Api\Core\Baskets\Factories\BasketLineFactory;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Search\Factories\SearchResultFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;
use GetCandy\Api\Core\Search\Interfaces\SearchResultInterface;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations();
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
     * Get some routes mapped.
     *
     * @return void
     */
    protected function mapRoutes()
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.client.php');
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
    }

    public function mapCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ElasticIndexCommand::class,
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
            $this->app->singleton($name.'.driver', function ($app) use ($driver) {
                return $app->make($driver);
            });
        }

        // New factory bindings
        $this->app->singleton(BasketInterface::class, function ($app) {
            return $app->make(BasketFactory::class);
        });

        $this->app->singleton(DiscountInterface::class, function ($app) {
            return $app->make(DiscountFactory::class);
        });

        $this->app->bind(ProductVariantInterface::class, function ($app) {
            return $app->make(ProductVariantFactory::class);
        });

        $this->app->bind(ProductInterface::class, function ($app) {
            return $app->make(ProductFactory::class);
        });

        $this->app->bind(BasketLineInterface::class, function ($app) {
            return $app->make(BasketLineFactory::class);
        });

        $this->app->bind(SearchResultInterface::class, function ($app) {
            return $app->make(SearchResultFactory::class);
        });

        $this->app->singleton(PaymentContract::class, function ($app) {
            return new PaymentManager($app);
        });
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
    }
}
