<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Currencies\CurrencyConverter;
use GetCandy\Api\Core\Currencies\Services\CurrencyService;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyServiceInterface;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;

class CurrencyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrencyConverterInterface::class, function ($app) {
            return $app->make(CurrencyConverter::class);
        });

        $this->app->bind(CurrencyServiceInterface::class, function ($app) {
            return $app->make(CurrencyService::class);
        });
    }
}
