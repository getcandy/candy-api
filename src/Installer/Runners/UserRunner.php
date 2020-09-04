<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;
use Spatie\Permission\Models\Role;

class UserRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        if (! DB::table('roles')->count()) {
            $this->installRoles();
        }

        $model = config('auth.providers.users.model');

        if (! DB::table('users')->count()) {
            $user = $this->setUpUser($model);
        } else {
            $user = (new $model)->first();
        }

        $user->assignRole('admin');
    }

    /**
     * Set up the user based on a model reference.
     *
     * @param  string  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setUpUser($model)
    {
        $this->command->info('Setting up user account');

        $name = $this->command->ask('What\'s your name?');

        $nameParts = explode(' ', $name, 2);

        $email = $this->command->ask("What's your email?");

        $tries = 0;

        $password = $this->command->secret('Choose a password (hidden)');

        $passwordConfirm = $this->command->secret('Confirm it (hidden)');

        while ($password != $passwordConfirm) {
            $password = $this->command->secret('Oop! Passwords didn\'t match try again');
            $passwordConfirm = $this->command->secret('Aaaand confirm it');
        }

        $this->command->info('Just creating your account now');

        // Get our auth user model..
        $user = new $model;

        $user->fill([
            'password' => bcrypt($password),
            'name' => $name,
            'email' => $email,
        ]);

        $user->save();
        $user->customer()->updateOrCreate([
            'firstname' => $nameParts[0],
            'lastname' => $nameParts[1] ?? null,
        ]);

        return $user;
    }

    /**
     * Install the roles.
     *
     * @return void
     */
    protected function installRoles()
    {
        // We have to install roles via the modals
        // otherwise the package won't recognise them
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }
}
