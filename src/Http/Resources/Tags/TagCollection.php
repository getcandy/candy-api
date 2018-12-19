<?php

namespace GetCandy\Api\Http\Resources\Tags;

use GetCandy\Api\Http\Resources\AbstractCollection;

class TagCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TagResource::class;
}
