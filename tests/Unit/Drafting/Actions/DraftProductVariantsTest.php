<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Drafting\Actions\DraftProductVariants;

/**
 * @group drafting
 */
class DraftProductVariantsTest extends TestCase
{
    public function test_can_draft_variant_customer_pricing()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();

        $product = factory(Product::class)->create();

        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        factory(ProductVariant::class)->create([
            'product_id' => $product->id
        ]);

        $this->assertCount(1, $product->variants);
        $this->assertCount(0, $draft->variants);

        (new DraftProductVariants)->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(1, $draft->refresh()->variants);
    }
}
