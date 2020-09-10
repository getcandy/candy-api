<?php

namespace Tests\Feature\Actions\Currencies;

use GetCandy\Api\Core\Currencies\Models\Currency;
use Tests\Feature\FeatureCase;

/**
 * @group currencies
 */
class FetchCurrencyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $currency = factory(Currency::class)->create();

        $response = $this->actingAs($user)->json('GET', "currencies/{$currency->encoded_id}");

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/currencies/{currencyId}', 'get');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $currency = factory(Currency::class)->create();
        $currency->delete();

        $response = $this->actingAs($user)->json('GET', "currencies/{$currency->encoded_id}");
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/currencies/{currencyId}', 'get');
    }
}
