<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Taxes\TaxCalculator;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class TaxServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TaxCalculatorInterface::class, function ($app) {
            return $app->make(TaxCalculator::class);
        });
    }
}
