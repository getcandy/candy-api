<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Observers\CustomerGroupObserver;
use GetCandy\Api\Core\Customers\Policies\CustomerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

class CustomerServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Customer::class => CustomerPolicy::class,
    ];

    public function register()
    {
        $this->registerPolicies();
    }

    public function boot()
    {
        CustomerGroup::observe(CustomerGroupObserver::class);
    }
}
