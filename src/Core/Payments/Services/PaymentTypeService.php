<?php

namespace GetCandy\Api\Core\Payments\Services;

use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Core\Scaffold\BaseService;

class PaymentTypeService extends BaseService
{
    public function __construct()
    {
        $this->model = new PaymentType;
    }
}
