<?php

namespace GetCandy\Api\Core\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractFactory;

class RouteFactory extends AbstractFactory implements RouteFactoryInterface
{
    public function get($slug, $elementType, $path = null)
    {
        return Route::whereSlug($slug)->wherePath($path)->whereElementType($elementType)->first();
    }

    public function getModelReference()
    {
        return Route::class;
    }
}