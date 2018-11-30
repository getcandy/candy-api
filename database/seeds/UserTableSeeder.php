<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Users\Models\UserDetail;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $language = app('api')->languages()->getDefaultRecord();

        $admin = User::create([
            'id' => 2,
            'name' => 'Alec',
            'email' => 'hello@itsalec.co.uk',
            'password' => \Hash::make('password'),
        ]);

        UserDetail::forceCreate([
            'user_id' => $admin->id,
            'title' => 'Mr',
            'firstname' => 'Alec',
            'lastname' => 'Ritson',
        ]);
        $admin->language()->associate($language);
        $admin->save();

        $customer = User::create([
            'id' => 7,
            'name' => 'Shaun',
            'email' => 'shaun@neondigital.co.uk',
            'password' => \Hash::make('password'),
        ]);
        UserDetail::forceCreate([
            'user_id' => $customer->id,
            'title' => 'Mr',
            'firstname' => 'Shaun',
            'lastname' => 'Rainer',
        ]);

        $group = CustomerGroup::find(2);

        $customer->groups()->attach($group->id);
        $customer->language()->associate($language);

        $customer->save();
    }
}
