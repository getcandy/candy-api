<?php

namespace Tests\Feature\Actions\Customers;

use Tests\Feature\FeatureCase;

/**
 * @group customer-groups
 */
class FetchCustomerGroupsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('GET', 'customer-groups');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customer-groups', 'get');
    }
}
