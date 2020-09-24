<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Addresses\Models\Address;
use Tests\Feature\FeatureCase;

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
            'default' => true,
        ]);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'default' => true,
        ]);
        $this->assertResponseValid($response, '/addresses/{addressId}', 'put');
    }
}
