<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;

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
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
            'prices' => new ShippingPriceCollection($this->whenLoaded('prices')),
            'zones' => new ShippingZoneCollection($this->whenLoaded('zones')),
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
        ];
    }
}
