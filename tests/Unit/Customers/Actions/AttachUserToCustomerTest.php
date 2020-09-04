<?php

namespace Tests\Unit\Customers\Actions;

use GetCandy\Api\Core\Customers\Actions\AttachUserToCustomer;
use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group customers_unit
 */
class AttachUserToCustomerTest extends FeatureCase
{
    public function test_can_attach_user_to_customer_record()
    {
        $user = $this->admin();

        $customer = factory(Customer::class)->create();
        $userToAttach = factory(User::class)->create();

        $this->assertNull($userToAttach->customer_id);

        (new AttachUserToCustomer)->actingAs($user)->run([
            'encoded_id' => $customer->encoded_id,
            'user_id' => $userToAttach->encoded_id,
        ]);

        $userToAttach->refresh();

        $this->assertEquals($customer->id, $userToAttach->customer_id);
    }
}
