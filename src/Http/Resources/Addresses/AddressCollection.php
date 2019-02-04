<?php

namespace GetCandy\Api\Http\Resources\Addresses;

use GetCandy\Api\Http\Resources\AbstractCollection;

class AddressCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AddressResource::class;
}
