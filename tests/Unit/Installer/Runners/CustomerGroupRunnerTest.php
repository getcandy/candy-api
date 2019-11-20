<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Core\GetCandy;
use Tests\TestCase;
use GetCandy\Api\Installer\Runners\CustomerGroupRunner;

/**
 * @group installer
 */
class CustomerGroupRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(CustomerGroupRunner::class);

        $this->assertEquals(0, DB::table('customer_groups')->count());

        $runner->run();

        $this->assertDatabaseHas('customer_groups', [
            'name' => 'Retail',
            'handle' => 'retail',
            'default' => true,
            'system' => true,
        ]);
        $this->assertDatabaseHas('customer_groups', [
            'name' => 'Guest',
            'handle' => 'guest',
            'default' => false,
            'system' => true,
        ]);
    }
}
