<?php

namespace GetCandy\Api\Providers;

use Drafting;
use GetCandy\Api\Core\Collections\Drafting\CollectionDrafter;
use GetCandy\Api\Core\Collections\Versioning\CollectionVersioner;
use Illuminate\Support\ServiceProvider;
use Versioning;

class CollectionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('collections', function ($app) {
            return $app->make(CollectionDrafter::class);
        });

        Versioning::extend('collections', function ($app) {
            return $app->make(CollectionVersioner::class);
        });
    }
}
