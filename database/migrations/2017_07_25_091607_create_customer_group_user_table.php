<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_group_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_group_id')->unsigned();
            $table->foreign('customer_group_id')->references('id')->onDelete('cascade')->on('customer_groups');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->onDelete('cascade')->on('users');
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
        Schema::dropIfExists('customer_group_user');
    }
}
