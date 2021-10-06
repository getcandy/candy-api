<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Drafting\Actions\DraftProductAssociations;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductAssociation;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftProductAssociationsTest extends TestCase
{
    public function test_can_draft_model_product_associations()
    {
        $user = $this->admin();
        $product = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        $group = factory(AssociationGroup::class)->create();

        factory(Product::class, 15)->create()->each(function ($p) use ($group, $product) {
            $assoc = new ProductAssociation();
            $assoc->group()->associate($group);
            $assoc->association()->associate($p);
            $assoc->parent()->associate($product);
            $assoc->save();
        });

        $this->assertCount(15, $product->associations);
        $this->assertCount(0, $draft->associations);

        (new DraftProductAssociations())->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(15, $draft->refresh()->associations);
    }
}
