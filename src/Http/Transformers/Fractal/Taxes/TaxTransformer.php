<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Taxes;

use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class TaxTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    public function transform(Tax $tax)
    {
        return [
            'id' => $tax->encodedId(),
            'name' => $tax->name,
            'percentage' => $tax->percentage,
            'default' => (bool) $tax->default,
        ];
    }
}
