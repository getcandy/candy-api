<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Addresses\Services\AddressService;

class AddressServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.addresses', function ($app) {
            return $app->make(AddressService::class);
        });
    }
}
