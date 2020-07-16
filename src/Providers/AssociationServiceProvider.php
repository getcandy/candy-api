<?php

namespace GetCandy\Api\Providers;

use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Associations\Services\AssociationGroupService;

class AssociationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind('getcandy.association_groups', function ($app) {
            return $app->make(AssociationGroupService::class);
        });
    }
}
