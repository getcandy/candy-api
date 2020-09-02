<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Channels\Models\Channel;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchChannelsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        factory(Channel::class, 10)->create();

        $response = $this->actingAs($user)->json('GET', 'channels');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/channels', 'get');
    }

    public function test_can_paginate_results()
    {
        $user = $this->admin();
        factory(Channel::class, 25)->create();

        $response = $this->actingAs($user)->json('GET', 'channels', [
            'per_page' => 5,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(5, $contents->data);
    }

    public function test_can_return_all_records()
    {
        $user = $this->admin();
        factory(Channel::class, 250)->create();

        $response = $this->actingAs($user)->json('GET', 'channels', [
            'paginate' => false,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(Channel::count(), $contents->data);
    }
}
