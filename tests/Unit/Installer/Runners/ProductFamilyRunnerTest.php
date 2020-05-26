<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Installer\Runners\ProductFamilyRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class ProductFamilyRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $this->mock(ProductFamilyRunner::class, function ($mock) {
            $mock->makePartial();
            $mock->shouldReceive('getChannelHandle')->andReturn('webstore');

            $attribute = new \stdClass;
            $attribute->id = 1;
            $mock->shouldReceive('getAttributes')->andReturn(collect([$attribute]));
        });

        $runner = app()->make(ProductFamilyRunner::class);

        $this->assertEquals(0, DB::table('product_families')->count());

        $runner->run();

        $this->assertDatabaseHas('product_families', [
            'name' => 'Default',
        ]);

        $this->assertDatabaseHas('attributables', [
            'attribute_id' => 1,
            'attributable_type' => ProductFamily::class,
            'attributable_id' => 1,
        ]);
    }
}
