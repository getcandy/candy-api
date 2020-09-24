<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Tests\Feature\FeatureCase;

/**
 * @group customer-groups
 */
class DeleteCustomerGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();
        $customerGroup->update([
            'default' => false,
        ]);
        $response = $this->actingAs($user)->json('DELETE', "customer-groups/{$customerGroup->encoded_id}");
        $response->assertStatus(204);
        $this->assertResponseValid($response, '/customer-groups/{customerGroupId}', 'delete');
    }
}
