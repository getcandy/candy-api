<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractCollection;

class ProductCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ProductResource::class;

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}
