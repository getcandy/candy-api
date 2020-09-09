<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Observers\LanguageObserver;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        Language::observe(LanguageObserver::class);
    }
}
