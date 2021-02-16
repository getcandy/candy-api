<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Drafting\Actions\DraftProductVariantTiers;

/**
 * @group drafting
 */
class DraftProductVariantTiersTest extends TestCase
{
    public function test_can_draft_variant_tiers()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id
        ]);
        $draft = $variant->replicate();

        $tiers = [
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 1, 'price' => 1.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 2, 'price' => 2.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 3, 'price' => 3.00],
        ];

        $variant->tiers()->createMany($tiers);

        $draft->save();
        $draft->update([
            'draft_parent_id' => $variant->id,
            'drafted_at' => now()
        ]);

        $this->assertCount(count($tiers), $variant->tiers);
        $this->assertCount(0, $draft->tiers);

        (new DraftProductVariantTiers)->actingAs($user)->run([
            'parent' => $variant,
            'draft' => $draft,
        ]);

        $this->assertCount(count($tiers), $draft->refresh()->tiers);
    }
}
