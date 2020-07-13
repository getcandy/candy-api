<?php

namespace GetCandy\Api\Core\Facades;

use GetCandy\Api\Core\GetCandy;
use Illuminate\Support\Facades\Facade;

class Route extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Route::class;
    }
}
