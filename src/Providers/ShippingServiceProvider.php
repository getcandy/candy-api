<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Shipping\ShippingCalculator;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ShippingCalculator::class, function ($app) {
            return $app->make(ShippingCalculator::class);
        });
    }
}
