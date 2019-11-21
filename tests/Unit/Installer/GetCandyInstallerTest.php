<?php

namespace Tests\Unit\Installer\Runners;

use GetCandy\Api\Installer\GetCandyInstaller;
use Illuminate\Console\Command;
use Tests\TestCase;

/**
 * @group installerbase
 */
class GetCandyInstallerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_invoke_the_runners()
    {
        // Mock all our installers.
        $commandMock = $this->spy(Command::class);

        $installer = app()->make(GetCandyInstaller::class);
        $installer->onCommand($commandMock);

        foreach ($installer->getRunners() as $runner) {
            $this->mock($runner, function ($mock) {
                $mock->shouldReceive('onCommand')->once();
                $mock->shouldReceive('run')->once();
                $mock->shouldReceive('after')->once();
            });
        }

        $installer->run();
    }

    public function test_can_add_and_replace_install_runners()
    {
        config()->set('getcandy.installer.runners', [
            'mocked_installer' => 'My\Test\Runner',
            'product_families' => 'My\Test\Runner\Override',
        ]);

        $commandMock = $this->spy(Command::class);

        $installer = app()->make(GetCandyInstaller::class);
        $installer->onCommand($commandMock);

        $runners = $installer->getRunners();

        $this->assertNotEmpty($runners['mocked_installer']);

        foreach ($runners as $runner) {
            $this->mock($runner, function ($mock) {
                $mock->shouldReceive('onCommand')->once();
                $mock->shouldReceive('run')->once();
                $mock->shouldReceive('after')->once();
            });
        }

        $installer->run();
    }
}
