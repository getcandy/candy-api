<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;

/**
 * @group customers
 */
class FetchCustomerTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $customer = factory(Customer::class)->create();

        $response = $this->actingAs($user)->json('GET', "customers/{$customer->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customers/{customerId}', 'get');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $customer = factory(Customer::class)->create();
        $customer->delete();

        $response = $this->actingAs($user)->json('GET', "customers/{$customer->encoded_id}");
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/customers/{customerId}', 'get');
    }
}
