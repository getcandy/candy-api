<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\ActivityLog\Criteria\ActivityLogCriteria;
use GetCandy\Api\Core\ActivityLog\Factories\ActivityLogFactory;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogFactoryInterface;
use GetCandy\Api\Core\ActivityLog\Interfaces\ActivityLogCriteriaInterface;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ActivityLogFactoryInterface::class, function ($app) {
            return $app->make(ActivityLogFactory::class);
        });

        $this->app->bind(ActivityLogCriteriaInterface::class, function ($app) {
            return $app->make(ActivityLogCriteria::class);
        });
    }
}
