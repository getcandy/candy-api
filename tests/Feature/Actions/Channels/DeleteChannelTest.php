<?php

namespace Tests\Feature\Actions\Channels;

use GetCandy\Api\Core\Channels\Models\Channel;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class DeleteChannelTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $channel = factory(Channel::class)->create();
        $response = $this->actingAs($user)->json('DELETE', "channels/{$channel->encoded_id}");
        $response->assertStatus(204);
        $this->assertResponseValid($response, '/channels/{channelId}', 'delete');
    }

    public function test_cant_delete_default_channel()
    {
        $user = $this->admin();

        $channel = factory(Channel::class)->create();
        $channel->update([
            'default' => true,
        ]);
        $channel = $channel->refresh();

        $response = $this->actingAs($user)->json('DELETE', "channels/{$channel->encoded_id}");

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/channels/{channelId}', 'delete');
    }
}
