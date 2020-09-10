<?php

namespace Tests\Feature\Actions\Currencies;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Currencies\Models\Currency;

/**
 * @group channels
 */
class FetchCurrenciesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        factory(Currency::class, 10)->create();

        $response = $this->actingAs($user)->json('GET', 'currencies');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/currencies', 'get');
    }

    public function test_can_paginate_results()
    {
        $user = $this->admin();
        factory(Currency::class, 25)->create();

        $response = $this->actingAs($user)->json('GET', 'currencies', [
            'per_page' => 5,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(5, $contents->data);
    }

    public function test_can_return_all_records()
    {
        $user = $this->admin();
        factory(Currency::class, 250)->create();

        $response = $this->actingAs($user)->json('GET', 'currencies', [
            'paginate' => false,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(Currency::count(), $contents->data);
    }
}
