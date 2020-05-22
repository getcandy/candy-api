<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Products\ProductVariantResource;

class OrderLineResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'quantity' => $this->quantity,
            'line_total' => $this->line_total,
            'discount_total' => $this->discount_total,
            'delivery_total' => $this->delivery_total,
            'unit_price' => $this->unit_price,
            'unit_qty' => $this->unit_qty,
            'tax_total' => $this->tax_total,
            'tax_rate' => $this->tax_rate,
            'description' => $this->description,
            'option' => $this->option,
            'sku' => $this->sku,
            'is_shipping' => (bool) $this->is_shipping,
            'is_manual' => (bool) $this->is_manual,
            'meta' => $this->meta,
        ];
    }

    public function includes()
    {
        return [
            'variant' => ['data' => new ProductVariantResource($this->whenLoaded('variant'))],
        ];
    }
}
