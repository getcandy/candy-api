<?php

namespace Tests\Feature\Actions\Routes;

use Tests\Feature\FeatureCase;

/**
 * @group routes
 */
class FetchRoutesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'routes');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/routes', 'get');
    }
}
