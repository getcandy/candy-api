<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSavedCardsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reusable_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('type')->nullable();
            $table->string('provider')->index();
            $table->string('last_four')->nullable();
            $table->string('token')->index();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('reusable_payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('reusable_payments');
    }
}
