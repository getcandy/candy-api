<?php

namespace GetCandy\Api\Providers;

use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Assets\Versioning\AssetVersioner;
use GetCandy\Api\Core\Assets\Services\AssetSourceService;

class AssetServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Versioning::extend('assets', function ($app) {
            return $app->make(AssetVersioner::class);
        });

        $this->app->bind('getcandy.asset_sources', function ($app) {
            return $app->make(AssetSourceService::class);
        });
    }
}
