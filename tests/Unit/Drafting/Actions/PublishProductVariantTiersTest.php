<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Drafting\Actions\PublishProductVariantTiers;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use Tests\TestCase;

/**
 * @group drafting
 */
class PublishProductVariantTiersTest extends TestCase
{
    public function test_can_publish_variant_tiers()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();

        $product = factory(Product::class)->create();

        $parent = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);
        $draft = $parent->replicate();

        $tiers = [
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 1, 'price' => 1.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 2, 'price' => 2.00],
            ['customer_group_id' => $customerGroup->id, 'lower_limit' => 3, 'price' => 3.00],
        ];

        $draft->save();
        $draft->update([
            'draft_parent_id' => $parent->id,
            'drafted_at' => now(),
        ]);

        $draft->tiers()->createMany($tiers);

        $this->assertCount(count($tiers), $draft->tiers);
        $this->assertCount(0, $parent->tiers);

        (new PublishProductVariantTiers)->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(count($tiers), $parent->refresh()->tiers);
    }
}
