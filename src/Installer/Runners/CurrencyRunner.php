<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class CurrencyRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

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
                'format' => '&#xa3;{price}',
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
                'format' => '&euro;{price}',
                'decimal_point' => '.',
                'thousand_point' => ',',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
