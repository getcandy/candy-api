<?php

namespace GetCandy\Api\Core\Countries\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class StateCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = StateResource::class;
}
