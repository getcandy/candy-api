<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyServiceInterface;
use GetCandy\Api\Core\Currencies\Services\CurrencyService;
use Illuminate\Support\ServiceProvider;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrencyConverterInterface::class, function ($app) {
            return $app->make(CurrencyConverter::class);
        });

        /**
         * @deprecated
         */
        $this->app->bind(CurrencyServiceInterface::class, function ($app) {
            return $app->make(CurrencyService::class);
        });

        $this->app->bind('getcandy.currencies', function ($app) {
            return $app->make(CurrencyService::class);
        });
    }
}
