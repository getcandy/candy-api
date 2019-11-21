<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\AssetRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class AssetRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(AssetRunner::class);

        $this->assertEquals(0, DB::table('asset_sources')->count());
        $this->assertEquals(0, DB::table('transforms')->count());

        $runner->run();

        $this->assertEquals(2, DB::table('transforms')->count());
        $this->assertEquals(3, DB::table('asset_sources')->count());
    }
}
