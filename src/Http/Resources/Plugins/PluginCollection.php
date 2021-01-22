<?php

namespace GetCandy\Api\Http\Resources\Plugins;

use GetCandy\Api\Http\Resources\AbstractCollection;

class PluginCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = PluginResource::class;
}
