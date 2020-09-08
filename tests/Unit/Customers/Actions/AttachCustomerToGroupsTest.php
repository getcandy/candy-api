<?php

namespace Tests\Unit\Customers\Actions;

use GetCandy\Api\Core\Customers\Actions\AttachCustomerToGroups;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Tests\TestCase;

/**
 * @group customer-groups-new
 */
class AttachCustomerToGroupsTest extends TestCase
{
    public function test_can_attach_customer_to_customer_groups()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();

        $this->assertCount(0, $customer->customerGroups()->get());

        $customerGroups = factory(CustomerGroup::class, 3)->create()->map(function ($group) {
            return $group->encoded_id;
        });

        (new AttachCustomerToGroups)->actingAs($user)->run([
            'customer_group_ids' => $customerGroups->toArray(),
            'customer_id' => $customer->encoded_id,
        ]);

        $this->assertCount(3, $customer->refresh()->customerGroups);
    }

    public function test_cant_attach_customer_to_same_groups_multiple_times()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();

        $this->assertCount(0, $customer->customerGroups()->get());

        $customerGroups = factory(CustomerGroup::class, 3)->create()->map(function ($group) {
            return $group->encoded_id;
        });

        (new AttachCustomerToGroups)->actingAs($user)->run([
            'customer_group_ids' => $customerGroups->toArray(),
            'customer_id' => $customer->encoded_id,
        ]);

        (new AttachCustomerToGroups)->actingAs($user)->run([
            'customer_group_ids' => $customerGroups->toArray(),
            'customer_id' => $customer->encoded_id,
        ]);

        $this->assertCount(3, $customer->refresh()->customerGroups);
    }
}
