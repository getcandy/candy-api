<?php

namespace GetCandy\Api\Core\Payments\Models;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasCustomerGroups;

class PaymentType extends BaseModel
{
    use HasCustomerGroups;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'payment_type_customer_group')->withPivot(['visible']);
    }
}
