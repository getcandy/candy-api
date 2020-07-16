<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Languages\Services\LanguageService;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.languages', function ($app) {
            return $app->make(LanguageService::class);
        });
    }
}
