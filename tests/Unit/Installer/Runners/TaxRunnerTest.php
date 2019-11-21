<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\TaxRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class TaxRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(TaxRunner::class);

        $this->assertEquals(0, DB::table('taxes')->count());

        $runner->run();

        $this->assertDatabaseHas('taxes', [
            'percentage' => 20,
            'name' => 'VAT',
            'default' => true,
        ]);
        $this->assertDatabaseHas('taxes', [
            'percentage' => 0,
            'name' => 'Tax Excempt',
            'default' => false,
        ]);
    }
}
