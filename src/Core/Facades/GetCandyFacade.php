<?php

namespace GetCandy\Api\Core\Facades;

use GetCandy\Api\Core\GetCandy;
use Illuminate\Support\Facades\Facade;

class GetCandyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GetCandy::class;
    }
}
