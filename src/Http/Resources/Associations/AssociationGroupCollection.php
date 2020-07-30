<?php

namespace GetCandy\Api\Http\Resources\Associations;

use GetCandy\Api\Http\Resources\AbstractCollection;

class AssociationGroupCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AssociationGroupResource::class;
}
