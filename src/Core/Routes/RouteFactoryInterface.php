<?php

namespace GetCandy\Api\Core\Routes;

interface RouteFactoryInterface
{
    public function get($slug, $elementType, $path = null);
}