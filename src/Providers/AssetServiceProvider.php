<?php

namespace GetCandy\Api\Providers;

use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Assets\Services\AssetService;
use GetCandy\Api\Core\Assets\Versioning\AssetVersioner;
use GetCandy\Api\Core\Assets\Services\AssetSourceService;
use GetCandy\Api\Core\Assets\Services\AssetTransformService;

class AssetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Versioning::extend('assets', function ($app) {
            return $app->make(AssetVersioner::class);
        });

        $this->app->bind('getcandy.assets', function ($app) {
            return $app->make(AssetService::class);
        });

        $this->app->bind('getcandy.asset_sources', function ($app) {
            return $app->make(AssetSourceService::class);
        });

        $this->app->bind('getcandy.asset_transforms', function ($app) {
            return $app->make(AssetTransformService::class);
        });
    }
}
