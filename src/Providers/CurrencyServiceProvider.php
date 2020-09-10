<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Currencies\Observers\CurrencyObserver;
use Illuminate\Support\ServiceProvider;

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
