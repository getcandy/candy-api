<?php

namespace GetCandy\Api\Core\Routes;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use InvalidArgumentException;

class RouteResolver
{
    public static $elementTypes = [
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
