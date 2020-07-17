<?php

namespace GetCandy\Api\Http\Controllers\Payments;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Payments\PaymentTypeCollection;

class PaymentTypeController extends BaseController
{
    public function index()
    {
        $types = GetCandy::paymentTypes()->all();

        return new PaymentTypeCollection($types);
    }
}
