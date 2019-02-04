<?php

namespace GetCandy\Api\Http\Resources\Assets;

use GetCandy\Api\Http\Resources\AbstractCollection;

class AssetCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AssetResource::class;
}
