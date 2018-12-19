<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CategoryCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CategoryResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
