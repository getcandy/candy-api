<?php

namespace Tests\Unit\Addresses\Services;

use Tests\TestCase;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Services\AddressService;

/**
 * @group addresses
 */
class AddressServiceTest extends TestCase
{
    /** @var AddressService */
    protected $service;

    public function setUp()
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

        $originalAddress->is_default = true;
        $originalAddress->save();

        $this->assertDefaultAddress($originalAddress);
        $this->assertNotDefaultAddress($newAddress);

        $this->service->makeDefault($newAddress->encode($newAddress->id));

        $originalAddress = Address::findOrFail($originalAddress->id);
        $newAddress = Address::findOrFail($newAddress->id);
        $this->assertNotDefaultAddress($originalAddress);
        $this->assertDefaultAddress($newAddress);
    }

    public function test_can_remove_default_from_address()
    {
        $address = Address::first();
        $address->is_default = true;
        $address->save();

        $this->assertDefaultAddress($address);

        $this->service->removeDefault($address->encode($address->id));

        $address = Address::findOrFail($address->id);
        $this->assertNotDefaultAddress($address);
    }

    private function assertDefaultAddress(Address $address)
    {
        $this->assertTrue((bool) $address->is_default);
    }

    private function assertNotDefaultAddress(Address $address)
    {
        $this->assertFalse((bool) $address->is_default);
    }
}
