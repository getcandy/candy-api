<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;

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
            'layout' => new LayoutResource($this->whenLoaded('layout')),
        ];
    }
}