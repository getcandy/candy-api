<?php

namespace GetCandy\Api\Core\Utils\Import\Pipelines;

use Closure;
use GetCandy\Api\Core\Utils\Import\PipesContract;

class UpdateVariant implements PipesContract
{
    public function handle($data, Closure $next)
    {
        $variant = $data[0];
        $line = $data[1];
        $import = $data[2];

        if (! $line->enabled) {
            return $next([$variant, $line, $import]);
        }

        $import->increment('updated');

        $variant->price = $line->price;
        $variant->unit_qty = $line->unit_qty;
        $variant->min_qty = $line->min_qty;
        $variant->min_batch = $line->min_batch;
        $variant->stock = $line->stock;
        $variant->backorder = $line->backorder;

        return $next([$variant, $line, $import]);
    }
}
