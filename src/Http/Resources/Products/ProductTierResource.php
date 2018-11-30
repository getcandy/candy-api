<?php

namespace GetCandy\Api\Http\Resources\Products;

use PriceCalculator;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;

class ProductTierResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'lower_limit' => $this->lower_limit,
            'price' => $this->total_cost,
            'tax' => $this->total_tax,
        ];
    }

    public function includes()
    {
        return [
        ];
    }
}