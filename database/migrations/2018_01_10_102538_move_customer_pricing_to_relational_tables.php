<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveCustomerPricingToRelationalTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('pricing');
            $table->boolean('group_pricing')->default(0);
        });

        Schema::create('product_customer_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_variant_id')->unsigned();
            $table->foreign('product_variant_id')->references('id')->on('product_variants');
            $table->integer('customer_group_id')->unsigned();
            $table->foreign('customer_group_id')->references('id')->on('customer_groups');
            $table->integer('tax_id')->unsigned()->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes');
            $table->decimal('price', 10, 2)->unsigned();
            $table->decimal('compare_at_price', 10, 2)->unsigned()->nullable();
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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('pricing');
            $table->dropColumn('group_pricing');
        });
        Schema::drop('product_customer_prices');
    }
}
