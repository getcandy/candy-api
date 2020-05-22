<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class CurrencyRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        if (DB::table('currencies')->count()) {
            return;
        }

        DB::table('currencies')->insert([
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'enabled' => true,
                'exchange_rate' => 1,
                'format' => '£{price}',
                'decimal_point' => '.',
                'thousand_point' => ',',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'enabled' => true,
                'exchange_rate' => 0.87260,
                'format' => '€{price}',
                'decimal_point' => '.',
                'thousand_point' => ',',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
