<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerGroupProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_group_product', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_group_id')->unsigned();
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

//          Extra Attributes
            $table->boolean('visible')->default(true);
            $table->boolean('purchasable')->default(true);
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
        Schema::table('customer_group_product', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['customer_group_id']);
        });
        Schema::dropIfExists('customer_group_product');
    }
}
