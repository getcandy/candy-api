<?php

namespace Tests\Feature\Actions\ReusablePayments;

use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class DeleteReusablePaymentTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $reusablePayment = factory(ReusablePayment::class)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->json('DELETE', "reusable-payments/{$reusablePayment->encoded_id}");

        $response->assertStatus(204);
    }

    public function test_can_validate_request()
    {
        $adminUser = $this->admin();

        $user = factory(User::class)->create();

        $reusablePayment = factory(ReusablePayment::class)->create(['user_id' => $adminUser->id]);

        $response = $this->actingAs($user)->json('DELETE', "reusable-payments/{$reusablePayment->encoded_id}");

        $response->assertStatus(403);
    }
}
