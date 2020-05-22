<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\LanguageRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class LanguageRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(LanguageRunner::class);

        $this->assertEquals(0, DB::table('languages')->count());

        $runner->run();

        $this->assertDatabaseHas('languages', [
            'lang' => 'en',
            'iso' => 'gb',
            'name' => 'English',
            'default' => 1,
            'enabled' => 1,
        ]);
    }
}
