<?php

namespace GetCandy\Api\Http\Resources\ActivityLog;

use GetCandy\Api\Http\Resources\AbstractCollection;

class ActivityCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ActivityResource::class;
}
