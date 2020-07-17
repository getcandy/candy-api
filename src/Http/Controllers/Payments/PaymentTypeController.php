<?php

namespace GetCandy\Api\Http\Controllers\Payments;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Payments\PaymentTypeTransformer;

class PaymentTypeController extends BaseController
{
    public function index()
    {
        $types = GetCandy::paymentTypes()->all();

        return $this->respondWithCollection($types, new PaymentTypeTransformer);
    }
}
