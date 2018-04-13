<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Taxes\TaxTransformer;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Products\Models\ProductVariant;

class ProductVariantTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'product', 'tax', 'pricing', 'tiers',
    ];

    public function transform(ProductVariant $variant)
    {
        // $price =
        $response = [
            'id'                => $variant->encodedId(),
            'sku'               => $variant->sku,
            'backorder'         => (bool) $variant->backorder,
            'requires_shipping' => (bool) $variant->requires_shipping,
            'price'             => $variant->total_price,
            'tax_total'         => round($variant->tax_total, 2),
            'inventory'         => $variant->stock,
            'thumbnail'         => $this->getThumbnail($variant),
            'weight'            => [
                'value' => $variant->weight_value,
                'unit'  => $variant->weight_unit,
            ],
            'height' => [
                'value' => $variant->height_value,
                'unit'  => $variant->height_unit,
            ],
            'width' => [
                'value' => $variant->width_value,
                'unit'  => $variant->width_unit,
            ],
            'depth' => [
                'value' => $variant->depth_value,
                'unit'  => $variant->depth_unit,
            ],
            'volume' => [
                'value' => $variant->volume_value,
                'unit'  => $variant->volume_unit,
            ],
            'options' => $variant->options,
        ];

        return $response;
    }

    public function includeTax(ProductVariant $variant)
    {
        if (!$variant->tax) {
            return $this->null();
        }

        return $this->item($variant->tax, new TaxTransformer());
    }

    public function includeProduct(ProductVariant $variant)
    {
        return $this->item($variant->product, new ProductTransformer());
    }

    public function includePricing(ProductVariant $variant)
    {
        return $this->collection($variant->customerPricing, new ProductCustomerPriceTransformer());
    }

    protected function getThumbnail($variant)
    {
        $asset = $variant->image()->count();

        if (!$asset) {
            return;
        }

        $data = $this->item($variant->image, new AssetTransformer());

        return app()->fractal->createData($data)->toArray();
    }

    public function includeTiers(ProductVariant $product)
    {
        $groups = \GetCandy::getGroups();

        //TODO: Review for performace
        $tiers = $product->tiers()->inGroups(
            $groups->pluck('id')->toArray()
        )->get();

        return $this->collection(
            $tiers,
            new ProductPricingTierTransformer()
        );
    }
}
