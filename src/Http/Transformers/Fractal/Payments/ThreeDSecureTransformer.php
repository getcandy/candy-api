<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Payments;

use GetCandy\Api\Core\Payments\ThreeDSecureResponse;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ThreeDSecureTransformer extends BaseTransformer
{
    public function transform(ThreeDSecureResponse $response)
    {
        return $response->params();
    }
}
