<?php

namespace Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;

class ShippingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create method
        $method = ShippingMethod::forceCreate([
            'attribute_data' => [
                'name' => [
                    'en' => 'Test Delivery',
                ],
            ],
            'type' => 'standard',
        ]);

        $channel = Channel::first();

        // Attach method to some channels
        $method->channels()->sync([
            $channel->id => ['published_at' => Carbon::now()],
        ]);

        // Create Price
        $price = ShippingPrice::forceCreate([
            'shipping_method_id' => $method->id,
            'rate' => 500,
            'currency_id' => Currency::first()->id,
            'min_weight' => 0,
            'min_basket' => 0,
        ]);

        $price->customerGroups()->sync([
            CustomerGroup::first()->id => ['visible' => true],
        ]);
    }
}
