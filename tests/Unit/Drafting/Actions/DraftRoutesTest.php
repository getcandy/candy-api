<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Drafting\Actions\DraftRoutes;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Routes\Models\Route;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftRoutesTest extends TestCase
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

        factory(Route::class, 2)->create([
            'element_type' => Product::class,
            'element_id' => $product->id,
        ]);

        $this->assertCount(2, $product->routes);
        $this->assertCount(0, $draft->routes);

        (new DraftRoutes())->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $draft->refresh()->routes);
    }
}
