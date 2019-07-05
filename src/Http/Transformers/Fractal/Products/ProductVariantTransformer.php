<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Http\Transformers\Fractal\Taxes\TaxTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;

class ProductVariantTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'product', 'tax', 'pricing', 'tiers',
    ];

    public function transform(ProductVariant $variant)
    {
        $variant = app()->make(ProductVariantFactory::class)->init($variant)->get();

        return [
            'id' => $variant->encodedId(),
            'sku' => $variant->sku,
            'backorder' => $variant->backorder,
            'requires_shipping' => (bool) $variant->requires_shipping,
            'price' => $variant->price,
            'factor_tax' => $variant->factor_tax,
            'unit_price' => $variant->unit_cost,
            'tax' => $variant->unit_tax,
            'total_tax' => $variant->total_tax,
            'unit_qty' => $variant->unit_qty,
            'min_qty' => $variant->min_qty,
            'max_qty' => $variant->max_qty,
            'inventory' => $variant->stock,
            'incoming' => $variant->incoming,
            'group_pricing' => (bool) $variant->group_pricing,
            'weight' => [
                'value' => $variant->weight_value,
                'unit' => $variant->weight_unit,
            ],
            'height' => [
                'value' => $variant->height_value,
                'unit' => $variant->height_unit,
            ],
            'width' => [
                'value' => $variant->width_value,
                'unit' => $variant->width_unit,
            ],
            'depth' => [
                'value' => $variant->depth_value,
                'unit' => $variant->depth_unit,
            ],
            'volume' => [
                'value' => $variant->volume_value,
                'unit' => $variant->volume_unit,
            ],
            'options' => $variant->options,
        ];
    }

    public function includeTax(ProductVariant $variant)
    {
        if (! $variant->tax) {
            return $this->null();
        }

        return $this->item($variant->tax, new TaxTransformer);
    }

    public function includeProduct(ProductVariant $variant)
    {
        return $this->item($variant->product, new ProductTransformer);
    }

    public function includePricing(ProductVariant $variant)
    {
        return $this->collection($variant->customerPricing, new ProductCustomerPriceTransformer);
    }

    protected function getThumbnail($variant)
    {
        $asset = $variant->image()->count();

        if (! $asset) {
            return;
        }

        $data = $this->item($variant->image, new AssetTransformer);

        return app()->fractal->createData($data)->toArray();
    }

    public function includeTiers(ProductVariant $product)
    {
        $groups = \GetCandy::getGroups();

        $tiers = $product->tiers->filter(function ($tier) use ($groups) {
            return $groups->contains($tier->group);
        });

        return $this->collection(
            $tiers,
            new ProductPricingTierTransformer
        );
    }
}
