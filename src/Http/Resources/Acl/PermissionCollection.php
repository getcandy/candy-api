<?php

namespace GetCandy\Api\Http\Resources\Acl;

use GetCandy\Api\Http\Resources\AbstractCollection;

class PermissionCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = PermissionResource::class;
}
