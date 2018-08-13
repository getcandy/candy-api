<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Shipping;

use PriceCalculator;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Currencies\CurrencyTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ShippingPriceTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'method',
        'customer_groups',
    ];

    protected $defaultIncludes = [
        'currency',
    ];

    public function transform(ShippingPrice $price)
    {
        $prices = PriceCalculator::get($price->rate, 'default');

        return [
            'id' => $price->encodedId(),
            'rate' => $prices->total_cost,
            'tax' => $prices->total_tax,
            'fixed' => (bool) $price->fixed,
            'min_basket' => $price->min_basket,
            'min_weight' => $price->min_weight,
            'weight_unit' => $price->weight_unit,
            'min_height' => $price->min_height,
            'height_unit' => $price->height_unit,
            'min_width' => $price->min_width,
            'width_unit' => $price->width_unit,
            'min_depth' => $price->min_depth,
            'depth_unit' => $price->depth_unit,
            'min_volume' => $price->min_volume,
            'volume_unit' => $price->volume_unit,
        ];
    }

    protected function includeMethod($price)
    {
        return $this->item($price->method, new ShippingMethodTransformer);
    }

    protected function includeCurrency($price)
    {
        return $this->item($price->currency, new CurrencyTransformer);
    }

    /**
     * @param Product $product
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCustomerGroups(ShippingPrice $price)
    {
        $groups = app('api')->customerGroups()->getGroupsWithAvailability($price, 'shipping_prices');

        return $this->collection($groups, new CustomerGroupTransformer);
    }
}
