<?php

namespace Tests\Feature\Actions\Languages;

use GetCandy\Api\Core\Routes\Models\Route;
use Tests\Feature\FeatureCase;

/**
 * @group routes
 */
class UpdateRouteTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $route = factory(Route::class)->create();

        $response = $this->actingAs($user)->json('put', "routes/{$route->encoded_id}", [
            'slug' => 'product-slug',
            'path' => 'products',
            'locale' => 'en',
            'default' => true,
            'description' => 'Main product route',
        ]);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/routes/{routeId}', 'put');
    }
}
