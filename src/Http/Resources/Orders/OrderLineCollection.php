<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractCollection;

class OrderLineCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = OrderLineResource::class;

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
