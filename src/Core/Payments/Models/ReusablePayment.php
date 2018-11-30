<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class ReusablePayment extends BaseModel
{
    protected $dates = ['expires_at'];

    protected $hashids = 'product';
}
