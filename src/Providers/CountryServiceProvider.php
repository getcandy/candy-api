<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Countries\Services\CountryService;

class CountryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.countries', function ($app) {
            return $app->make(CountryService::class);
        });
    }
}
