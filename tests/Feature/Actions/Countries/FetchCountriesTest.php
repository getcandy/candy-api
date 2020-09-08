<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Installer\Runners\CountryRunner;
use Tests\Feature\FeatureCase;

/**
 * @group countries
 */
class FetchCountriesTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        (new CountryRunner)->run();

        $response = $this->actingAs($user)->json('GET', 'countries');

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/countries', 'get');
    }

    public function test_can_paginate_results()
    {
        $user = $this->admin();

        (new CountryRunner)->run();

        $response = $this->actingAs($user)->json('GET', 'countries', [
            'per_page' => 5,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(5, $contents->data);
    }

    public function test_can_return_all_records()
    {
        $user = $this->admin();

        (new CountryRunner)->run();

        $response = $this->actingAs($user)->json('GET', 'countries', [
            'paginate' => false,
        ]);

        $contents = json_decode($response->content());

        $this->assertCount(Country::count(), $contents->data);
    }
}
