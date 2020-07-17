<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Shipping;

use GetCandy;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;
use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;

class ShippingMethodTransformer extends BaseTransformer
{
    use IncludesAttributes;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
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
     * @param  \GetCandy\Api\Core\Shipping\Models\ShippingMethod  $method
     * @return \League\Fractal\Resource\Collection
     */
    public function includeChannels(ShippingMethod $method)
    {
        $channels = GetCandy::channels()->getChannelsWithAvailability($method, 'shipping_methods');

        return $this->collection($channels, new ChannelTransformer);
    }
}
