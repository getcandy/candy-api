<?php

namespace Tests\Feature\Actions\Users;

use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class FetchUsersTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        factory(User::class, 25)->create();

        $response = $this->actingAs($user)->json('GET', 'users');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/users', 'get');
    }

    public function test_can_validate_request()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->json('GET', 'users');

        $response->assertStatus(403);
    }
}
