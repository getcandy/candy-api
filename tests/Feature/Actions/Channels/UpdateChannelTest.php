<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Channels\Models\Channel;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class UpdateChannelTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $channel = factory(Channel::class)->create();

        $response = $this->actingAs($user)->json('PUT', "channels/{$channel->encoded_id}", [
            'name' => 'Foo',
            'handle' => 'bar',
        ]);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/channels/{channelId}', 'put');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $channelA = factory(Channel::class)->create();
        $channelB = factory(Channel::class)->create();

        $response = $this->actingAs($user)->json('PUT', "channels/{$channelA->encoded_id}", [
            'name' => $channelB->name,
            'handle' => $channelB->handle,
        ]);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/channels/{channelId}', 'put');
    }
}
