<?php

namespace Tests\Unit\ReusablePayments\Actions;

use GetCandy\Api\Core\ReusablePayments\Actions\CreateReusablePayment;
use Tests\Feature\FeatureCase;

/**
 * @group reusable-payments
 */
class CreateReusablePaymentTest extends FeatureCase
{
    public function test_can_run_action_successfully()
    {
        $user = $this->admin();

        (new CreateReusablePayment)->actingAs($user)->run([
            'user_id' => $user->id,
            'type' => 'visa',
            'provider' => 'sagepay',
            'last_four' => '4444',
            'token' => 'tokenvalue',
            'expires_at' => \Carbon\Carbon::createFromFormat('my', 0322)->endOfMonth(),
        ]);

        $this->assertDatabaseHas('reusable_payments', [
            'user_id' => $user->id,
            'type' => 'visa',
        ]);
    }

    public function test_can_validate_action()
    {
        $user = $this->admin();

        $this->expectException('\Illuminate\Validation\ValidationException');

        (new CreateReusablePayment)->actingAs($user)->run([]);
    }
}
