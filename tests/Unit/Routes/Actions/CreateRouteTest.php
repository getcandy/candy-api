<?php

namespace Tests\Unit\Routes\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use Illuminate\Validation\ValidationException;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Routes\Actions\CreateRoute;

/**
 * @group routes
 */
class CreateRouteTest extends TestCase
{
    public function test_can_create_route()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $language = factory(Language::class)->create();

        $route = (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element_id' => $product->encoded_id,
            'element_type' => get_class($product),
            'language_id' => $language->encoded_id,
            'default' => true,
            'redirect' => false,
        ]);

        $this->assertCount(1, $product->load('routes')->routes);
    }

    public function test_cant_create_duplicate_routes()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $language = factory(Language::class)->create();

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element_id' => $product->encoded_id,
            'element_type' => get_class($product),
            'language_id' => $language->encoded_id,
            'default' => true,
            'redirect' => false,
        ]);

        $this->expectException(ValidationException::class);

        (new CreateRoute)->actingAs($user)->run([
            'slug' => 'foo-bar',
            'element_id' => $product->encoded_id,
            'element_type' => get_class($product),
            'language_id' => $language->encoded_id,
            'default' => true,
            'redirect' => false,
        ]);
    }
}
