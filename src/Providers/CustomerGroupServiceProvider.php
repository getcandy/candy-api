<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Customers\Services\CustomerGroupService;

class CustomerGroupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('getcandy.customer_groups', function ($app) {
            return $app->make(CustomerGroupService::class);
        });
    }
}
