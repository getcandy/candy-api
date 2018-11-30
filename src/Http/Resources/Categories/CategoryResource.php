<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;

class CategoryResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'sort' => $this->sort,
        ];
    }

    public function includes()
    {
        return [
            'children' => new CategoryCollection($this->whenLoaded('children'), $this->only),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'layout' => ['data' => new LayoutResource($this->whenLoaded('layout'))],
            'assets' => new AssetCollection($this->whenLoaded('assets')),
        ];
    }
}