<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Policies\AddressPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class AddressServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Address::class => AddressPolicy::class,
    ];

    public function register()
    {
        $this->registerPolicies();
    }
}
