<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;

/**
 * @group addresses
 */
class UpdateAddressActionTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $user->addresses()->create(
            factory(Address::class)->make()->toArray()
        );
        $address = $user->addresses->first();
        $address->update([
            'default' => false,
        ]);

        $response = $this->actingAs($user)->json('PUT', "addresses/{$address->encoded_id}", [
            'default' => true
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'default' => true
        ]);
        $this->markTestIncomplete(
            'This test requires open api spec validation'
        );
    }
}
