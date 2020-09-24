<?php

namespace GetCandy\Api\Http\Resources\Pages;

use GetCandy\Api\Http\Resources\AbstractCollection;

class PageCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = PageResource::class;
}
