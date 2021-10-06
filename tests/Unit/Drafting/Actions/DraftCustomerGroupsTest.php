<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Drafting\Actions\DraftCustomerGroups;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftCustomerGroupsTest extends TestCase
{
    public function test_can_draft_model_channels()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        factory(CustomerGroup::class, 2)->create()->each(function ($group) use ($product) {
            $product->customerGroups()->attach($group->id, [
                'purchasable' => true,
                'visible' => true,
            ]);
        });

        $this->assertCount(2, $product->customerGroups);
        $this->assertCount(0, $draft->customerGroups);

        (new DraftCustomerGroups())->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $draft->refresh()->customerGroups);
    }
}
