<?php

namespace Tests\Unit\Products\Actions\Versioning;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Actions\Versioning\VersionProductVariantTiers;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionProductVariantTiersTest extends TestCase
{
    public function test_can_version_variant_tiers()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $customerGroup = factory(CustomerGroup::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $tiers = [
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 1, 'price' => 1.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 2, 'price' => 2.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 3, 'price' => 3.00],
        ];

        $variant->tiers()->createMany($tiers);

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $variant,
        ]);

        (new VersionProductVariantTiers)->actingAs($user)->run([
            'version' => $version,
            'variant' => $variant,
        ]);

        foreach ($variant->tiers as $tier) {
            $versionedTier = $version->relations->first(function ($relation) use ($tier) {
                return $relation->versionable_id == $tier->id && $relation->versionable_type == get_class($tier);
            });
            foreach ($versionedTier->model_data as $attribute => $value) {
                $this->assertEquals($tier->getAttributes()[$attribute], $value);
            }
        }
    }
}
