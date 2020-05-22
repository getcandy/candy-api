<?php

namespace Seeds;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use Illuminate\Database\Seeder;

class ProductFamilyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $atts = Attribute::where('group_id', '=', 1)->get();

        $fam = ProductFamily::create([
            'name' => 'Shoes',
        ]);

        foreach ($atts as $att) {
            $fam->attributes()->attach($att);
        }

        $fam = ProductFamily::create([
            'name' => 'Bags',
        ]);

        foreach ($atts as $att) {
            $fam->attributes()->attach($att);
        }

        $fam = ProductFamily::create([
            'name' => 'Jewellery',
        ]);

        foreach ($atts as $att) {
            $fam->attributes()->attach($att);
        }

        $fam = ProductFamily::create([
            'name' => 'House items',
        ]);

        foreach ($atts as $att) {
            $fam->attributes()->attach($att);
        }
    }
}
