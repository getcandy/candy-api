<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\SettingsRunner;
use Tests\TestCase;

/**
 * @group installer
 */
class SettingsRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $runner = app()->make(SettingsRunner::class);

        $this->assertEquals(0, DB::table('settings')->count());

        $runner->run();

        $settings = ['products', 'categories', 'orders', 'users'];

        foreach ($settings as $setting) {
            $this->assertDatabaseHas('settings', [
                'handle' => $setting,
            ]);
        }
    }
}
