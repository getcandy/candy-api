<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products');
            $table->integer('related_product_id')->unsigned();
            $table->foreign('related_product_id')->references('id')->on('products');
            $table->integer('count')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_recommendations', function (Blueprint $table) {
            $table->dropForeign(['related_product_id']);
            $table->dropForeign(['product_id']);
        });
        Schema::dropIfExists('product_recommendations');
    }
}
