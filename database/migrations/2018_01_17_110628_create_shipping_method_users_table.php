<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingMethodUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_method_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned();
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_method_user', function (Blueprint $table) {
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('shipping_method_user');
    }
}
