<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Drafting\Actions\PublishProductVariantCustomerPricing;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductCustomerPrice;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Taxes\Models\Tax;
use Tests\TestCase;

/**
 * @group drafting
 */
class PublishProductVariantCustomerPricingTest extends TestCase
{
    public function test_can_publish_variant_customer_pricing()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();
        $tax = factory(Tax::class)->create();

        $product = factory(Product::class)->create();

        $parent = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $draft = $parent->replicate();
        $draft->save();
        $draft->update([
            'draft_parent_id' => $parent->id,
            'drafted_at' => now(),
        ]);

        $pricing = new ProductCustomerPrice();
        $pricing->product_variant_id = $draft->id;
        $pricing->customer_group_id = $customerGroup->id;
        $pricing->tax_id = $tax->id;
        $pricing->price = 30;
        $pricing->save();

        $this->assertCount(1, $draft->customerPricing);
        $this->assertCount(0, $parent->customerPricing);

        (new PublishProductVariantCustomerPricing())->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(1, $parent->refresh()->customerPricing);
    }
}
