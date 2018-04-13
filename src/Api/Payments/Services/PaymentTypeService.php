<?php

namespace GetCandy\Api\Payments\Services;

use GetCandy\Api\Payments\Models\PaymentType;
use GetCandy\Api\Scaffold\BaseService;

class PaymentTypeService extends BaseService
{
    public function __construct()
    {
        $this->model = new PaymentType();
    }
}
