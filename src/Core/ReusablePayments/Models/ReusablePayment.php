<?php

namespace GetCandy\Api\Core\ReusablePayments\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class ReusablePayment extends BaseModel
{
    protected $dates = ['expires_at'];

    /**
     * The Hashid connection name for encoding the id.
     *
     * @var string
     */
    protected $hashids = 'product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
