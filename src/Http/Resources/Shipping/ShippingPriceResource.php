<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Core\Pricing\PriceCalculator;
use GetCandy\Api\Http\Resources\AbstractResource;

class ShippingPriceResource extends AbstractResource
{
    public function payload()
    {
        $prices = app()->getInstance()->make(PriceCalculator::class)->get($this->rate, 'default');

        $minBasketPrices = app()->getInstance()->make(PriceCalculator::class)->get($this->min_basket, 'default');

        return [
            'id' => $this->encodedId(),
            'rate' => $prices->total_cost,
            'tax' => $prices->total_tax,
            'fixed' => (bool) $this->fixed,
            'min_basket' => $minBasketPrices->total_cost,
            'min_basket_tax' => $minBasketPrices->total_tax,
            'min_weight' => $this->min_weight,
            'weight_unit' => $this->weight_unit,
            'min_height' => $this->min_height,
            'height_unit' => $this->height_unit,
            'min_width' => $this->min_width,
            'width_unit' => $this->width_unit,
            'min_depth' => $this->min_depth,
            'depth_unit' => $this->depth_unit,
            'min_volume' => $this->min_volume,
            'volume_unit' => $this->volume_unit,
        ];
    }

    public function includes()
    {
        return [
            'method' => $this->include('method', ShippingMethodResource::class),
        ];
    }
}
