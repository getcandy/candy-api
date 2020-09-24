<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Tests\Feature\FeatureCase;

/**
 * @group customer-groups
 */
class FetchCustomerGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $customer = factory(CustomerGroup::class)->create();

        $response = $this->actingAs($user)->json('GET', "customer-groups/{$customer->encoded_id}");

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customer-groups/{customerGroupId}', 'get');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $customer = factory(CustomerGroup::class)->create();
        $customer->delete();

        $response = $this->actingAs($user)->json('GET', "customer-groups/{$customer->encoded_id}");
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/customer-groups/{customerGroupId}', 'get');
    }
}
