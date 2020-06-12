<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class ReusablePayment extends BaseModel
{
    protected $dates = ['expires_at'];

    /**
     * The Hashid connection name for enconding the id.
     * 
     * @var string
     */
    protected $hashids = 'product';
}
