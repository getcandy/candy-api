<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Drafting\Actions\DraftCategories;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftCategoriesTest extends TestCase
{
    public function test_can_draft_model_categories()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        factory(Category::class, 2)->create()->each(function ($category) use ($product) {
            $product->categories()->attach($category);
        });

        $this->assertCount(2, $product->categories);
        $this->assertCount(0, $draft->categories);

        (new DraftCategories)->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $draft->refresh()->categories);
    }
}
