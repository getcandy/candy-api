<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;

/**
 * @group customers
 */
class UpdateCustomerTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();

        $newFields = factory(Customer::class)->make()->toArray();

        $response = $this->actingAs($user)->json('PUT', "customers/{$customer->encoded_id}", $newFields);

        $response->assertJsonFragment($newFields);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customers/{customerId}', 'put');
    }
}
