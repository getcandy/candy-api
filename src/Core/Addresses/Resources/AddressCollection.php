<?php

namespace GetCandy\Api\Core\Addresses\Resources;

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
