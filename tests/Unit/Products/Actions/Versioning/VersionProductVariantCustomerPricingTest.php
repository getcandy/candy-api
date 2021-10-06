<?php

namespace Tests\Unit\Products\Actions\Versioning;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Actions\Versioning\VersionProductVariantCustomerPricing;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductCustomerPrice;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionProductVariantCustomerPricingTest extends TestCase
{
    public function test_can_version_variant_customer_pricing()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $customerGroup = factory(CustomerGroup::class)->create();
        $tax = factory(Tax::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        for ($i = 0; $i < 2; $i++) {
            $pricing = new ProductCustomerPrice();
            $pricing->product_variant_id = $variant->id;
            $pricing->customer_group_id = $customerGroup->id;
            $pricing->tax_id = $tax->id;
            $pricing->price = 30;
            $pricing->save();
        }

        $version = (new CreateVersion())->actingAs($user)->run([
            'model' => $variant,
        ]);

        (new VersionProductVariantCustomerPricing())->actingAs($user)->run([
            'version' => $version,
            'variant' => $variant,
        ]);

        foreach ($variant->customerPricing as $price) {
            $versionedPrice = $version->relations->first(function ($relation) use ($price) {
                return $relation->versionable_id == $price->id && $relation->versionable_type == get_class($price);
            });
            foreach ($versionedPrice->model_data as $attribute => $value) {
                $this->assertEquals($price->getAttributes()[$attribute], $value);
            }
        }
    }
}
