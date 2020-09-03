<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Policies\CustomerPolicy;
use GetCandy\Api\Core\Customers\Services\CustomerGroupService;
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
        $this->app->bind('getcandy.customer_groups', function ($app) {
            return $app->make(CustomerGroupService::class);
        });
    }
}
