<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Languages\Observers\LanguageObserver;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    public function register()
    {
        Language::observe(LanguageObserver::class);
    }
}
