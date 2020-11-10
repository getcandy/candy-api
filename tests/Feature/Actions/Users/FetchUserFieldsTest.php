<?php

namespace Tests\Feature\Actions\Users;

use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchUserFieldsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'users/fields');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/users/fields', 'get');
    }
}
