<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Http\Resources\AbstractCollection;

class ShippingMethodCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ShippingMethodResource::class;

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

    public function includes()
    {
        return [
            'prices' => new ShippingPriceCollection($this->whenLoaded('prices')),
            'zones' => new ShippingZoneCollection($this->whenLoaded('zones')),
            'users' => new UserCollection($this->whenLoaded('users')),
        ];
    }
}
