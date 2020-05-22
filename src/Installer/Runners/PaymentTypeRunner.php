<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class PaymentTypeRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        // Are languages already installed?
        if (DB::table('payment_types')->count()) {
            return;
        }

        DB::table('payment_types')->insert([
            'name' => 'Example',
            'handle' => 'example',
            'success_status' => 'offline-payment',
            'driver' => 'offline',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
