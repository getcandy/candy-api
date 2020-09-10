<?php

namespace Tests\Feature\Actions\Currencies;

use Tests\Feature\FeatureCase;

/**
 * @group currencies
 */
class CreateCurrencyTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'currencies', [
            'name' => 'Schmeckle',
            'code' => 'SHM',
            'enabled' => 1,
            'format' => '{price} Schmeckles',
            'exchange_rate' => '.5',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => 1,
        ]);

        $response->assertStatus(201);

        $this->assertResponseValid($response, '/currencies', 'post');
    }

    public function test_can_validate_request()
    {
        $user = $this->admin();

        $response = $this->actingAs($user)->json('POST', 'currencies', []);

        $response->assertStatus(422);
        $this->assertResponseValid($response, '/currencies', 'post');
    }
}
