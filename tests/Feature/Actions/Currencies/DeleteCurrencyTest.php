<?php

namespace Tests\Feature\Actions\Currencies;

use GetCandy\Api\Core\Currencies\Models\Currency;
use Tests\Feature\FeatureCase;

/**
 * @group currencies
 */
class DeleteCurrencyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $currency = factory(Currency::class)->create([
            'default' => false,
        ]);
        $response = $this->actingAs($user)->json('DELETE', "currencies/{$currency->encoded_id}");

        $response->assertStatus(204);
        $this->assertResponseValid($response, '/currencies/{currencyId}', 'delete');
    }

    public function test_cant_delete_default_currency()
    {
        $user = $this->admin();

        $currency = factory(Currency::class)->create([
            'default' => true,
        ]);

        $response = $this->actingAs($user)->json('DELETE', "currencies/{$currency->encoded_id}");

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/currencies/{currencyId}', 'delete');
    }
}
