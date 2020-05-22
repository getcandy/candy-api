<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Channels\Models\Channel;

/**
 * @group feature
 */
class ChannelControllerTest extends FeatureCase
{
    public function test_can_list_all_channels()
    {
        $user = $this->admin();
        $response = $this->actingAs($user)->json('GET', 'channels');
        $response->assertStatus(200);
        $this->assertResponseValid($response, '/channels');
    }

    public function test_can_show_a_channel_by_id()
    {
        $user = $this->admin();
        $channelId = Channel::first()->encodedId();
        $response = $this->actingAs($user)->json('GET', "channels/{$channelId}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/channels/{channelId}');
    }

    public function test_missing_shows_appropriate_response()
    {
        $user = $this->admin();
        $channelId = Channel::first()->encodedId();
        $response = $this->actingAs($user)->json('GET', "channels/123123123");

        $response->assertStatus(404);

        $this->assertResponseValid($response, '/channels/{channelId}');
    }

    public function test_can_update_a_channel()
    {
        $user = $this->admin();
        $channelId = Channel::first()->encodedId();
        $response = $this->actingAs($user)->json('PUT', "channels/{$channelId}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/channels/{channelId}', 'put');
    }

    public function test_validation_works_on_update()
    {
        $existing = Channel::first();
        $new = Channel::forceCreate([
            'name' => 'Test',
            'handle' => 'test'
        ]);

        $user = $this->admin();
        $channelId = Channel::first()->encodedId();
        $response = $this->actingAs($user)->json('PUT', "channels/{$new->id}", [
            'handle' => $existing->handle,
        ]);

        $response->assertStatus(422);

        $this->assertResponseValid($response, '/channels/{channelId}', 'put');
    }

    public function test_can_delete_a_channel()
    {
        $user = $this->admin();
        $channel = Channel::forceCreate([
            'name' => 'Test Channel',
            'handle' => 'test-channel',
        ]);
        $response = $this->actingAs($user)->json('DELETE', "channels/{$channel->encodedId()}");
        $response->assertStatus(204);
        $this->assertResponseValid($response, '/channels/{channelId}', 'delete');
    }
}
