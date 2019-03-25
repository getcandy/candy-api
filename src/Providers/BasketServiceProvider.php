<?php

namespace GetCandy\Api\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Baskets\BasketCriteria;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Factories\BasketLineFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketLineInterface;
use GetCandy\Api\Core\Baskets\Factories\BasketDiscountFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Baskets\Interfaces\BasketDiscountFactoryInterface;

class BasketServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::replacer('min_batch', function ($message, $attribute, $rule, $parameters) {
            return str_replace([':min_batch'], [$parameters[0] ?? 1], $message);
        });
        Validator::replacer('min_quantity', function ($message, $attribute, $rule, $parameters) {
            return str_replace([':min_qty'], [$parameters[0] ?? 1], $message);
        });
    }

    public function register()
    {
        $this->app->bind(BasketCriteriaInterface::class, function ($app) {
            return $app->make(BasketCriteria::class);
        });

        $this->app->singleton(BasketFactoryInterface::class, function ($app) {
            return $app->make(BasketFactory::class);
        });

        $this->app->bind(BasketLineInterface::class, function ($app) {
            return $app->make(BasketLineFactory::class);
        });

        $this->app->singleton(BasketDiscountFactoryInterface::class, function ($app) {
            return $app->make(BasketDiscountFactory::class);
        });
    }
}
