<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Routes\Models\Route;

trait HasRoutes
{
    public function route()
    {
        return $this->morphOne(Route::class, 'element');
    }

    public function routes()
    {
        return $this->morphMany(Route::class, 'element');
    }
}
