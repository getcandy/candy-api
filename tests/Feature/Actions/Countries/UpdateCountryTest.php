<?php

namespace Tests\Feature\Actions\Customers;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Installer\Runners\CountryRunner;
use Tests\Feature\FeatureCase;

/**
 * @group countries
 */
class UpdateCountryTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        (new CountryRunner)->run();

        $country = Country::first();

        $country->update([
            'preferred' => false,
            'enabled' => false,
        ]);

        $this->assertFalse((bool) $country->preferred);
        $this->assertFalse((bool) $country->enabled);

        $response = $this->actingAs($user)->json('PUT', "countries/{$country->encoded_id}", [
            'preferred' => true,
            'enabled' => true,
        ]);

        $countryResponse = json_decode($response->content());

        $this->assertTrue((bool) $countryResponse->preferred);
        $this->assertTrue((bool) $countryResponse->enabled);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/countries/{countryId}', 'put');
    }

    public function test_country_isnt_updated_with_omitted_values()
    {
        $user = $this->admin();

        (new CountryRunner)->run();

        $country = Country::first();

        $country->update([
            'preferred' => true,
            'enabled' => true,
        ]);

        $this->assertTrue((bool) $country->preferred);
        $this->assertTrue((bool) $country->enabled);

        $response = $this->actingAs($user)->json('PUT', "countries/{$country->encoded_id}");

        $countryResponse = json_decode($response->content());

        $this->assertTrue((bool) $countryResponse->preferred);
        $this->assertTrue((bool) $countryResponse->enabled);

        $response->assertStatus(200);

        $this->assertResponseValid($response, '/countries/{countryId}', 'put');
    }
}
