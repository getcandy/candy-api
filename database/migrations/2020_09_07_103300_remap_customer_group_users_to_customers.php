<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemapCustomerGroupUsersToCustomers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Get our users
        $userModel = GetCandy::getUserModel();
        $users = (new $userModel)->withoutGlobalScopes()->whereHas('customer')->get();

        $customerMapping = $users->map(function ($user) {
            return [
                'customer_id' => $user->customer->id,
                'customer_groups' => $user->groups->pluck('id'),
            ];
        });

        Schema::dropIfExists('customer_group_user');

        if (! Schema::hasTable('customer_customer_group')) {
            Schema::create('customer_customer_group', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('customer_group_id')->unsigned();
                $table->foreign('customer_group_id')->references('id')->onDelete('cascade')->on('customer_groups');
                $table->integer('customer_id')->unsigned();
                $table->foreign('customer_id')->references('id')->onDelete('cascade')->on('customers');
                $table->timestamps();
            });
        }

        foreach ($customerMapping as $data) {
            foreach ($data['customer_groups'] as $groupId) {
                DB::table('customer_customer_group')->insert([
                    'customer_group_id' => $groupId,
                    'customer_id' => $data['customer_id'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
