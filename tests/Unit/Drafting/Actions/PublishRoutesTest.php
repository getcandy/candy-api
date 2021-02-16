<?php

namespace Tests\Unit\Drafting\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Drafting\Actions\PublishRoutes;

/**
 * @group drafting
 */
class PublishRoutesTest extends TestCase
{
    public function test_can_publish_routes()
    {
        $user = $this->admin();

        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        factory(Route::class, 2)->create([
            'element_type' => Product::class,
            'element_id' => $draft->id,
        ]);

        $this->assertCount(2, $draft->routes);
        $this->assertCount(0, $parent->routes);

        (new PublishRoutes)->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $parent->refresh()->routes);
    }
}
