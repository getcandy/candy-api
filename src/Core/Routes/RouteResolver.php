<?php

namespace GetCandy\Api\Core\Routes;

use InvalidArgumentException;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;

class RouteResolver
{
    static $elementTypes = [
        'product' => Product::class,
        'category' => Category::class,
    ];

    public static function addElementType($key, $value)
    {
        if (isset(self::$elementTypes[$key])) {
            throw new InvalidArgumentException;
        }
        self::$elementTypes[$key] = $value;
    }

    public static function resolve($elementType)
    {
        if (class_exists($elementType)) {
            return $elementType;
        }
        return self::$elementTypes[$elementType] ?? $elementType;
    }
}