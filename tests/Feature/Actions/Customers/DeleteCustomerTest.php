<?php

namespace Tests\Feature\Actions\Customers;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Customers\Models\Customer;

/**
 * @group customers
 */
class DeleteCustomerTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $customer = factory(Customer::class)->create();
        $response = $this->actingAs($user)->json('DELETE', "customers/{$customer->encoded_id}");
        $response->assertStatus(204);
        $this->assertResponseValid($response, '/customers/{customerId}', 'delete');
    }

    public function test_cant_delete_customer_that_has_users_assigned()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();

        $user->customer_id = $customer->id;
        $user->save();

        $response = $this->actingAs($user)->json('DELETE', "customers/{$customer->encoded_id}");

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/customers/{customerId}', 'delete');
    }
}
