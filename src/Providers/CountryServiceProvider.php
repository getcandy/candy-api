<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Countries\Services\CountryService;
use Illuminate\Support\ServiceProvider;

class CountryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.countries', function ($app) {
            return $app->make(CountryService::class);
        });
    }
}
