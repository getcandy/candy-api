<?php

namespace Tests\Feature\Actions\Users;

use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class CreateUserTest extends FeatureCase
{
    public function test_can_run_action_as_controller_without_customer_id()
    {
        $attributes = [
            'firstname' => 'Customer',
            'lastname' => 'Unknown',
            'email' => 'test@email.com',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ];

        $response = $this->json('POST', 'users', $attributes);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/users', 'post');
    }

    public function test_can_run_action_as_controller_with_customer_id()
    {
        $customer = factory(Customer::class)->create();
        factory(User::class)->create(['customer_id' => $customer->id]);
        $customer->invites()->create(['email' => 'test@email.com']);

        $attributes = [
            'firstname' => 'Customer',
            'lastname' => 'Unknown',
            'email' => 'test@email.com',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
            'customer_id' => $customer->encoded_id,
        ];

        $response = $this->json('POST', 'users', $attributes);

        $response->assertStatus(201);
        $this->assertResponseValid($response, '/users', 'post');
    }

    public function test_bails_when_run_as_controller_without_invite()
    {
        $customer = factory(Customer::class)->create();

        $attributes = [
            'firstname' => 'Customer',
            'lastname' => 'Unknown',
            'email' => 'test@email.com',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
            'customer_id' => $customer->encoded_id,
        ];

        $response = $this->json('POST', 'users', $attributes);

        $response->assertStatus(403);
        $this->assertResponseValid($response, '/users', 'post');
    }
}
