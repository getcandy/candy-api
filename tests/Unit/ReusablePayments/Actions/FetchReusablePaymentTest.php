<?php

namespace Tests\Unit\ReusablePayments\Actions;

use GetCandy\Api\Core\ReusablePayments\Actions\FetchReusablePayment;
use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use Tests\Feature\FeatureCase;

/**
 * @group reusable-payments
 */
class FetchReusablePaymentTest extends FeatureCase
{
    public function test_can_fetch_record_by_numeric_id()
    {
        $user = $this->admin();
        $reusablePayment = factory(ReusablePayment::class)->create(['user_id' => $user->id]);

        $record = (new FetchReusablePayment)->actingAs($user)->run([
            'id' => $reusablePayment->id,
        ]);

        $this->assertEquals($reusablePayment->id, $record->id);
    }

    public function test_can_fetch_record_by_encoded_id()
    {
        $user = $this->admin();
        $reusablePayment = factory(ReusablePayment::class)->create(['user_id' => $user->id]);

        $record = (new FetchReusablePayment)->actingAs($user)->run([
            'encoded_id' => $reusablePayment->encoded_id,
        ]);

        $this->assertEquals($reusablePayment->id, $record->id);
    }
}
