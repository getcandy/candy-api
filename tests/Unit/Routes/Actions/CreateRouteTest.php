<?php

namespace Tests\Unit\Languages\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Routes\Actions\CreateRoute;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * @group routes
 */
class CreateRouteTest extends TestCase
{
    public function test_can_create_route()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $route = (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $this->assertCount(1, $product->load('routes')->routes);
    }

    public function test_cant_create_duplicate_routes()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $this->expectException(ValidationException::class);

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'path' => 'bar-baz',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);

        $this->expectException(ValidationException::class);

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'path' => 'bar-baz',
            'element' => $product,
            'lang' => 'en',
            'default' => true,
        ]);
    }
}
