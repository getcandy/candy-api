<?php

namespace Tests\Unit\Addresses\Services;

use Tests\TestCase;
use Tests\Stubs\User;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Addresses\Services\AddressService;

/**
 * @group addresses
 */
class AddressServiceTest extends TestCase
{
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(AddressService::class);
    }

    public function test_can_make_address_default()
    {
        $address = Address::first();

        $this->assertNotDefaultAddress($address);

        $this->service->makeDefault($address->encode($address->id));

        $address = Address::findOrFail($address->id);
        $this->assertDefaultAddress($address);
    }

    public function test_can_undefault_other_addresses_when_make_address_default()
    {
        $originalAddress = Address::first();
        $newAddress = $originalAddress->replicate();
        $newAddress->save();

        $originalAddress->default = true;
        $originalAddress->save();

        $this->assertDefaultAddress($originalAddress);
        $this->assertNotDefaultAddress($newAddress);

        $this->service->makeDefault($newAddress->encode($newAddress->id));

        $originalAddress = Address::findOrFail($originalAddress->id);
        $newAddress = Address::findOrFail($newAddress->id);
        $this->assertNotDefaultAddress($originalAddress);
        $this->assertDefaultAddress($newAddress);
    }

    public function test_can_undefault_only_addresses_of_same_type()
    {
        $billingAddress = Address::where('billing', 1)->first();
        $shippingAddress = Address::where('shipping', 1)->first();
        $newBillingAddress = $billingAddress->replicate();
        $newBillingAddress->save();

        $billingAddress->default = true;
        $billingAddress->save();
        $shippingAddress->default = true;
        $shippingAddress->save();

        $this->assertDefaultAddress($billingAddress);
        $this->assertDefaultAddress($shippingAddress);
        $this->assertNotDefaultAddress($newBillingAddress);

        $this->service->makeDefault($newBillingAddress->encode($newBillingAddress->id));

        $billingAddress = Address::findOrFail($billingAddress->id);
        $shippingAddress = Address::findOrFail($shippingAddress->id);
        $newBillingAddress = Address::findOrFail($newBillingAddress->id);
        $this->assertNotDefaultAddress($billingAddress);
        $this->assertDefaultAddress($shippingAddress);
        $this->assertDefaultAddress($newBillingAddress);
    }

    public function test_can_remove_default_from_address()
    {
        $address = Address::first();
        $address->default = true;
        $address->save();

        $this->assertDefaultAddress($address);

        $this->service->removeDefault($address->encode($address->id));

        $address = Address::findOrFail($address->id);
        $this->assertNotDefaultAddress($address);
    }

    public function test_can_create_address_for_user()
    {
        $user = factory(User::class)->create();
        $country = factory(Country::class)->create();
        $data = [
            'country_id' => $country->encoded_id,
            'salutation' => 'Mr.',
            'firstname' => 'Charles',
            'lastname' => 'Davenport',
            'email' => 'email@example.org',
            'phone' => 1234567890,
            'company_name' => 'Davenport Enterprise',
            'address' => '1 Candy Way',
            'address_two' => 'Candy Street',
            'address_three' => 'Candy Address Three',
            'city' => 'Candy City',
            'county' => 'Candy County',
            'postal_code' => 'Candy Postcode',
            'billing' => true,
            'shipping' => false,
            'default' => true,
            'delivery_instructions' => 'Test Instructions',
            'last_used_at' => now(),
            'meta' => ['some' => 'value'],
        ];

        $address = $this->service->create($user, $data);

        foreach ($data as $key => $value) {
            switch($key) {
                case 'country_id':
                    $this->assertEquals($country->id, $address->country->id);
                break;
                case 'last_used_at':
                    $this->assertEquals(now()->format('y/m/d'), $address->last_used_at->format('y/m/d'));
                break;
                default:
                    $this->assertEquals($value, $address->getAttribute($key));
                break;
            }
        }
    }

    private function assertDefaultAddress(Address $address)
    {
        $this->assertTrue((bool) $address->default);
    }

    private function assertNotDefaultAddress(Address $address)
    {
        $this->assertFalse((bool) $address->default);
    }
}
