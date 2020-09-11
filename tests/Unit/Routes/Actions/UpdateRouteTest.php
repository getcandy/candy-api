<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Routes\Actions\UpdateRoute;
use GetCandy\Api\Core\Routes\Models\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * @group routes
 */
class UpdateRouteTest extends TestCase
{
    public function test_can_update_route()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $route = factory(Route::class)->create();

        $route = (new UpdateRoute)->actingAs($user)->run([
            'encoded_id' => $route->encoded_id,
            'slug' => 'foo-bar',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $this->assertEquals('foo-bar', $route->slug);
    }

    public function test_can_update_slug_and_path_to_the_same_values()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $route = factory(Route::class)->create([
            'path' => 'foo',
            'slug' => 'bar',
        ]);

        (new UpdateRoute)->actingAs($user)->run([
            'encoded_id' => $route->encoded_id,
            'slug' => 'bar',
            'path' => 'foo',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $product = factory(Product::class)->create();

        $route = factory(Route::class)->create([
            'slug' => 'bar',
        ]);

        (new UpdateRoute)->actingAs($user)->run([
            'encoded_id' => $route->encoded_id,
            'slug' => 'bar',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $this->assertEquals('bar', $route->slug);
    }

    /**
     * @group bah
     */
    public function test_cant_update_route_to_another_resources_values()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $route = factory(Route::class)->create([
            'slug' => 'bar',
            'path' => null,
        ]);

        factory(Route::class)->create([
            'slug' => 'foo',
            'path' => null,
        ]);

        $this->expectException(ValidationException::class);

        $route = (new UpdateRoute)->actingAs($user)->run([
            'encoded_id' => $route->encoded_id,
            'slug' => 'foo',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);
    }
}
