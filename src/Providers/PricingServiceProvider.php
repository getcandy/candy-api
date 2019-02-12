<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Pricing\PriceCalculator;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

class PricingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PriceCalculatorInterface::class, function ($app) {
            return $app->make(PriceCalculator::class);
        });
    }
}
