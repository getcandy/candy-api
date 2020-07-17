<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Customers\Services\CustomerGroupService;
use GetCandy\Api\Core\Customers\Services\CustomerService;
use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('getcandy.customers', function ($app) {
            return $app->make(CustomerService::class);
        });
        $this->app->bind('getcandy.customer_groups', function ($app) {
            return $app->make(CustomerGroupService::class);
        });
    }
}
