<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\ChannelRunner;
use Illuminate\Console\Command;
use Tests\TestCase;

/**
 * @group installer
 */
class ChannelRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $this->mock(Command::class, function ($mock) {
            $mock->shouldReceive('anticipate')->andReturn('webstore');
            $mock->shouldReceive('ask')->andReturn('localhost');
        });

        $runner = app()->make(ChannelRunner::class);

        $this->assertEquals(0, DB::table('channels')->count());
        $runner->onCommand(app()->make(Command::class));
        $runner->run();

        $this->assertDatabaseHas('channels', [
            'name' => 'webstore',
            'handle' => 'webstore',
            'default' => 1,
            'url' => 'localhost',
        ]);
    }
}
