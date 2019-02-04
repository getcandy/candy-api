<?php

namespace GetCandy\Api\Http\Resources\Customers;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CustomerGroupCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CustomerGroupResource::class;
}
