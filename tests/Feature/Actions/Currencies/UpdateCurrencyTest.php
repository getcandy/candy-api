<?php

namespace Tests\Feature\Actions\Currencies;

use GetCandy\Api\Core\Currencies\Models\Currency;
use Tests\Feature\FeatureCase;

/**
 * @group currencies
 */
class UpdateCurrencyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $currency = factory(Currency::class)->create();

        $response = $this->actingAs($user)->json('PUT', "currencies/{$currency->encoded_id}", [
            'name' => 'Schmeckle',
            'code' => 'SHM',
            'enabled' => 1,
            'format' => '{price} Schmeckles',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => 1,
        ]);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/currencies/{currencyId}', 'put');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $currencyA = factory(Currency::class)->create();
        $currencyB = factory(Currency::class)->create();

        $response = $this->actingAs($user)->json('PUT', "currencies/{$currencyA->encoded_id}", [
            'name' => $currencyA->name,
            'code' => $currencyB->code,
        ]);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/currencies/{currencyId}', 'put');
    }
}
