<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractCollection;

class OrderCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = OrderResource::class;
}
