<?php

namespace Seeds;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'customer']);
    }
}
