<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;

class ShippingMethodResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'type' => $this->type,
        ];
    }

    public function includes()
    {
        return [
            'prices' => new ShippingPriceCollection($this->whenLoaded('prices')),
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
        ];
    }
}
