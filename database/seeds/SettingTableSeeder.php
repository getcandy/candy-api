<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Settings\Models\Setting;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'name' => 'Products',
            'handle' => 'products',
            'content' => [
                'asset_source' => 'products',
                'transforms' => ['large_thumbnail'],
            ],
        ]);

        Setting::create([
            'name' => 'Invoices',
            'handle' => 'invoices',
            'content' => [
                'next' => 1,
            ],
        ]);

        Setting::create([
            'name' => 'Orders',
            'handle' => 'orders',
            'content' => [
                'statuses' => [
                    'awaiting-payment' => 'Awaiting Payment',
                    'void' => 'Void',
                    'payment-received' => 'Payment Received',
                    'payment-processing' => 'Payment Processing',
                ],
                'default_status' => 'awaiting-payment',
            ],
        ]);
    }
}
