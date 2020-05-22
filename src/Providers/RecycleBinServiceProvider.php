<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\RecycleBin\Interfaces\RecycleBinServiceInterface;
use GetCandy\Api\Core\RecycleBin\Services\RecycleBinService;
use Illuminate\Support\ServiceProvider;

class RecycleBinServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RecycleBinServiceInterface::class, function ($app) {
            return $app->make(RecycleBinService::class);
        });
    }
}
