<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\AttributeRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class AttributeRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(AttributeRunner::class);

        $this->assertEquals(0, DB::table('attributes')->count());
        $this->assertEquals(0, DB::table('attribute_groups')->count());

        $runner->run();

        $groups = DB::table('attribute_groups')->select([
            'id',
            'handle',
        ])->get();

        $marketingGroup = $groups->first(function ($group) {
            return $group->handle == 'marketing';
        });

        $seoGroup = $groups->first(function ($group) {
            return $group->handle == 'seo';
        });

        $attributesToCheck = array_merge(
            $runner->getMarketingAttributes($marketingGroup->id),
            $runner->getSeoAttributes($marketingGroup->id)
        );

        $this->assertSame(
            count($attributesToCheck),
            DB::table('attributes')->count()
        );
    }
}
