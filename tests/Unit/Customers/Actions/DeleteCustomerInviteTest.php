<?php

namespace Tests\Unit\Customers\Actions;

use GetCandy\Api\Core\Customers\Actions\DeleteCustomerInvite;
use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Stubs\User;
use Tests\TestCase;

/**
 * @group customer-groups
 */
class DeleteCustomerInviteTest extends TestCase
{
    public function test_can_delete_customer_invite()
    {
        $customer = factory(Customer::class)->create();
        factory(User::class)->create(['customer_id' => $customer->id]);
        $invite = $customer->invites()->create(['email' => 'test@email.com']);

        $deletedInvite = DeleteCustomerInvite::run(['encoded_id' => $invite->encoded_id]);

        $this->assertTrue($deletedInvite);
    }
}
