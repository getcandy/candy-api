<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Drafting\Actions\DraftCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\PublishCustomerGroups;

/**
 * @group drafting
 */
class PublishCustomerGroupsTest extends TestCase
{
    public function test_can_publish_model_customer_groups()
    {
        $user = $this->admin();

        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        factory(CustomerGroup::class, 2)->create()->each(function ($group) use ($draft) {
            $draft->customerGroups()->attach($group->id, [
                'purchasable' => true,
                'visible' => true,
            ]);
        });

        $this->assertCount(2, $draft->customerGroups);
        $this->assertCount(0, $parent->customerGroups);

        (new PublishCustomerGroups)->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $parent->refresh()->customerGroups);
    }
}
