<?php

namespace Tests\Unit\Customers\Actions;

use GetCandy\Api\Core\Customers\Actions\CreateCustomerInvite;
use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Stubs\User;
use Tests\TestCase;

/**
 * @group customer-groups
 */
class CreateCustomerInviteTest extends TestCase
{
    public function test_can_create_customer_invite()
    {
        $customer = factory(Customer::class)->create();
        $user = factory(User::class)->create(['customer_id' => $customer->id]);

        $invite = (new CreateCustomerInvite)
            ->actingAs($user)->run([
                'encoded_id' => $customer->encoded_id,
                'email' => 'test@email.com',
            ]);

        $this->assertEquals('test@email.com', $invite->email);
    }
}
