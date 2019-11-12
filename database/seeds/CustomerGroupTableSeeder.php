<?php

namespace Seeds;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Illuminate\Database\Seeder;

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
