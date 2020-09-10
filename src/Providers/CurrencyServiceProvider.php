<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Currencies\Observers\CurrencyObserver;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrencyConverterInterface::class, function ($app) {
            return $app->make(CurrencyConverter::class);
        });
        Currency::observe(CurrencyObserver::class);
    }
}
