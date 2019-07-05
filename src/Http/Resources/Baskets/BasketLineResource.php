<?php

namespace GetCandy\Api\Http\Resources\Baskets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Products\ProductVariantResource;

class BasketLineResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'quantity' => $this->quantity,
            'line_total' => $this->total_cost,
            'unit_price' => $this->unit_cost,
            'unit_tax' => $this->unit_tax,
            'line_discount' => $this->discount_total,
            'tax' => $this->total_tax,
            'meta' => $this->meta,
        ];
    }

    public function includes()
    {
        return [
            // 'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'variant' => ['data' => new ProductVariantResource($this->whenLoaded('variant'), $this->only)],
            // 'associations' => new ProductCollection($this->whenLoaded('associations'), $this->only),
            // 'assets' => new AssetCollection($this->whenLoaded('assets')),
            // 'routes' => new RouteCollection($this->whenLoaded('routes')),
        ];
    }
}
