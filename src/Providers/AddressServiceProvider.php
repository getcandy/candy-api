<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Addresses\Services\AddressService;
use Illuminate\Support\ServiceProvider;

class AddressServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.addresses', function ($app) {
            return $app->make(AddressService::class);
        });
    }
}
