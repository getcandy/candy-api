<?php
namespace GetCandy\Api\Traits;

use GetCandy\Api\Scopes\CompletedScope;

trait HasCompletion
{
    public static function bootHasCompletion()
    {
        static::addGlobalScope(new CompletedScope);
    }
}
