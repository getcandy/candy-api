<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Shipping\ShippingCalculator;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('shipping_calculator', function ($app) {
            return new ShippingCalculator($app);
        });
    }
}
