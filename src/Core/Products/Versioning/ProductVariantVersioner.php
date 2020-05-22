<?php

namespace GetCandy\Api\Core\Products\Versioning;

use GetCandy\Api\Core\Products\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Versioning\Interfaces\VersionerInterface;
use NeonDigital\Versioning\Versioners\AbstractVersioner;

class ProductVariantVersioner extends AbstractVersioner implements VersionerInterface
{
    public function create(Model $variant, $relationId = null)
    {
        $version = $this->createFromObject($variant, $relationId);

        // Tiers
        foreach ($variant->tiers ?? [] as $tier) {
            $this->createFromObject($tier, $version->id);
        }

        // Prices
        foreach ($variant->customerPricing as $price) {
            $this->createFromObject($price, $version->id);
        }
    }

    public function restore($version, $parent = null)
    {
        $data = $version->model_data;
        $data['options'] = json_decode($data['options']);
        unset($data['id']);
        $variant = new ProductVariant;
        $variant->forceFill($data);
        $variant->asset_id = null;

        if ($parent) {
            $variant->product_id = $parent->id;
        }
        $variant->drafted_at = now();
        $variant->save();
        // foreach ($version->relations as $relation) {
        //     dd($relation);
        // }
        return $variant;
    }
}
