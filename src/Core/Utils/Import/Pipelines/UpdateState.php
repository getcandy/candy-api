<?php

namespace GetCandy\Api\Core\Utils\Import\Pipelines;

use Closure;
use Carbon\Carbon;
use GetCandy\Api\Core\Utils\Import\PipesContract;

class UpdateState implements PipesContract
{
    public function handle($data, Closure $next)
    {
        $variant = $data[0];
        $line = $data[1];
        $import = $data[2];

        $product = $variant->product;

        if (! $line->enabled) {
            $import->increment('deleted');
        }

        $product->update([
            'deleted_at' => ! $line->enabled ? Carbon::now() : null,
        ]);

        return $next([$variant, $line, $import]);
    }
}
