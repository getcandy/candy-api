<?php
namespace GetCandy\Api\Payments\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Payments\Models\PaymentType;

class PaymentTypeService extends BaseService
{
    public function __construct()
    {
        $this->model = new PaymentType;
    }
}
