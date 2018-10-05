<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PaymentTypeTransformer extends BaseTransformer
{
    public function transform(PaymentType $type)
    {
        return [
            'id' => $type->encodedId(),
            'name' => $type->name,
            'handle' => $type->handle,
            'driver' => $type->driver,
        ];
    }
}
