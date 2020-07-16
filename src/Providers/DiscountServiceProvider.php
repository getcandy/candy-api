<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Discounts\DiscountFactory;
use GetCandy\Api\Core\Discounts\DiscountInterface;
use GetCandy\Api\Core\Discounts\Services\DiscountService;

class DiscountServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DiscountInterface::class, function ($app) {
            return $app->make(DiscountFactory::class);
        });

        $this->app->bind('getcandy.discounts', function ($app) {
            return $app->make(DiscountService::class);
        });
    }
}
