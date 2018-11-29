<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;

class ProductTierResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
        ];
    }

    public function includes()
    {
        return [
        ];
    }
}