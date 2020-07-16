<?php

namespace GetCandy\Api\Providers;

use Drafting;
use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Drafting\ProductDrafter;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Core\Products\Factories\ProductFactory;
use GetCandy\Api\Core\Products\Observers\ProductObserver;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Versioning\ProductVersioner;
use GetCandy\Api\Core\Products\Services\ProductVariantService;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;
use GetCandy\Api\Core\Products\Versioning\ProductVariantVersioner;

class ProductServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('products', function ($app) {
            return $app->make(ProductDrafter::class);
        });

        Versioning::extend('products', function ($app) {
            return $app->make(ProductVersioner::class);
        });
        Versioning::extend('product_variants', function ($app) {
            return $app->make(ProductVariantVersioner::class);
        });

        Product::observe(ProductObserver::class);
    }

    public function register()
    {
        $this->app->bind(ProductVariantInterface::class, function ($app) {
            return $app->make(ProductVariantFactory::class);
        });

        $this->app->bind(ProductInterface::class, function ($app) {
            return $app->make(ProductFactory::class);
        });

        $this->app->bind('getcandy.product_variants', function ($app) {
            return $app->make(ProductVariantService::class);
        });

        $this->app->bind('getcandy.products', function ($app) {
            return $app->make(ProductService::class);
        });
    }
}
