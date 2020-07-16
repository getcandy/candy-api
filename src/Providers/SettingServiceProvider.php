<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Settings\Services\SettingService;

class SettingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.settings', function ($app) {
            return $app->make(SettingService::class);
        });
    }
}
