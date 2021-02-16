<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Products\Models\ProductCustomerPrice;
use GetCandy\Api\Core\Drafting\Actions\DraftProductVariantCustomerPricing;

/**
 * @group drafting
 */
class DraftProductVariantCustomerPricingTest extends TestCase
{
    public function test_can_draft_variant_customer_pricing()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();
        $tax = factory(Tax::class)->create();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id
        ]);

        $pricing = new ProductCustomerPrice;
        $pricing->product_variant_id = $variant->id;
        $pricing->customer_group_id = $customerGroup->id;
        $pricing->tax_id = $tax->id;
        $pricing->price = 30;
        $pricing->save();


        $draft = $variant->replicate();
        $draft->save();
        $draft->update([
            'draft_parent_id' => $variant->id,
            'drafted_at' => now()
        ]);

        $this->assertCount(1, $variant->customerPricing);
        $this->assertCount(0, $draft->customerPricing);

        (new DraftProductVariantCustomerPricing)->actingAs($user)->run([
            'parent' => $variant,
            'draft' => $draft,
        ]);

        $this->assertCount(1, $draft->refresh()->customerPricing);
    }
}
