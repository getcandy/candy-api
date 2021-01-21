<?php

namespace Tests\Feature\Actions\Users;

use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class FetchUserTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', "users/{$user->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/users/{userId}', 'get');
    }

    public function test_can_validate_request()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('GET', "users/{$user->encoded_id}");

        $response->assertStatus(403);
    }
}
