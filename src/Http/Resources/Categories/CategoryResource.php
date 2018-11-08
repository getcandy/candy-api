<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Http\Resources\AbstractResource;

class CategoryResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'sort' => $this->sort,
            'depth' => $this->depth,
            'products_count' => $this->products->count(),
            'children_count' => $this->children->count(),
            'parent' => new $this($this->whenLoaded('parent')),
        ];
    }

    public function includes()
    {
        return [
            'descendants' => (new CategoryCollection($this->whenLoaded('descendants')))->only($this->only),
            // 'routes' => new RouteCollection($this->whenLoaded('routes')),
        ];
    }
}