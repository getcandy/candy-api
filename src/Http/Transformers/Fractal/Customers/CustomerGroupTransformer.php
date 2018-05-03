<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Customers;

use League\Fractal\TransformerAbstract;
use GetCandy\Api\Customers\Models\CustomerGroup;

class CustomerGroupTransformer extends TransformerAbstract
{
    public function transform(CustomerGroup $customerGroup)
    {
        return  [
            'id' => $customerGroup->encodedId(),
            'name' => $customerGroup->name,
            'handle' => $customerGroup->handle,
            'visible' => $customerGroup->visible ? true : false,
            'purchasable' => $customerGroup->purchasable ? true : false,
        ];
    }
}
