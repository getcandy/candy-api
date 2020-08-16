<?php

namespace Tests\Unit\Addresses\Actions;

use GetCandy\Api\Core\Addresses\Actions\FetchAddressAction;
use GetCandy\Api\Core\Addresses\Models\Address;
use Tests\Stubs\User;
use Tests\TestCase;

/**
 * @group addresses
 */
class FetchAddressActionTest extends TestCase
{
    public function test_can_retrieve_address_by_id()
    {
        $user = factory(User::class)->create();

        $user->addresses()->create(
            factory(Address::class)->make()->toArray()
        );

        $address = $user->addresses->first();

        $result = (new FetchAddressAction)->actingAs($user)->run([
            'encoded_id' => $address->encoded_id,
        ]);

        $this->assertEquals($address->id, $result->id);

        $result = (new FetchAddressAction)->actingAs($user)->run([
            'id' => $address->id,
        ]);

        $this->assertEquals($address->id, $result->id);
    }

    public function test_user_cannot_get_other_users_address()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $userA->addresses()->create(
            factory(Address::class)->make()->toArray()
        );

        $address = $userA->addresses->first();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $result = (new FetchAddressAction)->actingAs($userB)->run([
            'encoded_id' => $address->encoded_id,
        ]);
    }
}
