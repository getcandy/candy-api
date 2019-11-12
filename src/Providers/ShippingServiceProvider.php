<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Shipping\ShippingCalculator;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ShippingCalculator::class, function ($app) {
            return $app->make(ShippingCalculator::class);
        });
    }
}
