<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrateUsersToCustomers extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $customerMapping = DB::table('user_details')->select('user_id', 'id')->get();

        Schema::table('user_details', function (Blueprint $table) {
            $table->dropForeign('user_details_user_id_foreign');
            $table->dropColumn('user_id');
        });

        Schema::rename('user_details', 'customers');

        Schema::table('users', function (Blueprint $table) {
            $table->integer('customer_id')->after('id')->nullable()->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        foreach ($customerMapping as $map) {
            DB::table('users')->whereId($map->user_id)->update([
                'customer_id' => $map->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}
