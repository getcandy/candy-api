<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Channels\Models\Channel;

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
