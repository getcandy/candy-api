<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AddAddressPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Get our admin role.
        $admin = Role::whereName('admin')->first();
        $customer = Role::whereName('customer')->first();

        // Create our missions
        $permissions = [
            'create-address',
            'manage-addresses',
        ];

        if ($admin) {
            foreach ($permissions as $permission) {
                $permission = Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
                $admin->givePermissionTo($permission);
            }
        }
        if ($customer) {
            $customer->givePermissionTo('create-address');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
