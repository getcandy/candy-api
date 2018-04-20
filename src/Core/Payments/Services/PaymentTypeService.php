<?php

namespace GetCandy\Api\Core\Payments\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Payments\Models\PaymentType;

class PaymentTypeService extends BaseService
{
    public function __construct()
    {
        $this->model = new PaymentType;
    }
}
