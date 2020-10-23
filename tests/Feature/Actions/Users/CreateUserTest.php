<?php

namespace Tests\Feature\Actions\Users;

use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class CreateUserTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $attributes = [
            'firstname' => 'Customer',
            'lastname' => 'Unknown',
            'email' => 'test@email.com',
            'password' => 'supersecret'
        ];

        $response = $this->json('POST', "users", $attributes);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/users', 'post');
    }
}
