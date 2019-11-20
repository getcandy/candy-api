<?php

namespace Tests\Unit\Installer\Runners;

use DB;
use GetCandy\Api\Core\GetCandy;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Installer\Runners\UserRunner;
use Illuminate\Console\Command;
use Tests\TestCase;

/**
 * @group installer
 */
class UserRunnerTest extends TestCase
{
    protected $withSeedData = false;

    public function test_install_can_run()
    {
        if (!config('auth.providers.users.model')) {
            config()->set('auth.providers.users.model', 'Tests\Stubs\User');
        }

        $this->mock(Command::class, function ($mock) {
            $mock->shouldReceive('info');
            $mock->shouldReceive('ask')->withArgs(function ($arg) {
                return $arg == 'What\'s your name?';
            })->andReturn('Alec');

            $mock->shouldReceive('ask')->withArgs(function ($arg) {
                return $arg == "What's your email?";
            })->andReturn('alec@neondigital.co.uk');

            $mock->shouldReceive('secret')->withArgs(function ($arg) {
                return $arg == 'Choose a password (hidden)';
            })->andReturn('password');

            $mock->shouldReceive('secret')->withArgs(function ($arg) {
                return $arg == 'Confirm it (hidden)';
            })->andReturn('password');
        });

        $runner = app()->make(UserRunner::class);

        $this->assertEquals(0, DB::table('users')->count());

        $runner->run();

        $this->assertDatabaseHas('users', [
            'email' => 'alec@neondigital.co.uk',
            'name' => 'Alec',
        ]);

        // Make sure password doesn't exist in plain text.
        $this->assertDatabaseMissing('users', [
            'password' => 'password',
        ]);
    }
}
