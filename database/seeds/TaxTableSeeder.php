<?php

namespace Seeds;

use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Seeder;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tax::create([
            'percentage' => 20,
            'name' => 'VAT',
            'default' => true,
        ]);
    }
}
