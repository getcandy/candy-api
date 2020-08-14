<?php

namespace GetCandy\Api\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Policies\AddressPolicy;
use GetCandy\Api\Core\Addresses\Services\AddressService;

class AddressServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Address::class => AddressPolicy::class
    ];

    public function register()
    {
        $this->registerPolicies();
        $this->app->singleton('getcandy.addresses', function ($app) {
            return $app->make(AddressService::class);
        });
    }
}
