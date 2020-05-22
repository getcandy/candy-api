<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Assets\Versioning\AssetVersioner;
use Illuminate\Support\ServiceProvider;
use Versioning;

class AssetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Versioning::extend('assets', function ($app) {
            return $app->make(AssetVersioner::class);
        });
    }
}
