<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Migrations\Migration;

class AddFetchUserPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Get our admin role.
        $admin = Role::whereName('admin')->first();

        // Create our missions
        $permissions = [
            'view-users',
            'create-users',
            'manage-users'
        ];

        foreach ($permissions as $permission) {
            $permission = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            $admin->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
