<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Languages\Services\LanguageService;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.languages', function ($app) {
            return $app->make(LanguageService::class);
        });
    }
}
