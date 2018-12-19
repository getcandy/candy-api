<?php

namespace GetCandy\Api\Http\Resources\Assets;

use GetCandy\Api\Http\Resources\AbstractCollection;

class AssetTransformCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AssetTransformResource::class;
}
