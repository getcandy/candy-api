<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Drafting\Actions\PublishProductVariants;

/**
 * @group drafting
 */
class PublishProductVariantsTest extends TestCase
{
    public function test_can_publish_product_variants()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();

        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        factory(ProductVariant::class)->create([
            'product_id' => $draft->id
        ]);

        $this->assertCount(1, $draft->variants);

        $this->assertCount(0, $parent->variants);

        (new PublishProductVariants)->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(1, $parent->refresh()->variants);
    }
}
