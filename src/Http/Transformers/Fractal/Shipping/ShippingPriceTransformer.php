<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Shipping;

use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Currencies\CurrencyTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ShippingPriceTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'method',
        'customer_groups',
        'zone',
    ];

    protected $defaultIncludes = [
        'currency',
    ];

    public function transform(ShippingPrice $price)
    {
        $prices = app()->getInstance()->make(PriceCalculatorInterface::class)->get($price->rate, 'default');
        $minBasketPrices = app()->getInstance()->make(PriceCalculatorInterface::class)->get($price->min_basket, 'default');

        return [
            'id' => $price->encodedId(),
            'rate' => $prices->total_cost,
            'tax' => $prices->total_tax,
            'fixed' => (bool) $price->fixed,
            'min_basket' => $minBasketPrices->total_cost,
            'min_basket_tax' => $minBasketPrices->total_tax,
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

    public function includeZone(ShippingPrice $price)
    {
        return $this->item($price->zone, new ShippingZoneTransformer);
    }
}
