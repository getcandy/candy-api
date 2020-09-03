<?php

namespace Tests\Feature\Actions\Addresses;

use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class CreateChannelTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'channels', [
            'name' => 'Foo',
            'handle' => 'bar',
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/channels', 'post');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'channels', []);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/channels', 'post');
    }
}
