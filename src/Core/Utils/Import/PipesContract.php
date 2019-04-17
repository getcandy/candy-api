<?php

namespace GetCandy\Api\Core\Utils\Import;

use Closure;

interface PipesContract
{
    public function handle($variant, Closure $next);
}
