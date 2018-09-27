<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRewardProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discount_reward_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products');
            $table->integer('discount_reward_id')->unsigned();
            $table->foreign('discount_reward_id')->references('id')->on('discount_rewards');
            $table->integer('quantity')->default(1);
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
        Schema::table('discount_reward_products', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['discount_reward_id']);
        });
        Schema::dropIfExists('discount_reward_products');
    }
}
