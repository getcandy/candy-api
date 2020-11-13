<?php

namespace Tests\Feature\Actions\Addresses;

use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Customers\Models\Customer;
use Tests\Feature\FeatureCase;

/**
 * @group addresses
 */
class CreateAddressActionTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $country = factory(Country::class)->create();

        $attributes = [
            'salutation' => 'mr',
            'firstname' => 'Alec',
            'lastname' => 'Ritson',
            'company_name' => 'Candy Inc.',
            'email' => 'candy@candy.io',
            'phone' => '1111111',
            'address' => '1 Candy Lane',
            'address_two' => 'Candy Street',
            'address_three' => 'Candy Lane',
            'city' => 'Candy City',
            'state' => 'Candy State',
            'postal_code' => 'CAN2 1DY',
            'country_id' => $country->encoded_id,
            'shipping' => 0,
            'billing' => 1,
            'default' => 1,
            'last_used_at' => now()->toIso8601String(),
            'delivery_instructions' => 'Leave outside',
            'meta' => [
                'foo' => 'bar',
            ],
        ];

        $response = $this->actingAs($user)->json('POST', 'addresses', $attributes);
        $response->assertStatus(201);
        $this->assertResponseValid($response, '/addresses', 'post');
    }

    public function test_can_run_and_attach_to_customer()
    {
        $user = $this->admin();

        $country = factory(Country::class)->create();
        $customer = factory(Customer::class)->create();

        $attributes = [
            'salutation' => 'mr',
            'firstname' => 'Alec',
            'lastname' => 'Ritson',
            'company_name' => 'Candy Inc.',
            'email' => 'candy@candy.io',
            'phone' => '1111111',
            'address' => '1 Candy Lane',
            'address_two' => 'Candy Street',
            'address_three' => 'Candy Lane',
            'city' => 'Candy City',
            'state' => 'Candy State',
            'postal_code' => 'CAN2 1DY',
            'country_id' => $country->encoded_id,
            'shipping' => 0,
            'billing' => 1,
            'default' => 1,
            'last_used_at' => now()->toIso8601String(),
            'delivery_instructions' => 'Leave outside',
            'meta' => [
                'foo' => 'bar',
            ],
            'customer_id' => $customer->encoded_id,
        ];

        $response = $this->actingAs($user)->json('POST', 'addresses', $attributes);
        $response->assertStatus(201);
        $this->assertResponseValid($response, '/addresses', 'post');
    }
}
