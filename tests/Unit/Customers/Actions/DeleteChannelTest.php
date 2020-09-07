<?php

namespace Tests\Unit\CustomerGroups\Actions;

use GetCandy\Api\Core\Customers\Actions\DeleteCustomerGroup;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Exceptions\DefaultRecordRequiredException;
use Tests\Feature\FeatureCase;

/**
 * @group customer-groups
 */
class DeleteCustomerGroupTest extends FeatureCase
{
    public function test_exception_thrown_when_deleting_default_record()
    {
        $user = $this->admin();
        $customerGroup = factory(CustomerGroup::class)->create();
        $customerGroup->default = true;
        $customerGroup->save();

        $this->expectException(DefaultRecordRequiredException::class);

        (new DeleteCustomerGroup)->actingAs($user)->run([
            'encoded_id' => $customerGroup->encoded_id,
        ]);
    }
}
