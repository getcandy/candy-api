<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Taxes\TaxResource;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;

class ProductVariantResource extends AbstractResource
{
    public function payload()
    {
        $factory = new ProductVariantFactory;
        $this->resource = $factory->init($this->resource)->get();

        return [
            'id' => $this->encodedId(),
            'sku' => $this->sku,
            'backorder' => $this->backorder,
            'requires_shipping' => (bool) $this->requires_shipping,
            'price' => $this->price,
            'unit_price' => $this->unit_cost,
            'unit_tax' => $this->unit_tax,
            'unit_qty' => $this->unit_qty,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'inventory' => $this->stock,
            'incoming' => $this->incoming,
            'group_pricing' => (bool) $this->group_pricing,
            'weight' => [
                'value' => $this->weight_value,
                'unit' => $this->weight_unit,
            ],
            'height' => [
                'value' => $this->height_value,
                'unit' => $this->height_unit,
            ],
            'width' => [
                'value' => $this->width_value,
                'unit' => $this->width_unit,
            ],
            'depth' => [
                'value' => $this->depth_value,
                'unit' => $this->depth_unit,
            ],
            'volume' => [
                'value' => $this->volume_value,
                'unit' => $this->volume_unit,
            ],
            'options' => $this->options,
        ];
    }

    public function includes()
    {
        return [
            'product' => ['data' => new ProductResource($this->whenLoaded('product'), $this->only)],
            'tiers' => ['data '=> new ProductTierCollection($this->whenLoaded('tiers'))],
            'tax' => $this->include('tax', TaxResource::class),
        ];
    }
}
