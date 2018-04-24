<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Currencies\Models\Currency;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::ForceCreate([
            'code' => 'GBP',
            'name' => 'British Pound',
            'enabled' => true,
            'exchange_rate' => 1,
            'format' => '&#xa3;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
            'default' => true,
        ]);

        Currency::ForceCreate([
            'code' => 'EUR',
            'name' => 'Euro',
            'enabled' => true,
            'exchange_rate' => 0.87260,
            'format' => '&euro;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
        ]);

        Currency::ForceCreate([
            'code' => 'USD',
            'name' => 'US Dollars',
            'enabled' => true,
            'exchange_rate' => 0.71,
            'format' => '&euro;{price}',
            'decimal_point' => '.',
            'thousand_point' => ',',
        ]);
    }
}
