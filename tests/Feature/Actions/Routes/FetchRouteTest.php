<?php

namespace Tests\Feature\Actions\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
use Tests\Feature\FeatureCase;

/**
 * @group routes
 */
class FetchRouteTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $route = factory(Route::class)->create();

        $response = $this->actingAs($user)->json('GET', "routes/{$route->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/routes/{routeId}', 'get');
    }
}
