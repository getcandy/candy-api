<?php
namespace GetCandy\Api\Payments\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Orders\Models\Order;
use GetCandy\Api\Traits\HasCustomerGroups;
use GetCandy\Api\Customers\Models\CustomerGroup;

class PaymentType extends BaseModel
{
    use HasCustomerGroups;

    protected $hashids = 'main';

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'payment_type_customer_group')->withPivot(['visible']);
    }
}
