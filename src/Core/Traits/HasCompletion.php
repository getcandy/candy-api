<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Scopes\CompletedScope;

trait HasCompletion
{
    public static function bootHasCompletion()
    {
        static::addGlobalScope(new CompletedScope);
    }
}
