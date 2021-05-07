<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class SettingsRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        $settings = [
            [
                'name' => 'Products',
                'handle' => 'products',
                'content' => json_encode([
                    'asset_source' => 'products',
                    'transforms' => ['large_thumbnail'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Categories',
                'handle' => 'categories',
                'content' => json_encode([
                    'asset_source' => 'categories',
                    'transforms' => ['large_thumbnail'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Orders',
                'handle' => 'orders',
                'content' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Users',
                'handle' => 'users',
                'content' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($settings as $setting) {
            if (DB::table('settings')->whereHandle($setting['handle'])->exists()) {
                continue;
            }
            DB::table('settings')->insert($setting);
        }
    }
}
