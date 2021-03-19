<?php

namespace Tests\Feature\Actions\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
use Tests\Feature\FeatureCase;

/**
 * @group fail
 */
class SearchRouteTest extends FeatureCase
{
    public function test_validation_runs()
    {
        $user = $this->admin();

        $route = factory(Route::class)->create();

        $response = $this->actingAs($user)->json('GET', 'routes/search', [
            'slug' => $route->slug,
            'element_type' => $route->element_type,
        ]);

        $response->assertStatus(422);

        $this->assertResponseValid($response, '/routes/search', 'get');
    }

    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $route = factory(Route::class)->create();

        $response = $this->actingAs($user)->json('GET', 'routes/search', [
            'slug' => $route->slug,
            'element_type' => $route->element_type,
            'language_code' => $route->language->code,
        ]);

        $response->assertStatus(200);

        $this->assertEquals($route->slug, json_decode($response->content())->data->slug);

        $this->assertResponseValid($response, '/routes/search', 'get');
    }
}
