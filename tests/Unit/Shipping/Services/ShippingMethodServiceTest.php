<?php

namespace Tests\Unit\Shipping\Factories;

use Tests\TestCase;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Shipping\Models\ShippingZone;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;
use GetCandy\Api\Core\Shipping\Models\ShippingRegion;
use GetCandy\Api\Core\Shipping\Services\ShippingMethodService;

/**
 * @group shipping
 */
class ShippingMethodServiceTest extends TestCase
{
    public function test_can_get_correct_shipping_methods()
    {
        $country = Country::forceCreate([
            'name' => 'United Kingdom',
            'region' => 'Europe',
            'iso_a_2' => 'GB',
            'iso_a_3' => 'GBR',
            'iso_numeric' => 826,
        ]);

        $methods = [
            $methodA = $this->createMethod('A'),
            $methodB = $this->createMethod('B'),
            $methodC = $this->createMethod('C'),
        ];

        $zones = [
            $zoneA = $this->createZone('A'),
            $zoneB = $this->createZone('B'),
            $zoneC = $this->createZone('C'),
            $zoneD = $this->createZone('D'),
        ];

        $zoneA->countries()->attach($country);
        $zoneA->methods()->attach($methodA);
        $zoneB->methods()->attach($methodA);
        $zoneB->countries()->attach($country);
        $zoneC->methods()->attach($methodB);
        $zoneC->countries()->attach($country);
        $zoneD->methods()->attach($methodC);
        $zoneD->countries()->attach($country);

        $this->createRegion('AL', $zoneA);
        $this->createRegion('E', $zoneA);
        $this->createRegion('EC', $zoneB);
        $this->createRegion('CM', $zoneB);
        $this->createRegion('HP', $zoneC);
        $this->createRegion('KT', $zoneC);

        $this->createPricing($zones);

        $basket = Basket::forceCreate([
            'currency' => 'GBP',
        ]);

        // So create an order
        $order = Order::forceCreate([
            'currency' => 'GBP',
            'shipping_country' => 'United Kingdom',
            'shipping_zip' => 'CM6 6TH',
            'basket_id' => $basket->id,
        ]);

        $service = app()->getInstance()->make(ShippingMethodService::class);

        $options = $service->getForOrder($order->encoded_id);

        // We should have at least one method here...
        $this->assertCount(1, $options);
        $this->assertEquals('Shipping Method A', $options->first()->method->attribute('name'));

        $order->shipping_zip = 'KT8 5TH';
        $order->save();

        $options = $service->getForOrder($order->encoded_id);
        $this->assertCount(1, $options);
        $this->assertEquals('Shipping Method B', $options->first()->method->attribute('name'));

        $order->shipping_zip = 'EC6 6th';
        $order->save();

        $options = $service->getForOrder($order->encoded_id);
        $this->assertCount(1, $options);
        $this->assertEquals('Shipping Method A', $options->first()->method->attribute('name'));

        $order->shipping_zip = 'EC6 6th';
        $order->save();

        $options = $service->getForOrder($order->encoded_id);
        $this->assertCount(1, $options);
        $this->assertEquals('Shipping Method A', $options->first()->method->attribute('name'));
    }

    private function createPricing($zones)
    {
        $groups = CustomerGroup::all();
        foreach ($zones as $zone) {
            $price = new ShippingPrice;
            $price->rate = 0;
            $price->min_basket = 0;
            $price->currency_id = 1;
            $price->shipping_zone_id = $zone->id;
            $price->shipping_method_id = $zone->methods->first()->id;
            $price->save();

            foreach ($groups as $group) {
                \DB::table('shipping_customer_group_price')->insert([
                    'shipping_price_id' => $price->id,
                    'customer_group_id' => $group->id,
                    'visible' => 1,
                ]);
            }

            $price = new ShippingPrice;
            $price->rate = 795;
            $price->min_basket = 2500;
            $price->currency_id = 1;
            $price->shipping_zone_id = $zone->id;
            $price->shipping_method_id = $zone->methods->first()->id;
            $price->save();

            foreach ($groups as $group) {
                \DB::table('shipping_customer_group_price')->insert([
                    'shipping_price_id' => $price->id,
                    'customer_group_id' => $group->id,
                    'visible' => 1,
                ]);
            }
        }
    }

    private function createRegion($region, $zone)
    {
        return ShippingRegion::forceCreate([
            'country_id' => 1,
            'shipping_zone_id' => $zone->id,
            'region' => $region,
            'address_field' => 'postcode',
        ]);
    }

    private function createZone($suffix)
    {
        return ShippingZone::forceCreate([
            'name' => "Shipping Zone {$suffix}",
            'regional' => true,
        ]);
    }

    /**
     * Creates a shipping method with a suffix in the name.
     *
     * @param string $suffix
     * @return array
     */
    private function createMethod($suffix)
    {
        $method = ShippingMethod::forceCreate([
            'attribute_data' => [
                'name' => [
                    'en' => "Shipping Method {$suffix}",
                ],
            ],
            'type' => 'regional',
        ]);

        // Make sure we have channels set up.
        foreach (Channel::all() as $channel) {
            $method->channels()->save($channel, ['published_at' => now()]);
        }

        return $method;
    }
}
