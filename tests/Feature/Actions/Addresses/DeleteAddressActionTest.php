<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Addresses\Models\Address;
use Tests\Feature\FeatureCase;

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
        $this->assertResponseValid($response, '/addresses/{addressId}', 'delete');
    }
}
