<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasCustomerGroups;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class PaymentType extends BaseModel
{
    use HasCustomerGroups;

    protected $hashids = 'main';

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'payment_type_customer_group')->withPivot(['visible']);
    }
}
