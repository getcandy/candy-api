<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Core\Payments\ThreeDSecureResponse;

class ThreeDSecureTransformer extends BaseTransformer
{
    public function transform(ThreeDSecureResponse $response)
    {
        return $response->params();
    }
}
