<?php

namespace Tests\Unit\Products\Actions\Versioning;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Products\Actions\Versioning\VersionProductVariants;

/**
 * @group versioning
 */
class VersionChannelsTest extends TestCase
{
    public function test_can_version_variants()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(ProductVariant::class, 2)->create([
            'product_id' => $product->id,
        ]);

        $this->assertCount(2, $product->variants);

        $version = (new CreateVersion)->actingAs($user)->run([
            'model' => $product
        ]);

        (new VersionProductVariants)->actingAs($user)->run([
            'version' => $version,
            'product' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($product->variants as $variant) {
            $versionedVariant = $version->relations->first(function ($relation) use ($variant) {
                return $relation->versionable_id == $variant->id && $relation->versionable_type == get_class($variant);
            });
            foreach ($versionedVariant->model_data as $attribute => $value) {
                $this->assertEquals($variant->getAttributes()[$attribute], $value);
            }
        }
    }
}
