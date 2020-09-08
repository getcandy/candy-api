<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Tests\Feature\FeatureCase;

/**
 * @group customer-groups
 */
class CreateCustomerGroupTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'customer-groups', [
            'name' => 'Foo',
            'handle' => 'bar',
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/customer-groups', 'post');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $customerGroup = factory(CustomerGroup::class)->create();

        $response = $this->actingAs($user)->json('POST', 'customer-groups', [
            'name' => 'Foo',
            'handle' => $customerGroup->handle,
        ]);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/customer-groups', 'post');
    }
}
