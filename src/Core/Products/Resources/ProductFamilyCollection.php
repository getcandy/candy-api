<?php

namespace GetCandy\Api\Core\Products\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class ProductFamilyCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ProductFamilyResource::class;

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
