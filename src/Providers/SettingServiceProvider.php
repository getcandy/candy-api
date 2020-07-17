<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Settings\Services\SettingService;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.settings', function ($app) {
            return $app->make(SettingService::class);
        });
    }
}
