<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Shipping;

use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;

class ShippingMethodTransformer extends BaseTransformer
{
    use IncludesAttributes;

    protected $availableIncludes = [
        'zones', 'prices', 'attribute_groups', 'channels', 'users', 'attributes',
    ];

    public function transform(ShippingMethod $method)
    {
        return [
            'id' => $method->encodedId(),
            'type' => $method->type,
            'attribute_data' => $method->attribute_data,
        ];
    }

    protected function includePrices($method)
    {
        return $this->collection($method->prices, new ShippingPriceTransformer);
    }

    protected function includeZones(ShippingMethod $method)
    {
        return $this->collection($method->zones, new ShippingZoneTransformer);
    }

    protected function includeUsers(ShippingMethod $method)
    {
        return $this->collection($method->users, new UserTransformer);
    }

    /**
     * @param ShippingMethod $method
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(ShippingMethod $method)
    {
        $channels = app('api')->channels()->getChannelsWithAvailability($method, 'shipping_methods');

        return $this->collection($channels, new ChannelTransformer);
    }
}
