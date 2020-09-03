<?php

namespace GetCandy\Api\Core\Attributes\Resources;

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
