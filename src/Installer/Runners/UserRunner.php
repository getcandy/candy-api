<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Illuminate\Console\Command;

class UserRunner extends AbstractRunner implements InstallRunnerContract
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        if (!DB::table('roles')->count()) {
            $this->installRoles();
        }

        $adminRole = $this->getAdminRole();


        if (DB::table('users')->count()) {
            return;
        }

        $this->command->info('Setting up user account');

        $name = $this->command->ask('What\'s your name?');

        $firstAndLast = explode(' ', $name);

        $email = $this->command->ask("What's your email?");

        $tries = 0;

        while (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($tries < 3) {
                $message = 'Oops! That email looks invalid, can we try again?';
            } elseif ($tries >= 3 && $tries <= 6) {
                $message = 'No really, give me a proper email...';
            } elseif ($tries >= 6 && $tries <= 9) {
                $message = 'Seriously now, lets not do this all day... what is it?';
            } elseif ($tries >= 10) {
                $this->command->error('I give up');
                exit();
            }

            $email = $this->ask($message);
            $tries++;
        }

        $password = $this->command->secret('Choose a password (hidden)');

        $passwordConfirm = $this->command->secret('Confirm it (hidden)');

        while ($password != $passwordConfirm) {
            $password = $this->command->secret('Oop! Passwords didn\'t match try again');
            $passwordConfirm = $this->command->secret('Aaaand confirm it');
        }

        $this->command->info('Just creating your account now');

        // Get our auth user model..

        $model = config('auth.providers.users.model');

        $user = new $model;

        $user->fill([
            'password' => bcrypt($password),
            'name' => $name,
            'email' => $email,
        ]);

        $user->save();

        $user->assignRole('admin');
    }

    protected function getAdminRole()
    {
        return DB::table('roles')->whereName('admin')->first();
    }

    protected function installRoles()
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'guard_name' => 'admin',
            ],
            [
                'name' => 'customer',
                'guard_name' => 'customer',
            ],
        ]);
    }
}
