<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Channels\Models\Channel;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchChannelTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $channel = factory(Channel::class)->create();

        $response = $this->actingAs($user)->json('GET', "channels/{$channel->encoded_id}");

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/channels/{channelId}', 'get');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $channel = factory(Channel::class)->create();
        $channel->delete();

        $response = $this->actingAs($user)->json('GET', "channels/{$channel->encoded_id}");
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/channels/{channelId}', 'get');
    }
}
