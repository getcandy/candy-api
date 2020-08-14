<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Addresses\Models\Address;

/**
 * @group addresses
 */
class DeleteAddressActionTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $user->addresses()->create(
            factory(Address::class)->make()->toArray()
        );
        $addressId = $user->addresses->first()->encoded_id;
        $response = $this->actingAs($user)->json('DELETE', "addresses/{$addressId}");
        $response->assertStatus(204);
        $this->markTestIncomplete(
            'This test requires open api spec validation'
        );
    }
}
