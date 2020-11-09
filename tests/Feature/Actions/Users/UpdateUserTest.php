<?php

namespace Tests\Feature\Actions\Users;

use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class UpdateUserTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = factory(User::class)->create([
            'email' => 'test@email.com',
            'name' => 'Customer',
        ]);
        $customer = factory(Customer::class)->create();
        $customer->users()->save($user);

        $attributes = [
            'email' => 'test2@email.com',
        ];

        $response = $this->actingAs($user)->json('PUT', "users/{$user->encoded_id}", $attributes);

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/users/{userId}', 'put');
    }
}
