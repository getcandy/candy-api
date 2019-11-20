<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class ProductFamilyRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

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
            'attribute_data' => json_encode([
                'name' => [
                    $channelHandle => [
                        'en' => 'Default',
                    ]
                ]
            ]),
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
                'attributable_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('attributables')->insert($attributeInsert);
    }
}
