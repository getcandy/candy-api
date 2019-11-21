<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class TaxRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        if (DB::table('taxes')->count()) {
            return;
        }

        DB::table('taxes')->insert([
            [
                'percentage' => 20,
                'name' => 'VAT',
                'default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'percentage' => 0,
                'name' => 'Tax Excempt',
                'default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
