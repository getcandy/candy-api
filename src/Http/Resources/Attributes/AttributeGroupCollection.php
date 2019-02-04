<?php

namespace GetCandy\Api\Http\Resources\Attributes;

use GetCandy\Api\Http\Resources\AbstractCollection;

class AttributeGroupCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AttributeGroupResource::class;
}
