<?php

namespace Tests\Unit\Customers\Actions;

use Tests\TestCase;
use Tests\Stubs\User;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Actions\AttachUserToCustomer;
use GetCandy\Api\Core\Customers\Actions\AttachCustomerToGroups;

/**
 * @group customer-groups-new
 */
class AttachCustomerToGroupsTest extends TestCase
{
    public function test_can_attach_customer_to_customer_groups()
    {
        $customer = factory(Customer::class)->create();

        $this->assertCount(0, $customer->customerGroups()->get());

        AttachCustomerToGroups::run([
            'customer_group_ids' => [1,2,3]
        ]);

    }
}
