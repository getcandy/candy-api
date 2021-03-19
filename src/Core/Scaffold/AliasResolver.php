<?php

namespace GetCandy\Api\Core\Scaffold;

use InvalidArgumentException;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Exceptions\AliasResolutionException;

class AliasResolver
{
    public static $aliases = [
        'product' => Product::class,
        'category' => Category::class,
    ];

    public static function addAliases($key, $value)
    {
        if (isset(self::$aliases[$key])) {
            throw new InvalidArgumentException;
        }
        self::$aliases[$key] = $value;
    }

    public static function resolve($alias)
    {
        if (class_exists($alias)) {
            return $alias;
        }
        if (empty(self::$aliases[$alias]) || !class_exists(self::$aliases[$alias])) {
            throw new AliasResolutionException("Unable to resolve alias \"{$alias}\" into a usable class");
        }
        return self::$aliases[$alias];
    }
}
