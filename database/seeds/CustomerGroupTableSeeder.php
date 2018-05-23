<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class CustomerGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // CustomerGroup::forceCreate([
        //     'name' => 'Retail',
        //     'handle' => 'retail',
        //     'default' => true,
        //     'system' => true
        // ]);

        CustomerGroup::forceCreate([
            'name' => 'Guest',
            'handle' => 'guest',
            'default' => true,
            'system' => true,
        ]);

        CustomerGroup::forceCreate([
            'name' => 'Member',
            'handle' => 'member',
            'default' => false,
            'system' => false,
        ]);
    }
}
