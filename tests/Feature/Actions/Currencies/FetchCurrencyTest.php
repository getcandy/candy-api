<?php

namespace Tests\Feature\Actions\Currencies;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Currencies\Models\Currency;

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
