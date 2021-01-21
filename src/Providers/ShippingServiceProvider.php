<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Shipping\Services\ShippingMethodService;
use GetCandy\Api\Core\Shipping\Services\ShippingPriceService;
use GetCandy\Api\Core\Shipping\Services\ShippingZoneService;
use GetCandy\Api\Core\Shipping\ShippingCalculator;
use Illuminate\Support\ServiceProvider;

class ShippingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ShippingCalculator::class, function ($app) {
            return $app->make(ShippingCalculator::class);
        });

        $this->app->bind('getcandy.shipping_prices', function ($app) {
            return $app->make(ShippingPriceService::class);
        });

        $this->app->bind('getcandy.shipping_methods', function ($app) {
            return $app->make(ShippingMethodService::class);
        });

        $this->app->bind('getcandy.shipping_zones', function ($app) {
            return $app->make(ShippingZoneService::class);
        });
    }
}
