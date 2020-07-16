<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Associations\Services\AssociationGroupService;
use Illuminate\Support\ServiceProvider;

class AssociationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('getcandy.association_groups', function ($app) {
            return $app->make(AssociationGroupService::class);
        });
    }
}
