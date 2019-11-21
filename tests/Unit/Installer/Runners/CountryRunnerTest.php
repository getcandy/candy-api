<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Runners\CountryRunner;
use Illuminate\Console\Command;
use Mockery;
use Tests\TestCase;

/**
 * @group installer
 */
class CountryRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        $this->mock(CountryRunner::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->makePartial();
            $countries = collect([
                'name' => [
                    'common' => 'United Kingdom',
                ],
                'cca2' => 'GB',
                'cca3' => 'GBR',
                'ccn3' => '826',
                'region' => 'Europe',
                'subregion' => 'Northern Europe',
            ]);
            $mock->shouldReceive('getCountries')
                ->andReturn($countries);
        });

        $commandMock = Mockery::mock(Command::class);
        $commandMock->shouldReceive('info')->andReturn('Test');

        $runner = new CountryRunner($commandMock);

        $this->assertEquals(0, DB::table('countries')->count());

        $runner->onCommand(app()->make(Command::class));
        $runner->run();

        $this->assertDatabaseHas('countries', [
            'name' => 'United Kingdom',
            'iso_a_2' => 'GB',
            'iso_a_3' => 'GBR',
            'iso_numeric' => '826',
            'region' => 'Europe',
            'sub_region' => 'Northern Europe',
        ]);
    }
}
