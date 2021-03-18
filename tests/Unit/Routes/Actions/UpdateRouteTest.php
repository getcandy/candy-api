<?php

namespace Tests\Unit\Routes\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Products\Models\Product;
use Illuminate\Validation\ValidationException;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Routes\Actions\UpdateRoute;

/**
 * @group routes
 */
class UpdateRouteTest extends TestCase
{
    public function test_can_update_route()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $language = factory(Language::class)->create();

        $route = factory(Route::class)->create();

        $route = (new UpdateRoute)->actingAs($user)->run([
            'encoded_id' => $route->encoded_id,
            'slug' => 'foo-bar',
            'language_id' => $language->encoded_id,
            'default' => true,
        ]);

        $this->assertEquals('foo-bar', $route->slug);
    }
}
