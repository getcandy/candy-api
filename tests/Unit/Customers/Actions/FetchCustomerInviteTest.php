<?php

namespace Tests\Unit\Customers\Actions;

use GetCandy\Api\Core\Customers\Actions\FetchCustomerInvite;
use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Stubs\User;
use Tests\TestCase;

/**
 * @group customer-groups
 */
class FetchCustomerInviteTest extends TestCase
{
    public function test_can_fetch_customer_invite_by_encoded_id()
    {
        $customer = factory(Customer::class)->create();
        factory(User::class)->create(['customer_id' => $customer->id]);
        $invite = $customer->invites()->create(['email' => 'test@email.com']);

        $fetchedInvite = FetchCustomerInvite::run(['encoded_id' => $invite->encoded_id]);

        $this->assertEquals($invite->email, $fetchedInvite->email);
    }
}
