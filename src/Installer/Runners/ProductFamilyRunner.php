<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class ProductFamilyRunner extends AbstractRunner implements InstallRunnerContract
{
    public function getChannelHandle()
    {
        $channel = DB::table('channels')->select('handle')->whereDefault(true)->first();

        return $channel->handle;
    }

    public function getAttributes()
    {
        return DB::table('attributes')->get();
    }

    public function run()
    {
        if (DB::table('product_families')->count()) {
            return;
        }

        $channelHandle = $this->getChannelHandle();

        $familyId = DB::table('product_families')->insertGetId([
            'name' => 'Default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get our attributes and attach them to the product family
        $attributes = $this->getAttributes();

        $attributeInsert = [];

        foreach ($attributes as $attribute) {
            $attributeInsert[] = [
                'attribute_id' => $attribute->id,
                'attributable_type' => ProductFamily::class,
                'attributable_id' => $familyId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('attributables')->insert($attributeInsert);
    }
}
