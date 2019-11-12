<?php

namespace Seeds;

use GetCandy\Api\Core\Channels\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Channel::create([
            'name' => 'Webstore',
            'handle' => 'webstore',
            'default' => true,
        ]);
        if (getenv('APP_ENV') != 'testing') {
            Channel::create([
                'name' => 'Mobile',
                'handle' => 'mobile',
                'default' => false,
            ]);
            Channel::create([
                'name' => 'Print',
                'handle' => 'print',
                'default' => false,
            ]);
        }
    }
}
