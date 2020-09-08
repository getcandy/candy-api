<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group customer-groups
 */
class AttachCustomerToGroupsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();
        $userToAttach = factory(User::class)->create();

        $response = $this->actingAs($user)->json('POST', "customers/{$customer->encoded_id}/users", [
            'user_id' => $userToAttach->encoded_id,
        ]);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/customers/{customerId}/users', 'post');
    }
}
