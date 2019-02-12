<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Discounts\DiscountFactory;
use GetCandy\Api\Core\Discounts\DiscountInterface;

class DiscountServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DiscountInterface::class, function ($app) {
            return $app->make(DiscountFactory::class);
        });
    }
}
