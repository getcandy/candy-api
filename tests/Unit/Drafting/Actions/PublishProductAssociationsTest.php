<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Drafting\Actions\PublishProductAssociations;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductAssociation;
use Tests\TestCase;

/**
 * @group drafting
 */
class PublishProductAssociationsTest extends TestCase
{
    public function test_can_publish_product_associations()
    {
        $user = $this->admin();
        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        $group = factory(AssociationGroup::class)->create();

        factory(Product::class, 15)->create()->each(function ($p) use ($group, $draft) {
            $assoc = new ProductAssociation();
            $assoc->group()->associate($group);
            $assoc->association()->associate($p);
            $assoc->parent()->associate($draft);
            $assoc->save();
        });

        $this->assertCount(15, $draft->associations);
        $this->assertCount(0, $parent->associations);

        (new PublishProductAssociations())->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(15, $parent->refresh()->associations);
    }
}
