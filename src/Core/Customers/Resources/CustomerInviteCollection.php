<?php

namespace GetCandy\Api\Core\Customers\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CustomerInviteCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CustomerInviteResource::class;
}
