<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\AssociationGroupRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class AssociationGroupRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(AssociationGroupRunner::class);

        $this->assertEquals(0, DB::table('association_groups')->count());

        $runner->run();

        $this->assertEquals(3, DB::table('association_groups')->count());
    }
}
